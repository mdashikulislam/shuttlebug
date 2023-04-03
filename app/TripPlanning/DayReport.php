<?php
/**
 * Created by PhpStorm.
 * User: Ray
 * Date: 2018/04/02
 * Time: 7:11 AM
 */

/*
 * Day Report
 * adds analytics to report for day trips
 *
 * the report allows for evaluation of the processes on the trip plan page
 */

namespace App\TripPlanning;


class DayReport
{
    /**
     * Add entry to leg report
     *
     * @param $entry
     * @param $data
     */
    public function add($entry, $data)
    {
        $report = session('planner.legreport') ?? [];

        /*
         * pickup
         */
        if ( $entry == 'pickup' ) {
            $report['pickup'] = [
                'leg'           => $data['leg'],
                'time'          => $data['pickup']->time,
                'venue'         => $data['pickup']->venue,
                'passengers'    => $data['pickup']->passengers,
                'zone'          => $data['pickup']->zone
            ];
        }

        /*
         * available vehicles
         */
        if ( $entry == 'available' ) {
            foreach ( $data as $vehicle ) {
                $report['available'][$vehicle->id] = $vehicle->available_seats.'~'.$vehicle->available_by;
            }
        }

        /*
         * vehicle ranking
         */
        if ( $entry == 'ranking' ) {
            $ranking = session('ranking');
            if ( count($ranking ) > 0 ) {
                ksort($ranking);
                $report['ranking'] = $ranking;
            }
            $report['criteria'][] = session('flash');
            session()->forget('flash');
            session()->forget('ranking');
        }

        /*
         * vehicle passengers
         */
        if ( $entry == 'pass' ) {
            $pass = session('pass');
            ksort($pass);
            $report['pass'] = $pass;
            session()->forget('pass');
        }

        /*
         * vehicle zone
         */
        if ( $entry == 'vzone' ) {
            $vzone = session('vzone');
            ksort($vzone);
            $report['vzone'] = $vzone;
            session()->forget('vzone');
        }

        /*
         * trip completed time
         */
        if ( $entry == 'free' ) {
            $free = session('free');
            ksort($free);
            $report['free'] = $free;
            session()->forget('free');
        }

        session()->put('planner.legreport', $report);

        return;
    }

    /**
     * Move leg report to day report
     */
    public function move()
    {
        $reports = session('planner.report') ?? [];
        $reports[] = session('planner.legreport');
        session()->put('planner.report', $reports);
        session()->forget('planner.legreport');

        return;
    }

    /**
     * When a fatal error is encountered add it to the report
     * currently not in use
     *
     * @param $entry
     */
    public function error($entry)
    {
        $report = session('planner.legreport') ?? [];

        if ( $entry == 'available' ) {
            $report['error'] = 'No vehicles available for the pickup';
        }
        if ( $entry == 'capacity' ) {
            $report['error'] = 'Not enough seats available for the pickup';
        }
        if ( $entry == 'leg' ) {
            $report['error'] = 'No vehicles selected for the pickup';
        }
        if ( $entry == 'leg_capacity' ) {
            $report['error'] = 'Not enough seats selected for the pickup';
        }

        session()->put('planner.legreport', $report);

        $reports = session('planner.report') ?? [];
        $reports[] = session('planner.legreport');
        session()->put('planner.report', $reports);
        session()->forget('planner.legreport');

        return;
    }
}