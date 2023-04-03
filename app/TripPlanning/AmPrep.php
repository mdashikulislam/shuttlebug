<?php

/*
 * Am Prep
 * Collect the data required for building morning trips.
 *
 */

namespace App\TripPlanning;


use App\Models\Booking;
use Carbon\Carbon;

class AmPrep
{
    /**
     * @var $request
     */
    protected $request;

    /**
     * AmPrep constructor.
     *
     * @param $request
     */
    public function __construct($request)
    {
        $this->request = $request;
    }

    /**
     * Build the required data from bookings for morning trips
     *
     */
    public function collect()
    {
        /**
         * Get the morning bookings for the day
         */
        $trips = Booking::with('passenger','puloc','doloc')
            ->where('date', $this->request->date)
            ->where('putime', '<', '09:00:00')
            ->where('journal', '!=', 'cancelled')
            ->orderBy('dotime')
            ->get();

        /**
         * Create the required data arrays from the bookings
         */
        list($passengers, $schools) = $this->bookingData($trips);

        /**
         * Prep is complete, put working variables in session
         */
        session()->put('planner.schools', $schools);
        session()->put('planner.passengers', $passengers);
        session()->put('planner.fleet', []);

        return;
    }

    /**
     * Return pickups, drop offs, schools & passengers
     *
     * @param array $trips
     * @return array
     */
    private function bookingData($trips)
    {
        $passengers = $schools = $pickups = $dropoffs = [];
        $combine_zones = $this->combineZones($trips);

        foreach ( $trips as $trip ) {

            $passenger = (object) [
                'booking'   => $trip->id,
                'passenger' => $trip->passenger->name,
                'pass_id'   => $trip->passenger->id,
                'age'       => $trip->passenger->dob > '0000-00-00' ? Carbon::parse($trip->passenger->dob)->age : '?',
                'hoadrs'    => substr($trip->puloc->address,0,strrpos($trip->puloc->address,',')),
                'dovenue'   => $trip->doloc_type != 'user' ? $trip->doloc->name : '',
                'hlatlon'   => $trip->puloc->geo,
                'puzone'    => in_array($trip->id, $combine_zones) ? 'llcf' : getZone($trip->puloc->geo),
                'school'    => $trip->doloc->name,
                'slatlon'   => $trip->doloc->geo,
                'dozone'    => in_array($trip->id, $combine_zones) ? 'llcf' : getZone($trip->doloc->geo),
                'time'      => $trip->dotime,
                'putime'    => $trip->putime > '00:00' ? $trip->putime : '00:00'
            ];

            $school = (object) [
                'school'    => $trip->doloc->name,
                'slatlon'   => $trip->doloc->geo,
                'dozone'    => getZone($trip->doloc->geo),
                'from'      => $trip->doloc->dropfrom ?? Carbon::parse($trip->dotime)->subMinutes(5)->toTimeString(),
                'by'        => $trip->doloc->dropby ?? Carbon::parse($trip->dotime)->addMinutes(5)->toTimeString(),
                'address'   => substr($trip->doloc->address,0,strrpos($trip->doloc->address,','))
            ];

            $passengers[] = $passenger;
            $schools[] = $school;
        }

        $schools = collect($schools)->unique()->all();

        return [$passengers, $schools];
    }

    /**
     * Return the booking ids of llandudno drop-offs and clifton pickups
     * these will be given a unique zone so that the trips can be combined
     *
     * @param $trips
     * @return array
     */
    private function combineZones($trips)
    {
//        $llandudno = collect($trips)->where('doloc_id', 700002)->pluck('id')->all();
//        if ( count($llandudno) > 0 ) {
//            $clifton = collect($trips)->filter(function($item) {
//                return $item->puloc->suburb == 'Clifton';
//            })->pluck('id')->all();
//            if ( count($clifton) > 0 ) {
//                return array_merge($llandudno, $clifton);
//            }
//        }

        return [];
    }
}