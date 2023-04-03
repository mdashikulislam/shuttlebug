<?php
/**
 * Created by PhpStorm.
 * User: Ray
 * Date: 2018/03/30
 * Time: 1:27 PM
 */

/*
 * Day Plan
 * Plan the day trips
 *
 * Planning assumes the fleet is unknown. So vehicles will be created as required.
 * 6 and 4 seater vehicles are created and reserved during preparation for the
 * pickups that require them, to ensure they are available when required.
 *
 * While building the plan, smaller vehicles will be created to handle any overflow.
 *
 * For in-zone pickups different vehicles are allocated to each drop off zone
 * and vehicles are limited to a max of 4 destinations.
 *
 * For out-zone pickups drop off zones & number of destinations are ignored
 * ie vehicles carry max number of passengers.
 */

namespace App\TripPlanning;

use App\Models\TripSettings;
use App\Models\Xmural;
use Illuminate\Support\Facades\DB;

class DayPlan
{
    /**
     * Plan the day routes
     *
     * @param $request
     */
    public function run($request)
    {
        session()->forget('planner');
        session()->put('planner.settings', TripSettings::first());
        session()->put('planner.day_vehicles', $request->day_vehicles);
        session()->put('planner.day_attendants', $request->day_attendants);
        session()->put('planner.hacks', DB::table('trip_hacks')->where('date', $request->date)->get());
        session()->put('planner.timed_xms', Xmural::timeCriticalXmurals());

        /*
         * Prep the bookings data
         */
        $prep = new DayPrep($request);
        $prep->collect();

        /*
         * Build the drop off routes
         */
        $builder = new DayBuild();
        $builder->run();

        /*
         * Wrap up the plan
         */
        $wrapup = new TripWrap();
        $wrapup->dayWrap();

        /*
         * Save data to database
         */
        $db = new TripData();
        $db->dayData($request);

        return;
    }
}