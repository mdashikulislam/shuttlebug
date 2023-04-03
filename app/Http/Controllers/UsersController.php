<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomerRequest;
use App\Mail\BulkMail;
use App\Mail\ConfirmRegistration;
use App\Models\Children;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class UsersController extends Controller
{
    /**
     * @var User
     */
    protected $user;

    /**
     * UsersController constructor.
     *
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Display manage page
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show()
    {
        return view('office.users.manage');
    }

    /**
     * Display customers listing
     *
     * @param null $q
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|string
     */
    public function index($q = null)
    {
        $customers = $this->user->customersWithChildren($q);

        $data = [
            'customers'     => $customers->sortBy('alpha_name'),
            'suburbs'       => $customers->sortBy('suburb')->pluck('suburb','suburb')->all(),
            'listing'       => $q
        ];

        return view('office.users.index', $data);
    }

    /**
     * Display form for creating a customer
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {
        $data = [
            'user'  => null
        ];

        return view('office.users.form-profile', $data);
    }

    /**
     * Display form for editing a customer
     *
     * @param int   $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit($id)
    {
        $data = [
            'user'  => $this->user->find($id)
        ];

        if ( $id == Auth::user()->id ) {
            return view('myaccount.profile', $data);
        }

        return view('office.users.form-profile', $data);
    }

    /**
     * Store a new customer
     *
     * @param CustomerRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(CustomerRequest $request)
    {
        $user = $this->user->create($request->all());

        if ( $request->has('send_email') ) {
            $user->rawpw = $request->password;
            $user->exadmin = true;
            Mail::to($request->user())->send(new ConfirmRegistration($user));
        }

        return redirect('office/users/customers/edit/'.$user->id)->with('confirm', 'Profile has been saved');
    }

    /**
     * Update an existing customer
     *
     * @param CustomerRequest $request
     * @param int        $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(CustomerRequest $request, $id)
    {
        $user = $this->user->find($id);
        $old_status = $user->status;

        $user->update($request->all());

        if ( $request->status != $old_status ) {
            foreach( $user->children as $child ) {
                Children::find($child->id)->update(['status' => $request->status]);
            }
        }

        return back()->with('confirm', 'Changes have been saved');
    }

    /**
     * Activate customer as well as children
     *
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function restore($id)
    {
        $user = $this->user->with('children')->find($id);
        $user->update(['status' => 'active']);
        foreach( $user->children as $child ) {
            Children::find($child->id)->update(['status' => 'active']);
        }

        return back()->with('confirm', 'Changes have been saved');
    }

    /**
     * Display form for sending bulk emails
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function bulkMail()
    {
        $data = [
            'customers' => $this->user->active()->get()->pluck('alpha_name', 'id')->all(),
            'admins' => $this->user->where('role', 'admin')->where('relation', '!=', 'Driver')->get()->pluck('name', 'email')->all()
        ];

        return view('office.users.form-bulkmail', $data);
    }

    /**
     * Send bulk mail
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function bulkSend(Request $request)
    {
        if ( $request->has('selected') ) {
            $users = $this->user->whereIn('id', $request->selected)->get();

        } elseif ( $request->customers == 'all' ) {
            $users = $this->user->where('role', 'customer')->get();

        } else {
            $users = $this->user->where('role', 'customer')->where('status', $request->customers)->get();
        }

        $data = (object) [
            'from'          => $request->from ?? 'lyn@shuttlebug.co.za',
            'salutation'    => $request->salutation ?? 'Hi',
            'subject'       => $request->subject ?? ' ',
            'message'       => $request->message
        ];

        foreach ( $users as $user ) {
            Mail::to($user)->send(new BulkMail($user, $data));
        }

        if ( $request->has('admins') ) {
            $users = $this->user->where('role', 'admin')->where('relation', 'System')->get();
            foreach ( $users as $user ) {
                Mail::to($user)->send(new BulkMail($user, $data));
            }
        }

        return back()->withInput()->with('confirm', count($users).' customers have been mailed');
    }

    /**
     * Return user search results
     *
     * @param string    $find
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function search($find)
    {
        $data = [
            'users' => $this->user->searchUsers($find)
        ];

        return view('office.users.searchdata', $data);
    }
}
