<?php
/**
 * Created by PhpStorm.
 * User: Ray
 * Date: 2018/03/27
 * Time: 12:47 PM
 */

/*
    |--------------------------------------------------------------------------
    | Trip Data
    |--------------------------------------------------------------------------
    |
    | Extracts plan data into input data and updates database
    |
    */

namespace App\TripPlanning;


use App\Models\Booking;
use App\Models\PlanningReport;
use App\Models\Vehicle;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TripData
{
    /**
     * Prepare database input for morning trips
     *  Allocates vehicles and attendants for each zone trip
     *
     * @param $request
     */
    public function amData($request)
    {
        $routes = session("planner.routes");
        $warnings = session("planner.warnings") ?? null;
        $schools = session('planner.schools');

        foreach ( $routes as $zone => $route ) {
            $passengers = session("planner.passengers");
            $data = $data_route = [];

            // transfer the route into data route which is formatted for db input
            foreach ( $route as $leg ) {

                // remove trailing 'at school' from leg description
                if ( strpos($leg->description, ' at ') !== false ) {
                    $description = trim(substr($leg->description, 0, strpos($leg->description, ' at')));
                } else {
                    $description = $leg->description;
                }

                // clone this leg in data_route, multiple times for multiple drop off passengers
                if ( $leg->type == 'dropoff' ) {
                    if ( strpos($description, ',') !== false ) {
                        $leg_passengers = explode(',', $description);
                        foreach ( $leg_passengers as $leg_passenger ) {
                            $newleg = clone ($leg);
                            $newleg->description = $leg_passenger;

                            // pickup might include siblings so get pickup 'like' passenger name
                            $this_pickup = collect($route)->where('type', 'pickup')->filter(function ($item) use ($leg_passenger) {
                                return false !== stristr($item->description, $leg_passenger);
                            });

                            $newleg->putime = $this_pickup->first()->arrive;
                            $data_route[] = clone ($newleg);
                        }
                    } else {
                        $newleg = clone ($leg);
                        $newleg->description = $description;

                        // pickup might include siblings so get pickup 'like' passenger name
                        $this_pickup = collect($route)->where('type', 'pickup')->filter(function ($item) use ($description) {
                            return false !== stristr($item->description, $description);
                        });

                        $newleg->putime = $this_pickup->first()->arrive;
                        $data_route[] = clone ($newleg);
                    }

                } else {
                    $data_route[] = clone ($leg);
                }
            }

            // create vehicle for the route
            $pass = [];
            foreach ( $data_route as $leg ) {
                if ( $leg->type == 'pickup' ) {
                    $pu = count(explode(',', $leg->description));
                    $pass[] = isset($pass[count($pass)-1]) ? $pass[count($pass)-1] + $pu : $pu;
                } elseif ( $leg->type == 'dropoff' ) {
                    $pass[] = $pass[count($pass)-1] - 1;
                }
            }

            $id = Fleet::create(max($pass), [], [], 'am'.$zone, $route[0]->depart);
            $report = session("planner.routes.$zone");
            session()->put("planner.routes.$id", $report);
            session()->forget("planner.routes.$zone");
            $vehicle = Fleet::get($id);
            $route_id = -1;

            // put pickup & drop off legs into data for vehicle table
            foreach ( $data_route as $leg ) {
                if ( $leg->type !== 'depart' ) {

                    if ( $leg->type == 'dropoff' ) {
                        $passenger = collect($passengers)->where('passenger', $leg->description)->first();
                        $address = collect(Arr::flatten($schools))->where('school', $leg->venue)->first()->address;

                        if ( !is_null($warnings) ) {
                            foreach ( $warnings as $warning ) {
                                if ( $warning->vehicle == $vehicle->id ) {
                                    if ( $leg->venue == $warning->loc && $leg->arrive == $warning->time ) {
                                        $this_warning = $warning->arrive;
                                    }
                                }
                            }
                        }
                    } else {
                        $route_id ++;
                    }

                    $data[] = [
                        'date'          => $request->date,
                        'plan'          => 'am',
                        'route'         => $route_id,
                        'type'          => $leg->type,
                        'putime'        => $leg->putime,
                        'dotime'        => $leg->type == 'pickup' ? $leg->dotime : $leg->arrive,
                        'arrive'        => $leg->arrive,
                        'depart'        => $leg->depart,
                        'venue'         => $leg->type == 'pickup' ? $leg->venue : $passenger->dovenue,
                        'passengers'    => $leg->description,
                        'age'           => $leg->type == 'pickup' ? $passenger->age ?? 0 : '',
                        'pass_id'       => $passenger->pass_id ?? 0,
                        'address'       => $leg->type == 'pickup' ? $leg->venue : $address,
                        'geo'           => $leg->latlon,
                        'warning'       => $this_warning ?? '',
                    ];

                    unset($this_warning);
                    unset($passenger);
                }
            }

            session()->put("planner.table.$vehicle->id", $data);
        }

        $this->dbUpdate($request);

        return;
    }

    /**
     * Prepare db data for day trips
     *
     * @param $request
     */
    public function dayData($request)
    {
        $passengers = session("planner.passengers");
        $warnings = session("planner.warnings") ?? null;

        foreach ( Fleet::list() as $vehicle ) {
            $data = [];

            if ( session()->has("planner.trips.$vehicle->id") ) {
                $route = session("planner.trips.$vehicle->id");

                /*
                 * create input for each trip leg
                 * begin route index at 10 to avoid conflict with morning trips
                 */
                foreach ( $route as $ix => $trip ) {

                    foreach ( $trip as $leg ) {
                        $venue = $leg->type == 'pickup' ?  substr($leg->description, strpos($leg->description, ' at')+4) : '';
                        $leg_passengers = $leg->type == 'pickup' ?
                            substr($leg->description, 0, strpos($leg->description, ' at')) : $leg->description;
                        $passenger = $leg->type == 'dropoff' ? collect($passengers)->where('passenger', $leg->description)->where('putime', $leg->putime)->first() : null;
                        $this_warning = $leg->type == 'pickup' ? collect($warnings)->where('vehicle', $vehicle->id)->where('loc', $venue)->where('putime', $leg->putime)->first() : null;

                        $data[] = [
                            'date'       => $request->date,
                            'plan'       => 'day',
                            'route'      => $ix + 10,
                            'type'       => $leg->type,
                            'putime'     => $leg->putime,
                            'dotime'     => $leg->type == 'dropoff' ? $leg->arrive : '',
                            'arrive'     => $leg->arrive,
                            'depart'     => $leg->depart,
                            'venue'      => $leg->type == 'pickup' ? $venue : $passenger->dovenue,
                            'passengers' => $leg_passengers,
                            'age'        => $passenger->age ?? 0,
                            'pass_id'    => $passenger->passid ?? 0,
                            'legacy'     => $leg->legacy,
                            'address'    => $leg->address,
                            'geo'        => $leg->latlon,
                            'warning'    => $this_warning->time ?? ''
                        ];
                    }
                }

                session()->put("planner.table.$vehicle->id", $data);
            }
        }

        $this->dbUpdate($request);

        return;
    }

    /**
     * Save data to database
     *
     * @param $request
     */
    private function dbUpdate($request)
    {
        $fleet = Fleet::list();
        $bookings = Booking::where('date', $request->date)->get();
        $this->cleanVehicleTables($request);
        $this->cleanShuttleLog($request);

        foreach ( $fleet as $vehicle ) {
            $data = session("planner.table.$vehicle->id") ?? [];

            if ( count($data) > 0 ) {
                $table = 'ts_' . $vehicle->id;
                if ( !Schema::hasTable($table) ) {
                    DB::statement("CREATE TABLE $table LIKE ts_102");
                }
                foreach ( $data as $row ) {
                    DB::table($table)->insert($row);
                }

                // update bookings with vehicle
                foreach ( $data as $row ) {
                    if ( $row['type'] == 'dropoff' ) {
                        if ( $request->period == 'am' ) {
                            $booking = $bookings->where('passenger_id', $row['pass_id'])
                                ->where('putime', '00:00:00')->first();
                        } else {
                            $booking = $bookings->where('passenger_id', $row['pass_id'])
                                ->where('putime', $row['putime'] . ':00')->first();
                        }

                        if ( !is_null($booking) ) {
                            $booking->update([
                                'vehicle' => $vehicle->id,
                            ]);
                        }
                    }
                }

                // update shuttle log
                // mileage updated for HH to excl to/from office (25/2/20)
                if ( $request->period == 'am' ) {
                    $route = session("planner.routes.$vehicle->id");
                    if ( $vehicle->id == 102 ) {
                        $km = round(collect(Arr::flatten($route))->sum('distance') / 1000);
                    } else {
                        $dropoffs = collect(Arr::flatten($route))->where('type', 'dropoff')->all();
                        $km = round(collect($dropoffs)->where('type', 'dropoff')->sum('distance') / 1000);
                    }
                } else {
                    $route = session("planner.trips.$vehicle->id");
                    if ( $vehicle->id == 102 ) {
                        $km = round(collect(Arr::flatten($route))->sum('distance') / 1000);
                    } else {
                        $dropoffs = collect(Arr::flatten($route))->where('type', 'dropoff')->all();
                        $km = round(collect($dropoffs)->sum('distance') / 1000);
                    }
                }

                DB::table('_log_shuttle_mileage')->insert([
                    'reg'   => $vehicle->id,
                    'date'  => $request->date,
                    'route' => $request->period,
                    'km'    => $km
                ]);
            }
        }

        // save report & warnings
        $report = $request->period == 'am' ? session('planner.routes') : session('planner.report');
        $veh_field = $request->period == 'am' ? 'am_vehicles' : 'day_vehicles';
        $upd_field = $request->period == 'am' ? 'updated_am' : 'updated_day';
        $warnings = session()->has('planner.warnings') ? session('planner.warnings') : [];
        $vehicles = $this->reportVehicles($fleet);

        $db = PlanningReport::where('date', $request->date)->first();
        if ( !is_null($db) ) {
            $db->update([
                $request->period => $report,
                'warnings' => $warnings,
                $veh_field => $vehicles,
                $upd_field => now()
            ]);
        }
        else {
            PlanningReport::create([
                'date' => $request->date,
                $request->period => $report,
                'warnings' => $warnings,
                $veh_field => $vehicles,
                $upd_field => now()
            ]);
        }

        return;
    }

    /**
     * Remove entries from all vehicle tables before adding new
     * as previous runs of the plan might have used different vehicles
     * surplus tables are checked to catch tables for vehicles created on-the-fly
     *
     * @param $request
     */
    private function cleanVehicleTables($request)
    {
        $existing = Vehicle::where('status', '!=', 'history')->orderBy('id')->get()->pluck('id')->all();
        for ( $i = Arr::first($existing); $i <= Arr::last($existing) + 5; $i++ ) {
            $table = 'ts_' . $i;
            if ( Schema::hasTable($table) ) {
                DB::table($table)->where('date', $request->date)->where('plan', $request->period)->delete();
            }
        }
    }

    /**
     * Remove shuttle log entries for this date
     * as previous runs of the plan might have used different vehicles
     *
     * @param $request
     */
    private function cleanShuttleLog($request)
    {
        DB::table('_log_shuttle_mileage')
            ->where('date', $request->date)
            ->where('route', $request->period)
            ->delete();
    }

    /**
     * Summarise fleet for report vehicles
     *      to allow hacking this now includes all vehicles, even if not used
     *
     * @param $fleet
     * @return mixed
     */
    private function reportVehicles($fleet)
    {
        $data = session('planner.table');

        foreach ( $fleet as $id => $item ) {
//            if ( isset($data[$id]) ) {
//            if ( isset($data[$id]) || ( $item->attendant == 'None' && collect($fleet)->where('attendant', 'None')->count() == 1 )) {
                $matrix[$id] = [
                    'seats' => $item->seats,
                    'pass'  => isset($data[$id]) ? collect($data[$id])->where('type', 'dropoff')->count() : 0,
                    'att'   => $item->attendant
                ];
//            }
        }
        ksort($matrix);

        return $matrix;
    }
}
