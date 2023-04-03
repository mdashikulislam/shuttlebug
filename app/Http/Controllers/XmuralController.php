<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Xmural;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class XmuralController extends Controller
{
    /**
     * @var Xmural
     */
    protected $xmural;

    /**
     * XmuralController constructor.
     *
     * @param Xmural $xmural
     */
    public function __construct(Xmural $xmural)
    {
        $this->xmural = $xmural;
    }

    /**
     * Show xmurals form
     *
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show($id)
    {
        $user = User::find($id);

        $data = [
            'user'      => $user,
            'xmurals'   => $user->xmurals()->orderBy('venue')->get()->pluck('venue','id')->all(),
        ];

        if ( $id == Auth::user()->id ) {
            return view('myaccount.xmural', $data);
        }

        return view('office.users.form-xmurals', $data);
    }

    /**
     * Show xmural form
     *
     * @param $user_id
     * @param $xmural_id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function form($user_id, $xmural_id)
    {
        $xmural = is_null($xmural_id) ? null : $this->xmural->find($xmural_id);
        $user = User::find($user_id);

        $data = [
            'xmurals'   => $xmural,
            'user'      => $user,
            'xm_list'   => $this->xmural->where('view', '!=', 'pvt')->orderBy('venue')->get()->pluck('venue_location','id')->all(),
        ];

        return view('office.users.xmural-form', $data);
    }

    /**
     * Store a new xmural
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // reject if nothing submitted
        if ( !$request->filled('id') && !$request->filled('venue') ) {
            return back()->with('warning', 'Nothing Submitted for saving');
        }

        // add known venue to user's xmurals
        elseif ( $request->filled('id') ) {
            $xmural = $this->xmural->find($request->id);
            $xmural->user()->attach($request->user_id);

            return back()->with('confirm', 'Xmural has been saved');
        }

        // create a new xmural and add to user's xmurals
        $xmural = $this->xmural->create($request->all());
        $xmural->user()->attach($request->user_id);

        return back()->with('confirm', 'Xmural has been saved');
    }

    /**
     * Update an existing xmural
     *
     * @param Request $request
     * @param int        $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $xmural = $this->xmural->find($id);
        $xmural->update($request->all());

        return back()->with('confirm', 'Changes have been saved');
    }
}
