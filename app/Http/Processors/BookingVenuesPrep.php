<?php
/**
 * Created by PhpStorm.
 * User: Ray
 * Date: 2018/02/24
 * Time: 8:39 AM
 */

namespace App\Http\Processors;


use App\Models\Children;
use App\Models\School;
use App\Models\User;


class BookingVenuesPrep
{
    /**
     * Collect available pickup & drop off venues for this booking
     *
     * @param $request
     * @return array
     */
    public function handle($request, $bookings)
    {
        $venues = [];
        $venues[$request->customer] = 'Home';
        $venues = $this->passengerSchool($request->passenger, $venues);
        $venues = $this->customerXmurals($request->customer, $venues);
        $venues = $this->schoolXmurals($bookings, $venues);

        return $venues;
    }

    /**
     * Return list of schools excluding passenger's school
     *
     * @param $venues
     * @return array
     */
    public function otherSchools($venues)
    {
        return School::whereNotIn('id', array_keys((array)$venues))
            ->where('status', 'active')
            ->orderBy('name')
            ->get()->pluck('name','id')->all();
    }

    /**
     * Push school onto venue array
     *
     * @param $passenger
     * @param $venues
     * @return mixed
     */
    private function passengerSchool($passenger, $venues)
    {
        $school = School::find(Children::find($passenger)->school_id);
        $venues[$school->id] = 'My School ('.$school->name.')';

        return $venues;
    }

    /**
     * Push customer's xmurals onto venue array
     *
     * @param $customer
     * @param $venues
     * @return mixed
     */
    private function customerXmurals($customer, $venues)
    {
        $xmurals = User::find($customer)->xmurals()->orderBy('venue')->get();
        foreach ( $xmurals as $xmural ) {
            $venues[$xmural->id] = $xmural->venue;
        }

        return $venues;
    }

    /**
     * Push this booking's other schools onto venues array
     *
     * @param $bookings
     * @param $venues
     * @return mixed
     */
    private function schoolXmurals($bookings, $venues)
    {
        foreach ( $bookings as $booking ) {
            if ( $booking->puloc_type == 'school' && !isset($venues[$booking->puloc_id]) ) {
                $venues[$booking->puloc_id] = $booking->puloc->name;
            }
            if ( $booking->doloc_type == 'school' && !isset($venues[$booking->doloc_id]) ) {
                $venues[$booking->doloc_id] = $booking->doloc->name;
            }
        }

        // also add the option to choose another school
        $venues[700000] = 'Other School';

        return $venues;
    }
}