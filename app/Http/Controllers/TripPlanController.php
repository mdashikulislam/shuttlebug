<?php

namespace App\Http\Controllers;

use App\Models\PlanningReport;
use App\Models\School;
use App\Models\TripSettings;
use App\Models\Vehicle;
use App\Notifications\WebmasterNotes;
use App\TripPlanning\TripBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;


class TripPlanController extends Controller
{
    /**
     * @var TripSettings
     * @var PlanningReport
     */
    protected $settings;
    protected $report;

    /**
     * TripPlanController constructor.
     *
     * @param TripSettings $settings
     * @param PlanningReport $report
     */
    public function __construct(TripSettings $settings, PlanningReport $report)
    {
        $this->settings = $settings;
        $this->report = $report;
    }

    /**
     * Display manage page
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show()
    {
        $vehicles   = Vehicle::where('status', '!=', 'history')->get();

        $data = [
            'vehicles'      => Vehicle::where('status', '!=', 'history')->get(),
            'attendants'    => DB::table('attendants')->where('status', '!=', 'history')->get(),
        ];

        return view('office.tripplans.manage', $data);
    }

    /**
     * Display settings form
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function settings()
    {
        $schools = School::orderBy('name')->get()->pluck('name','id')->all();

        $data = [
            'settings'  => $this->settings->first(),
            'vehicles'  => Vehicle::where('status', '!=', 'history')->orderBy('model')->get()->pluck('model','id')->all(),
            'schools'   => $schools + [300014 => 'Kate McCallum Ballet']
        ];

        return view('office.tripplans.settings', $data);
    }

    /**
     * Update settings
     *
     * @param Request $request
     * @param         $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateSettings(Request $request, $id)
    {
        foreach ( $request->school_pudelay as $item ) {
            $delays[] = $item * 60;
        }
        $request->merge(['school_pudelay' => $delays ?? []]);

        foreach ( $request->trip_times as $item ) {
            $times[] = $item * 60;
        }
        $request->merge(['trip_times' => $times ?? []]);

        $settings = $this->settings->find($id);
        $settings->update($request->all());

        return back()->with('confirm', 'Changes have been saved');
    }

    /**
     * Display the plan page if plan exists, else the loading page
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showPlan(Request $request)
    {
        $data = $this->getPlan($request);

        // no bookings, report
        if ( !$data ) {
            return back()->withInput()->with('danger', 'There are no '.$request->period.' bookings on '.$request->date);
        }

        // no plan, create
        // show plan-loader which calls buildPlan
        if ( !isset($data['report']) ) {
            return view('office.tripplans.plan-loader');
        }

        // existing plan, show plan
        if ( $request->period == 'am' ) {
            return view('office.tripplans.am-plan', $data);
        }

        return view('office.tripplans.day-plan', $data);
    }

    /**
     * Retrieve or create the plan
     *
     * @param Request $request
     * @return mixed
     */
    private function getPlan(Request $request)
    {
        /**
         * format the day's vehicles in the request
         * merge day's vehicles with request
         * save request in session for use in re-run
         */
        $day_vehicles = $day_attendants = [];

        if ( $request->has('vehicle') ) {
            foreach ( $request->vehicle as $id => $vehicle ) {
                if ( isset($vehicle['available']) ) {
                    $day_vehicles[] = $id;
                }
            }
        }
        if ( $request->has('attendant') ) {
            foreach ( $request->attendant as $id => $attendant ) {
                if ( isset($attendant['available']) ) {
                    $day_attendants[] = $id;
                }
            }
        }

        $request->merge(['day_vehicles' => $day_vehicles, 'day_attendants' => $day_attendants]);
        session()->put('plan.request', new Request($request->all()));

        /**
         * check if there is a saved plan
         * false indicates no bookings
         * null indicates no plan
         * non-null warning indicates plan with different vehicles
         */
        list($report, $warning) = $this->report->getExisting($request);

        // no bookings so return notification
        if ( $report === false ) {
            // need to clear vehicle tables just in case a single booking existed
            // which was subsequently cancelled (entries will still exist in the vehicle table)
            $this->updateVehicleTables($request);

            return false;
        }
        // no saved plan so show loading page
        if ( is_null($report) ) {
            return $request->all();
        }

        // return the data for the plan page
        return $this->planData($request, $report, $warning);
    }

    /**
     * Collect the plan data for the view
     * When there are no bookings on the date the report is false
     *
     * @param $request
     * @param $report
     * @param $warning
     * @return array
     */
    private function planData($request, $report, $warning)
    {
        if ( is_null($report) ) {
            // if we're here and the report is null there is a problem !!
            $data = ['subj' => 'Trip Plan Error', 'msg' => $request];
            Notification::route('mail', 'webmaster@shuttlebug.co.za')->notify(new WebmasterNotes($data));
        }
        elseif ( $report ) {
            $vehicle_list = [];
            $list = $request->period == 'am' ? array_keys($report->am_vehicles) : array_keys($report->day_vehicles);
            foreach ( $list as $id ) {
                $vehicle_list[$id] = $id;
            }
            $list = $request->period == 'am' ?  $report->am : [];
            foreach ( $list as $vehicle => $data ) {
                $zones[$data[0]['zone']] = $data[0]['zone'] == 'in' ? 'Hout Bay' : $data[0]['zone'];
            }
        }

        return [
            'request'       => $request,
            'vehicles'      => $request->period == 'am' ? $report->am_vehicles : $report->day_vehicles,
            'vehicle_list'  => isset($vehicle_list) ? $vehicle_list : [],
            'warn_vehicles' => is_null($warning) ? null : 'This saved report uses different vehicles',
            'report'        => $report,
            'zones'         => isset($zones) ? $zones : [],
            'hacks'         => $request->period == 'day' ?
                DB::table('trip_hacks')->where('date', $request->date)->whereIn('vehicle', array_keys($report->day_vehicles))->get() : []
        ];
    }

    /**
     * Run the trip planner and show the plan page
     *  called by loading page
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function buildPlan()
    {
        $t_start = microtime(true);

        $request = session('plan.request');
        $builder = new TripBuilder();

        // build and save the plan
        $result = $builder->handle($request);

        // am planner will return error here
        if ( substr($result, 0, 5) == 'error' ) {
            $data = [
                'request'   => $request,
                'error'     => $result,
                'report'    => null
            ];

            return view('office.tripplans.am-plan', $data);
        }

        // remove trip builder session variables
        session()->forget('planner');
        $t_end = microtime(true);

        list($report, $warning) = $this->report->getExisting($request);
        $data = $this->planData($request, $report, $warning);
        $data['runtime'] = round($t_end - $t_start,0);

        if ( $request->period == 'am' ) {
            return view('office.tripplans.am-plan', $data);
        }

        return view('office.tripplans.day-plan', $data);
    }

    /**
     * Force re-run of am plan
     * Forces the re-run by removing vehicles from the saved plan
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function rerunAmPlan()
    {
        PlanningReport::where('date', session('plan.request')->date)->update(['am_vehicles' => null]);

        return redirect('office/operations/tripplans/plan/build');
    }

    /**
     * Force re-run of day plan
     * Forces the re-run by removing vehicles from the saved plan
     *
     * @param $data
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function rerunPlan($data = null)
    {
        if ( !is_null($data) ) {
            session()->put('move_pickup', unserialize($data));
        }

        PlanningReport::where('date', session('plan.request')->date)->update(['day_vehicles' => null]);

        return view('office.tripplans.plan-loader');
    }

    /**
     * Rerun plan with user's requested vehicle changes
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function updatePlan(Request $request)
    {
        DB::table('trip_hacks')->where('date', $request->date)->delete();

        if ($request->call == 'update' ) {
            foreach ( $request->hacks as $hack ) {
                $time = array_keys($hack);
                foreach ( Arr::first($hack) as $short => $vehicle ) {
                    if ( $vehicle > '' ) {
                        $input[] = [
                            'putime'    => $time[0],
                            'pushort'   => $short,
                            'vehicle'   => $vehicle,
                            'date'      => $request->date
                        ];
                    }
                }
            }

            // a single hack updated to auto will not have an input (it's simply deleted)
            if ( isset($input) ) {
                DB::table('trip_hacks')->where('date', $request->date)->insert($input);
            }
        }

        PlanningReport::where('date', session('plan.request')->date)->update(['day_vehicles' => '']);

        return view('office.tripplans.plan-loader');
    }

    /**
     * Clear legacy entries in vehicle tables
     * These exist if a single booking was cancelled
     * because the trip planner updates vehicle tables
     * but is not run when there are no longer any bookings
     *
     * @param Request $request
     */
    private function updateVehicleTables($request)
    {
        $existing = Vehicle::where('status', '!=', 'history')->get()->pluck('id')->all();
        for ( $i = Arr::first($existing); $i <= Arr::last($existing) + 5; $i++ ) {
            $table = 'ts_' . $i;
            if ( Schema::hasTable($table) ) {
                DB::table($table)->where('date', $request->date)->where('plan', $request->period)->delete();
            }
        }
    }
}
