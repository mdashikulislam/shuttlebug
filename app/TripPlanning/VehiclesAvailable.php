<?php
/**
 * Created by PhpStorm.
 * User: Ray
 * Date: 2018/04/02
 * Time: 9:34 AM
 */

/*
 * Vehicles Available
 * Determines which vehicles are available for this pickup.
 *
 * Returns only the waiting vehicles if they have the capacity
 * or the reserved vehicles if they have the capacity
 * otherwise, includes free and busy vehicles that are available
 * finally, creates new vehicles if required.
 */

namespace App\TripPlanning;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class VehiclesAvailable
{
    /**
     * VehiclesAvailable constructor.
     *
     */
    public function __construct()
    {
        $this->settings = session('planner.settings');
    }

    /**
     * Collect the vehicles available for this pickup
     *
     * @param object $pickup
     * @return array
     */
    public function collect($pickup)
    {
        /*
         * initialise working variables
         */
        $capacity['unallocated'] = $capacity['south'] = $capacity['north'] = $capacity['east'] = $capacity['in'] = 0;
        $available_vehicles = [];

        /*
         * maintain # vehicle passengers for every vehicle using capacity[unallocated],
         * required when creating additional vehicles
         */
        session()->put('planner.unallocated_capacity', []);

        /*
         * all calls to route mappers use this pickup's latlon
         * so reduce db hits by calling it once
         */
        $durations = DB::table('map_distances')->where('from', $pickup->latlon)->orWhere('to', $pickup->latlon)->get();

        /*
         * get after hours vehicle
         * determined by settings and takes precedence over all other
         *  obsolete as is better managed via hacks
         */
//        if ( $pickup->time >= '17:30' ) {
//            list($available_vehicles, $capacity) = $this->getAfterHoursVehicles($pickup, $durations, $capacity, $available_vehicles);
//
//            return $available_vehicles;
//        }

        /*
         * get waiting vehicles
         * these are the priority vehicles so are processed first
         * @todo farm whole waiting function to own class
         */
        list($available_vehicles, $capacity) = $this->getWaitingVehicles($pickup, $durations, $capacity, $available_vehicles);

        foreach ( $pickup->passengers as $zone => $count ) {
            if ( $capacity[$zone] < $count ) {
                $empty_zone = true;
                break;
            }
        }

        /*
         * don't get more vehicles if the waiting vehicles cater for the required capacity
         */
        if ( !isset($empty_zone) ) {
            return $available_vehicles;
        }

        /*
         * get reserved vehicles
         * these are the priority vehicles after waiting vehicles
         * so are processed before any others
         */
        if ( Fleet::count() > 0 ) {
            list($available_vehicles, $capacity) = $this->getReservedVehicles($pickup, $durations, $available_vehicles, $capacity);

            /*
             * don't get more vehicles if capacity catered for
             * reserved vehicles only add allocated passengers so array_sum will be accurate
             */
            if ( array_sum($capacity) >= array_sum($pickup->passengers) ) {
                return $available_vehicles;
            }
        }

        /*
         * get free vehicles
         * by including all vehicles the option exists later to hack a particular choice
         */
        list($available_vehicles, $capacity) = $this->getFreeVehicles($pickup, $durations, $available_vehicles, $capacity);

        /*
         * get busy vehicles
         * again, all are included to give hacking options later on
         */
        list($available_vehicles, $capacity) = $this->getBusyVehicles($pickup, $available_vehicles, $capacity, $durations);

        /*
         * if capacity requirements not yet met, create new vehicles to cater for remaining capacity
         */
        if ( array_sum($capacity) - $capacity['unallocated'] < array_sum($pickup->passengers) ) {
            $this->createAdditionalVehicles($pickup, $capacity);
            list($available_vehicles, $capacity) = $this->getFreeVehicles($pickup, $durations, $available_vehicles, $capacity);
        }

        return $available_vehicles;
    }

    /**
     * After Hours vehicles:
     * determined by trip settings
     *  this obsolete as is better managed via hacks
     * @todo catering for capacities must be confirmed & if reserved vehicle allocated attendant must be revised
     * @param $pickup
     * @param $durations
     * @param $capacity
     * @param $available_vehicles
     * @return array
     */
//    private function getAfterHoursVehicles($pickup, $durations, $capacity, $available_vehicles)
//    {
//        foreach ( $pickup->passengers as $zone => $load ) {
//            if ( $load > 0 ) {
//
//                // use innova if preferred & available
//                if ( $this->settings->pref_pm_vehicle == 'sb' ) {
//                    $vehicle = collect(Fleet::list())->where('primary', 1)->first();
//
//                // use homeheroes vehicle
//                } else {
//                    $vehicle = collect(Fleet::list())->where('attendant', 'None')->first();
//
//                    // if no am vehicle create vehicle
//                    if ( is_null($vehicle) ) {
//                        $id = Fleet::create(3, [], [], $zone, $pickup->time);
//                        $vehicle = Fleet::get($id);
//
//                    }
//                }
//
//                if ( !in_array($vehicle->id, array_column($available_vehicles, 'id')) &&
//                    !$this->isVehicleWaiting($pickup, $vehicle) &&
//                    !$this->dndConflict($vehicle, $pickup, $capacity) ) {
//
//                    if ( Carbon::parse($vehicle->depart) <= Carbon::parse($pickup->time)->addSeconds($this->settings->buffer) ) {
//                        $journey = $this->getDuration($durations, $vehicle->geo, $pickup->latlon);
//
//                        if ( Carbon::parse($vehicle->depart)->addSeconds($journey->duration) <= Carbon::parse($pickup->time)->addSeconds($this->settings->buffer) ) {
//                            $available_vehicles[] = $this->makeAvailable($vehicle, $journey, 'free');
//                            $capacity[$zone] += $vehicle->seats;
//                        }
//                    }
//                }
//            }
//        }
//
//        return [$available_vehicles, $capacity];
//    }

    /**
     * Waiting vehicles:
     * vehicles are marked as waiting for this pickup
     * waiting vehicles are drop off zone specific
     *
     * Waiting set in VehiclesLeg & removed in VehiclePassengers
     *
     * @param $pickup
     * @param $durations
     * @param $capacity
     * @param $available_vehicles
     * @return array
     */
    private function getWaitingVehicles($pickup, $durations, $capacity, $available_vehicles)
    {
        $waiting_vehicles = collect(Fleet::list())->where('waiting', $pickup->latlon);

        foreach ( $waiting_vehicles as $vehicle ) {
            $route = session("planner.trips.$vehicle->id");
            $trip = end($route);
            /*
             * ignore this vehicle if route has already been interrupted
             */
            if ( !is_null(collect($trip)->where('legacy', 'true')->first()) ) {
                break;
            }

            /*
             * in the case of waiting vehicles the previous route is interrupted
             * immediately after the pickup and, as it is at the same location timing is not an issue
             */
            $leg = $trip[0];
            $journey = $this->getDuration($durations, $leg->latlon, $pickup->latlon);

            if ( $leg->empty_seats > 0 ) {
                /*
                 * if this is a reserved 6 seater vehicle the max passengers is set in 1st leg
                 * and this leg passengers should be set to max - carrying
                 * other vehicles are set to leg->empty seats
                 */
                $reserved = collect($vehicle->pickups)->where('group', $pickup->legix)->first();
                if ( !is_null($reserved) && $vehicle->seats > 4 ) {
                    $load = $reserved->pass - $vehicle->adjusted_seats;
                    $vehicle->adjusted_seats = $load;
                } else {
                    $load = $leg->empty_seats;
                }
                $available_vehicles[] = $this->makeAvailable($vehicle, $journey, 'busy', $leg);
                $capacity[$vehicle->zone] += $load;

                $leg->interrupt = 'flag';
                $leg->duration  = $journey->duration;
                session()->put("planner.trips.$vehicle->id", $route);
            }
        }

        return [$available_vehicles, $capacity];
    }

    /**
     * Add the vehicles that are reserved for this pickup
     * these vehicles are reserved for a specific zone and # of passengers
     *
     * @param object $pickup
     * @param object $durations
     * @param array $available_vehicles
     * @param array $capacity
     * @return array
     */
    private function getReservedVehicles($pickup, $durations, $available_vehicles, $capacity)
    {
        foreach ( $pickup->vehicles as $id ) {

            /**
             * ignore waiting vehicles as they might already include reserved vehicles
             */
            if ( in_array($id, array_column($available_vehicles, 'id')) ) {
                continue;
            }

            $vehicle = Fleet::get($id);
            $journey = $this->getDuration($durations, $vehicle->geo, $pickup->latlon);

            /*
             * add 18min (10km) travel if this is the vehicle's first pickup & is in out-zones
             */
            if ( is_null($vehicle->geo) && $pickup->zone != 'in' ) {
                $journey->duration += 800;
                $journey->distance += 10000;
            }

            /*
             * six seaters need adjusted available seats if the pickup is grouped
             * get the adjustment and flag that it is adjusted to avoid removing it
             * after allocating passengers
             */
            $vehicle->adjusted_seats = $this->zixSeaterFirstLeg($pickup, $vehicle);
            $load = !is_null($vehicle->adjusted_seats) ? $vehicle->adjusted_seats : $vehicle->pickups[$pickup->legix]->pass;
            $available_vehicles[] = $this->makeAvailable($vehicle, $journey, 'free');
            $capacity[$vehicle->pickups[$pickup->legix]->zone] += $load;
        }

        return [$available_vehicles, $capacity];
    }

    /**
     * Free vehicles:
     * vehicle is not waiting
     * vehicle is not marked as do-not-disturb at this time
     * vehicle departed last drop off before pickup time plus buffer ( used to eliminate unnecessary google hits )
     * vehicle arrives before pickup time plus buffer
     *
     * reserved vehicles caught here are regarded as spare vehicles
     *
     * @param object $pickup
     * @param object $durations
     * @param array $available_vehicles
     * @param array $capacity
     * @return array
     */
    private function getFreeVehicles($pickup, $durations, $available_vehicles, $capacity)
    {
        foreach ( Fleet::list() as $vehicle ) {

            /**
             * ignore allocated vehicles, waiting vehicles & vehicles which cannot be disturbed
             */
            if ( in_array($vehicle->id, array_column($available_vehicles, 'id')) ||
                $this->isVehicleWaiting($pickup, $vehicle) ||
                $this->dndConflict($vehicle, $pickup, $capacity) ) {
                continue;
            }

            if ( Carbon::parse($vehicle->depart) <= Carbon::parse($pickup->time)->addSeconds($this->settings->buffer) ) {
                $journey = $this->getDuration($durations, $vehicle->geo, $pickup->latlon);

                if ( Carbon::parse($vehicle->depart)->addSeconds($journey->duration) <= Carbon::parse($pickup->time)->addSeconds($this->settings->buffer) ) {
                    $available_vehicles[] = $this->makeAvailable($vehicle, $journey, 'free');
                    $capacity['unallocated'] += $vehicle->seats;
                    session()->push('planner.unallocated_capacity', $vehicle->seats);
                }
            }
        }

        return [$available_vehicles, $capacity];
    }

    /**
     * Busy vehicles:
     * vehicle is busy on a route
     * vehicle can be disturbed
     * vehicle's last route drop off zone matches new passenger's drop off zone
     * interrupting previous drop off route will get it here on time
     * the vehicle's last route was not interrupted
     *
     * reserved vehicles caught here are regarded as spare vehicles
     *
     * @param object $pickup
     * @param array $available_vehicles
     * @param array $capacity
     * @param object $durations
     * @return array
     */
    private function getBusyVehicles($pickup, $available_vehicles, $capacity, $durations)
    {
        foreach ( Fleet::list() as $vehicle ) {
            /**
             * ignore allocated vehicles, vehicles which cannot be disturbed & vehicles crossing zones
             */
            if ( in_array($vehicle->id, array_column($available_vehicles, 'id')) ||
                $this->dndConflict($vehicle, $pickup, $capacity) ||
                $vehicle->zone != $pickup->zone ) {
                continue;
            }

            /*
             * Interrupt the vehicle's previous route if not already interrupted
             * find the latest leg of previous route that can be interrupted to get here on time
             * will be excluded if cannot get here on time
             */
            $route = session("planner.trips.$vehicle->id");
            if ( !is_null($route) ) {
                $legs = array_reverse(end($route), true);
                $exclude = false;

                /*
                 * ignore vehicle if route has already been interrupted
                 */
                foreach ( $legs as $leg ) {
                    if ( $leg->putime < end($legs)->putime ) {
                        $exclude = true;
                        break;
                    }
                }

                /*
                 * ignore vehicle if crossing between Hout Bay & Llandudno
                 * or legacy passengers and new passengers are in different zones
                 * this latter condition recently added and only partially tested
                 */
                if ( (Arr::first($legs)->zone == 'in' &&  $pickup->venue == 'Llandudno Prim') ||
                    $pickup->passengers[Arr::first($legs)->zone] == 0 ) {
                    $exclude = true;
                }

                if ( !$exclude ) {
                    list($leg, $journey) = $this->getLegToInterrupt($legs, $pickup, $vehicle, $durations);

                    if ( $leg ) {
                        $available_vehicles[] = $this->makeAvailable($vehicle, $journey, 'busy', $leg);
                        $capacity[end($legs)->zone] += $leg->empty_seats;

                        $leg->interrupt = 'flag';
                        $leg->duration  = $journey->duration;
                        session()->put("planner.trips.$vehicle->id", $route);
                    }
                }
            }
        }

        return [$available_vehicles, $capacity];
    }

    /**
     * Add additional vehicles for remaining capacity
     *
     * not grouped, will be marked as waiting in legVehicles.
     * no dnd, will be added in vehicleRoutes.
     *
     * vehicle will be processed as free.
     *
     * @param object $pickup
     * @param int $capacity
     */
    private function createAdditionalVehicles($pickup, $capacity)
    {
        /*
         * this process will check that the unallocated capacity (zone unknown) is sufficient
         * to cover the passenger/zone requirements of this pickup.
         * however the unallocated capacity is just total number of seats, and as a vehicle's seats cannot
         * be shared by different zones, the session variable provides an array of # of seats for each vehicle
         * so that that number of seats is removed from unallocated each time a vehicle is created.
         */

        $unallocated = session('planner.unallocated_capacity');
        arsort($unallocated);

        /*
         * add a vehicle for each zone passengers not catered for by the unallocated capacity
         */
        $required = collect($pickup->passengers)->map(function ($value, $key) use($capacity) {
            return $value - $capacity[$key];
        })->toArray();
        $required = array_filter($required);
        arsort($required);

        foreach ( $required as $zone => $count ) {
            $max = count($unallocated) > 0 ? array_shift($unallocated) : 0;

            while ( $count > $max ) {
                $seats  = 3;
                $dnd    = [];
                $pups   = [];
                Fleet::create($seats, $pups, $dnd, $zone);
                $count -= 3;
            }
        }

        return;
    }

    /**
     * Check if vehicle is waiting at this venue
     * vehicles waiting at venues for the next pickup must be excluded from being seen as free
     * so that their route is interrupted immediately after the pickup
     * this will force them to be seen as 'closest' in the leg vehicle selection process.
     *
     * @param $pickup
     * @param $vehicle
     * @return bool
     */
    private function isVehicleWaiting($pickup, $vehicle)
    {
        if ( !empty($vehicle->waiting) ) {
            if ( $pickup->latlon == $vehicle->waiting ) {
                /*
                 * ignore vehicle if it's just dropped off at this location (allow it to be seen as free)
                 */
                if ( $vehicle->geo != $pickup->latlon ) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Create the available vehicle
     *
     * @param mixed $vehicle
     * @param object $journey
     * @param string $status
     * @param mixed $leg
     * @return mixed
     */
    private function makeAvailable($vehicle, $journey, $status, $leg = null)
    {
        $depart = is_null($leg) ? $vehicle->depart : $leg->depart;
        $arrival = Carbon::parse($depart)->addSeconds($journey->duration)->format('H:i');
        $add_vehicle = clone $vehicle;

        $add_vehicle->status             = $status;
        $add_vehicle->available_by       = $arrival;
        $add_vehicle->available_in       = $journey->duration;
        $add_vehicle->journey_distance   = $journey->distance;
        $add_vehicle->available_seats    = !is_null($leg) ?  $leg->empty_seats : $vehicle->seats;

        return $add_vehicle;
    }

    /**
     * Find the latest leg of a route that can be interrupted to arrive on time
     * The legs of the route have been reversed so starting with last drop off
     *
     * @param array $legs
     * @param object $pickup
     * @param object $vehicle
     * @param object $durations
     * @return mixed
     */
    private function getLegToInterrupt($legs, $pickup, $vehicle, $durations)
    {
        foreach ( $legs as $key => $leg ) {

            /*
             * ignore the last drop off of the route ( would have been seen as free )
             */
            if ( $key == count($legs) - 1 ) {
                continue;
            }

            /*
             * ignore if this leg's destination is same as previous leg's destination ( sibling )
             */
            if ( $key > 0 && ($leg->latlon == $legs[$key - 1]->latlon) ) {
                continue;
            }

            /*
             * ignore all drop off legs for waiting vehicles ( must interrupt after pickup )
             */
            if ( $this->isVehicleWaiting($pickup, $vehicle) && $key > 0 ) {
                continue;
            }

            /*
             * interrupt this leg if it can get here on time
             * & the new extended route is not in conflict with the vehicle's do-not-disturb times
             */
            $time_limit = Carbon::parse($pickup->time)->addSeconds($this->settings->buffer);

            if ( Carbon::parse($leg->depart) <= $time_limit || $key == 0 ) {
                $journey = $this->getDuration($durations, $leg->latlon, $pickup->latlon);

                if ( Carbon::parse($leg->depart)->addseconds($journey->duration) <= $time_limit ) {
                    if ( !$this->dndConflict($vehicle, $pickup, [], $vehicle->seats - $leg->empty_seats) ) {
                        if ( $leg->empty_seats > 0 ) {
                            return [$leg, $journey];
                        }
                    }
                }
            }
        }

        return [false, false];
    }

    /**
     * Return duration between given geos
     * null geo is first pickup of day, set to 5min
     *
     * @param $durations
     * @param $geo
     * @param $pickup
     * @return object
     */
    private function getDuration($durations, $geo, $pickup)
    {
        if ( is_null($geo) ) {
            return (object) [
                'duration'  => 300,
                'distance'  => 2000
            ];
        } elseif ( $geo == $pickup ) {
            return (object) [
                'duration'  => 0,
                'distance'  => 0
            ];
        }

        $journey = collect($durations)->where('to', $geo)->first() ?? null;
        if ( is_null($journey) ) {
            $journey = collect($durations)->where('from', $geo)->first() ?? null;
        }
        if ( is_null($journey) ) {
            $journey = RouteMappers::pointDistance($geo, $pickup);
        }

        return $journey;
    }

    /**
     * Return true if estimated trip time will conflict with this vehicle's do-not-disturb times
     * ignore for reserved vehicle on second leg of grouped pickup
     * called by getFree, getBusy, getLegToInterrupt
     *
     * @param object $vehicle
     * @param object $pickup
     * @param array $capacity
     * @param int|null $interrupt
     * @return bool
     */
    private function dndConflict($vehicle, $pickup, $capacity, $interrupt = null)
    {
        $capacity = isset($capacity['unallocated']) ? array_sum($capacity) - $capacity['unallocated'] : array_sum($capacity);
        $carrying = is_null($interrupt) ? array_sum($pickup->passengers) - $capacity : array_sum($pickup->passengers) + $interrupt;
        $carrying = $carrying > $vehicle->seats ? $vehicle->seats : $carrying;
        $carrying = $carrying < 0 ? array_sum($pickup->passengers) : $carrying;

        foreach ( $vehicle->dnd as $from => $to ) {
            $res_start = Carbon::parse($from);
            $res_end = Carbon::parse($to);

            /*
             * reject if this pickup is in conflict
             */
            if ( Carbon::parse($pickup->time)->between($res_start, $res_end) ||
                Carbon::parse($pickup->time)->addSeconds($this->settings->trip_times[$carrying] ?? 3300)->addMinutes($pickup->zone == 'in' ? 0 : 15)->between($res_start, $res_end) ||
                (Carbon::parse($pickup->time) < $res_start && Carbon::parse($pickup->time)->addMinutes($pickup->zone == 'in' ? 0 : 15) > $res_end) ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Limit the number of passengers on the first leg of a grouped pickup
     * for a six seater vehicle to allow for siblings on the second leg
     *
     * first leg adjusted seats = #pass - # sib on the 2nd leg or pickup passengers if less
     * second leg = #pass - adjusted seats
     *
     * adjusted seats is also put onto a 6 seater on a non-grouped pickup
     * so that the correct capacity is applied when selecting leg vehicles
     * this is removed after completing the vehicle route.
     *
     * @param $pickup
     * @param $vehicle
     * @return int
     */
    private function zixSeaterFirstLeg($pickup, $vehicle)
    {
        if ( $vehicle->seats > 4 ) {
            if ( $vehicle->pickups[$pickup->legix]->group > 0 ) {
                $legtwo_passengers = collect(session('planner.passengers'))
                    ->where('puleg', $vehicle->pickups[$pickup->legix]->group)
                    ->where('dozone', $vehicle->pickups[$pickup->legix]->zone)
                    ->groupBy('dolatlon')
                    ->all();

                $geos = collect($legtwo_passengers)->map(function ($item) {
                    return collect($item)->count();
                })->toArray();
                arsort($geos);

                $siblings = 0;
                foreach ( $geos as $geo => $count ) {
                    if ( $count > 1 ) {
                        $siblings += $count;
                    }
                }

                if ( $vehicle->pickups[$pickup->legix]->pass - $siblings > $pickup->passengers[$vehicle->pickups[$pickup->legix]->zone] ) {
                    $load = $pickup->passengers[$vehicle->pickups[$pickup->legix]->zone];

                } elseif ( $vehicle->pickups[$pickup->legix]->pass - $siblings < 0 ) {
                    $load = 0;

                } else {
                    $load =  $vehicle->pickups[$pickup->legix]->pass - $siblings;
                }

                return $load;

            /*
             * 6 seater non-grouped pickup
             */
            } else {
                return $vehicle->pickups[$pickup->legix]->pass;
            }
        }

        return null;
    }
}
