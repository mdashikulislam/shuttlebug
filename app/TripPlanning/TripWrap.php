<?php
/**
 * Created by PhpStorm.
 * User: Ray
 * Date: 2018/03/27
 * Time: 9:12 AM
 */

/*
 * Trip Wrap up
 * Final adjustments and route clean up
 *
 */

namespace App\TripPlanning;


use Carbon\Carbon;

class TripWrap
{
    /**
     * Morning trips
     *  Adjust route times to best fit
     *
     */
    public function amWrap()
    {
        $routes = session("planner.routes");

        foreach ( $routes as $zone => $route ) {
            $buffers = [];

            /**
             * The route is built to reach the first school at opening time
             * to reduce the chances of arriving late at the rest of schools.
             * This may result in the entire trip being unnecessarily early so
             * try to move the entire timeline to a best-fit.
             */
            foreach ( $route as $key => $leg ) {
                if ( $leg->type == 'dropoff' ) {
                    $buffers[] = Carbon::parse($leg->by)->timestamp - Carbon::parse($leg->arrive)->timestamp;
                }
            }

            $max_buffer = min($buffers);

            // limit the adjustment to arrive at least 10 min before school start time
            $max_buffer = $max_buffer > 600 ? $max_buffer - 600 : $max_buffer;

            // move timeline
            foreach ( $route as $leg ) {
                $leg->arrive = Carbon::parse($leg->arrive)->addSeconds($max_buffer)->format('H:i');
                $leg->depart = Carbon::parse($leg->depart)->addSeconds($max_buffer)->format('H:i');
                if ( $leg->type == 'pickup' ) {
                    $leg->putime = Carbon::parse($leg->putime)->addSeconds($max_buffer)->format('H:i');
                    $leg->dotime = Carbon::parse($leg->dotime)->addSeconds($max_buffer)->format('H:i');
                }
            }

            session()->put("planner.routes.$zone", $route);
        }

        return;
    }

    /**
     * Clean up interrupt flags and create warnings & revise vehicle seats
     */
    public function dayWrap()
    {
        $settings = session('planner.settings');
        $pickups = session('planner.pickups');

        foreach ( Fleet::list() as $vehicle ) {
            if ( session()->has("planner.trips.$vehicle->id") ) {
                $trips = session("planner.trips.$vehicle->id");

                foreach ( $trips as $route ) {

                    foreach ( $route as $ix => $leg ) {
                        // remove unused interrupt flags
                        if ( isset($leg->interrupt) ) {
                            if ( $leg->empty_seats == $vehicle->seats ) {
                                unset($leg->interrupt);
                                unset($leg->duration);
                            } elseif ( $ix < count($route) - 1 ) {
                                unset($leg->interrupt);
                                unset($leg->duration);
                            }
                        }

                        // warnings for late arrivals
                        $arrive = Carbon::parse($leg->arrive);
                        $due    = Carbon::parse($leg->putime);
                        if ( $ix == 0 && $arrive > $due->copy()->addSeconds($settings->buffer) ) {
                            $venue = collect($pickups)->where('latlon', $route[0]->latlon)->first()->venue;
                            $warnings[] = (object) [
                                'warning' => $vehicle->id . ' @ ' . $venue . ' @ ' .
                                    $leg->putime . ' is ' . $arrive->diffInMinutes($due) . ' min late',
                                'vehicle' => $leg->vehicle,
                                'loc'     => $venue,
                                'putime'  => $leg->putime,
                                'time'    => $arrive->diffInMinutes($due)
                            ];
                        }
                    }
                }

                session()->put("planner.trips.$vehicle->id", $trips);
            }
        }

        if ( isset($warnings) ) {
            session()->put('planner.warnings', $warnings);
        }

        return;
    }
}