<?php
/**
 * Created by PhpStorm.
 * User: Ray
 * Date: 2018/02/27
 * Time: 4:13 PM
 */

namespace App\Http\Processors;



class GoogleApi
{
    /*
     * Google api key
     */
    protected function key()
    {
        return 'AIzaSyAX1xKuub_cJqm_icVEDQ_iqe1iESyVXNY';
    }

    /**
     * Return the distance & duration between two geos
     *
     * @param string    $from
     * @param string    $to
     * @return null|object
     */
    public function pointDistance($from, $to)
    {
        $url = 'https://maps.googleapis.com/maps/api/distancematrix/json?origins='.$from.'&destinations='.$to.'&mode=driving&key='.$this->key();

        if (\App::isLocal()) {
            $stream_opts = ["ssl" => ["verify_peer"=>false,"verify_peer_name"=>false,]];
            $google = file_get_contents($url, false, stream_context_create($stream_opts));
        } else {
            $google = file_get_contents($url);
        }
        $matrix = json_decode($google, TRUE);

        if ( $matrix['status'] == "OK" ) {

            $data = (object) [
                'from'      => $from,
                'to'        => $to,
                'distance'  => $matrix['rows'][0]['elements'][0]['distance']['value'],
                'duration'  => $matrix['rows'][0]['elements'][0]['duration']['value']
            ];

            return $data;
        }

        return null;
    }

    /**
     * Return the distance & duration of multiple geos from given start geo
     *
     * @param string    $from
     * @param array     $locations
     * @return null|object
     */
    public function distanceOrder($from, $locations)
    {
        $locations = array_values($locations);
        $to = implode('|', $locations);

        $url = 'https://maps.googleapis.com/maps/api/distancematrix/json?origins='.$from.'&destinations='.$to.'&mode=driving&key='.$this->key();

        if (\App::isLocal()) {
            $stream_opts = ["ssl" => ["verify_peer"=>false,"verify_peer_name"=>false,]];
            $google = file_get_contents($url, false, stream_context_create($stream_opts));
        } else {
            $google = file_get_contents($url);
        }
        $matrix = json_decode($google, TRUE);

        if ( $matrix['status'] == "OK" ) {
            foreach ( $matrix['rows'][0]['elements'] as $key => $val ) {
                $data[$key] = (object) [
                    'from'      => $from,
                    'to'        => $locations[$key],
                    'distance'  => $val['distance']['value'],
                    'duration'  => $val['duration']['value']
                ];
            }

            return $data;
        }

        return null;
    }

    /**
     * Return the locations from start to destination with waypoints
     * with the distance & duration to each next location.
     * $order determines if the route is optimised or used as given.
     *
     * @param string    $start
     * @param string    $destination
     * @param array     $locations
     * @param string    $order
     * @return array|null   array of objects
     */
    public function legOrder($start, $destination, $locations, $order)
    {
        $locations = array_values($locations);
        $stops  = implode('|', $locations);
        $optimise = $order == 'optimised' ? 'true' : 'false';

        $url = 'https://maps.googleapis.com/maps/api/directions/json?origin='.$start.'&destination='.$destination.'&waypoints=optimize:'.$optimise.'|'.$stops.'&key='.$this->key();

        if (\App::isLocal()) {
            $stream_opts = ["ssl" => ["verify_peer"=>false,"verify_peer_name"=>false,]];
            $google = file_get_contents($url, false, stream_context_create($stream_opts));
        } else {
            $google = file_get_contents($url);
        }
        $result = json_decode($google, TRUE);

        if ( $result['status'] == "OK" ) {

            foreach($result['routes'][0]['legs'] as $idx => $leg) {
                $thisloc = $idx == 0 ? $start : $locations[$result['routes'][0]['waypoint_order'][$idx-1]];
                $legorder[] = (object) [
                    'this_latlon'     => $thisloc,
                    'next_distance'   => $leg['distance']['value'],
                    'next_duration'   => $leg['duration']['value']
                ];
            }
            // there is no distance/duration on the destination so just record the location
            $legorder[] = (object) ['this_latlon' => $destination];

            return $legorder;
        }

        return null;

    }

    /**
     * Return the geo coordinates of a given address
     *
     * @param string    $address
     * @return null|string
     */
    public function geocodeAddress($address)
    {
        $url = 'https://maps.googleapis.com/maps/api/geocode/json?address='.urlencode($address).'&key='.$this->key();

        if (\App::isLocal()) {
            $stream_opts = ["ssl" => ["verify_peer"=>false,"verify_peer_name"=>false,]];
            $google = file_get_contents($url, false, stream_context_create($stream_opts));
        } else {
            $google = file_get_contents($url);
        }
        $data = json_decode($google, TRUE);

        if ( $data['status'] == "OK" ) {
            $lat = $data['results'][0]['geometry']['location']['lat'];
            $lon = $data['results'][0]['geometry']['location']['lng'];

            return $lat.','.$lon;
        }

        return null;
    }


}