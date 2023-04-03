<?php
/**
 * Created by PhpStorm.
 * User: Ray
 * Date: 2018/03/21
 * Time: 8:47 AM
 */

/*
    |--------------------------------------------------------------------------
    | Am Plan
    |--------------------------------------------------------------------------
    |
    | Plan morning trips from home to school.
    | The school arrivals tester has been removed and replaced with warnings only.
    |
    | All session variables are arrays of objects.
    |
    */

namespace App\TripPlanning;


class AmPlan
{
    /**
     * Create the trips and return the form data
     *
     * @param $request
     * @return mixed
     */
    public function run($request)
    {
        session()->put('planner.day_vehicles', $request->day_vehicles);
        session()->put('planner.day_attendants', $request->day_attendants);

        /**
         * Prep the bookings data
         */
        $prep = new AmPrep($request);
        $prep->collect();

        /**
         * Build the full morning trips
         */
        $builder = new AmBuild();
        $builder->run();

        /**
         * Wrap up the plan
         */
        $wrapup = new TripWrap();
        $wrapup->amWrap();

        /**
         * Save data to database
         */
        $db = new TripData();
        $db->amData($request);

        return;
    }
}