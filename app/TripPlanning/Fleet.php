<?php
/**
 * Created by PhpStorm.
 * User: Ray
 * Date: 2018/03/22
 * Time: 9:45 AM
 */

/*
 * Vehicle Log
 * Creates and updates fleet vehicles.
 *
 * tracks changes to each vehicle status throughout the build process
 */

namespace App\TripPlanning;


use App\Models\PlanningReport;
use App\Models\TripSettings;
use App\Models\Vehicle;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class Fleet
{
    /**
     * Return the vehicles in the fleet
     *
     * @return \Illuminate\Session\SessionManager|\Illuminate\Session\Store|mixed
     */
    public static function list()
    {
        $fleet = session('planner.fleet');
        ksort($fleet);
        return $fleet;
    }

    /**
     * Return the number of vehicles in the fleet
     *
     * @return int
     */
    public static function count()
    {
        return count(session('planner.fleet'));
    }

    /**
     * Return the specified vehicle from the fleet
     *
     * @param $id
     * @return array
     */
    public static function get($id)
    {
        return collect(session('planner.fleet'))->where('id', $id)->first();
    }

    /**
     * Add a vehicle to the fleet
     * reserved vehicles are limited to day_vehicles
     * so if a vehicle is being created by available_vehicles with a capacity
     * greater than 3 seats it should be limited to 3 seats.
     *
     * @param int $seats
     * @param array $pups
     * @param array $dnd
     * @param string $zone
     * @param string $depart
     * @return string
     */
    public static function create($seats, $pups, $dnd, $zone, $depart = null)
    {
        $fleet = session('planner.fleet');
        $vehicle = substr($zone,0,2) == 'am' ?
            self::selectAmVehicle($seats, $zone, $depart) : self::selectDayVehicle($seats, $depart);

        $fleet[$vehicle->id] = (object) [];
        $fleet[$vehicle->id]->id = $vehicle->id;
        $fleet[$vehicle->id]->model = $vehicle->model;
        $fleet[$vehicle->id]->seats = $vehicle->seats;
        $fleet[$vehicle->id]->primary = $vehicle->primary;
        $fleet[$vehicle->id]->geo = $vehicle->geo;
        $fleet[$vehicle->id]->adjusted_seats = null;
        $fleet[$vehicle->id]->zone = 'in';
        $fleet[$vehicle->id]->depart = '06:00';
        $fleet[$vehicle->id]->waiting = '';
        $fleet[$vehicle->id]->pickups = $pups;
        $fleet[$vehicle->id]->dnd = $dnd;
        $fleet[$vehicle->id]->attendant = $vehicle->attendant ?? '';

        session()->put('planner.fleet', $fleet);

        return $vehicle->id;
    }

    /**
     * Add pickups and do-not-disturb times to a reserved vehicle
     *
     * @param $id
     * @param $pups
     * @param $dnd
     */
    public static function updateReserve($id, $pups, $dnd)
    {
        $fleet = session('planner.fleet');

        $fleet[$id]->pickups += $pups;
        $fleet[$id]->dnd += $dnd;

        session()->put('planner.fleet', $fleet);

        return;
    }

    /**
     * Update the status of a vehicle
     *
     * @param $vehicle
     * @param $pickup
     * @param $route
     * @param $pickups
     */
    public static function update($vehicle, $pickup, $route, $pickups)
    {
        $fleet = session('planner.fleet');

        foreach ($fleet as $logvehicle) {
            if ($logvehicle->id == $vehicle->id) {
                $logvehicle->depart = end($route)->depart;
                $logvehicle->geo = end($route)->latlon;
                $logvehicle->adjusted_seats = $vehicle->adjusted_seats ?? null;
                $logvehicle->zone = end($route)->zone;
                $logvehicle->last_pickup = $pickup->latlon;
                $logvehicle->putime = $pickup->time;
                $logvehicle->status = 'free';
                $logvehicle->waiting = $vehicle->waiting;
                $logvehicle->waiting_for = $vehicle->waiting_for ?? null;
                $logvehicle->pickups = $vehicle->pickups;
                $logvehicle->dnd = $vehicle->dnd;

                /*
                 * revise dnd estimated end-time with actual end-time
                 * check both single pickups and grouped pickups
                 */
                if ( isset($logvehicle->dnd[$pickup->time]) &&
                    (isset($logvehicle->pickups[$pickup->legix]) && $logvehicle->pickups[$pickup->legix]->group == 0) ) {
                    $logvehicle->dnd[$pickup->time] = end($route)->depart;

                } elseif ( $pickup->legix > 0 &&
                    !is_null(collect($logvehicle->pickups)->where('group', $pickup->legix)->first()) ) {
                    $leg = $pickup->legix;
                    $leg1 = Arr::where($vehicle->pickups, function ($item, $key) use($leg) {
                        return $item->group == $leg;
                    });
                    $logvehicle->dnd[$pickups[key($leg1)]->time] = end($route)->depart;
                }
                break;
            }
        }

        session()->put('planner.vehicles', $fleet);

        return;
    }

    /**
     * Create the reserved vehicle only if it is available today
     * will return 0, 4, 5 or 6
     * if a 4 seater is required and not available a bigger vehicle will
     * be created instead if it's available
     *
     * returning 0 will not create a vehicle, forcing 3 seaters to be created later if required.
     *
     * @param int $seats
     * @param null|string $am
     * @return int|mixed
     */
    public static function isAvailableVehicle($seats, $am = null)
    {
        $fleet = array_keys(self::list());
        $available = Vehicle::whereIn('id', session('planner.day_vehicles'))->whereNotIn('id', $fleet)->get()->pluck('seats', 'id')->all();

        /*
         * if creating an am vehicle remove the innova if it's not the preferred vehicle
         */
        if ( !is_null($am) ) {
            $settings = TripSettings::first();
            if ( $settings->pref_am_vehicle != 'sb' ) {
                unset($available[102]);
            }
        }

        $create = 0;

        if ( $seats == 6 ) {
            $create = Arr::first($available, function($value) { return $value > 4; }, 0);

        } elseif ( (!is_null($am) && $settings->pref_am_vehicle == 'sb') || is_null($am) ) {
            $create = Arr::first($available, function($value) { return $value < 5; }, 0);

            /*
             * if no 4 seater, create a bigger vehicle instead if exists
             */
            if ( $create == 0 ) {
                $create = Arr::first($available, function($value) { return $value > 4; }, 0);
            }
        }

        return $create;
    }

    /**
     * Add the am 3 seater vehicles to the day fleet
     * if the attendant is available & not already allocated to a larger vehicle.
     * This keeps attendants on the same vehicles and makes all vehicles available for hacking
     * called by DayPrep after creating 4+ seater vehicles
     *
     * @todo 'other' attendant should add ALL other attendants (ie for more than 2 attendants)
     */
    public static function addAmVehicles()
    {
        $fleet = session('planner.fleet');
        $attendants = DB::table('attendants')->whereIn('id', session('planner.day_attendants'))->get();
        $plan = PlanningReport::where('date', session('plan.request')->date)->first();
        $am_vehicles = !empty($plan['am_vehicles']) ? $plan['am_vehicles'] : [];

        // add 3 seater with senior attendant if available & not already allocated to a larger vehicle
        $senior = collect($attendants)->where('role', 'senior')->first();

        if ( !is_null($senior) && !in_array($senior->first_name, array_column($fleet, 'attendant')) ) {
            $am_vehicles[109] = ['seats' => 3, 'att' => $senior->first_name];
        } else {
            unset($am_vehicles[109]);
        }

        // add 3 seater with other attendant if available & not already allocated to a larger vehicle
        $other = collect($attendants)->whereNotIn('role', ['primary','senior'])->first();
        if ( !is_null($other) && !in_array($other->first_name, array_column($fleet, 'attendant')) ) {
            $am_vehicles[110] = ['seats' => 3, 'att' => $other->first_name];
        } else {
            unset($am_vehicles[110]);
        }

        // provide at least one 3 seater with no attendant for hacks
        if ( !in_array('None', array_column($fleet, 'attendant')) ) {
            $am_vehicles[111] = ['seats' => 3, 'pass' => 0, 'att' => 'None'];
        }

        foreach ( $am_vehicles as $id => $am_vehicle ) {
            if ( !in_array($id, collect(Fleet::list())->pluck('id')->all()) ) {
                $fleet[$id] = (object) [];
                $fleet[$id]->id = $id;
                $fleet[$id]->model = $am_vehicle['seats'].' seater';
                $fleet[$id]->seats = $am_vehicle['seats'];
                $fleet[$id]->primary = $id == 102 ? 1 : 0;
                $fleet[$id]->geo = '-34.042108,18.350409';
                $fleet[$id]->adjusted_seats = null;
                $fleet[$id]->zone = 'in';
                $fleet[$id]->depart = '08:45';//'09:00';
                $fleet[$id]->waiting = '';
                $fleet[$id]->pickups = [];
                $fleet[$id]->dnd = [];
                $fleet[$id]->attendant = $am_vehicle['att'];

                session()->put('planner.fleet', $fleet);
            }
        }

        return;
    }

    /**
     * Return the selected am vehicle
     *
     * @param int $seats
     * @param string $zone
     * @param string $depart
     * @return mixed
     */
    private static function selectAmVehicle($seats, $zone, $depart)
    {
        $vehicles = Vehicle::whereIn('id', session('planner.day_vehicles'))->orderBy('seats')->get();
        $fleet_ids = collect(Fleet::list())->pluck('id')->all();
        $settings = TripSettings::first();

        /*
         * This will force the Innova to do the north zone if it exists
         * To use default allocations comment out the first 'if' option & remove the 'if' code lines
         */

        $routes = session("planner.routes");
//        if ( isset($routes['exnorth']) || isset($routes['llcf']) ) {
//
//            if ( $zone == 'amexnorth' || $zone == 'amllcf' ) {
//                $target = $vehicles->where('primary', 1)->where('seats', '>=', $seats)->first();
//                $target->attendant = self::vehicleAttendant('am', $depart, true)->first_name ?? '';
//                if ( !is_null($target) ) {
//                    return $target;
//                }
//            }
//
//            $attendant = self::vehicleAttendant('am', $depart, false);
//            if ( !is_null($attendant) && $attendant->role == 'senior' ) {
//                $id = 109;
//            } elseif ( !is_null($attendant) ) {
//                $id = 110;
//            } else {
//                $id = end($fleet_ids) > 110 ? end($fleet_ids) + 1 : 111;
//            }
//
//            return (object) [
//                'id'        => $id,
//                'model'     => '3 seater',
//                'seats'     => 3,
//                'primary'   => 0,
//                'geo'       => '-34.042108,18.350409',
//                'attendant' => $attendant->first_name ?? 'None'
//            ];
//
//        } else {

            // allocate primary vehicle if preferred & available
            $target = $vehicles->where('primary', 1)->where('seats', '>=', $seats)->first();
            if ( $settings->pref_am_vehicle == 'sb' &&
                substr($zone, 2, 2) == 'in' &&
                !is_null($target) &&
                !in_array($target->id, $fleet_ids) ) {

                $target->attendant = self::vehicleAttendant('am', $depart, true)->first_name ?? '';

                return $target;

            // or allocate large secondary vehicle
            } else {
                if ( $seats >= 4 ) {
                    $target = $vehicles->where('seats', '>=', $seats)->where('primary', 0)->whereNotIn('id', $fleet_ids)->first();
                    if ( !is_null($target) ) {
                        $target->attendant = self::vehicleAttendant('am', $depart, false)->first_name ?? 'None';

                        return $target;
                    }
                }
            }

            // or allocate 3 seater vehicle (first 3 seater = 109)
            $attendant = self::vehicleAttendant('am', $depart, false);
            if ( !is_null($attendant) && $attendant->role == 'senior' ) {
                $id = 109;
            } elseif ( !is_null($attendant) ) {
                $id = 110;
            } else {
                $id = end($fleet_ids) > 110 ? end($fleet_ids) + 1 : 111;
            }

            return (object) [
                'id'        => $id,
                'model'     => '3 seater',
                'seats'     => 3,
                'primary'   => 0,
                'geo'       => '-34.042108,18.350409',
                'attendant' => $attendant->first_name ?? 'None'
            ];
//        }
    }

    /**
     * Return the selected day vehicle
     *
     * @param int $seats
     * @param string|null $depart
     * @return mixed
     */
    private static function selectDayVehicle($seats, $depart)
    {
        $vehicles = Vehicle::whereIn('id', session('planner.day_vehicles'))->orderBy('seats')->get();
        $fleet_ids = collect(Fleet::list())->pluck('id')->all();

        /*
         * 4 seater
         */
        if ( $seats == 4 ) {
            // allocate primary vehicle if available
            if ( is_null(collect(Fleet::list())->where('seats', $seats)->first()) && !is_null($vehicles->where('seats', $seats)->where('primary', 1)->first()) ) {
                $target = $vehicles->where('seats', $seats)->where('primary', 1)->first();
                $target->attendant = self::vehicleAttendant('day', $depart, true)->first_name ?? '';
                return $target;

            // allocate secondary 4 seater if available
            } else {
                if ( !is_null($vehicles->where('seats', $seats)->whereNotIn('id', $fleet_ids)->first()) ) {
                    $target = $vehicles->where('seats', $seats)->where('primary', 0)->first();
                    $target->attendant = self::vehicleAttendant('day', $depart, false)->first_name ?? 'None';

                    return $target;
                }
            }
        }

        /*
         * 5/6 seater
         * will also allocate a bigger vehicle if 4 seats requested and not available
         * innova might be 4 or 5 seater
         */
        if ( $seats >= 4 ) {
            if ( !is_null($vehicles->where('seats', '>=', 4)->first()) ) {

                // allocate primary vehicle if available
                if ( is_null(collect(Fleet::list())->where('seats', $seats)->first()) && !is_null($vehicles->where('seats', $seats)->where('primary', 1)->first()) ) {
                    $target = $vehicles->where('seats', $seats)->where('primary', 1)->first();
                    $target->attendant = self::vehicleAttendant('day', $depart, true)->first_name ?? '';
                    return $target;

                    // allocate secondary 4 seater if available
                } else {
                    if ( !is_null($vehicles->where('seats', $seats)->whereNotIn('id', $fleet_ids)->first()) ) {
                        $target = $vehicles->where('seats', $seats)->where('primary', 0)->first();
                        $target->attendant = self::vehicleAttendant('day', $depart, false)->first_name ?? 'None';

                        return $target;
                    }
                }

//                $target = $vehicles->where('seats', $seats)->first() != null ?
//                    $vehicles->where('seats', $seats)->first() : $vehicles->where('seats', '>', 4)->first();
//                $target->attendant = self::vehicleAttendant('day', $depart, false)->first_name ?? 'None';
//
//                return $target;
            }
        }

        /*
         * 3 seater
         */
        $attendant = self::vehicleAttendant('day', $depart, false);
        if ( !is_null($attendant) && $attendant->role == 'senior' ) {
            $id = 109;
        } elseif ( !is_null($attendant) ) {
            $id = 110;
        } else {
            $id = end($fleet_ids) > 110 ? end($fleet_ids) + 1 : 111;
        }
        return (object) [
            'id'      => $id,
            'model'   => '3 seater',
            'seats'   => 3,
            'primary' => 0,
            'geo'     => '-34.042108,18.350409',
            'attendant' => $attendant->first_name ?? 'None'
        ];
    }

    /**
     * Return the attendant for this vehicle
     *
     * @param string $call
     * @param string $depart
     * @param bool $primary
     * @return object
     */
    private static function vehicleAttendant($call, $depart, $primary)
    {
        $attendants = DB::table('attendants')->whereIn('id', session('planner.day_attendants'))->orderBy('id')->get();
        $used_attendants = collect(Fleet::list())->pluck('attendant')->all();
        $attendant = null;

        if ( $call == 'am' ) {
            if ( $primary ) {
                $attendant = $attendants->whereIn('role', ['primary'])
                    ->where('from', '<=', $depart)
                    ->whereNotIn('first_name', $used_attendants)
                    ->first();
            }

            if ( is_null($attendant) ) {
                $attendant = $attendants->whereNotIn('role', ['primary'])
                    ->where('from', '<=', $depart)
                    ->whereNotIn('first_name', $used_attendants)
                    ->first();
            }
        }

        if ( $call == 'day' ) {
//            if ( is_null($depart) ) {
//                $depart = '17:30';
//            }

            if ( $primary ) {
                $attendant = $attendants->whereIn('role', ['primary'])
//                    ->where('to', '<', $depart)
                    ->whereNotIn('first_name', $used_attendants)
                    ->first();
            }

            if ( is_null($attendant) ) {
                $attendant = $attendants->whereNotIn('role', ['primary'])
//                    ->where('to', '<', $depart)
                    ->whereNotIn('first_name', $used_attendants)
                    ->first();
            }
        }

        return $attendant;
    }
}
