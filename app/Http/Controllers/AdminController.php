<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminRequest;
use App\Models\User;
use App\Notifications\WebmasterNotes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class AdminController extends Controller
{
    /**
     * @var User
     */
    protected $admin;

    /**
     * AdminController constructor.
     *
     * @param User $admin
     */
    public function __construct(User $admin)
    {
        $this->admin = $admin;
    }

    /**
     * Display admins listing
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|string
     */
    public function index()
    {
        $data = [
            'admins'    => $this->admin->where('role', 'admin')->orderBy('last_name')->get()
        ];

        return view('office.admins.index', $data);
    }

    /**
     * Display the form for creating an admin
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {
        // authorise
        if ( Auth::user()->relation != 'System' ) {
            return back()->with('danger', "You don't have permission to do this.");
        }

        $data = [
            'admin'     => null,
            'relations' => $this->admin->adminRelations(),
            'names'     => $this->admin->where('role', 'admin')->get()->pluck('first_name')->all()
        ];

        return view('office.admins.form', $data);
    }

    /**
     * Display the form for editing an admin
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit($id)
    {
        // authorise
        if ( Auth::user()->relation != 'System' && Auth::user()->id != $id ) {
            return back()->with('danger', "You don't have permission to do this.");
        }

        $data = [
            'admin'     => $this->admin->find($id),
            'relations' => $this->admin->adminRelations(),
            'names'     => $this->admin->where('role', 'admin')->get()->pluck('first_name')->all()
        ];

        return view('office.admins.form', $data);
    }

    /**
     * Store a new admin
     *
     * @param AdminRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(AdminRequest $request)
    {
        $admin = $this->admin->create($request->all());

        $data = ['subj' => 'Create Email A/c', 'msg' => $request->email.' (pw: '.$request->emailpw.')'];
        Notification::route('mail', 'webmaster@shuttlebug.co.za')->notify(new WebmasterNotes($data));

        return redirect('office/users/admins/index')->with('confirm', $admin->name . ' has been saved');
    }

    /**
     * Update an existing admin
     * array_filter is used on input to remove empty inputs
     * thereby avoiding updating empty password
     *
     * @param AdminRequest $request
     * @param            $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(AdminRequest $request, $id)
    {
        if ( $request->has('remove') ) {
            $this->delete($id);

            return redirect('office/users/admins/index')->with('confirm', $request->first_name . ' ' . $request->last_name . ' has been removed.');
        }

        $admin = $this->admin->find($id);
        $admin->update(array_filter($request->all()));

        return back()->with('confirm', 'Changes have been saved');
    }

    /**
     * Destroy the admin
     *
     * @param $id
     */
    public function delete($id)
    {
        $this->admin->find($id)->delete();

        return;
    }
}
