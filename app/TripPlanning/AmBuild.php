<?php
/**
 * Created by PhpStorm.
 * User: Ray
 * Date: 2018/03/24
 * Time: 5:15 AM
 */

/*
    |--------------------------------------------------------------------------
    | Am Route Builder
    |--------------------------------------------------------------------------
    |
    | Build the full morning trips using the prepared pickup trips and
    | injecting school drop offs where appropriate.
    |
    | Different routes are created for each zone.
    | zone is unique based on combination of pu and do
    | eg. east -> hb is different zone to hb -> east
    |
    | Vehicles or attendants are allocated later in TripData
    |
    */


namespace App\TripPlanning;


use App\Models\TripSettings;
use Carbon\Carbon;

class AmBuild
{
    /**
     * Build the raw morning route
     *
     */
    public function run()
    {
        /**
         * Retrieve prepped data
         */
        $settings   = TripSettings::first();
        $schools    = session("planner.schools");
        $passengers = session("planner.passengers");
        session()->put("planner.routes", []);
        $zones = ['llcf','in','east','east','north','north','south','south'];
        $done = [];

        foreach ( $zones as $zone ) {
            $legs = [];
            if ( $zone == 'llcf' ) {
                $file = 'llcf';
                $done[] = $file;
                if ( collect($passengers)->where('puzone', $zone)->where('dozone', $zone)->count() > 0 ) {
                    $legs = $this->combinedLegs($schools, $passengers);
                }
            } elseif ( $zone == 'in' ) {
                $file = 'inin';
                $done[] = $file;
                if ( collect($passengers)->where('puzone', $zone)->where('dozone', $zone)->count() > 0 ) {
                    $legs = $this->inLegs($schools, $passengers);
                }
            } elseif ( !in_array('in'.$zone, $done) ) {
                $file = 'in' . $zone;
                $done[] = $file;
                if ( collect($passengers)->where('puzone', 'in')->where('dozone', $zone)->count() > 0 ) {
                    $legs = $this->inToZoneLegs($zone, $schools, $passengers);
                }
            } elseif ( !in_array('ex'.$zone, $done) ) {
                $file = 'ex' . $zone;
                $done[] = $file;
                if ( collect($passengers)->where('puzone', $zone)->where('dozone', 'in')->count() > 0 ) {
                    $legs = $this->exZoneLegs($zone, $schools, $passengers);
                }
            }

            $route = [];

            foreach ( $legs as $ix => $leg_routes ) {
                $this_route = [];
                if ( substr($file,2) == 'cf' ) {
                    $putraffic = 300;
                    $dotraffic = 0;
                } elseif ( substr($file,2) == 'north' ) {
                    $putraffic = 600;
                    $dotraffic = 0;
                } elseif (substr($file,2) == 'east') {
                    $putraffic = 2100;
                    $dotraffic = 0;
                }

                foreach ( $leg_routes as $legix => $leg ) {
                    // First leg_route is vehicle departure & has no passengers
                    if ( $ix == 0 && $legix == 0 ) {
                        $this_route[] = (object) [
                            'type'          => 'depart',
                            'description'   => 'office',
                            'latlon'        => $leg->this_latlon,
                            'putime'        => '00:00',
                            'venue'         => 'office',
                            'zone'          => $zone
                        ];

                    // pickup legs
                    } elseif ( $legix > 0 && $legix < count($leg_routes) - 1 ) {
                        $pup_passengers = collect($passengers)->where('hlatlon', $leg->this_latlon)->all();

                        if ( count($pup_passengers) > 0 ) {
                            $this_route[] = (object) [
                                'type'          => 'pickup',
                                'description'   => implode(',', array_column($pup_passengers, 'passenger')),
                                'latlon'        => $leg->this_latlon,
                                'putime'        => collect($pup_passengers)->first()->putime,
                                'venue'         => collect($pup_passengers)->first()->hoadrs ?? '',
                                'distance'      => $leg_routes[$legix - 1]->next_distance,
                                'travel'        => $file != 'inin' ? $leg_routes[$legix - 1]->next_duration + $putraffic : $leg_routes[$legix - 1]->next_duration
                            ];
                        }

                    // drop off leg
                    } elseif ( $legix == count($leg_routes) - 1 ) {
                        // this pup_passengers is sometimes an issue depending on the zone
                        // an error here causes dropoff leg to have no passenger in description
                        if ( substr($file,0,2) == 'in' && $zone != 'in' ) {
                            $pup_passengers = collect($passengers)->where('puzone', 'in')->where('slatlon', $leg->this_latlon)->pluck('passenger')->all();
                        } else {
                            $pup_passengers = collect($passengers)->where('puzone', $zone)->where('slatlon', $leg->this_latlon)->pluck('passenger')->all();
                        }
                        $venue = collect($schools)->where('slatlon', $leg->this_latlon)->first();

                        $this_route[] = (object) [
                            'type'        => 'dropoff',
                            'description' => implode(',', $pup_passengers) . ' at ' . $venue->school,
                            'latlon'      => $leg->this_latlon,
                            'venue'       => $venue->school,
                            'distance'    => $leg_routes[$legix - 1]->next_distance,
                            'travel'      => $file != 'inin' ? $leg_routes[$legix - 1]->next_duration + $dotraffic : $leg_routes[$legix - 1]->next_duration,
                            'arrive'      => Carbon::parse($venue->from)->format('h:i'),
                            'depart'      => Carbon::parse($venue->from)->addSeconds($settings->school_dodelay)->format('h:i'),
                            'from'        => Carbon::parse($venue->from)->format('h:i'),
                            'by'          => Carbon::parse($venue->by)->format('h:i')
                        ];
                    }
                }

                if ( $ix == 0 ) {
                    $this_route = $this->timeFirstRoute($this_route, $settings);
                } else {
                    $depart = collect($route)->last()->depart;
                    $this_route = $this->timeRoute($this_route, $settings, $depart);
                }

                $dotime = collect($this_route)->last()->arrive;
                foreach ( $this_route as $leg ) {
                    if ( $leg->type == 'pickup' ) {
                        $leg->dotime = $dotime;
                    }
                    array_push($route, $leg);
                }
            }

            // route is complete, save for later use
            if ( count($route) > 0 ) {
                session()->put("planner.routes.$file", $route);
            }
        }
    }

    /**
     * Return the legs for the combined zones route
     * combining llandudno and clifton
     *
     * @param $schools
     * @param $passengers
     * @return array
     */
    private function combinedLegs($schools, $passengers)
    {
        /**
         * Llandudno is created as the first route leg if it exists
         */
        $llandudno = collect($schools)->where('school', 'Llandudno Prim')->first();
        if ( !is_null($llandudno) ) {
            $pickups = collect($passengers)
                ->where('school', $llandudno->school)
                ->where('puzone', 'llcf')
                ->pluck('hlatlon')->unique()->all();

            if ( count($pickups) > 0 ) {
                $depart = '-34.026404,18.358739';
                $destination = $llandudno->slatlon;
                $legs[] = RouteMappers::legOrder($depart, $destination, $pickups, 'optimised');
            }
        }

        $zone_schools = collect($schools)->where('school', '!=', 'Llandudno Prim')->where('dozone', 'in');
        $manifest = [];

        if ( count($zone_schools) > 0 ) {
            foreach ( $zone_schools as $school ) {
                $pickups = collect($passengers)
                    ->where('school', $school->school)
                    ->where('puzone', 'llcf')
                    ->whereNotIn('hlatlon', $manifest)
                    ->pluck('hlatlon')->unique()->all();

                if ( count($pickups) > 0 ) {
                    $depart = isset($legs) ? collect(end($legs))->last()->this_latlon : '-34.026404,18.358739';
                    $destination = $school->slatlon;
                    $legs[] = RouteMappers::legOrder($depart, $destination, $pickups, 'optimised');
                    $manifest = array_merge($manifest, $pickups);
                }
            }
        }

        return $legs;
    }

    /**
     * Return the legs for in-zone route
     *
     * @param $schools
     * @param $passengers
     * @return array
     */
    private function inLegs($schools, $passengers)
    {
        /**
         * Llandudno is created as the first route leg if it exists
         * picks up all passengers destined for Llandudno (including any siblings - which may be going elsewhere)
         */
        $llandudno = collect($schools)->where('school', 'Llandudno Prim')->first();
        if ( !is_null($llandudno) ) {
            $pickups = collect($passengers)
                ->where('school', $llandudno->school)
                ->where('puzone', 'in')
                ->pluck('hlatlon')->unique()->all();

            if ( count($pickups) > 0 ) {
                $depart = '-34.026404,18.358739';
                $destination = $llandudno->slatlon;
                $legs[] = RouteMappers::legOrder($depart, $destination, $pickups, 'optimised');
            }

            // collect pickups in this leg going to hb schools - to be added to hb dropoffs
            $ll_legs = collect($legs[0])->pluck('this_latlon')->all();
            $ll_pickups = collect($passengers)
                ->where('school', '!=', $llandudno->school)
                ->where('puzone', 'in')
                ->whereIn('hlatlon', $ll_legs)
                ->pluck('school', 'school')->unique()->all();
        }

        /**
         * Hout Bay route legs are ordered by school 'by' time and each leg includes departure and dropoff
         * nb. already picked up siblings of Llandudno passengers - only need dropoff leg
         */
        $hb_schools = collect($schools)->where('school', '!=', 'Llandudno Prim')->where('dozone', 'in');
        $manifest = isset($pickups) ? $pickups : [];

        if ( count($hb_schools) > 0 ) {
            foreach ( $hb_schools as $school ) {
                $pickups = collect($passengers)
                    ->where('school', $school->school)
                    ->where('puzone', 'in')
                    ->whereNotIn('hlatlon', $manifest)
                    ->pluck('hlatlon')->unique()->all();

                // add each sibling from Llandudno trip going to this school
                if ( isset($ll_pickups[$school->school]) ) {
                    $pickups = $pickups + [collect($legs[0])->last()->this_latlon];
                }

                if ( count($pickups) > 0 ) {
                    $depart = isset($legs) ? collect(end($legs))->last()->this_latlon : '-34.026404,18.358739';
                    $destination = $school->slatlon;
                    $legs[] = RouteMappers::legOrder($depart, $destination, $pickups, 'optimised');
                    $manifest = array_merge($manifest, $pickups);
                }
            }
        }

        return $legs ?? [];
    }

    /**
     * Return the legs for trips from Hout Bay into the given zone
     *
     * @todo this should collect Hout Bay and {zone} children going to all {zone} schools
     * @param $zone
     * @param $schools
     * @param $passengers
     * @return array
     */
    private function inToZoneLegs($zone, $schools, $passengers)
    {
        $zone_schools = collect($schools)->where('dozone', $zone);
        $manifest = $legs = [];

        if ( count($zone_schools) > 0 ) {
            foreach ( $zone_schools as $school ) {
                $pickups = collect($passengers)
                    ->where('school', $school->school)
                    ->where('puzone', 'in')
                    ->whereNotIn('hlatlon', $manifest)
                    ->pluck('hlatlon')->unique()->all();

                if ( count($pickups) > 0 ) {
                    $depart = count($legs) > 0 ? collect(end($legs))->last()->this_latlon : '-34.026404,18.358739';
                    $destination = $school->slatlon;
                    $legs[] = RouteMappers::legOrder($depart, $destination, $pickups, 'optimised');
                    $manifest = array_merge($manifest, $pickups);
                }
            }
        }

        return $legs;
    }

    /**
     * Return the legs for trips from the given zone to Hout Bay
     *
     * @param $zone
     * @param $schools
     * @param $passengers
     * @return array
     */
    private function exZoneLegs($zone, $schools, $passengers)
    {
        $zone_schools = collect($schools)->where('dozone', 'in');
        $manifest = $legs = [];

        if ( count($zone_schools) > 0 ) {
            foreach ( $zone_schools as $school ) {
                $pickups = collect($passengers)
                    ->where('school', $school->school)
                    ->where('puzone', $zone)
                    ->whereNotIn('hlatlon', $manifest)
                    ->pluck('hlatlon')->unique()->all();

                if ( count($pickups) > 0 ) {
                    $depart = count($legs) > 0 ? collect(end($legs))->last()->this_latlon : '-34.026404,18.358739';
                    $destination = $school->slatlon;
                    $legs[] = RouteMappers::legOrder($depart, $destination, $pickups, 'optimised');
                    $manifest = array_merge($manifest, $pickups);
                }
            }
        }

        return $legs;
    }

    /**
     * Add times to first route legs
     *
     * @param $this_route
     * @param $settings
     * @return mixed
     */
    private function timeFirstRoute($this_route, $settings)
    {
        $route = array_reverse($this_route);

        foreach ( $route as $ix => $leg ) {
            if ( $leg->type != 'dropoff' ) {
                $depart = Carbon::parse($route[$ix - 1]->arrive)->subSeconds($route[$ix - 1]->travel + $settings->home_delay);
                $leg->arrive = $depart->copy()->subSeconds($settings->home_delay)->format('h:i');
                $leg->depart = $depart->copy()->format('h:i');
                if ( $leg->type == 'pickup' ) {
                    $leg->putime = $depart->copy()->subSeconds($settings->home_delay)->format('h:i');
                }
            }
        }

        return array_reverse($route);
    }

    /**
     * Add times to rest of route legs
     *
     * @param $this_route
     * @param $settings
     * @param $depart
     * @return mixed
     */
    private function timeRoute($this_route, $settings, $depart)
    {
        foreach ( $this_route as $ix => $leg ) {
            if ( $ix == 0 ) {
                $arrive = Carbon::parse($depart)->addSeconds($leg->travel);
            } else {
                $arrive = Carbon::parse($this_route[$ix - 1]->depart)->addSeconds($leg->travel);
            }
            $leg->arrive = $arrive->copy()->format('h:i');
            $leg->depart = $leg->type == 'pickup' ?
                $arrive->copy()->addSeconds($settings->home_delay)->format('h:i') :
                $arrive->copy()->addSeconds($settings->school_dodelay)->format('h:i');
            if ( $leg->type == 'pickup' ) {
                $leg->putime = $arrive->copy()->format('h:i');
            }
        }

        return $this_route;
    }
}
