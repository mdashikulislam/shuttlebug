<?php

namespace App\Http\Controllers;

use App\Http\Requests\SchoolRequest;
use App\Models\School;

class SchoolController extends Controller
{
    /**
     * @var School
     */
    protected $school;

    /**
     * SchoolController constructor.
     *
     * @param School $school
     */
    public function __construct(School $school)
    {
        $this->school = $school;
    }

    /**
     * Display the schools web page
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function webpage()
    {
        return view('schools');
    }

    /**
     * Display manage page
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show()
    {
        return view('office.schools.manage');
    }

    /**
     * Display school listing
     *
     * @param null $q
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|string
     */
    public function index($q = null)
    {
        $schools = $this->school->schoolsWithChildrenCount($q);

        $data = [
            'schools'       => $schools->sortBy('name'),
            'cities'        => $schools->sortBy('city')->pluck('city','city')->all(),
            'suburbs'       => $schools->sortBy('suburb')->pluck('suburb','suburb')->all(),
            'listing'       => $q
        ];

        return view('office.schools.index', $data);
    }

    /**
     * Display form for creating a school
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {
        $data = [
            'school' => null,
        ];

        return view('office.schools.form', $data);
    }

    /**
     * Display form for editing a school
     *
     * @param int   $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit($id)
    {
        $data = [
            'school' => $this->school->find($id),
        ];

        return view('office.schools.form', $data);
    }

    /**
     * Store a new school
     *
     * @param SchoolRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(SchoolRequest $request)
    {
        $school = $this->school->create($request->all());

        return redirect('office/schools/edit/'.$school->id)->with('confirm', 'School has been saved');
    }

    /**
     * Update an existing school
     *
     * @param SchoolRequest $request
     * @param int        $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(SchoolRequest $request, $id)
    {
        $school = $this->school->find($id);
        $school->update($request->all());

        return back()->with('confirm', 'Changes have been saved');
    }

    /**
     * Return school search results
     *
     * @param string    $find
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function search($find)
    {
        $data = [
            'schools' => $this->school->searchSchools($find)
        ];

        return view('office.schools.searchdata', $data);
    }
}
