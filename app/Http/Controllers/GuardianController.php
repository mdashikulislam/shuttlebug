<?php

namespace App\Http\Controllers;

use App\Models\Guardian;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class GuardianController extends Controller
{
    /**
     * @var Guardian
     */
    protected $guardian;

    /**
     * GuardianController constructor.
     *
     * @param Guardian $guardian
     */
    public function __construct(Guardian $guardian)
    {
        $this->guardian = $guardian;
    }

    /**
     * Display form for editing guardians
     *
     * @param int   $id     user_id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit($id)
    {
        $data = [
            'user'      => User::find($id),
            'guardian'  => $this->guardian->where('user_id', $id)->where('role', 'Guardian')->first(),
            'receiver'  => $this->guardian->where('user_id', $id)->where('role', 'Receiver')->first()
        ];

        if ( $id == Auth::user()->id ) {
            return view('myaccount.guardians', $data);
        }

        return view('office.users.form-guardians', $data);
    }

    /**
     * Save guardians
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // guardian
        if ( !is_null($request->guardian['first_name']) || !is_null($request->guardian['last_name']) ) {
            $guardian = $this->guardian->find($request->guardian['id']);
            if ( is_null($guardian) ) {
                $this->guardian->create(Arr::collapse($request->only('guardian')));
            } else {
                $guardian->update(Arr::collapse($request->only('guardian')));
            }
        }

        // receiver
        if ( !is_null($request->receiver['first_name']) || !is_null($request->receiver['last_name']) ) {
            $receiver = $this->guardian->find($request->receiver['id']);
            if ( is_null($receiver) ) {
                $this->guardian->create(Arr::collapse($request->only('receiver')));
            } else {
                $receiver->update(Arr::collapse($request->only('receiver')));
            }
        }

        return back()->with('confirm', 'Changes have been saved');
    }
}
