<?php

namespace App\Http\Controllers;

use App\Http\Processors\EventInput;
use App\Http\Requests\EventBookingRequest;
use App\Mail\ConfirmEventBookings;
use App\Models\EventBooking;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class EventBookingController extends Controller
{
    /**
     * @var EventBooking
     */
    protected $booking;

    /**
     * EventBookingController constructor.
     *
     * @param EventBooking $booking
     */
    public function __construct(EventBooking $booking)
    {
        $this->booking = $booking;
    }

    /**
     * Display manage page
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show()
    {
        $data = [
            'list_customers'    => $this->booking->customersWithCurrentBookings(),
            'customers'         => User::active()->get()->pluck('alphaname','id')->all(),
            'email_customers'   => $this->booking->customersWithEmailableBookings(),
            'passengers'        => []
        ];

        return view('office.events.manage', $data);
    }

    /**
     * Display customer bookings summary
     *
     * @param int $id
     * @param string|null $q
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function customerIndex($id, $q = null)
    {
        $bookings = $this->booking->customerBookings($id, $q);
        $ids = $this->booking->extractSchools($bookings);

        $data = [
            'bookings'      => $bookings,
            'user'          => User::find($id),
            'schools'       => School::whereIn('id', $ids)->get()->pluck('name','id')->all(),
            'q'             => $q
        ];

        return view('office.events.customer-index', $data);
    }

    /**
     * Display form for creating and editing event bookings
     *
     * @param int $id   user_id
     * @param string $date
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit($id, $date = null)
    {
        $date = is_null($date) ? session('bookingdate') : $date;
        session(['bookingdate' => $date]);

        $bookings = $this->booking->where('user_id', $id)->where('date', $date)->orderBy('putime')->get();
        $user = User::find($id);

        $data = [
            'bookings'  => $bookings,
            'customer'  => $user,
            'date'      => $date,
            'venues'    => $this->booking->eventVenues($user->id),
            'schools'   => School::where('status', 'active')->orderBy('name')->get()->pluck('name','id')->all()
        ];

        return view('office.events.form', $data);
    }

    /**
     * Store or update the event bookings
     *
     * @param EventBookingRequest $request
     * @param EventInput $process
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(EventBookingRequest $request, EventInput $process)
    {
        $input = $process->handle($request);

        // edit
        foreach ( $input['edits'] as $id => $data ) {
            $this->booking->find($id)->update($data);
        }

        // cancel
        $this->booking->whereIn('id', $input['cancellations'])->delete();

        // create
        foreach ( $input['new'] as $data ) {
            $this->booking->create($data);
        }

        return redirect('office/events/edit/'.$request->user_id)->with('confirm', 'Bookings have been saved');
    }

    /**
     * Email bookings to customer
     *
     * @param $id
     * @param $review
     * @return \Illuminate\Http\RedirectResponse
     */
    public function emailBookings($id, $review = null)
    {
        $data = [
            'customer'  => User::find($id),
            'bookings'  => $this->booking->bookingsForEmail($id)
        ];

        // show review
        if ( !is_null($review) ) {
            return view('office.events.form-email', $data);
        } else {
            // send email
            $user = $data['customer'];
            $bookings = $data['bookings'];
            Mail::to($user)->send(new ConfirmEventBookings($user, $bookings));
        }

        return redirect('office/events')->with('confirm', 'Bookings have been emailed');
    }

    /**
     * Cancel given event bookings (bulk submission)
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request)
    {
        if ( $request->has('cancel') ) {
            $this->booking->whereIn('id', $request->cancel)->delete();
        }

        return back()->with('confirm', 'Cancellations have been removed');
    }
}
