<?php
/**
 * Created by PhpStorm.
 * User: Ray
 * Date: 2018/03/21
 * Time: 7:43 AM
 */

/*
    |--------------------------------------------------------------------------
    | Trip Builder
    |--------------------------------------------------------------------------
    |
    | This is the gateway to the trip building process.
    | The builder hands off the process to the requested period process,
    | cleans up the session and returns the plan to the controller.
    |
    */

namespace App\TripPlanning;


class TripBuilder
{
    /**
     * Run the requested planner
     *
     * @param $request
     * @return mixed
     */
    public function handle($request)
    {
        if ( $request->period == 'am' ) {
            $plan = new AmPlan();
            $result = $plan->run($request);

            // has error
            if ( substr($result, 0, 5) == 'error' ) {
                return $result;
            }

        } else {
            $plan = new DayPlan();
            $plan->run($request);
        }

        return 'ok';
    }
}