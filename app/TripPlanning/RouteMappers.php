<?php
/**
 * Created by PhpStorm.
 * User: Ray
 * Date: 2018/03/21
 * Time: 3:45 PM
 */

/*
    |--------------------------------------------------------------------------
    | Route Mappers
    |--------------------------------------------------------------------------
    |
    | Controls all mapping related functions such as distances and routes.
    | Will find the requested data in the map distance table and if not found
    | will call for the data from google using the GoogleApi and add it to the
    | table.
    |
    | Data is always returned as an array of objects.
    |
    | The purpose is to reduce google calls (time consuming) by saving to db.
    |
    */

namespace App\TripPlanning;

use App\Http\Processors\GoogleApi;
use Illuminate\Support\Facades\DB;

class RouteMappers
{

    /**
     * Return the driving distance and duration between two geos
     * This is used instead of legOrder when there is a single location
     * in which case the data is formatted as expected from legOrder.
     * This call is identified by the use of $order
     *
     * @param string    $from
     * @param string    $to
     * @param string|null    $order
     * @return mixed
     */
    public static function pointDistance($from, $to, $order = null)
    {
        if ( $from == $to ) {
            $data = (object) [
                'from'      => $from,
                'to'        => $to,
                'distance'  => 0,
                'duration'  => 0
            ];
        } else {
            $data = DB::table('map_distances')
                ->where([
                    ['from', $from],
                    ['to', $to]])
                ->orWhere([
                    ['from', $to],
                    ['to', $from]])
                ->first();
        }

        if ( is_null($data) ) {
            $google = new GoogleApi();
            $data = $google->pointDistance($from, $to);

            if ( !is_null($data) ) {
                DB::table('map_distances')->insert([
                    'from'      => $data->from,
                    'to'        => $data->to,
                    'distance'  => $data->distance,
                    'duration'  => $data->duration
                ]);
            } else {
                dump('pointDistance: GoogleApi problem');
                dd('from = '.$from.' to = '.$to);
            }
        }

        // format data for legOrder call
        if ( !is_null($order) ) {
            $data = [
                0 => (object) [
                    'this_latlon'   => $from,
                    'next_distance' => $data->distance,
                    'next_duration' => $data->duration
                ],
                1 => (object) [
                    'this_latlon'   => $to
                ]
            ];
        }

        return $data;
    }

    /**
     * Returns the given locations in the order of closest to furthest from the given start.
     * While this only returns an array of locations it will collect distances and durations
     * from google to place into map_distances for future use.
     *
     * @param string    $from
     * @param array     $locations
     * @return array|null
     */
    public static function distanceOrder($from, $locations)
    {
        $db = DB::table('map_distances')
            ->where('from', $from)
            ->whereIn('to', $locations)
            ->orderBy('distance')
            ->get();

        if ( is_null($db) || count($db) != count($locations) ) {
            $google = new GoogleApi();
            $data = $google->distanceOrder($from, $locations);

            if ( !is_null($data) ) {
                foreach ( $data as $stats ) {
                    if ( is_null($db->where('to', $stats->to)->where('from', $stats->from)->first()) ) {
                        DB::table('map_distances')->insert([
                            'from'     => $stats->from,
                            'to'       => $stats->to,
                            'distance' => $stats->distance,
                            'duration' => $stats->duration
                        ]);
                    }
                }

                usort($data, function ($item1, $item2) {
                    return $item1->distance <=> $item2->distance;
                });

                return array_column($data, 'to');
            } else {
                dump('distanceOrder: GoogleApi problem');
                dump('from = '.$from);
                dump('locations = ');
                dd($locations);
            }
        }

        return array_column($db->toArray(), 'to');
    }

    /**
     * Returns the closest or furthest destination from the start to multiple destinations
     * While this only returns the requested destination it will collect all distances and durations
     * from google to place into map_distances for future use.
     *
     * @param string    $from
     * @param array     $locations
     * @param string    $option
     * @return mixed
     */
    public static function multipleDistances($from, $locations, $option)
    {
        $db = DB::table('map_distances')
            ->where('from', $from)
            ->whereIn('to', $locations)
            ->orderBy('distance')
            ->get();

        if ( is_null($db) || count($db) != count($locations) ) {
            $google = new GoogleApi();
            $data = $google->distanceOrder($from, $locations);

            if ( !is_null($data) ) {
                foreach ( $data as $stats ) {
                    if ( is_null($db->where('to', $stats->to)->first()) ) {
                        DB::table('map_distances')->insert([
                            'from'     => $from,
                            'to'       => $stats->to,
                            'distance' => $stats->distance,
                            'duration' => $stats->duration
                        ]);
                    }
                }

                if ( count($data) > 1 ) {
                    usort($data, function ($item1, $item2) {
                        return $item1->distance <=> $item2->distance;
                    });
                }

                return $option == 'closest' ? collect($data)->first() : collect($data)->last();
            } else {
                dump('multipleDistances: GoogleApi problem');
                dump('from = '.$from);
                dump('locations = ');dump($locations);
                dd('option = '.$option);
            }
        }

        return $option == 'closest' ? collect($db)->first() : collect($db)->last();
    }

    /**
     * Returns the given locations with travel distance and duration data
     *
     * @param string    $start
     * @param string|array    $destination
     * @param array     $locations
     * @param string    $order
     * @return array|null
     */
    public static function legOrder($start, $destination, $locations, $order)
    {
        $destination = is_array($destination) ? $destination[0] : $destination;

        $google = new GoogleApi();
        $data = $google->legOrder($start, $destination, $locations, $order);

        if ( is_null($data) ) {
            dump('legOrder: GoogleApi problem');
            dump('start = '.$start);
            dump('destination = '.$destination);
            dump('locations:');dump($locations);
            dd('order = '.$order);
        }

        return $data;
    }
}