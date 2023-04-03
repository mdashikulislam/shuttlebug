<?php
/**
 * Created by PhpStorm.
 * User: Ray
 * Date: 2018/02/27
 * Time: 3:14 AM
 */

/*
    |--------------------------------------------------------------------------
    | BookingInput
    |--------------------------------------------------------------------------
    |
    | Collates the form input into groups for editing, cancelling & creating.
    | Provides input for all required fields and calls BookingPrice for the
    | booking's ruling price for each shuttle.
    |
    | Bookings outside than the current invoicing month generate journal entries
    |
    */

namespace App\Http\Processors;


use App\Models\Booking;
use App\Models\DebtorsStatement;
use App\Models\TripSettings;
use Illuminate\Http\Request;


class BookingInput
{
    /**
     * @var BookingPrice
     */
    protected $price;
    protected $settings;

    /**
     * BookingInput constructor.
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
     * Process the booking form input
     *
     * @param Request $request
     * @return array
     */
    public function handle(Request $request)
    {
        $bookings['edits'] = $request->has('edit') ? $this->bookingEdits($request) : [];
        $bookings['cancellations'] = $request->has('edit') ? $this->bookingCancellations($request) : [];
        $bookings['new'] = $request->has('create') ? $this->bookingNew($request) : [];

        return $bookings;
    }

    /**
     * Collect bookings for editing
     *
     * @param Request $request
     * @return mixed
     */
    private function bookingEdits($request)
    {
        $edits = [];
        foreach ( $request->edit as $id => $item ) {

            if ( !isset($item['cancel']) ) {
                $q = Booking::find($id);

                // ignore if there's no change to the booking
                if ( $q->puloc_id != $item['puloc_id'] || $q->doloc_id != $item['doloc_id'] ||
                    ($q->putime == '00:00:00' && !empty($item['putime'])) ||
                    ($q->putime > '00:00:00' && $q->putime != $item['putime'].':00') ) {

                    // edited bookings only need validation if the pickup venue or drop off venue has changed
                    if ( $q->puloc_id != $item['puloc_id'] || $q->doloc_id != $item['doloc_id'] ) {
                        $valid = Booking::validBooking($item, $request->date, $this->locationType($item['puloc_id']), $this->locationType($item['doloc_id']));
                    } else {
                        $valid = true;
                    }
                    if ( $valid ) {
                        $input = $request->only('user_id', 'passenger_id', 'date');
                        $input = $input + $item;
                        $input['puloc_type'] = $this->locationType($input['puloc_id']);
                        $input['doloc_type'] = $this->locationType($input['doloc_id']);
                        $ruling = $this->price->shuttlePrice((object) $input);
                        $input['price'] = $ruling['price'];
                        $input['promo'] = $ruling['promo'];
                        $edits[$id] = $input;
                    } else {
                        return ['invalid' => []];
                    }
                }
            }
        }

        return $edits;
    }

    /**
     * Collect bookings for cancellation
     * array of cancellations for journal entries
     * array of cancellations for bookings table
     *
     * @param Request $request
     * @return array
     */
    private function bookingCancellations($request)
    {
        $invMonth = DebtorsStatement::postingMonth();
        $cancellations['journal'] = $cancellations['booking'] = [];

        foreach ( $request->edit as $id => $item ) {
            if ( isset($item['cancel']) ) {
                /**
                 * Journal entries are required when the transaction occurs after invoice production
                 * so that the debtor's statement can be updated.
                 * cancellations earlier than the current invoicing month will be journalised.
                 * if it's month-end today and later than noon, this month's cancellations will also be journalised.
                 */

                if ( $request->date < $invMonth->start ||
                    ($request->date <= $invMonth->end && now()->toDateString() == $invMonth->end && now()->hour > 12) ) {
                    $cancellations['journal'][$id] = $item['cancel'];
                } else {
                    $cancellations['booking'][] = $id;
                }
            }
        }

        return $cancellations;
    }

    /**
     * Collect new bookings
     *
     * @param Request $request
     * @return array
     */
    private function bookingNew($request)
    {
        $invMonth = DebtorsStatement::invMonth();
        $new = [];

        foreach ( $request->create as $item ) {
            if ( !isset($item['cancel']) && $item['puloc_id'] > '' ) {
                // process booking if valid
                if ( Booking::validBooking($item, $request->date, $this->locationType($item['puloc_id']), $this->locationType($item['doloc_id'])) ) {

                    $input = $request->only('user_id', 'passenger_id', 'date');
                    $input = $input + $item;
                    $input['puloc_type'] = $this->locationType($input['puloc_id']);
                    $input['doloc_type'] = $this->locationType($input['doloc_id']);
                    $ruling = $this->price->shuttlePrice((object) $input);
                    $input['price'] = $ruling['price'];
                    $input['promo'] = $ruling['promo'];

                    // new bookings earlier than the current invoicing month must be journalised
                    // if it's month-end today and later than noon, this month's cancellations must also be journalised
                    if ( $request->date < $invMonth->start ||
                        ($request->date <= $invMonth->end && now()->toDateString() == $invMonth->end && now()->hour > 12) ) {
                        $input['journal'] = 'added';
                    }
                    $new[] = $input;
                } else {
                    return ['invalid' => []];
                }
            }
        }

        return $new;
    }

    /**
     * Return the venue type
     *
     * @param $id
     * @return string
     */
    private function locationType($id)
    {
        if ( $id < 200000 ) {
            return 'user';
        } elseif ( $id < 400000 ) {
            return 'xmural';
        }

        return 'school';
    }
}
