<?php
/**
 * Created by PhpStorm.
 * User: Ray
 * Date: 2018/04/04
 * Time: 12:25 PM
 */

/*
 * Vehicle Routes
 * Creates drop off routes for the vehicles on this leg.
 *
 * Routes are optimised and time-critical xmurals are prioritised.
 * Vehicles dropping off at pickup venues are flagged to wait.
 * Vehicle do-not-disturb times are updated with actual trip times.
 * Vehicle log is updated.
 */


namespace App\TripPlanning;


use Carbon\Carbon;

class VehicleRoutes
{
    /**
     * ReserveVehicles constructor.
     *
     */
    public function __construct()
    {
        $this->settings = session('planner.settings');
    }

    /**
     * Build the drop off routes for each vehicle
     *
     * @param array $leg_vehicles
     * @param array $leg_passengers
     * @param object $pickup
     * @param object $pickups
     */
    public function route($leg_vehicles, $leg_passengers, $pickup, $pickups)
    {
        foreach ($leg_vehicles as $vehicle) {
            $ordered_passengers = [];
            $route = [];

            /*
             * Extract the vehicle passengers
             */
            $vehicle_passengers = collect($leg_passengers)->where('vehicle', $vehicle->id)->all();

            /*
             * Get the drop off legs
             * passengers going to time-critical xmurals will be prioritised
             */
            $dof_legs = $this->dofLegs($vehicle_passengers, $pickup);

            /*
             * Update passengers with drop off leg index
             */
            foreach ( $dof_legs as $ix => $dofleg ) {
                if ( $ix > 0 ) {
                    $latlon   = $dofleg->this_latlon;
                    $siblings = collect($vehicle_passengers)->where('dolatlon', $latlon)->all();

                    foreach ( $siblings as $sibling ) {
                        $sibling->dof_leg       = $ix;
                        $ordered_passengers[]   = (object) $sibling;
                    }
                }
            }

            /*
             * Compile route leg data and add to route
             * dof_legs are unique locations while route legs are required for every location
             * so dof_legs must be duplicated for each sibling.
             * dof_legs includes pickup (key=0) so dof[x] = passenger[x-1]
             * drop offs is an array of current geo, next distance and next duration.
             */
            unset($dropoffs);
            $dropoffs[] = $dof_legs[0];

            foreach ( $ordered_passengers as $passenger ) {
                $dof = collect($dof_legs)->where('this_latlon', $passenger->dolatlon)->first();
                $dropoffs[] = clone $dof;
            }

            /*
             * initialize
             */
            $pu_description = $this->pickupDescription($ordered_passengers, $pickup);
            $empty_seats = $vehicle->seats - count($ordered_passengers);
            $arrive = $vehicle->available_by;

            /*
             * if multiple vehicles are being used we need to know the number of passengers
             * being picked up by each vehicle to determine departure time
             */
            $pass = collect($vehicle_passengers)->where('putime', $pickup->time)->count();
            $depart = $this->schoolDepartureTime($pickup, $arrive, $pass);

            foreach ( $dropoffs as $key => $leg ) {
                $this_passenger = $key == 0 ? '' : $ordered_passengers[$key-1];

                /*
                 * Arrival / Departure / Seats / Distance:
                 * Use the previous drop off arrival/departure for siblings else calculate new time.
                 * The first route is the pickup.
                 */
                if ( count($route) > 0 ) {
                    $arrive = $leg->this_latlon == $dropoffs[$key - 1]->this_latlon ?
                        $arrive :
                        Carbon::parse($route[$key - 1]->depart)->addSeconds($dropoffs[$key - 1]->next_duration)->format('H:i');

                    $depart = $leg->this_latlon == $dropoffs[$key - 1]->this_latlon ?
                        $depart :
                        Carbon::parse($arrive)->addSeconds($this->settings->home_delay)->format('H:i');

                    $empty_seats ++;

                    $distance = $leg->this_latlon == $dropoffs[$key - 1]->this_latlon ? 0 : $dropoffs[$key - 1]->next_distance;
                }

                $route[] = (object) [
                    'type'          => $key == 0 ? 'pickup' : 'dropoff',
                    'description'   => $key == 0 ? $pu_description : $this_passenger->passenger,
                    'address'       => $key == 0 ? $pickup->address : $this_passenger->doaddress,
                    'latlon'        => $leg->this_latlon,
                    'zone'          => $key == 0 ? $pickup->zone : $this_passenger->dozone,
                    'putime'        => $key == 0 ? $pickup->time : $this_passenger->putime,
                    'dotime'        => $key == 0 ? '' : $arrive,
                    'arrive'        => $arrive < '07:00' ? Carbon::parse($pickup->time)->subMinutes(5)->format('H:i') : $arrive,
                    'depart'        => $depart,
                    'empty_seats'   => $empty_seats,
                    'vehicle'       => $vehicle->id,
                    'legacy'        => $key > 0 && $this_passenger->putime < $pickup->time ? true : false,
                    'distance'      => $key == 0 ? $vehicle->journey_distance : $distance
                ];
            }

            /*
             * Save the route
             */
            if (session()->has("planner.trips.$vehicle->id")) {
                $trips[$vehicle->id] = session("planner.trips.$vehicle->id");
            }
            $trips[$vehicle->id][] = $route;
            session()->put("planner.trips.$vehicle->id", $trips[$vehicle->id]);

            /*
             * report the time the vehicle is free
             */
            $free[$vehicle->id] = end($route)->depart;

            /*
             * this has been disabled. could be enabled provided drop off is at end of route
             * and no other vehicle is set to wait for the target pickup. does not however seem
             * important enough to worry about.
             * Flag vehicle to wait if dropping off at a pickup venue.
             */
//            $vehicle = $this->flagWaitingVehicle($route, $pickups, $pickup->legix, $vehicle);

            /*
             * Update vehicle waiting flags
             * adjusted seats only set for 6 seaters and will be null if not in use
             */
            if ( !empty($vehicle->waiting) ) {
                /*
                 * remove waiting flags from next leg if vehicle full or no longer required
                 */
                if ( $vehicle->available_seats == 0 ||
                    $pickups[$vehicle->waiting_for]->passengers[$vehicle->zoned_for] == 0 ) {

                    $pickups = $this->removePickupVehicle($vehicle, $pickup);
                    $vehicle->waiting = '';
                    $vehicle->waiting_for = null;
                    $vehicle->adjusted_seats = null;

                /*
                 * remove waiting flags from completed waiting groups
                 */
                } elseif ( $vehicle->waiting_for == $pickup->legix ) {
                    $vehicle->adjusted_seats = null;
                    $vehicle->waiting = '';
                    $vehicle->waiting_for = null;
                }
            }

            /*
             * remove adjusted seats from 6 seater on non-grouped pickup
             */
            if ( $vehicle->seats > 4 && isset($vehicle->pickups[$pickup->legix]) && $vehicle->pickups[$pickup->legix]->group == 0 ) {
                $vehicle->adjusted_seats = null;
            }

            /*
             * mark vehicles crossing zones as do-not-disturb
             */
            $vehicle = $this->dndZoneCrossing($vehicle, $pickup, $route);

            /*
             * update fleet log
             */
            Fleet::update($vehicle, $pickup, $route, $pickups);
        }

        session()->put('free', $free);

        return;
    }

    /**
     * Return the ordered drop-off legs
     * prioritises xmurals before home drop-offs & optimises each group of drop-offs
     *
     * @param $vehicle_passengers
     * @param $pickup
     * @return array
     */
    private function dofLegs($vehicle_passengers, $pickup)
    {
        $xm_legs = $home_legs = [];
        $start = $pickup->latlon;

        $xm_locs = collect($vehicle_passengers)->whereIn('dovenue', session('planner.timed_xms'))->pluck('dolatlon')->unique()->values()->all();
        $home_locs = collect($vehicle_passengers)->whereNotIn('dolatlon', $xm_locs)->pluck('dolatlon')->unique()->values()->all();

        if ( count($xm_locs) > 0 ) {
            $locations = count($xm_locs) > 1 ? RouteMappers::distanceOrder($start, $xm_locs) : $xm_locs;
            $destination = array_pop($locations);
            if ( count($locations) == 0 ) {
                $xm_legs = RouteMappers::pointDistance($start, $destination, 'order');
            } else {
                $xm_legs = RouteMappers::legOrder($start, $destination, $locations, 'optimised');
            }

            $start = $destination;
        }

        if ( count($home_locs) > 0 ) {
            $locations = count($home_locs) > 1 ? RouteMappers::distanceOrder($start, $home_locs) : $home_locs;
            $destination = array_pop($locations);
            if ( count($locations) == 0 ) {
                $home_legs = RouteMappers::pointDistance($start, $destination, 'order');
            } else {
                $home_legs = RouteMappers::legOrder($start, $destination, $locations, 'optimised');
            }

            /*
             * the destination of xmural group is the start of home group so it should not be duplicated
             */
            array_pop($xm_legs);
        }

        return array_merge($xm_legs, $home_legs);
    }

    /**
     * Return pickup description for this route
     *
     * @param $ordered_passengers
     * @param $pickup
     * @return string
     */
    private function pickupDescription($ordered_passengers, $pickup)
    {
        $pu_passengers = collect($ordered_passengers)
            ->where('pulatlon', $pickup->latlon)
            ->where('putime', $pickup->time)
            ->pluck('passenger')->all();

        $pu_description = implode(',',$pu_passengers). ' at '.$pickup->venue;

        return $pu_description;
    }

    /**
     * Calculate departure time from pickup
     * adds settings->pudelay to arrival time
     * HB Int delay increased by 5 min due to their queuing system
     *
     * @param object $pickup
     * @param string $arrive
     * @param int $pass
     * @return string
     */
    private function schoolDepartureTime($pickup, $arrive, $pass)
    {
        $ready = Carbon::parse($arrive) > Carbon::parse($pickup->time) ? $arrive : $pickup->time;
        $pass = $pass > 6 ? 6 : $pass;
        $delay = $this->settings->school_pudelay[$pass];

        if ( $pickup->venue == 'Hout Bay International Prim' ) {
            $delay += 5 * 60;
        }

        return Carbon::parse($ready)->addSeconds($delay)->format('H:i');
    }

    /**
     * Remove a full vehicle or an obsolete drop off zone
     * from the grouped pickup second leg
     *
     * @param object $vehicle
     * @param object $pickup
     * @return array
     */
    private function removePickupVehicle($vehicle, $pickup)
    {
        $pickups = session('planner.pickups');

        if ( isset($vehicle->pickups[$pickup->legix]) ) {
            $list = $pickups[$vehicle->pickups[$pickup->legix]->group]->vehicles;
            $vid = $vehicle->id;
            $list = array_filter($list, function ($item) use ($vid) {
                return $item != $vid;
            });
            $pickups[$vehicle->pickups[$pickup->legix]->group]->vehicles = $list;

            session()->put('planner.pickups', $pickups);

            $vehicle->pickups[$pickup->legix]->group = 0;
        }

        return $pickups;
    }

    /**
     * Add do-not-disturb times for vehicles crossing zones
     *
     * @param object $vehicle
     * @param object $pickup
     * @param array $route
     * @return mixed
     */
    private function dndZoneCrossing($vehicle, $pickup, $route)
    {
        if ( $pickup->zone != 'in' || $pickup->zone != $vehicle->zoned_for ) {
            $exists = false;

            foreach ( $vehicle->dnd as $start => $end ) {
                $from = Carbon::parse($start);
                $to = Carbon::parse($end);
                if ( Carbon::parse($pickup->time)->between($from, $to) ) {
                    $exists = true;
                    break;
                }
            }

            if ( !$exists ) {
                $vehicle->dnd[$pickup->time] = end($route)->depart;
            }
        }

        return $vehicle;
    }

    /**
     * Flag vehicle to wait if dropping off at a pickup venue.
     * Pickup time must be within 5 minutes, vehicle must not already be flagged &
     * vehicle must have capacity for pickups.
     *
     * @param $route
     * @param $pickups
     * @param $legix
     * @param $vehicle
     * @return mixed
     */
//    private function flagWaitingVehicle($route, $pickups, $legix, $vehicle)
//    {
//        foreach ($route as $dof) {
//            if ($dof->type == 'dropoff') {
//
//                for ($p = 1; $p < count($pickups) - $legix; $p++) {
//
//                    if ($pickups[$legix + $p]->latlon == $dof->latlon) {
//                        $max = Carbon::parse($pickups[$legix + $p]->time)->addSeconds(300);
//                        $min = Carbon::parse($pickups[$legix + $p]->time)->subSeconds(180);
//
//                        if (Carbon::parse($dof->arrive)->between($min, $max)) {
//                            if ( $dof->empty_seats >= $pickups[$legix + $p]->seats ) {
//                                $vehicle->waiting = $vehicle->waiting > '' ? $vehicle->waiting : $dof->latlon;
//                                break;
//                            }
//                        }
//                    }
//                }
//            }
//        }
//
//        return $vehicle;
//    }
}