<?php
/**
 * Created by PhpStorm.
 * User: Ray
 * Date: 2018/03/22
 * Time: 9:45 AM
 */

/*
    |--------------------------------------------------------------------------
    | Vehicle Log
    |--------------------------------------------------------------------------
    |
    | Initialise and update the vehicle log .
    | The log tracks changes to the vehicle status throughout the build process
    |
    */

namespace App\TripPlanning;


use App\Models\Vehicle;

class VehiclesLog
{
    /**
     * Sets up the vehicle logs at start of morning trips and start of day trips
     * The logs are updated to the latest status after each trip is added to
     * the plan so that the planner has access to each vehicle's current location and status.
     *
     * @param object    $request
     */
    public static function initialise($request)
//    public static function initialise($request, $suburbs)
    {
        session()->forget('allocated');

//        $vlog = Vehicle::where('status', 'active')
//            ->whereIn('id', array_keys($request->day_vehicles))
//            ->orderBy('seats')
//            ->get();
        $vlog = Vehicle::whereIn('id', array_keys($request->day_vehicles))
            ->orderBy('seats')
            ->get();

        foreach ( $vlog as $vehicle ) {
            unset($vehicle->licence);
            $vehicle->driver_id     = $request->day_vehicles[$vehicle->id]['driver'];
            $vehicle->restricted    = $request->day_vehicles[$vehicle->id]['from'].','.$request->day_vehicles[$vehicle->id]['to'];
            $vehicle->time          = $request->day_vehicles[$vehicle->id]['from'];
            $vehicle->status        = 'free';

//            if ( $request->period == 'am' ) {
//                $vehicle->am_destination = self::amDestination($request->day_vehicles, $vehicle, $suburbs);
//            }

            /**
             * For day trips assume that the vehicle is starting from vehicle's home geo
             * because the last morning trip destination is no longer relevant.
             * Should this change, the log could show the last morning drop off as the
             * vehicle's starting geo.
            */
            if ( $request->period == 'day' ) {
                $vehicle->endtime       = $request->day_vehicles[$vehicle->id]['to'];
                $vehicle->pickup        = 'shuttle';
                $vehicle->zone          = 'in';
                $vehicle->waiting       = '';
            }
        }

        session()->put('planner.vlog', $vlog);
    }

    /**
     * Update the vehicle log
     *
     * @param $vehicle
     * @param $pickup
     * @param $route
     */
    public static function update($vehicle, $pickup, $route)
    {
        $vlog = session('planner.vlog');

        foreach ($vlog as $logvehicle) {
            if ($logvehicle->id == $vehicle->id) {
                $logvehicle->time = end($route)->depart;
                $logvehicle->geo = end($route)->latlon;
                $logvehicle->zone = end($route)->zone;
                $logvehicle->pickup = $pickup->type;
                $logvehicle->last_pickup = $pickup->pulatlon;
                $logvehicle->putime = $pickup->putime;
                $logvehicle->status = 'free';
                $logvehicle->waiting = $vehicle->waiting;
                if ( isset($vehicle->do_not_disturb) ) {
                    $logvehicle->do_not_disturb = true;
                } else {
                    unset($logvehicle->do_not_disturb);
                }
                break;
            }
        }
        session()->put('planner.vlog', $vlog);

        return;
    }

    /**
     * Allocate a suburb to this vehicle
     *
     * @param $day_vehicles
     * @param $vehicle
     * @param $suburbs
     * @return string
     */
    private static function amDestination($day_vehicles, $vehicle, $suburbs)
    {
        $am_destination = '';

        foreach ( $suburbs as $suburb ) {
            // hb hack
            if ( $suburb == 'Hout Bay' && in_array(103,array_keys($day_vehicles)) ) {
                if ( $vehicle->id == 103 ) {
                    $am_destination = $suburb;
                    session()->push('allocated', $suburb);
                } else {
                    continue;
                }
            }

            // without hack
            if ( !session()->has('allocated') || !in_array($suburb, session('allocated')) ) {
                if ( $vehicle['from'] < '09:00' ) {
                    $am_destination = $suburb;
                    session()->push('allocated', $suburb);
                    break;
                }
            }
        }

        return $am_destination;
    }
}