<?php

namespace App\Http\Controllers;

use App\Models\Children;
use App\Models\School;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChildrenController extends Controller
{
    /**
     * @var Children
     */
    protected $children;

    /**
     * ChildrenController constructor.
     *
     * @param Children $children
     */
    public function __construct(Children $children)
    {
        $this->children = $children;
    }

    public function photos()
    {
        $children = $this->children
            ->whereHas('bookings', function($q) {
                $q->where('date', '>', now()->startOfYear()->toDateString());
            })
            ->where('friend', '')
            ->orderBy('last_name')
            ->get();
//            ->sortBy('last_name')->pluck('name', 'id')->all();

        $data = [
            'children' => $children
        ];

        return view('office.users.photos', $data);
    }

    /**
     * Show children form
     *
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show($id)
    {
        $user = User::find($id);
        $children = $this->children->where('user_id', $id)->orderBy('first_name')->get();

        $data = [
            'user'       => $user,
            'children'   => $children->where('friend', '')->pluck('name_with_status','id')->all(),
            'friends'    => $children->where('friend', 'friend')->pluck('name_with_status','id')->all(),
        ];

        if ( $id == Auth::user()->id ) {
            return view('myaccount.children', $data);
        }

        return view('office.users.form-children', $data);
    }

    /**
     * Show child form
     *
     * @param $user_id
     * @param $child_id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function form($user_id, $child_id)
    {
        $child = is_null($child_id) ? null : $this->children->find($child_id);
        $user = User::find($user_id);

        $data = [
            'children'  => $child,
            'user'      => $user,
            'schools'   => School::get()->sortBy('name')->pluck('name','id')->all(),
            'years'		=> dobYears(),
            'months'	=> dobMonths(),
            'days'		=> dobDays()
        ];

        return view('office.users.child-form', $data);
    }

    /**
     * Store a new child
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->merge(['dob' => $this->ymdToDob($request->age)]);
        $child = $this->children->create($request->all());

        return back()->with('confirm', $child->name.' has been saved')->with('returning', $child->id);
    }

    /**
     * Update an existing child
     *
     * @param Request $request
     * @param int        $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $request->merge(['dob' => $this->ymdToDob($request->age)]);

        $child = $this->children->find($id);
        $child->update($request->all());

        return back()->with('confirm', $child->name.' has been updated')->with('returning', $child->id);
    }

    /**
     * Return option list of children for given parent
     *
     * @param int $parent
     * @return string
     */
    public function selectChildren($parent)
    {
        $data = '<option value="">Select Passenger</option>';
        $children = $this->children->parentsActiveChildren($parent)->pluck('name','id')->all();

        foreach ( $children as $id => $name ) {
            $data .= '<option value="'.$id.'">'.$name.'</option>';
        }

        return $data;
    }

    /**
     * Return date from year,month & date fields
     *
     * @param array $age
     * @return string
     */
    private function ymdToDob($age)
    {
        foreach ( $age as $item ) {
            if ( empty($item) ) {
                $invalid = true;
            }
        }

        if ( isset($invalid) ) {
            return '0000-00-00';
        }

        return Carbon::createFromDate($age['year'], $age['month'], $age['day'])->toDateString();
    }
}
