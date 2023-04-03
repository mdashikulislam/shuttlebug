<?php
/**
 * Created by PhpStorm.
 * User: Ray
 * Date: 2018/03/06
 * Time: 2:57 PM
 */

/*
    |--------------------------------------------------------------------------
    | EventInput
    |--------------------------------------------------------------------------
    |
    | Collates the event booking form input into groups for editing, cancelling & creating.
    | Provides input for all required fields.
    |
    */

namespace App\Http\Processors;


use App\Models\EventBooking;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\Request;

class EventInput
{
    /**
     * Process the booking form input
     *      Return the input data for each of edits, cancellations & creating
     *
     * @param Request $request
     * @return array
     */
    public function handle(Request $request)
    {
        $bookings['edits'] = $request->has('edit') ? $this->bookingEdits($request) : [];
        $bookings['cancellations'] = $request->has('edit') ? $this->bookingCancellations($request) : [];
        $bookings['new'] = $request->has('create') ? $this->bookingNew($request) : [];
        session()->forget('google');

        return $bookings;
    }

    /**
     * Collect bookings for editing
     *      Use existing booking data if location has not changed
     *      Return input arrays
     *
     * @param Request $request
     * @return array
     */
    private function bookingEdits($request)
    {
        $edits = [];

        foreach ( $request->edit as $id => $item ) {
            if ( !isset($item['cancel']) ) {
                $input = $request->only('user_id', 'date');
                $input = $input + $item;

                $existing = EventBooking::find($id);

                if ( $input['puloc'] == $existing->puloc ) {
                    $input['pugeo'] = $existing->pugeo;
                } else {
                    $input = $this->pulocData($input);
                }

                if ( $input['doloc'] == $existing->doloc ) {
                    $input['dogeo'] = $existing->pugeo;
                } else {
                    $input = $this->dolocData($input);
                }
                $edits[$id] = $input;
            }
        }

        return $edits;
    }

    /**
     * Collect bookings for cancellation
     *      Return array of booking ids
     *
     * @param Request $request
     * @return array
     */
    private function bookingCancellations($request)
    {
        $cancellations = [];

        foreach ( $request->edit as $id => $item ) {
            if ( isset($item['cancel']) ) {
                $cancellations[] = $id;
            }
        }

        return $cancellations;
    }

    /**
     * Collect the new bookings
     *      Return input arrays
     *
     * @param Request $request
     * @return array
     */
    private function bookingNew($request)
    {
        $new = [];

        foreach ( $request->create as $item ) {
            if ( $item['puloc'] > '' ) {
                $input = $request->only('user_id', 'date');
                $input = $input + $item;
                $input = $this->pulocData($input);
                $input = $this->dolocData($input);
                $new[] = $input;
            }
        }

        return $new;
    }

    /**
     * Populate the pickup fields with the appropriate data
     *      school will get the school geo
     *      home will get the user's geo
     *      addresses will be cleaned & google will be used to get the geo
     *      unless the same geo has already been requested in which case it
     *      is retrieved from the session.
     *
     * @param $input
     * @return mixed
     */
    private function pulocData($input)
    {
        if ( substr($input['puloc'],0,2) == '70' ) {
            $school = School::find($input['puloc']);
            $input['pugeo'] = $school->geo;
        } elseif ( $input['puloc'] == 'home' ) {
            $input['pugeo'] = User::find($input['user_id'])->geo;
        } else {
            $input['puloc'] = $this->cleanAddress($input['puloc']);

            // retrieve from session if recently captured
            if ( session()->has('google.'.$input['puloc']) ) {
                $input['pugeo'] = session('google.'.$input['puloc']);
            } else {
                // else get from google
                $google = new GoogleApi();
                $input['pugeo'] = $google->geocodeAddress($input['puloc']);
                session(['google.' . $input['puloc'] => $input['pugeo']]);
            }
        }

        return $input;
    }

    /**
     * Populate the drop off fields with the appropriate data
     *      school will get the school geo
     *      home will get the user's geo
     *      addresses will be cleaned & google will be used to get the geo
     *      unless the same geo has already been requested in which case it
     *      is retrieved from the session.

     *
     * @param $input
     * @return mixed
     */
    private function dolocData($input)
    {
        if ( substr($input['doloc'],0,2) == '70' ) {
            $school = School::find($input['doloc']);
            $input['dogeo'] = $school->geo;
        } elseif ( $input['doloc'] == 'home' ) {
            $input['dogeo'] = User::find($input['user_id'])->geo;
        } else {
            $input['doloc'] = $this->cleanAddress($input['doloc']);

            // retrieve from session if recently captured
            if ( session()->has('google.'.$input['doloc']) ) {
                $input['dogeo'] = session('google.'.$input['doloc']);
            } else {
                // else get from google
                $google = new GoogleApi();
                $input['dogeo'] = $google->geocodeAddress($input['doloc']);
                session(['google.' . $input['doloc'] => $input['dogeo']]);
            }
        }

        return $input;
    }

    /**
     * Return cleaned address
     *      Strip spaces and capitalise words
     *
     * @param $address
     * @return string
     */
    private function cleanAddress($address)
    {
        $lines = explode(',', $address);
        foreach ( $lines as $line ) {
            $cleaned[] = ucwords(strtolower(trim($line)));
        }

        return implode(',', $cleaned);
    }
}