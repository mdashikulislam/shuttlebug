<?php
/**
 * Created by PhpStorm.
 * User: Ray
 * Date: 2018/04/02
 * Time: 9:34 AM
 */

/*
 * Vehicles Leg
 * Selects the vehicles to use for this pickup from the available vehicles
 *
 * ranks vehicles according to suitability based on various criteria
 * will override ranking for hacks (manual allocations)
 * flags vehicles to wait for grouped pickups and removes obsolete flags
 * removes interrupt flags from unused busy available vehicles
 *
 */

namespace App\TripPlanning;


use Carbon\Carbon;

class VehiclesLeg
{
    /**
     * VehiclesLeg constructor.
     *
     */
    public function __construct()
    {
        $this->settings = session('planner.settings');
    }

    /** Select the vehicles for this pickup
     *
     * @todo if more than 1 6 seater consider using 2nd only when essential so Audi is not over-used
     * @todo could reduce vehicles by allowing interrupts more often
     * @todo penalise reserved vehicles for out-zone pickups if other vehicles available
     * @param object $pickup
     * @param array  $available_vehicles
     * @param array  $pickups
     * @return array
     */
    public function select($pickup, $available_vehicles, $pickups)
    {
        /*
         * Initialise working variables
         */
        $capacity           = $pickup->passengers;
        $filter             = 'ranking';
        $leg_vehicles       = [];
        $used               = [];
        $carrying_sibling   = $this->vehicleWithSibling($available_vehicles, $pickup);

        /*
         * Rank vehicles
         */
        $available_vehicles = $this->rankVehicles($available_vehicles, $carrying_sibling, $pickup);

        /*
         * Apply the ranking
         * sort vehicles desc by rank & asc by id with equal rank
         */
        if ( $filter == 'ranking' ) {
            usort($available_vehicles, function ($a, $b) {
                if ( $a->rank == $b->rank ) {
                    return ($a->id < $b->id) ? - 1 : 1;
                }
                return ($a->rank > $b->rank) ? - 1 : 1;
            });
        }
        $flash = $filter;

        /*
         * Apply requested hack for this pickup in preference to anything else
         * @todo evaluate if this is still possible and if so, capacity must be fixed by zone
         */
        if ( $vehicle = $this->applyHacks($available_vehicles, $pickup) ) {

            if ( $pickup->zone == 'in' ) {
                foreach ( $capacity as $zone => $count ) {
                    if ( $count > 0 && $vehicle->seats >= $count ) {
                        $vehicle->zoned_for = $zone;
                        $leg_vehicles[] = $vehicle;
                        $used[] = $vehicle->id;
                        $capacity[$zone] -= !is_null($vehicle->adjusted_seats) ? $vehicle->adjusted_seats : $vehicle->available_seats;
                        $flash = 'vehicle changed';
                        break;
                    }
                }
            } else {
                if ( array_sum($capacity) > 0 && $vehicle->seats >= array_sum($capacity) ) {
                    $vehicle->zoned_for = null;
                    $leg_vehicles[] = $vehicle;
                    $used[] = $vehicle->id;
                    $capacity['in'] -= $vehicle->available_seats;
                    $flash = 'vehicle changed';
                }
            }
        }

        /*
         * Select the leg vehicles
         * in-zone pickups require vehicles per drop off zone
         * out-zone pickups ignore zone & carry all passengers
         */
        if ( $pickup->zone == 'in' ) {
            foreach ( $capacity as $zone => $count ) {
                if ( $count > 0 ) {
                    foreach ( $available_vehicles as $vehicle ) {
                        if ( !in_array($vehicle->id, $used) && $capacity[$zone] > 0 ) {
                            $vehicle->zoned_for = $zone;
                            $leg_vehicles[] = $vehicle;
                            $used[] = $vehicle->id;
                            $capacity[$zone] -= !is_null($vehicle->adjusted_seats) ? $vehicle->adjusted_seats : $vehicle->available_seats;
                        }
                    }
                }
            }

        } else {
            foreach ( $available_vehicles as $vehicle ) {
                if ( array_sum($capacity) > 0 && !in_array($vehicle->id, $used) ) {
                    $vehicle->zoned_for = null;
                    $leg_vehicles[] = $vehicle;
                    $used[] = $vehicle->id;
                    $capacity['in'] -= $vehicle->available_seats;
                }
            }
        }

        /*
         * Wrap up vehicle selection
         */

        /*
         * flag vehicle to wait for grouped pickups
         * at this stage we don't know which passengers are on each vehicle
         * so all vehicles at this pickup will be marked to wait
         * and removed from waiting if full or not applicable in VehicleRoute
         */
        list($waiting_seats, $waiting_geo, $waiting_leg) = $this->getWaitingDetails($pickups, $pickup);

        if ( $waiting_seats > 0 ) {
            foreach ( $leg_vehicles as $vehicle ) {
                $vehicle->waiting = $waiting_geo;
                $vehicle->waiting_for = $waiting_leg;
                $vehicle->dnd[$pickup->time] = Carbon::parse($pickup->time)->addMinutes(15)->format('H:i');
            }
        }

        // record ranking
        foreach ( $available_vehicles as $vehicle ) {
            $ranking[$vehicle->id] = $vehicle->rankdesc.' '.$vehicle->rank;
        }

        /*
         * Remove interrupt flags from the routes of vehicles that were marked
         * as available but not used.
         * (Their last route would have been modified with an interrupt flag).
         */
        foreach ( $available_vehicles as $vehicle ) {
            if ( $vehicle->status == 'busy' ) {
                if ( !in_array($vehicle->id, collect($leg_vehicles)->pluck('id')->all()) ) {
                    $route = session("planner.trips.$vehicle->id");
                    $legs = end($route);
                    foreach ( $legs as $leg ) {
                        unset($leg->interrupt);
                        unset($leg->duration);
                    }
                    session()->put("planner.trips.$vehicle->id", $route);
                }
            }
        }

        session()->put('flash', $flash);
        session()->put('ranking', $ranking);

        return $leg_vehicles;
    }

    /**
     * Rank the available vehicles
     *
     * @param array  $available_vehicles
     * @param string $carrying_sibling
     * @param object $pickup
     * @return mixed
     */
    private function rankVehicles($available_vehicles, $carrying_sibling, $pickup)
    {
        foreach ( $available_vehicles as $vehicle ) {
            $rank = 0;
            $rankdesc = '';

            /*
             * waiting
             * vehicle is set to wait at this venue
             */
            if ( $vehicle->waiting == $pickup->latlon ) {
                $rank += 90;
                $rankdesc .= 'waiting(90),';
            }

            /*
             * reserved
             * vehicle is reserved for this pickup
             */
            elseif ( in_array($vehicle->id, $pickup->vehicles) ) {
                $rank += 90;
                $rankdesc .= 'reserved(90),';
            }

            else {
                /*
                 * free
                 */
                $rank += $vehicle->status == 'free' ? 35 : 0;
                $rankdesc .= $vehicle->status == 'free' ? 'free(35),' : '';

                /*
                 * preferred
                 * prefer reserved vehicles to overflow vehicles
                 * prefer late vehicle
                 */
                if ( count($vehicle->pickups) > 0 ) {
                    $rank += $vehicle->primary ? 30 : 25;
                    $rankdesc .= $vehicle->primary ? 'prim(30),' : 'pref(25),';
                } elseif ( $pickup->time >= '17:30' ) {
                    $rank += $this->settings->pref_pm_vehicle == 'hh' && $vehicle->id != 102 ? 40 : 0;
                    $rankdesc .= $this->settings->pref_pm_vehicle == 'hh' && $vehicle->id != 102 ? 'pref(40),' : '';
                }

                /*
                 * carrying sibling
                 */
                $rank += $vehicle->status == 'busy' && $vehicle->id == $carrying_sibling ? 20 : 0;
                $rankdesc .= $vehicle->status == 'busy' && $vehicle->id == $carrying_sibling ? 'sib(20),' : '';

                /*
                 * on time
                 * superfluous. if not on-time will be busy & lose 'free' ranking.
                 */
//                $cond = Carbon::parse($vehicle->available_by) <= Carbon::parse($pickup->time)->addSeconds($this->settings->buffer) ? true : false;
//                $rank += $cond ? 15 : 0;
//                $rank += strtotime($vehicle->available_by) <= strtotime($pickup->time) ? 15 : 0;
//                $rankdesc .= $cond ? 'time(15),' : '';
//                $rankdesc .= strtotime($vehicle->available_by) <= strtotime($pickup->time) ? 'time(15),' : '';
            }

            $vehicle->rank = $rank;
            $vehicle->rankdesc = $rankdesc;
        }

        /*
         * closest
         * don't boost 3 seater vehicles
         */
        usort($available_vehicles, function($a, $b) { return ($a->available_in < $b->available_in) ? -1 : 1; });
        $vehicle = $available_vehicles[0];
        $vehicle->rank += $vehicle->seats > 3 ? 5 : 0;
        $vehicle->rankdesc .= $vehicle->seats > 3 ? 'close(5),' : '';

        return $available_vehicles;
    }

    /**
     * Return venue details of a pickup within the wait period
     *
     * @param array  $pickups
     * @param object $pickup
     * @return array
     */
    private function getWaitingDetails($pickups, $pickup)
    {
        $waiting_seats = $waiting_geo = $waiting_leg = '';

        for ( $p = 1; $p < count($pickups) - $pickup->legix; $p ++ ) {
            if ( Carbon::parse($pickups[$pickup->legix + $p]->time) <= Carbon::parse($pickup->time)->addSeconds($this->settings->vehicle_wait) &&
                $pickups[$pickup->legix + $p]->latlon == $pickup->latlon ) {

                $waiting_seats = $pickups[$pickup->legix + $p]->passengers;
                $waiting_geo = $pickups[$pickup->legix + $p]->latlon;
                $waiting_leg = $pickup->legix + $p;
                break;
            }
        }

        return [$waiting_seats, $waiting_geo, $waiting_leg];
    }

    /**
     * If a busy vehicle is carrying a sibling of a passenger at this pickup
     * flag the vehicle for higher ranking.
     *
     * @param array  $available_vehicles
     * @param object $pickup
     * @return int
     */
    private function vehicleWithSibling($available_vehicles, $pickup)
    {
        $carrying_sibling = null;

        foreach ( $available_vehicles as $vehicle ) {
            if ( $vehicle->status == 'busy' ) {
                $route = session("planner.trips.$vehicle->id");
                foreach ( end($route) as $dof ) {
                    $carrying[] = $dof->latlon;
                }

                $collecting = collect(session('planner.passengers'))->where('puleg', $pickup->legix)->pluck('dolatlon')->all();

                foreach ( $collecting as $pup ) {
                    if ( in_array($pup, $carrying) ) {
                        if ( $vehicle->available_seats >= array_sum($pickup->passengers) ) {
                            $carrying_sibling = $vehicle->id;
                            break 2;
                        }
                    }
                }
            }
        }

        return $carrying_sibling;
    }

    /**
     * Return the vehicle that has a hack for this pickup
     *
     * @param $available_vehicles
     * @param $pickup
     * @return mixed
     */
    private function applyHacks($available_vehicles, $pickup)
    {
        foreach ( session('planner.hacks') as $hack ) {
            if ( $hack->putime == $pickup->time && strpos($pickup->venue, $hack->pushort) !== false ) {
                foreach ( $available_vehicles as $vehicle ) {
                    if ( $vehicle->id == $hack->vehicle ) {
                        return $vehicle;
                    }
                }
            }
        }

        return false;
    }
}