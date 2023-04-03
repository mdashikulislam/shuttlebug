<?php

namespace App\Http\Controllers;

use App\Http\Processors\BookingDuplicate;
use App\Http\Processors\BookingInput;
use App\Http\Processors\BookingVenuesPrep;
use App\Http\Requests\BookingRequest;
use App\Mail\ConfirmBookings;
use App\Models\Booking;
use App\Models\Children;
use App\Models\DebtorsJournal;
use App\Models\DebtorsStatement;
use App\Models\PlanningReport;
use App\Models\TripSettings;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class BookingController extends Controller
{
    /**
     * @var Booking
     */
    protected $booking;
    protected $settings;


    /**
     * BookingController constructor.
     *
     * @param Booking $booking
     * @param TripSettings $settings
     */
    public function __construct(Booking $booking, TripSettings $settings)
    {
        $this->booking = $booking;
        $this->settings = $settings->first();
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
            'passengers'        => [],
        ];

        return view('office.bookings.manage', $data);
    }

    /**
     * Display daily bookings summary
     *
     * @param $date
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function dailyIndex($date)
    {
        $data = [
            'bookings'  => $this->booking->dailyBookings($date),
            'date'      => $date
        ];

        return view('office.bookings.daily-index', $data);
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
        $data = [
            'bookings'      => $this->booking->customerBookings($id, $q),
            'user'          => User::find($id),
            'q'             => $q
        ];

        if ( $id == Auth::user()->id ) {
            return view('myaccount.bookings', $data);
        }

        return view('office.bookings.customer-index', $data);
    }

    /**
     * Display form for creating and editing bookings
     *
     * @param int $id   passenger_id
     * @param string $date
     * @param BookingVenuesPrep $process
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit(BookingVenuesPrep $process, $id, $date = null)
    {
        $date = is_null($date) ? session('bookingdate') : $date;
        session(['bookingdate' => $date]);

        $passenger = Children::with('school')->find($id);
        $request = (object) ['customer' => $passenger->user_id, 'passenger' => $id, 'date' => $date];

        $bookings = $this->booking->passengerBookings($id, $date);
        $venues = $process->handle($request, $bookings);

        $data = [
            'bookings'  => $bookings,
            'passenger' => $passenger,
            'date'      => $date,
            'venues'    => $venues,
            'schools'   => $process->otherSchools($venues),
            'invMonth'  => DebtorsStatement::invMonth(),
        ];

        return view('office.bookings.form', $data);
    }

    /**
     * Store or update the bookings
     * handle completed bookings via journal
     * validation is obsolete
     *
     * @param BookingRequest $request
     * @param BookingInput   $process
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(BookingRequest $request, BookingInput $process)
    {
        $input = $process->handle($request);

        // reject invalid bookings
        if ( (isset($input['new']) && key($input['new']) === 'invalid') ||
            (isset($input['edits']) && key($input['edits']) === 'invalid') ) {

//            $type = isset($input['new']) && key($input['new']) === 'invalid' ? 'new' : 'edits';
//            $venue = key(Arr::first($input[$type]));
//            $time = Arr::first($input[$type]['invalid']);

            return back()->withInput()->with('danger', 'Cannot make this booking: there is no price for the trip');
        }

        // edit
        if ( count($input['edits']) > 0 ) {
            foreach ( $input['edits'] as $id => $data ) {
                $this->booking->find($id)->update($data);
            }
        }

        // cancel
        if ( count($input['cancellations']) > 0 ) {
            $this->booking->whereIn('id', $input['cancellations']['booking'])->delete();
            // mark journal booking as 'cancelled'
            foreach ( $input['cancellations']['journal'] as $id => $data ) {
                $this->booking->find($id)->update(['journal' => 'cancelled']);
            }
            // post to journal
            $this->postJournal($input['cancellations']['journal'], $request, 'cancel');
        }

        // create
        foreach ( $input['new'] as $data ) {
            $booking = $this->booking->create($data);
            if ( $booking->journal == 'added' ) {
                $this->postJournal($booking, $request, 'add');
            }
        }

        // sync planner
        $this->syncPlanReport($request, $input);

        return redirect('office/bookings/edit/'.$request->passenger_id)->with('confirm', 'Bookings have been saved');
    }

    /**
     * Cancel given bookings
     * bulk submission from customer summary page
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request)
    {
        // sync planner
        $this->syncPlanReport($request, null);

        $this->booking->whereIn('id', $request->cancel)->delete();

        return back()->with('confirm', 'Cancellations have been removed');
    }

    /**
     * Show form for duplicating bookings
     *
     * @param int $id   passenger_id
     * @param null $date
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showDuplicate($id, $date = null)
    {
        $date = is_null($date) ? session('bookingdate') : $date;
        session(['bookingdate' => $date]);
        $year = Carbon::createFromFormat('Y-m-d', $date)->year;

        $data = [
            'passenger' => Children::find($id),
            'source'    => $this->booking->weekBookings($id, $date),
            'terms'     => DB::table('schoolterms')->whereYear('start', $year)->orderBy('start')->get()
        ];

        return view('office.bookings.form-duplicate', $data);
    }

    /**
     * Duplicate given bookings on dates in given period
     *
     * @param Request          $request
     * @param BookingDuplicate $process
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeDuplicate(Request $request, BookingDuplicate $process)
    {
        if ( !$request->has('source') ) {
            return back()->with('danger', 'Select the bookings to duplicate.');
        } elseif ( !$request->has('option') ) {
            return back()->with('danger', 'Select the duplicating option.');
        }

        $duplications = $process->handle($request);

        return back()->with('confirm', $duplications.' bookings successfully duplicated');
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
            return view('office.bookings.form-email', $data);

        // send email
        } else {
            $user = $data['customer'];
            $cc = User::find(100101);
            $bookings = $data['bookings'];
            Mail::to($user)
                ->bcc($cc)
                ->send(new ConfirmBookings($user, $bookings));
        }

        return redirect('office/bookings')->with('confirm', 'Bookings have been emailed');
    }

    /**
     * Post booking entries to journal
     * entries on the 28th are dated the 29th
     *
     * @param $data
     * @param $request
     * @param $type
     */
    private function postJournal($data, $request, $type)
    {
        if ( $type == 'cancel' ) {
            foreach ( $data as $id => $price ) {
                DebtorsJournal::create([
                    'user_id'   => $request->user_id,
                    'date'      => now()->day == 28 ? now()->addDay()->toDateString() : now()->toDateString(),
                    'entry'     => 'Cancelled booking '.$id,
                    'amount'    => $price * -1
                ]);
            }
        }

        if ( $type == 'add' ) {
            DebtorsJournal::create([
                'user_id'   => $request->user_id,
                'date'      => now()->day == 28 ? now()->addDay()->toDateString() : now()->toDateString(),
                'entry'     => 'Shuttle w/o booking '.$data->id,
                'amount'    => $data->price
            ]);
        }

        return;
    }

    /**
     * Clear plan report effected by booking edits
     * called by store and destroy
     * syncing also done in BookingDuplicate where the entire day is removed from PlanningReport
     *
     * @param Request $request
     * @param array|null $input
     */
    private function syncPlanReport($request, $input)
    {
        $am = $day = false;

        // bulk cancellations
        if ( is_null($input) ) {
            $cancellations = $this->booking->whereIn('id', $request->cancel)->get();
            foreach ( $cancellations as $booking ) {
                $col = $booking->putime == '00:00:00' ? 'am' : 'day';
                $veh_col = $booking->putime == '00:00:00' ? 'am_vehicles' : 'day_vehicles';
                $report = PlanningReport::where('date', $booking->date)->first();
                if ( !is_null($report) ) {
                    $report->update([$col => null, $veh_col => null]);
                }
            }

        // booking edits
        } else {
            // new bookings
            if ( isset($input['new']) ) {
                foreach ( $input['new'] as $data ) {
                    $am = empty($data['putime']) ? true : $am;
                    $day = !empty($data['putime']) ? true : $day;
                }
            }

            // cancelled bookings
            if ( isset($input['cancellations']['booking']) ) {
                foreach ( $input['cancellations']['booking'] as $id ) {
                    $am = empty($request->edit[$id]['putime']) ? true : $am;
                    $day = !empty($request->edit[$id]['putime']) ? true : $day;
                }
            }

            // edited bookings
            if ( isset($input['edits']) ) {
                foreach ( $input['edits'] as $data ) {
                    $am = empty($data['putime']) ? true : $am;
                    $day = !empty($data['putime']) ? true : $day;
                }
            }

            if ( $am ) {
                $report = PlanningReport::where('date', $request->date)->first();
                if ( !is_null($report) ) {
                    $report->update(['am' => null, 'am_vehicles' => null]);
                }
            }
            if ( $day ) {
                $report = PlanningReport::where('date', $request->date)->first();
                if ( !is_null($report) ) {
                    $report->update(['day' => null, 'day_vehicles' => null]);
                }
            }
        }

        return;
    }
}
