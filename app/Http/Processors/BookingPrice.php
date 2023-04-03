<?php
/**
 * Created by PhpStorm.
 * User: Ray
 * Date: 2018/02/27
 * Time: 10:07 AM
 */

namespace App\Http\Processors;


/*
    |--------------------------------------------------------------------------
    | BookingPrice
    |--------------------------------------------------------------------------
    |
    | Calculates the price applicable to this booking.
    | Evaluates the special prices and promotions ruling on the booking date
    | as well as the standard default price.
    |
    | New promotions and special prices must be added as private functions in this class
    | and promotions must be added to the model in methods that return the bookings for the promotion.
    |
    | Where more than one price could apply (eg. short_trip & vip) evaluate lowest price first.
    */

use App\Models\Price;
use App\Models\Promotion;
use App\TripPlanning\RouteMappers;

class BookingPrice
{
    /**
     * Calculate the price applicable to this booking
     * disable obsolete specials
     *
     * @param $input
     * @return mixed
     */
    public function shuttlePrice($input)
    {
        // default price
        $standard = Price::where('date', '<=', $input->date)->orderBy('date', 'desc')->first();
        // booking suburbs & geos
        list($suburbs, $geos) = $this->bookingData($input);

//        $morningMayhem = $this->morningMayhem($input, $suburbs);
//        if ( !is_null($morningMayhem) ) {
//            return $morningMayhem;
//        }

        $southernSuburbs = $this->southernSuburbs($input, $suburbs);
        if ( !is_null($southernSuburbs) ) {
            return $southernSuburbs;
        }

        $overTheMountain = $this->overTheMountain($input, $suburbs);
        if ( !is_null($overTheMountain) ) {
            return $overTheMountain;
        }

        $otherSuburbs = $this->otherSuburbs($input, $suburbs);
        if ( !is_null($otherSuburbs) ) {
            return $otherSuburbs;
        }

        $remoteSchools = $this->remoteSchools($input, $suburbs);
        if ( !is_null($remoteSchools) ) {
            return $remoteSchools;
        }

        $shortTrip = $this->shortTrip($input, $suburbs, $geos);
        if ( !is_null($shortTrip) ) {
            return $shortTrip;
        }

        $vip = $this->vip($input);
        if ( !is_null($vip) ) {
            return $vip;
        }

        return ['price' => (int)$standard->basic_rate, 'promo' => 0];
    }

    /**
     * Promotion: Morning Mayhem
     * Qualification:
     *      puloc & doloc in Hout Bay or Llandudno
     *      dotime <= 8:45
     *
     * @param $input
     * @param $suburbs
     * @return null
     */
//    private function morningMayhem($input, $suburbs)
//    {
//        if ( $promo = Promotion::isActive('Morning Mayhem', $input->date) ) {
//            $promo_suburbs = ['Hout Bay','Llandudno'];
//
//            // qualify venue and time
//            if ( $input->puloc_type == 'home' && $input->doloc_type == 'school' && $input->dotime <= '08:45:00' ) {
//
//                // qualify suburbs
//                if ( in_array($suburbs['pu'], $promo_suburbs) && in_array($suburbs['do'], $promo_suburbs) ) {
//                    return ['price' => (int)$promo->rate, 'promo' => $promo->id];
//                }
//            }
//        }
//
//        return null;
//    }

    /**
     * Special Price: Over The Mountain
     * Qualification:
     *      one of puloc or doloc in Hout Bay or Llandudno
     *      the other puloc or doloc in Constantia or Camps Bay
     *
     * @param $input
     * @param $suburbs
     * @return null
     */
    private function overTheMountain($input, $suburbs)
    {
        if ( $promo = Promotion::isActive('Over The Mountain', $input->date) ) {
            $local_suburbs = ['Hout Bay','Llandudno'];
            $list = $promo->list;

            // qualify suburbs
            if ( in_array($suburbs['pu'], $local_suburbs) && in_array($suburbs['do'], $list) ) {
                return ['price' => (int)$promo->rate, 'promo' => $promo->id];
            } elseif ( in_array($suburbs['do'], $local_suburbs) && in_array($suburbs['pu'], $list) ) {
                return ['price' => (int)$promo->rate, 'promo' => $promo->id];
            }
        }

        return null;
    }

    /**
     * Special Price: Other Suburbs
     * Qualification:
     *      one of puloc or doloc in Hout Bay or Llandudno
     *      the other puloc or doloc in Clifton
     *
     * @param $input
     * @param $suburbs
     * @return null
     */
    private function otherSuburbs($input, $suburbs)
    {
        if ( $promo = Promotion::isActive('Other Suburbs', $input->date) ) {

            $local_suburbs = ['Hout Bay','Llandudno'];
            $list = $promo->list;

            // qualify suburbs
            if ( in_array($suburbs['pu'], $local_suburbs) && in_array($suburbs['do'], $list) ) {
                return ['price' => (int)$promo->rate, 'promo' => $promo->id];
            } elseif ( in_array($suburbs['do'], $local_suburbs) && in_array($suburbs['pu'], $list) ) {
                return ['price' => (int)$promo->rate, 'promo' => $promo->id];
            }
        }

        return null;
    }


    /**
     * Special Price: Remote Schools
     * Qualification:
     *      puloc & doloc must be in Hout Bay & given school
     *
     * @param $input
     * @param $suburbs
     * @return null
     */
    private function remoteSchools($input, $suburbs)
    {
        if ( $promo = Promotion::isActive('Remote Schools', $input->date) ) {
            $local_suburbs = ['Hout Bay'];
            $list = $promo->list;

            // qualify trip
            if ( in_array($suburbs['pu'], $local_suburbs) && in_array($input->doloc_id, $list) ) {
                return ['price' => (int)$promo->rate, 'promo' => $promo->id];
            } elseif ( in_array($suburbs['do'], $local_suburbs) && in_array($input->puloc_id, $list) ) {
                return ['price' => (int)$promo->rate, 'promo' => $promo->id];
            }
        }

        return null;
    }

    /**
     * Special Price: Southern Suburbs
     * Qualification:
     *      both puloc & doloc in Southern Suburbs
     *
     * @param $input
     * @param $suburbs
     * @return null
     */
    private function southernSuburbs($input, $suburbs)
    {
        if ( $promo = Promotion::isActive('Southern Suburbs', $input->date) ) {
            $list = $promo->list;

            // qualify suburbs
            if ( in_array($suburbs['pu'], $list) && in_array($suburbs['do'], $list) ) {
                return ['price' => (int)$promo->rate, 'promo' => $promo->id];
            }
        }

        return null;
    }

    /**
     * Special Price: Short Trip
     * Qualification:
     *      both puloc & doloc in Hout Bay
     *      distance between puloc & doloc <= 1km
     *
     * @param $input
     * @param $suburbs
     * @param $geos
     * @return null
     */
    private function shortTrip($input, $suburbs, $geos)
    {
        if ( $promo = Promotion::isActive('Short Trip', $input->date) ) {
            $local_suburbs = ['Hout Bay'];

            // qualify suburbs
            if ( in_array($suburbs['pu'], $local_suburbs) && in_array($suburbs['do'], $local_suburbs) ) {

                // qualify distance
                $trip = RouteMappers::pointDistance($geos['pu'], $geos['do']);
                if ( !is_null($trip) && $trip->distance <= 1000 ) {
                    return ['price' => (int)$promo->rate, 'promo' => $promo->id];
                }
            }
        }

        return null;
    }

    /**
     * Special Price: Vip
     * Qualification:
     *      customers listed in promotion customers
     *
     * @param $input
     * @return null
     */
    private function vip($input)
    {
        if ( $promo = Promotion::isActive('Vip', $input->date) ) {

            // qualify customers
            if ( !is_null($promo->list) && in_array($input->user_id, $promo->list) ) {
                return ['price' => (int)$promo->rate, 'promo' => $promo->id];
            }
        }

        return null;
    }

    /**
     * Return the booking pickup and dropoff suburbs and geos
     *
     * @param $input
     * @return mixed
     */
    private function bookingData($input)
    {
        $model = 'App\Models\\'.ucfirst($input->puloc_type);
        $pu = $model::find($input->puloc_id);
        $suburbs['pu'] = $pu->suburb;
        $geos['pu'] = $pu->geo;

        $model = 'App\Models\\'.ucfirst($input->doloc_type);
        $do = $model::find($input->doloc_id);
        $suburbs['do'] = $do->suburb;
        $geos['do'] = $do->geo;

        return [$suburbs, $geos];
    }
}
