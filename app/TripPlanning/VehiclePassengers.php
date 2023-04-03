<?php
/**
 * Created by PhpStorm.
 * User: Ray
 * Date: 2018/04/03
 * Time: 4:02 PM
 */

/*
 * Vehicle Passengers
 * Allocate passengers to given vehicles
 *
 * passengers at in-zone pickups:
 *      allocated by drop off zone - different vehicles for each zone
 *      allocated by number of destinations - max of 4 destinations on any vehicle
 *      waiting legs will group siblings on vehicles
 *
 * passengers at out-zone pickups:
 *      drop off zones are ignored and max number of passengers are allocated
 *      vehicles are marked as do-not-disturb
 *
 * if a vehicle is marked to wait for the next pickup but is filled at this pickup
 * the waiting flag is removed & the vehicle group is set to 0 so that it is ignored for the
 * grouped pickup.
 *
 * if a vehicle is interrupted for this pickup the passenger manifest will include
 * passengers being carried plus new pickup passengers.
 */

namespace App\TripPlanning;


class VehiclePassengers
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
     * Allocate passengers
     *
     * #todo allocation can be more sophisticated by grouping close drop offs together for same vehicle
     * either in conjunction with siblings or in the absence of siblings
     *
     * @param array  $leg_vehicles
     * @param object $pickup
     * @return array
     */
    public function allocate($leg_vehicles, $pickup)
    {
        $leg_passengers = collect(session('planner.passengers'))->where('puleg', $pickup->legix)->values()->all();

        /*
         * handle pickup zone
         */
        if ( $pickup->zone == 'in' ) {

            foreach ( $leg_vehicles as $vehicle ) {
                $zone = $vehicle->zoned_for;
                $available = !is_null($vehicle->adjusted_seats) ? $vehicle->adjusted_seats : $vehicle->available_seats;

                /*
                 * waiting vehicles must give priority to any siblings from the 1st leg
                 * before loading rest of new passengers
                 */
                $priority_geos = $this->waitingSiblings($vehicle, $pickup);

                foreach ( $leg_passengers as $passenger ) {
                    if ( $available > 0 ) {
                        if ( in_array($passenger->dolatlon, $priority_geos) ) {
                            if ( !isset($passenger->vehicle) ) {
                                collect($leg_passengers)->where('passid', $passenger->passid)->first()->vehicle = $vehicle->id;
                                $passenger->vehicle = $vehicle->id;
                                $vehicle->available_seats -= 1;
                                $available -= 1;
                            }
                        }
                    }
                }

                $sorted_passengers = $this->sortByZoneSiblings($leg_passengers);

                foreach ( $sorted_passengers[$zone] as $passenger ) {
                    if ( $available > 0 ) {
                        if ( $passenger->dozone == $zone ) {
                            if ( !isset($passenger->vehicle) ) {
                                collect($leg_passengers)->where('passid', $passenger->passid)->first()->vehicle = $vehicle->id;
                                $passenger->vehicle = $vehicle->id;
                                $vehicle->available_seats -= 1;
                                $available -= 1;
                            }
                        }
                    } else {
                        break;
                    }
                }
            }

        } else {
            $leg_passengers = $this->sortBySiblings($leg_passengers);

            foreach ( $leg_vehicles as $vehicle ) {
                foreach ( $leg_passengers as $passenger ) {
                    if ( $vehicle->available_seats > 0 ) {
                        if ( !isset($passenger->vehicle) ) {
                            $passenger->vehicle = $vehicle->id;
                            $vehicle->available_seats -= 1;
                        }
                    } else {
                        break;
                    }
                }
            }
        }

        /**
         * Transfer interrupted passengers to this leg.
         */
        $this->transferUndeliveredPassengers($leg_vehicles, $leg_passengers);

        foreach ( $leg_vehicles as $vehicle ) {
            $pass[$vehicle->id] = collect($leg_passengers)->where('vehicle', $vehicle->id)->count();
            $vzone[$vehicle->id] = $vehicle->zoned_for;
        }
        session()->put('pass', $pass);
        session()->put('vzone', $vzone);

        return [$leg_vehicles, $leg_passengers];
    }

    /**
     * Transfer passengers from interrupted leg for busy vehicles
     *
     * @param $leg_vehicles
     * @param $leg_passengers
     */
    private function transferUndeliveredPassengers(&$leg_vehicles, &$leg_passengers)
    {
        foreach ( $leg_vehicles as $vehicle ) {
            $transferred_passengers = [];

            if ( $vehicle->status == 'busy' ) {
                $route      = session("planner.trips.$vehicle->id");
                $last_trip  = end($route);

                foreach ( $last_trip as $ix => $trip_leg ) {
                    if ( isset($trip_leg->interrupt) ) {

                        /*
                         * separate undelivered passengers on the interrupted leg
                         */
                        $delayed_passengers = array_splice($last_trip, $ix+1);

                        /*
                         * update route with just the delivered passengers in the interrupted leg
                         */
                        $route[count($route)-1] = $last_trip;
                        session()->put("planner.trips.$vehicle->id", $route);

                        /*
                         * add undelivered passengers to this leg
                         */
                        foreach ( $delayed_passengers as $legdata ) {
                            /*
                             * given data is a route leg so need to get each passenger from the description field
                             * (more than one if siblings) and add this to vehicle passenger manifest
                             */
                            $transfers = collect(session('planner.passengers'))->where('dolatlon', $legdata->latlon)->all();

                            foreach ( $transfers as $person ) {
                                if ( strpos($legdata->description, $person->passenger) !== false ) {
                                    if ( $legdata->putime == $person->putime ) {
                                        $person->vehicle = $vehicle->id;
                                        $transferred_passengers[] = $person;
                                    }
                                }
                            }
                        }

                        $leg_passengers = array_merge($leg_passengers, $transferred_passengers);
                        break;
                    }
                }
            }
        }

        return;
    }

    /**
     * Sort passengers in sibling order
     *
     * @param array $passengers
     * @return array
     */
    private function sortBySiblings($passengers)
    {
        $latlons = collect($passengers)
            ->groupBy('dolatlon')
            ->all();

        $geos = collect($latlons)->map(function ($item) {
            return collect($item)->count();
        })->toArray();
        arsort($geos);

        foreach ( $geos as $geo => $count ) {
            $extracted = collect($passengers)->where('dolatlon', $geo);
            foreach ( $extracted as $extract ) {
                $sorted[] = clone $extract;
            }
        }

        return $sorted;
    }

    /**
     * Sort passengers by zone in sibling order
     *
     * @param array $passengers
     * @return array
     */
    private function sortByZoneSiblings($passengers)
    {
        $zones = collect($passengers)->pluck('dozone')->unique()->all();

        foreach ( $zones as $zone ) {
            $latlons = collect($passengers)
                ->where('dozone', $zone)
                ->groupBy('dolatlon')
                ->all();

            $geos = collect($latlons)->map(function ($item) {
                return collect($item)->count();
            })->toArray();
            arsort($geos);

            foreach ( $geos as $geo => $count ) {
                $extracted = collect($passengers)->where('dolatlon', $geo);
                foreach ( $extracted as $extract ) {
                    $sorted[$zone][] = clone $extract;
                }
            }
        }

        return $sorted;
    }

    /**
     * Prioritise the selection of waiting siblings
     *
     * @param $vehicle
     * @param $pickup
     * @return array
     */
    private function waitingSiblings($vehicle, $pickup)
    {
        if ( !empty($vehicle->waiting) && $vehicle->waiting_for == $pickup->legix ) {
            $route      = session("planner.trips.$vehicle->id");
            $last_trip  = end($route);

            return collect($last_trip)->where('type', 'dropoff')->pluck('latlon')->unique()->all();
        }

        return [];
    }
}