<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VehicleController extends Controller
{
    /**
     * @var Vehicle
     */
    protected $vehicle;

    /**
     * VehicleController constructor.
     *
     * @param Vehicle $vehicle
     */
    public function __construct(Vehicle $vehicle)
    {
        $this->vehicle = $vehicle;
    }

    /**
     * Display manage page
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show()
    {
        return view('office.vehicles.manage');
    }

    /**
     * Display vehicle listing
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|string
     */
    public function index()
    {
        $vehicles = $this->vehicle->where('status', '!=', 'history')->orderBy('id')->get();

        $data = [
            'vehicles'  => $vehicles
        ];

        return view('office.vehicles.index', $data);
    }

    /**
     * Display attendant listing
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|string
     */
    public function attendantIndex()
    {
        $data = [
            'attendants' => DB::table('attendants')->where('status', '!=', 'history')->get()
        ];

        return view('office.vehicles.attendants-index', $data);
    }

    /**
     * Display form for creating a vehicle
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {
        $data = [
            'vehicle'   => null,
        ];

        return view('office.vehicles.form', $data);
    }

    /**
     * Display form for editing a vehicle
     *
     * @param int   $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit($id)
    {
        $vehicle = $this->vehicle->find($id);

        $data = [
            'vehicle'   => $vehicle,
        ];

        return view('office.vehicles.form', $data);
    }

    /**
     * Store a new vehicle
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $vehicle = $this->vehicle->create($request->all());

        // create the vehicle table
        $table = 'ts_'.$vehicle->id;
        DB::statement("CREATE TABLE $table LIKE ts_102");

        return redirect('office/operations/vehicles/edit/'.$vehicle->id)->with('confirm', 'Vehicle has been saved');
    }

    /**
     * Update an existing vehicle
     *
     * @param Request $request
     * @param int        $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $vehicle = $this->vehicle->find($id);
        $vehicle->update($request->all());

        return back()->with('confirm', 'Changes have been saved');
    }

    /**
     * Display the form for creating or editing an attendant
     *
     * @param int|null $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function editAttendant($id = null)
    {
        $data = [
            'attendant' => is_null($id) ? null : DB::table('attendants')->find($id),
        ];

        return view('office.vehicles.attendant-form', $data);
    }

    /**
     * Store the attendant
     *
     * @param Request $request
     * @param int|null    $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeAttendant(Request $request, $id = null)
    {
        $input = $request->only('first_name','last_name','from','to','role','status');
        $input['first_name'] = formatName(trim($input['first_name']));
        $input['last_name'] = formatName(trim($input['last_name']));

        if ( is_null($id) ) {
            $id = DB::table('attendants')->insertGetId($input);
        } else {
            DB::table('attendants')->where('id', $id)->update($input);
        }

        return redirect('office/operations/attendants/edit/'.$id)->with('confirm', 'Attendant has been saved');
    }

    /**
     * Return vehicle search results
     *
     * @param string    $find
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function search($find)
    {
        $data = [
            'vehicles'  => $this->vehicle->searchVehicles($find),
        ];

        return view('office.vehicles.searchdata', $data);
    }
}
