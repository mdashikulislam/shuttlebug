<?php

namespace App\Http\Controllers;

use App\Http\Processors\HomeHeroes;
use App\Http\Processors\TripsheetFeedback;
use App\Models\Booking;
use App\Models\Children;
use App\Models\PlanningReport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;


class TripSheetController extends Controller
{
    /**
     * Display manage page
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show()
    {
        return view('office.tripsheets.manage');
    }

    /**
     * Display the summary page
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function summary(Request $request)
    {
        $data = $this->summaryData($request->date);

        return view('office.tripsheets.summary', $data);
    }

    /**
     * Collect the data for the summary view
     *
     * @param $date
     * @return array
     */
    private function summaryData($date)
    {
        $bookings = Booking::with('passenger')->where('date', $date)->orderBy('putime')->get();

        if ( count($bookings) > 0 ) {
            list($vehicles, $trips, $timeslots) = $this->vehicleTrips($date, $bookings);
        } else {
            $vehicles = $trips = $timeslots = $vehicle_pass = [];
        }

        // all vehicles are included for hacking purposes so remove the unused vehicles to clean up the summary page
        foreach ( $vehicles as $id => $data ) {
            if ( $data['pass'] == 0 ) {
                unset($vehicles[$id]);
            }
        }

        return [
            'date'          => $date,
            'timeslots'     => $timeslots,
            'vehicles'      => $vehicles,
            'trips'         => $trips,
            'passengers'    => count($bookings) == 0 ? [] : collect($bookings)->pluck('passenger.name', 'passenger_id')->all(),
            'colours'       => ['#0f859c', '#2ab27b', '#faaff9', '#e9967a', '#e9e07a', '#b6e97a']
        ];
    }

    /**
     * Display the driver login page
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showLogin()
    {
        return view('office.tripsheets.login');
    }

    /**
     * Log the attendant in and display vehicle log page
     *
     * @todo handle guest attendants
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function login(Request $request)
    {
        $attendants = DB::table('attendants')->where('status', '!=', 'history')->get();

        if ( is_null($attendants->where('id', $request->code)->first()) ) {
            return back()->withInput()->with('danger', 'Unknown Code');
        }

        if ( !$request->filled('date') ) {
            $date = now()->isWeekend() ? Carbon::parse('next monday')->toDateString() : now()->toDateString();
        } else {
            $date = $request->date;
        }
        session()->put('ts_date', $date);

        // get the data for the trip sheet
        list($vehicles, $summary, $vehicle, $attendant) = $this->vehicleLogData($date, $attendants, $request->code);

        // if there is no summary, this vehicle has no trips for the day so we don't need the rest
        if ( count($summary) > 0 ) {
            // put the day's bookings & passengers into session for use with feedback
            $bookings = Booking::with('passenger')->where('date', $date)->get();
            session()->put('ts_bookings', $bookings->map(function ($item) {
                return [
                    'id'           => $item->id,
                    'passenger_id' => $item->passenger_id,
                    'putime'       => $item->putime
                ];
            }));
            session()->put('ts_passengers', $bookings->pluck('passenger_id', 'passenger.name')->all());
        }

        $data = [
            'date'          => $date,
            'attendant'     => $attendant->first_name,
            'vehicle'       => convertArrayToObject($vehicle),
            'summary'       => $summary,
            'vehicles'      => $request->code == 1002 ? $vehicles : null,
        ];

        return view('office.tripsheets.triplog', $data);
    }

    /**
     * Display vehicle trip sheet
     *
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function tripsheet($id)
    {
        $feedback = DB::table('ts_feedback')->where('date', session('ts_date'))->where('vehicle', $id)->get();
        $plan = PlanningReport::where('date', session('ts_date'))->first();
        $vehicles = $this->getPlanVehicles($plan);

        $data = [
            'date'      => session('ts_date'),
            'vehicle'   => convertArrayToObject($vehicles[$id]),
            'trips'     => DB::table("ts_$id")->where('date', session('ts_date'))->orderBy('route')->get(),
            'departed'  => $feedback->where('passenger', 'departure')->pluck('duetime')->all(),
        ];

        return view('office.tripsheets.tripsheet', $data);
    }

    /**
     * Display home heroes schedule
     *
     * @param string $date
     * @param HomeHeroes $process
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function hhschedule(HomeHeroes $process, $date = null)
    {
        $date = $process->getDate($date);
        $plan = PlanningReport::where('date', $date)->first();
        $invoice = $process->getInvoice($date);

        if ( !is_null($plan) ) {
            list($times, $vehicles) = $process->buildData($date, $plan);
        } else {
            $times = $vehicles = [];
        }

        $data = [
            'date'      => $date,
            'vehicles'  => $vehicles,
            'times'     => $times,
            'plan'      => $plan,
            'invoice'   => $invoice
        ];

        return view('office.tripsheets.homeheroes', $data);
    }

    /**
     * Display home heroes statement
     *
     * @param null|string $week
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function hhstatement($week = null)
    {
        if ( is_null($week) ) {
            $dates = [now()->startOfWeek()->toDateString(), now()->startOfWeek()->addDays(4)->toDateString()];
        } else {
            $dates = [Carbon::parse($week)->startOfWeek()->toDateString(), Carbon::parse($week)->startOfWeek()->addDays(4)->toDateString()];
        }

        for ( $i = 0; $i <= 4; $i++ ) {
            $weekdates[] = Carbon::parse(Arr::first($dates))->addDays($i)->toDateString();
        }

        $db = DB::table('homeheroes_statement')->whereBetween('date', $dates)->orderBy('date')->get();
        foreach ( $weekdates as $date ) {
            foreach ( $db->where('date', $date) as $entry ) {
                $statement[$date][$entry->price] = [
                    'passengers' => $entry->passengers,
                    'price'      => $entry->price,
                    'value'      => $entry->value
                ];
            }
            $statement[$date]['day'] = $db->where('date', $date)->sum('value');
        }

        $data = [
            'dates'     => $weekdates,
            'statement' => $statement
        ];

        return view('office.tripsheets.homeheroes-statement', $data);
    }

    /**
     * Load the passenger profile
     *
     * @param $name
     * @param $time
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function profile($name, $time)
    {
        $feedback = DB::table('ts_feedback')
            ->where('date', session('ts_date'))
            ->where('passenger', $name)
            ->where('data', 'noshow')
            ->where('duetime', $time)->first();

        $data = [
            'passenger' => Children::with('user.guardians')->where(\DB::raw('CONCAT(first_name," ",last_name)'), 'LIKE', $name)->first(),
            'due'       => $time,
            'confirmed' => is_null($feedback) ? false : true
        ];

        return view('office.tripsheets.profile', $data);
    }

    /**
     * Load the passenger signature page
     *
     * @param $name
     * @param $time
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function signature($name, $time)
    {
        $feedback = DB::table('ts_feedback')
            ->where('date', session('ts_date'))
            ->where('passenger', $name)
            ->where('data', 'like', 'Hi%')
            ->where('duetime', $time)->first();

        $data = [
            'passenger' => Children::with('user')->where(\DB::raw('CONCAT(first_name," ",last_name)'), 'LIKE', $name)->first(),
            'due'       => $time,
            'confirmed' => is_null($feedback) ? false : true
        ];

        return view('office.tripsheets.signature', $data);
    }

    /**
     * Load the data for the route map
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function mapdata(Request $request)
    {
        return response()->json($this->tripMapdata($request));
    }

    /**
     * Handle ajax feedback calls
     *
     * @param Request           $request
     * @param TripsheetFeedback $process
     * @return \Illuminate\Http\JsonResponse
     */
    public function feedback(Request $request, TripsheetFeedback $process)
    {
        // allow only live feedback to be actioned
        if ( now()->toDateString() == session('ts_date') ) {
            return response()->json($process->handle());
        }

        return response()->json('failed');
    }

    /**
     * Get the vehicles, vehicle trips and timeslots from bookings and vehicle trip sheets
     *
     * @param $date
     * @param $bookings
     * @return array
     */
    private function vehicleTrips($date, $bookings)
    {
        $vehicles = $trips = $timeslots = [];
        $plan = PlanningReport::where('date', $date)->first();

        if ( !empty($plan->am_vehicles) ) {
            foreach ( $plan->am_vehicles as $id => $data ) {
                $trips[$id] = DB::table("ts_$id")->where('date', $date)->orderBy('arrive')->get();
                $amtimes[] = collect($trips[$id])->where('type', 'pickup')->where('putime', '<', '09:00:00')->pluck('putime')->all();
                $vehicles[$id] = $data;
            }
        }

        if ( !empty($plan->day_vehicles) ) {
            foreach ( $plan->day_vehicles as $id => $data ) {
                $trips[$id] = isset($trips[$id]) ? $trips[$id] :
                    $trips[$id] = DB::table("ts_$id")->where('date', $date)->orderBy('arrive')->get();
                $vehicles[$id] = [
                    'seats' => $data['seats'],
                    'pass'  => isset($vehicles[$id]) ? $vehicles[$id]['pass'] + $data['pass'] : $data['pass'],
                    'att'   => $data['att'] ?? ''
                ];
            }
            $daytimes = collect($bookings)->where('putime', '>=', '09:00:00')->pluck('putime')->unique()->all();
        }
        ksort($vehicles);

        $timeslots = array_unique(array_merge(Arr::flatten($amtimes ?? []), $daytimes ?? []));
        sort($timeslots);

        return [$vehicles, $trips, $timeslots];
    }

    /**
     * Prepare data for route map
     *
     * @param $request
     * @return array
     */
    private function tripMapdata($request)
    {
        $table = 'ts_'.$request->vehicle;
        $trips = DB::table($table)->where('date', session('ts_date'))->where('route', $request->route)->orderBy('dotime')->get();
        $done = [];

        foreach ( $trips as $trip ) {
            // group siblings
            if ( $trip->type == 'dropoff' ) {
                $passengers = '';
                $siblings = collect($trips)->where('geo', $trip->geo)->all();
                foreach ( $siblings as $sibling ) {
                    $passengers .= $sibling->passengers . ',';
                }
                $passengers = preg_replace('/^,|,\z/', '', $passengers);
            }

            if ( !in_array($trip->geo, $done) ) {
                $mapdata[] = [
                    'time'       => $trip->type == 'pickup' ? substr($trip->putime,0,5) : substr($trip->arrive,0,5),
                    'venue'      => $trip->type == 'pickup' ? $trip->venue : $trip->address,
                    'passengers' => $trip->type == 'pickup' ? $trip->passengers : $passengers,
                    'geo'        => $trip->geo
                ];
                $done[] = $trip->geo;
            }
        }

        return $mapdata;
    }

    /**
     * Return the vehicle data for the trip sheet page
     *
     * @param $date
     * @param $attendants
     * @param $attendant_id
     * @return array
     */
    private function vehicleLogData($date, $attendants, $attendant_id)
    {
        $summary = $vehicle = [];
        $plan = PlanningReport::where('date', $date)->first();
        $vehicles = $this->getPlanVehicles($plan);
        $attendant = collect($attendants)->where('id', $attendant_id)->first();

        // if the attendant is required today we need the vehicle data
        if ( !is_null($plan) && !is_null(collect($vehicles)->where('att', $attendant->first_name)->first()) ) {
            $vehicle = collect($vehicles)->where('att', $attendant->first_name)->first();

            // if the vehicle is in use today we need the summary data
            if ( !is_null($vehicle) ) {
                $ts = DB::table('ts_' . $vehicle['id'])->where('date', $date)->orderBy('route')->get();

                if ( count($ts) > 0 ) {
                    $summary['passengers'] = collect($ts)->where('type', 'dropoff')->count();
                    $summary['venues'] = collect($ts)->where('type', 'pickup')->count();
                    $summary['km'] = DB::table('_log_shuttle_mileage')->where('reg', $vehicle['id'])->where('date', $date)->sum('km');
                    $summary['start'] = collect($ts)->first()->putime;
                    $summary['end'] = collect($ts)->last()->dotime;
                }
            }
        }

        return [$vehicles, $summary, $vehicle, $attendant];
    }

    /**
     * Return the vehicles used in the plan formatted with required data
     *
     * @param $plan
     * @return mixed
     */
    private function getPlanVehicles($plan)
    {
        if ( !empty($plan->am_vehicles) ) {
            foreach ( $plan->am_vehicles as $id => $data ) {
                $vehicles[$id]['id'] = $id;
                $vehicles[$id]['model'] = $id == 102 ? 'Innova' : $data['seats'] . ' seater';
                $vehicles[$id]['att'] = $data['att'];
            }
        }

        if ( !empty($plan->day_vehicles) ) {
            foreach ( $plan->day_vehicles as $id => $data ) {
                if ( !isset($vehicles[$id]) ) {
                    $vehicles[$id]['id'] = $id;
                    $vehicles[$id]['model'] = $id == 102 ? 'Innova' : $data['seats'] . ' seater';
                    $vehicles[$id]['att'] = $data['att'];
                }
            }
        }

        return $vehicles ?? [];
    }
}
