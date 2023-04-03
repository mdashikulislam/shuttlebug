<?php
/**
 * Created by PhpStorm.
 * User: Ray
 * Date: 2018/12/03
 * Time: 4:24 PM
 */

namespace App\TripPlanning;

/*
 * ReserveVehicles
 * Creates & reserves 5/6 & 4 seaters where required for individual & grouped pickups.
 *
 * Allocates vehicles to pickups based on passenger drop off zones
 * 5/6 seaters are processed first, followed by 4 seaters for the overflow passengers from 6 seaters,
 * and finally to remaining 4 passenger pickups.
 *
 * 5/6 seaters may be limited to a max of 4 destinations (according to trip_settings, 5 or 6 passengers) in which case if
 * there are no siblings 4 seaters will be used instead of a 6 seater.
 *
 * The first 4 seater reserved is regarded as the primary vehicle (innova)
 * and preferred before any other 4 seaters so that the innova trips can be maximised.
 */

use Carbon\Carbon;
use Illuminate\Support\Arr;

class ReserveVehicles
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
     * Reserve vehicles for pickups requiring 6 seater vehicles
     *
     * @param array $pickups
     * @param array $passengers
     * @return array
     */
    public function sixSeaters($pickups, $passengers)
    {
        session()->forget('planner.overflow');

        /*
         * extract all pickup legs & groups with more than 4 passengers
         */
        $extract = $this->sixSeaterPickups($pickups);

        /*
         * process pickups
         */
        $pickups = $this->processVehicles($extract, $pickups, $passengers, 6);

        return $pickups;
    }

    /**
     * Reserve vehicles for pickups requiring 4 seater vehicles
     *
     * @param array $pickups
     * @param array $passengers
     * @param object $request
     * @return array
     */
    public function fourSeaters($pickups, $passengers, $request)
    {
        /*
         * first reserve vehicles that may have been saved for
         * 6 seater overflow passengers
         */
        $pickups = $this->overflowPassengers($pickups);
        session()->forget('planner.overflow');

        /*
         * extract remaining pickup legs & groups with 4 passengers
         */
        $extract = $this->fourSeaterPickups($pickups);

        /*
         * process pickups
         */
        $pickups = $this->processVehicles($extract, $pickups, $passengers, 4);

        /*
         * if no 4 seater vehicles have been reserved at this stage
         * add the innova if it's available for the day
         */
        if ( in_array(102, $request->day_vehicles) ) {
            if ( is_null(collect(Fleet::list())->where('id', 102)->first()) ) {
                $pups = [count($pickups) - 1 => (object) ['pass' => 4, 'zone' => 'in', 'group' => 0]];
                $dnd = [];
                Fleet::create(4, $pups, $dnd, 'in');
            }
        }

        return $pickups;
    }

    /**
     * Return the keys of individual & grouped pickups with more than 4 passengers
     * in-zone groups grouped by venue, out-zone groups grouped by zone
     *
     * @param array $pickups
     * @return array
     */
    private function sixSeaterPickups($pickups)
    {
        $extract = [];

        $legs = Arr::where($pickups, function ($pickup) {
            return array_sum($pickup->passengers) > 4;
        });
        foreach ( $legs as $key => $leg ) {
            $extract[$key] = $key;
        }

        foreach ( $pickups as $ix => $pickup ) {
            $criteria = $pickup->zone == 'in' ? 'venue' : 'zone';
            $next = Arr::where($pickups, function ($item) use ($ix, $pickup, $criteria) {
                return
                    $item->time > $pickup->time &&
                    $item->$criteria == $pickup->$criteria &&
                    $item->time <= Carbon::parse($pickup->time)->addMinutes(15)->toTimeString() &&
                    array_sum($pickup->passengers) + array_sum($item->passengers) > 4;
            });
            if ( count($next) > 0 ) {
                $extract[$ix] = key($next);
            }
        }

        /*
         * sort extracted pickups by pickup time
         */
        ksort($extract);

        return $extract;
    }

    /**
     * Reserve vehicles for overflow passengers from 6 seaters
     *
     * @param array $pickups
     * @return array
     */
    private function overflowPassengers($pickups)
    {
        if ( session()->has('planner.overflow') ) {
            foreach ( session('planner.overflow') as $data ) {
                $pickups = $this->reserveVehicles($pickups, $data['pulegs'], $data['dozone'], $data['v_pass'], 4);
            }
        }

        return $pickups;
    }

    /**
     * Return the keys of individual & grouped pickups with 4 passengers
     * which have not been reserved for 6 seaters.
     * in-zone groups grouped by venue, out-zone groups grouped by suburb
     *
     * @param array $pickups
     * @return array
     */
    private function fourSeaterPickups($pickups)
    {
        $extract = [];

        $legs = collect($pickups)->filter(function ($pickup) {
            return count($pickup->vehicles) == 0 && array_sum($pickup->passengers) == 4;
        })->toArray();

        foreach ( $legs as $key => $leg ) {
            $extract[$key] = $key;
        }

        foreach ( $pickups as $ix => $pickup ) {
            if ( count($pickup->vehicles) == 0 ) {
                $criteria = $pickup->zone == 'in' ? 'venue' : 'zone';
                $next = Arr::where($pickups, function ($item) use ($ix, $pickup, $criteria) {
                    return
                        count($item->vehicles) == 0 &&
                        $item->time > $pickup->time &&
                        $item->$criteria == $pickup->$criteria &&
                        $item->time <= Carbon::parse($pickup->time)->addMinutes(15)->toTimeString() &&
                        array_sum($pickup->passengers) + array_sum($item->passengers) == 4;
                });

                if ( count($next) > 0 ) {
                    $extract[$ix] = key($next);
                }
            }
        }

        /*
         * sort extracted pickups by pickup time
         */
        ksort($extract);

        return $extract;
    }

    /**
     * Process given legs and leg-groups
     *
     * @param array $extract
     * @param array $pickups
     * @param array $passengers
     * @param int $vtype
     * @return array
     */
    private function processVehicles($extract, $pickups, $passengers, $vtype)
    {
        foreach ( $extract as $key => $leg ) {
            $pickup = $pickups[$key];
            $pulegs = $key == $leg ? [$key] : [$key, $leg];
            $pickups = $this->reserveZoneVehicles($pickup, $pickups, $passengers, $pulegs, $vtype);
        }

        return $pickups;
    }

    /**
     * Reserve vehicles by zone for given pickups
     *
     * @param object $pickup
     * @param array $pickups
     * @param array $passengers
     * @param array $pulegs
     * @param int $vtype
     * @return array
     */
    private function reserveZoneVehicles($pickup, $pickups, $passengers, $pulegs, $vtype)
    {
        $count = $vtype == 6 ? 4 : 3;

        /*
         * in-zone pickups
         */
        if ( $pickup->zone == 'in' ) {
            foreach ( $this->passengersByZone($pulegs, $pickup, $pickups)  as $dozone => $passenger_count ) {
                $carrying = 0;

                if ( $passenger_count > $count ) {
                    /*
                     * get the number of vehicles required ( 4 seaters are always 1 )
                     */
                    if ( $vtype == 6 ) {
                        $destination_count = $this->tripDestinations($passengers, $pulegs, $dozone);
                        $seaters = $this->countSixSeaters($passenger_count, $destination_count, $pickup->zone);
                    } else {
                        $seaters['qty'] = 1;
                    }

                    /*
                     * reserve the vehicles
                     */
                    for ( $n = 1; $n <= $seaters['qty']; $n ++ ) {
                        $v_pass = $vtype == 4 ? 4 : $this->passOnThisVehicle($pickup->zone, $seaters, $n);
                        $carrying += $v_pass;
                        $pickups = $this->reserveVehicles($pickups, $pulegs, $dozone, $v_pass, $vtype);
                    }

                    /*
                     * save overflow for 4 seaters if required
                     */
                    $this->saveOverflow($vtype, $pulegs, $pickup, $carrying, $pickups, $dozone);
                }
            }

        /*
         * out-zone pickups
         */
        } else {
            $carrying = 0;
            $passenger_count = count($pulegs) == 1 ?
                array_sum($pickup->passengers) :
                array_sum($pickups[$pulegs[0]]->passengers) + array_sum($pickups[$pulegs[1]]->passengers);

            if ( $vtype == 6 ) {
                $seaters = $this->countSixSeaters($passenger_count, 0, $pickup->zone);
            } else {
                $seaters['qty'] = 1;
            }

            for ( $n = 1; $n <= $seaters['qty']; $n++ ) {
                $v_pass = $vtype == 4 ? 4 : $this->passOnThisVehicle($pickup->zone, $seaters, $n);
                $carrying += $v_pass;
                $pickups = $this->reserveVehicles($pickups, $pulegs, $pickup->zone, $v_pass, $vtype);
            }

            /*
             * save overflow for 4 seaters if required
             */
            $this->saveOverflow($vtype, $pulegs, $pickup, $carrying, $pickups, null);
        }

        return $pickups;
    }

    /**
     * the data is available for overflow passengers requiring 4 seater vehicles
     * so save it rather than re-creating it later because
     * 4 seaters should only be created after all 6 seaters have been created.
     *
     * @param int $vtype
     * @param array $pulegs
     * @param object $pickup
     * @param int $carrying
     * @param array $pickups
     * @param string $dozone
     */
    private function saveOverflow($vtype, $pulegs, $pickup, $carrying, $pickups, $dozone)
    {
        if ( $vtype == 6 ) {
            if ( $pickup->zone == 'in' ) {
                $overflow = count($pulegs) == 1 ?
                    $pickup->passengers[$dozone] - $carrying :
                    $pickups[$pulegs[0]]->passengers[$dozone] + $pickups[$pulegs[1]]->passengers[$dozone] - $carrying;
            } else {
                $overflow = count($pulegs) == 1 ?
                    array_sum($pickup->passengers) - $carrying :
                    array_sum($pickups[$pulegs[0]]->passengers) + array_sum($pickups[$pulegs[1]]->passengers) - $carrying;
            }

            if ( $overflow >= 4 ) {
                $four_seaters = (int) ($overflow / 4);
                for ( $n = 1; $n <= $four_seaters; $n ++ ) {
                    $array = [
                        'pulegs' => $pulegs,
                        'dozone' => $dozone,
                        'v_pass' => 4,
                        'seats'  => 4
                    ];
                    session()->push('planner.overflow', $array);
                }
            }
        }

        return;
    }

    /**
     * Return the number of passengers by zone for pickup trip
     *
     * @param array $pulegs
     * @param object $pickup
     * @param array $pickups
     * @return mixed
     */
    private function passengersByZone($pulegs, $pickup, $pickups)
    {
        if ( count($pulegs) > 1 ) {
            foreach ( $pickup->passengers as $zone => $count ) {
                $passengers[$zone] = $count + $pickups[$pulegs[1]]->passengers[$zone];
            }

            return $passengers;
        }

        return $pickup->passengers;
    }

    /**
     * Return number of unique drop off destinations by dozone for given trip
     *
     * @param array $passengers
     * @param array $puleg
     * @param string $zone
     * @return mixed
     */
    private function tripDestinations($passengers, $puleg, $zone)
    {
            return collect($passengers)
                ->whereIn('puleg', $puleg)
                ->where('dozone', $zone)
                ->groupBy('dolatlon')
                ->count();
    }

    /**
     * Return the number of 6 seater vehicles with passenger count
     * required for given trip destinations.
     * in-zone pickups have trips by dozone, out-zone pickups have a single trip
     *
     * assumes max family size is 2 siblings and allocates a max of 2 vehicles
     * no siblings = max 4 passengers therefore a 4 seater will be used
     * 1 set of siblings = 5 passengers
     * 2 or more sets of siblings = 6 passengers
     *
     * @todo modify algorithm to work for more than 2 vehicles
     * @todo modify algorithm to cater for families of 3 or more
     * @param int $pass
     * @param int $dest
     * @param string $puzone
     * @return array
     */
    private function countSixSeaters($pass, $dest, $puzone)
    {
        /*
         * default
         */
        $result = ['qty' => 0, 'pas' => 0];

        /*
         * in-zone pickups allocate passengers for up to 4 destinations
         * if passenger limit is 4 else up to 6 passengers
         */
        if ( $puzone == 'in' && $pass > 4 ) {

            if ( $this->settings->passenger_limit == 4 ) {
                /*
                 * families = number of sets of siblings
                 */
                $families = $pass - $dest;

                if ( $pass == 5 ) {
                    $use = $dest < 5 ? 1 : 0;
                    $pas = $use == 1 ? 5 : 0;

                } else {
                    /*
                     * up to 10 passengers will always require 1 vehicle
                     */
                    if ( $pass <= 10 ) {
                        $use = $families >= 1 ? 1 : 0;
                        if ( $use > 0 ) {
                            $pas = $families == 1 ? 5 : 6;
                        } else {
                            $pas = 0;
                        }

                        /*
                         * more than 10 passengers could require 2 vehicles
                         */
                    } else {
                        $use = $families >= 4 ? 2 : 1;
                        $use = $families == 0 ? 0 : $use;
                        if ( $use > 0 ) {
                            $pas = $use > 1 ? 11 : 6;
                            $pas = $families == 1 ? 5 : $pas;
                        } else {
                            $pas = 0;
                        }
                    }
                }

                if ( isset($use) ) {
                    $result = ['qty' => $use, 'pas' => $pas];
                }

            } else {
                $use = $pass % 6 == 5 ? (int)($pass / 6) + 1 : (int)($pass / 6);
                $pas = $use * 6 > $pass ? $pass : $use * 6;
                $result = ['qty' => $use, 'pas' => $pas];
            }

        /*
         * out-zone pickups allocate passengers for up to 6 destinations
         */
        } elseif ( $puzone != 'in' || $this->settings->passenger_limit == 6 ) {
            $qty = $pass % 6 == 5 ? (int)($pass / 6) + 1 : (int)($pass / 6);
            $pas = $qty * 6 > $pass ? $pass : $qty * 6;
            $result = ['qty' => $qty, 'pas' => $pas];
        }

        return $result;
    }

    /**
     * Return the number of passengers on this vehicle
     *
     * @param string $zone
     * @param array $ss seaters
     * @param int $n
     * @return int
     */
    private function passOnThisVehicle($zone, $ss, $n)
    {
        if ( $zone == 'in' ) {
            if ( $ss['pas'] / $ss['qty'] == 6 ) {
                return 6;
            } elseif ( $ss['qty'] > 1 ) {
                return $n == 1 ? 6 : 5;
            } else {
                return $ss['pas'];
            }
        } else {
            if ( $ss['pas'] > 6 ) {
                return $ss['pas'] - (($n-1) * 6) >= 6 ? 6 : $ss['pas'] - (($n-1) * 6);
            } else {
                return $ss['pas'];
            }
        }
    }

    /**
     * Reserve vehicle for pickup
     *
     * Adds required data to vehicles & vehicle id to pickups
     * in-zones do-not-disturb set to 45min, out-zones set to 60min
     *
     * @param array $pickups
     * @param array $pulegs
     * @param string $dozone
     * @param int $v_pass
     * @param int $seats
     * @return mixed
     */
    private function reserveVehicles($pickups, $pulegs, $dozone, $v_pass, $seats)
    {
        /*
         * reserve an existing vehicle if available for this pickup
         */
        if ( Fleet::count() > 0 ) {

            foreach ( Fleet::list() as $id => $data ) {
                /*
                 * check that the new pickup trip time does not conflict with any existing dnd times
                 */
                $conflict = $this->checkDndTimes($data->dnd, $pulegs, $pickups, $dozone);

                if ( !$conflict ) {
                    $pups = [$pulegs[0] => $this->setVehiclePickups($pulegs, $v_pass, $dozone, $data->seats)];
                    $dnd = [$pickups[$pulegs[0]]->time => $this->setVehicleDndTime($dozone, $pulegs, $pickups, $v_pass)];
                    Fleet::updateReserve($id, $pups, $dnd);
                    $pickups = $this->addVehicleToPickups($pickups, $pulegs, $id);

                    $reserved = true;
                    break;
                }
            }
        }

        /*
         * add a vehicle to reserve
         */
        if ( !isset($reserved) || Fleet::count() == 0 ) {
            /*
             * create the vehicle if it is available today
             */
            $seats = Fleet::isAvailableVehicle($seats);

            if ( $seats > 0 ) {
                $pups = [$pulegs[0] => $this->setVehiclePickups($pulegs, $v_pass, $dozone, $seats)];
                $dnd = [$pickups[$pulegs[0]]->time => $this->setVehicleDndTime($dozone, $pulegs, $pickups, $v_pass)];

                $id = Fleet::create($seats, $pups, $dnd, $pickups[$pulegs[0]]->zone);
                $pickups = $this->addVehicleToPickups($pickups, $pulegs, $id);
            }
        }

        return $pickups;
    }

    /**
     * Return the vehicle's pickup data
     *
     * @param array $pulegs
     * @param int $v_pass
     * @param string $dozone
     * @param int $seats
     * @return object
     */
    private function setVehiclePickups($pulegs, $v_pass, $dozone, $seats)
    {
        return (object) [
            'pass' => $v_pass > $seats ? $seats : $v_pass,
            'zone' => $dozone,
            'group'=> count($pulegs) > 1 ? $pulegs[1] : 0
        ];
    }

    /**
     * Return the vehicle's do-not-disturb times
     *
     * @param string $dozone
     * @param array $pulegs
     * @param array $pickups
     * @param int $v_pass
     * @return string
     */
    private function setVehicleDndTime($dozone, $pulegs, $pickups, $v_pass)
    {
        // trip time is zone dependant
        if ( $pickups[$pulegs[0]]->zone == 'in' ) {
            $trip_time = $dozone == 'in' && $v_pass < 6 ? 45 : 50;
        } else {
            $trip_time = 60;
        }

        if ( count($pulegs) > 1 ) {
            return Carbon::parse($pickups[$pulegs[1]]->time)->addMinutes($trip_time)->format('H:i');
        }

        return Carbon::parse($pickups[$pulegs[0]]->time)->addMinutes($trip_time)->format('H:i');
    }

    /**
     * Return the pickups with vehicle id added to pickup vehicles
     *
     * @param array $pickups
     * @param array $pulegs
     * @param int $id
     * @return mixed
     */
    private function addVehicleToPickups($pickups, $pulegs, $id)
    {
        if ( count($pulegs) == 1 ) {
            array_push($pickups[$pulegs[0]]->vehicles, $id);
        } else {
            array_push($pickups[$pulegs[0]]->vehicles, $id);
            array_push($pickups[$pulegs[1]]->vehicles, $id);
        }

        return $pickups;
    }

    /**
     * Return true if new time is in conflict with any of this vehicle's existing do-not-disturb times
     *
     * @param array $data
     * @param array $pulegs
     * @param array $pickups
     * @param string $dozone
     * @return bool
     */
    private function checkDndTimes($data, $pulegs, $pickups, $dozone)
    {
        /*
         * trip time is zone dependant
         */
        if ( $pickups[$pulegs[0]]->zone == 'in' ) {
            $trip_time = $dozone == 'in' ? 45 : 60;
        } else {
            $trip_time = 60;
        }

        /*
         * pickup legs have a single puleg, pickup groups have two pulegs
         */
        foreach ( $pulegs as $leg ) {
            $pickup = $pickups[$leg];

            /*
             * existing vehicle may have multiple do-not-disturb times
             */
            foreach ( $data as $from => $to ) {
                $res_start = Carbon::parse($from);
                $res_end = Carbon::parse($to);

                /*
                 * bail if start time, end time or time span is in conflict
                 */
                if ( Carbon::parse($pickup->time)->between($res_start, $res_end) ||
                    Carbon::parse($pickup->time)->addMinutes($trip_time)->between($res_start, $res_end) ||
                    (Carbon::parse($pickup->time) < $res_start && Carbon::parse($pickup->time)->addMinutes($trip_time) > $res_end) ) {
                    return true;
                }
            }
        }

        return false;
    }
}
