<?php
/**
 * Created by PhpStorm.
 * User: Ray
 * Date: 2018/03/01
 * Time: 9:04 AM
 */

/*
    |--------------------------------------------------------------------------
    | BookingDuplicate
    |--------------------------------------------------------------------------
    |
    | Duplicates all the selected bookings onto matching days for the duration
    | of the requested period, excluding holidays.
    | Weekends are available so that weekend bookings can also be duplicated.
    | All existing bookings on the target dates are removed.
    |
    */

namespace App\Http\Processors;


use App\Models\Booking;
use App\Models\Holiday;
use App\Models\PlanningReport;
use App\Models\TripSettings;
use Carbon\Carbon;


class BookingDuplicate
{
    /**
     * @var BookingPrice
     */
    protected $price;
    protected $settings;

    /**
     * BookingDuplicate constructor.
     *
     * @param BookingPrice $price
     * @param TripSettings $settings
     */
    public function __construct(BookingPrice $price, TripSettings $settings)
    {
        $this->price = $price;
        $this->settings = $settings->first();
    }

    /**
     * Process the booking duplication
     *
     * @param $request
     * @return int
     */
    public function handle($request)
    {
        // set constants
        $duplications = 0;
        $start = $request->date;
        $end = $this->optionEndDate($request->option, $request->date);

        $year = Carbon::parse($request->date)->year;
        $holidays = Holiday::allHolidays($year);

        // create array of duplication dates
        $target_dates = $this->targetDates($start, $end, $holidays);

        // duplicate bookings
        if ( count($target_dates) > 0 ) {
            $duplications = $this->duplicateBookings($request->source, $target_dates);
        }

        return $duplications;
    }

    /**
     * Duplicate bookings
     *
     * @param $source
     * @param $target_dates
     * @return int
     */
    private function duplicateBookings($source, $target_dates)
    {
        $duplications = 0;

        foreach ( $source as $id ) {
            $copy = Booking::find($id);
            $day_bookings = Booking::where('passenger_id', $copy->passenger_id)->where('date', $copy->date)->where('journal', '!=', 'cancelled')->get();
            $day = carbon::parse($copy->date)->dayOfWeekIso;

            if ( count($day_bookings) > 0 ) {
                foreach ( $target_dates as $tdate ) {
                    if ( carbon::parse($tdate)->dayOfWeekIso == $day ) {
                        // remove existing bookings from target
                        Booking::where('date', $tdate)->where('passenger_id', $copy->passenger_id)->delete();
                        // sync planning report
                        PlanningReport::where('date', $tdate)->delete();

                        // save duplicate bookings to target
                        foreach ( $day_bookings as $booking ) {
                            // duplicate if valid booking
//                            if ( Booking::validBooking($booking, $tdate) ) {
//                            if ( $this->validBooking($booking, $tdate) ) {
                                $input = collect($booking)->except('id', 'date', 'price', 'vehicle', 'promo', 'journal', 'created_at', 'updated_at');
                                $input['date'] = $tdate;
                                $ruling = $this->price->shuttlePrice(json_decode(json_encode($input)));
                                $input['price'] = $ruling['price'];
                                $input['promo'] = $ruling['promo'];
                                Booking::create($input->toArray());
                                $duplications ++;
//                            }
                        }
                    }
                }
            }
        }

        return $duplications;
    }

    /**
     * Return last date of given option
     *
     * @param $option
     * @param $date
     * @return string|static
     */
    private function optionEndDate($option, $date)
    {
        $dt = Carbon::createFromFormat('Y-m-d', $date);

        if ( $option == 'week' ) {
            $end = $dt->endOfWeek();
        } elseif ( $option == 'month' ) {
            $end = $dt->endOfMonth();
        } else {
            $term = Holiday::where('end', '>=', $dt->toDateString())
                ->orderBy('end')
                ->first();

            $end = Carbon::createFromFormat('Y-m-d', $term->end);
        }

        return $end->format('Y-m-d');
    }

    /**
     * Return array of dates for duplicates
     *
     * @param $start
     * @param $end
     * @param $holidays
     * @return array
     */
    private function targetDates($start, $end, $holidays)
    {
        $target_dates = [];
        $date = carbon::createFromFormat('Y-m-d', $start);

        while( $date->format('Y-m-d') <= $end ) {
            if ( !in_array($date->toDateString(), $holidays) ) {
                $target_dates[] = $date->format('Y-m-d');
            }
            $date->addDay();
        }

        return $target_dates;
    }
}