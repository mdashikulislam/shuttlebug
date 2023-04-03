<?php
/**
 * Created by PhpStorm.
 * User: Ray
 * Date: 2018/03/11
 * Time: 8:38 AM
 */

namespace App\Http\Processors;

/*
    |--------------------------------------------------------------------------
    | BuildInvoice
    |--------------------------------------------------------------------------
    |
    | Called when preparing a view of the customer's invoice for a given month.
    | Extracts shuttle bookings and event bookings & discount for the month.
    |
    */

use App\Models\Booking;
use App\Models\EventBooking;
use App\Models\Price;
use App\Models\Promotion;
use App\Models\School;
use Carbon\Carbon;

class BuildInvoice
{
    /**
     * BuildInvoice constructor.
     *
     */
    public function __construct()
    {
        //
    }

    /**
     * Return the invoice for given customer in given month
     * includes bookings and event bookings
     *
     * @param $customer
     * @param $month_end
     * @return array
     */
    public function invoiceLines($customer, $month_end)
    {
        $postingMonth = (object) [
            'end'   => $month_end,
            'start' => Carbon::createFromFormat('Y-m-d', $month_end)->subMonth()->addDay()->toDateString()
        ];

        $shuttles = $this->shuttleTrips($customer, $postingMonth);
        $events = $this->eventTrips($customer, $postingMonth);

        // when called for monthend, all the unfiltered bookings are required
        if ( is_null($customer) ) {
            return [$shuttles, $events];
        } else {
            $lines = $shuttles + $events;
        }

        return $lines;
    }

    /**
     * Return customer's discount for the given invoice
     *
     * @param $inv
     * @param $month_end
     * @param $ruling_disc
     * @return float|int
     */
    public function invoiceDiscount($id, $inv, $month_end, $ruling_disc = null)
    {
        $discount = 0;
        $passengers = array_column($inv,'passenger');
        $ruling_disc = is_null($ruling_disc) ? Price::rulingPrice($month_end)->volume_disc : $ruling_disc;

        $volume_discount = $this->volumeDiscount($inv, $passengers, $ruling_disc);
        $sibling_discount = $this->siblingDiscount($id, $month_end);

        return ceil($volume_discount + $sibling_discount);
    }

    /**
     * Return array of shuttles for given customer in given month
     *
     * @param $customer
     * @param $postingMonth
     * @return array
     */
    private function shuttleTrips($customer, $postingMonth)
    {
        $shuttles = [];

        $data = Booking::with('puloc','doloc')
            ->whereBetween('date', [$postingMonth->start, $postingMonth->end])
            ->where('journal', '')
            ->orderBy('passenger_id')
            ->orderBy('date')
            ->get();

        // when called for monthend, all the unfiltered bookings are required
        if ( is_null($customer) ) {
            return $data;
        }

        // filter if customer specified
        if ( !is_null($customer) ) {
            $data = $data->filter(function ($item) use($customer) {
                return $item->user_id == $customer;
            });
        }

        foreach ( $data as $shuttle ) {
            $shuttles[] = (object) [
                'passenger' => $shuttle->passenger_id,
                'date'      => $shuttle->date,
                'trip'      => $shuttle->puloc->venue.' -> '.$shuttle->doloc->venue,
                'amount'    => $shuttle->price
            ];
        }

        return $shuttles;
    }

    /**
     * Return array of events for given customer in given month
     *
     * @param $customer
     * @param $postingMonth
     * @return array
     */
    private function eventTrips($customer, $postingMonth)
    {
        $events = [];

        $data = EventBooking::whereBetween('date', [$postingMonth->start, $postingMonth->end])
            ->orderBy('date')
            ->get();

        // when called for monthend, all the unfiltered bookings are required
        if ( is_null($customer) ) {
            return $data;
        }

        // filter if customer specified
        if ( !is_null($customer) ) {
            $data = $data->filter(function ($item) use($customer) {
                return $item->user_id == $customer;
            });
        }

        foreach ( $data as $event ) {
            list($from, $to) = $this->eventVenues($event);

            $events[] = (object) [
                'passenger' => 'event',
                'date'      => $event->date,
                'trip'      => '('.$event->passengers.'pass) '.$from.' &rarr; '.$to,
                'amount'    => $event->tripfee
            ];
        }

        return $events;
    }

    /**
     * Return abbreviations for event pickups & drop offs
     *
     * @param $event
     * @return array
     */
    public function eventVenues($event)
    {
        if ( $event->puloc == 'home' ) {
            $from = 'Home';
        } elseif ( substr($event->puloc,0,2) == '70' ) {
            $from = School::find($event->puloc)->name;
        } else {
            $from = substr($event->puloc,0,strpos($event->puloc,','));
        }

        if ( $event->doloc == 'home' ) {
            $to = 'Home';
        } elseif ( substr($event->doloc,0,2) == '70' ) {
            $to = School::find($event->doloc)->name;
        } else {
            $to = substr($event->doloc,0,strpos($event->doloc,','));
        }

        return [$from, $to];
    }

    /**
     * Calculate volume discount for this invoice
     *
     * @param $inv
     * @param $passengers
     * @param $ruling_disc
     * @return float
     */
    private function volumeDiscount($inv, $passengers, $ruling_disc)
    {
        $discount = 0;

        foreach ( array_unique($passengers) as $passenger ) {
            // ignoring event bookings
            if ( $passenger != 'event' ) {

                // extract trips for each passenger
                $passenger_trips = array_filter($inv, function ($item) use($passenger) {
                    return $item->passenger == $passenger;
                });

                // add the discount if trips exceed 15
                if ( count($passenger_trips) > 15 ) {
                    $value = array_sum(array_column($passenger_trips, 'amount'));
                    $discount += $value * ($ruling_disc / 100);
                }
            }
        }

        return $discount;
    }

    /**
     * Calculate the sibling discount for this invoice
     *
     * @param $id
     * @param $month_end
     * @return int
     */
    private function siblingDiscount($id, $month_end)
    {
        $discount = 0;
        $year = Carbon::parse($month_end)->year;

        $ruling_disc = Price::rulingPrice($month_end)->sibling_disc;

        if ( $ruling_disc > 0 ) {
            $month_start = Carbon::parse($month_end)->subMonth()->addDay()->toDateString();

            // get all customer's bookings for the month
            $bookings = Booking::where('user_id', $id)
                ->whereBetween('date', [$month_start,$month_end])
                ->get();

            $used = [];

            // get the unique trips shared by siblings
            foreach( $bookings as $booking ) {

                // HB Int prim & hi are same location so combine if applicable
                $puloc = $booking->puloc_id == 700008 || $booking->puloc_id == 700028 ?
                    [700008,700028] : [$booking->puloc_id];
                $doloc = $booking->doloc_id == 700008 || $booking->doloc_id == 700028 ?
                    [700008,700028] : [$booking->doloc_id];

                $shared = $bookings->where('date', $booking->date)
                    ->where('putime', $booking->putime)
//                    ->where('puloc_id', $booking->puloc_id)
                    ->whereIn('puloc_id', $puloc)
//                    ->where('doloc_id', $booking->doloc_id)
                    ->whereIn('doloc_id', $doloc)
                    ->where('id', '!=', $booking->id)
                    ->whereNotIn('id', $used)
                    ->all();

                // eliminate this trip so it's not counted again
                $used[] = $booking->id;
                foreach( $shared as $dup ) {
                    $used[] = $dup->id;
                }

                // apply the discount to additional siblings who shared the trip
                if ( count($shared) > 0 ) {
                    $discount += count($shared) * $booking->price * ($ruling_disc / 100);
//                    $discount += round(count($shared) * $booking->price * ($ruling_disc / 100));
                }
            }
        }

        return $discount;
    }
}