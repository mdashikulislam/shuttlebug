<?php
/**
 * Created by PhpStorm.
 * User: Ray
 * Date: 2018/03/30
 * Time: 1:37 PM
 */

/*
 * Day Prep
 * Collect the data required for building day trips.
 *
 * Creates pickups, passengers and 6 & 4 seater vehicles
 */

namespace App\TripPlanning;


use App\Models\Booking;
use App\Models\PlanningReport;
use Carbon\Carbon;

class DayPrep
{
    /**
     * @var $request
     * @var ReserveVehicles
     */
    protected $request;
    protected $reserveVehicles;

    /**
     * DayPrep constructor.
     *
     * @param $request
     */
    public function __construct($request)
    {
        $this->request = $request;
        $this->settings = session('planner.settings');
        $this->reserveVehicles = new ReserveVehicles();

    }

    /**
     * Build the required data from bookings
     *
     */
    public function collect()
    {
        /*
         * Get the bookings for the day
         */
        $bookings = Booking::with('passenger', 'puloc', 'doloc')
            ->where('date', $this->request->date)
            ->where('putime', '>=', '09:00')
            ->where('journal', '!=', 'cancelled')
            ->orderBy('putime')
            ->get();

        /*
         * create pickups and passengers
         */
        $pickups = $passengers = [];

        foreach ( $bookings as $trip ) {
            $pickups[] = (object) [
                'legix'      => null,
                'time'       => substr($trip->putime, 0, 5),
                'locid'      => $trip->puloc_id,
                'venue'      => $trip->puloc_type == 'user' ? $this->trimAddress($trip->puloc->address) : $trip->puloc->name,
                'address'    => $this->trimAddress($trip->puloc->address),
                'latlon'     => $trip->puloc->geo,
                'zone'       => getZone($trip->puloc->geo),
                'passengers' => [],
                'vehicles'   => []
            ];

            $passengers[] = (object) [
                'booking'   => $trip->id,
                'passenger' => $trip->passenger->name,
                'passid'    => $trip->passenger_id,
                'age'       => $trip->passenger->dob > '0000-00-00' ? Carbon::parse($trip->passenger->dob)->age : '?',
                'putime'    => substr($trip->putime, 0, 5),
                'puvenue'   => $trip->puloc_type == 'user' ? $this->trimAddress($trip->puloc->address) : $trip->puloc->name,
                'puaddress' => $this->trimAddress($trip->puloc->address),
                'pulatlon'  => $trip->puloc->geo,
                'dotime'    => $trip->doloc_type == 'xmural' && $trip->doloc->time ? 'yes' : null,
                'dovenue'   => $trip->doloc_type != 'user' ? $trip->doloc->name : 'Home',
                'doaddress' => $this->trimAddress($trip->doloc->address),
                'dolatlon'  => $trip->doloc->geo,
                'dozone'    => getZone($trip->doloc->geo),
                'puleg'     => null
            ];

        }

        /*
         * Remove duplicates from pickups, sort & add leg ix to pickup
         * Changed sort order on 2019-02-01 to time asc, pass desc
         * uses old method prior to change date so as to not alter homeheroes
         */
        if ( $this->request->date <= '2019-02-01' ) {
            $pickups = collect($pickups)->unique()->values()->toArray();
            foreach ( $pickups as $leg => $pickup ) {
                $pickup->legix = $leg;
            }
        } else {
            $pickups = collect($pickups)->unique()->values()->toArray();
            foreach ( $pickups as $leg => $pickup ) {
                $pass = collect($passengers)->where('puvenue', $pickup->venue)->where('putime', $pickup->time)->count();
                $pickup->pass = $pass;
            }
            $pickups = multiSortCollection($pickups, 'time asc,pass desc');
            foreach ( $pickups as $leg => $pickup ) {
                $pickup->legix = $leg;
            }
        }

        /*
         * Add pickup leg to passengers
         */
        foreach ( $pickups as $key => $pickup ) {
            foreach ( $passengers as $passenger ) {
                if ( $passenger->puvenue == $pickup->venue && $passenger->putime == $pickup->time ) {
                    $passenger->puleg = $key;
                }
            }
        }

        /*
         * Add zoned passenger count to pickup
         */
        $zones = ['in', 'east', 'north', 'south'];
        foreach ( $pickups as $leg => $pickup ) {
            foreach ( $zones as $zone ) {
                $pickup->passengers[$zone] = collect($passengers)->where('puleg', $leg)->where('dozone', $zone)->count();
            }
        }

        /*
         * Create & reserve 6 & 4 seater vehicles
         * add am vehicles to fleet
         */
        session()->put('planner.fleet', []);
        $pickups = $this->reserveVehicles->sixSeaters($pickups, $passengers);
        $pickups = $this->reserveVehicles->fourSeaters($pickups, $passengers, $this->request);
        Fleet::addAmVehicles();

        session()->put('planner.pickups', $pickups);
        session()->put('planner.passengers', $passengers);
    }

    /**
     * Trim address to exclude city
     *
     * @param $address
     * @return string
     */
    private function trimAddress($address)
    {
        return strpos($address, ',') == true ?
            substr($address, 0, strrpos($address, ',')) :
            $address;
    }
}