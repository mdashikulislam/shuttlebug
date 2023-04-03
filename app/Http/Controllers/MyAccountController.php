<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MyAccountController extends Controller
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
     * Display password page
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function password()
    {
        $data = [
            'user'  => Auth::user()
        ];

        return view('myaccount.password', $data);
    }

    /**
     * Update the password
     *
     * @param Request $request
     * @param         $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function passwordUpdate(Request $request, $id)
    {
        if ( $request->password != $request->password_confirmation ) {
            return back()->with('danger', 'Password and Confirmation do not match');
        } elseif ( strlen($request->password) < 8 ) {
            return back()->with('danger', 'Password must be at least 8 characters');
        }

        $user = $this->user->find($id);
        $user->update($request->all());

        return back()->with('confirm', 'Changes have been saved');
    }

    /**
     * Close the account
     *
     * @todo handle request to close account
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function close($id)
    {
        // do something

        return back()->with('confirm', "Received your notification. We'll send an email in due course.");
    }
}
