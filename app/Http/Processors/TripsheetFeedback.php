<?php
/**
 * Created by PhpStorm.
 * User: Ray
 * Date: 2018/04/23
 * Time: 7:30 AM
 */

namespace App\Http\Processors;


use App\Models\Booking;
use App\Models\Children;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TripsheetFeedback
{
    /**
     * TripsheetFeedback constructor.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Handles the trip sheet feedback from vehicles
     *
     * @return string
     */
    public function handle()
    {
        // departure ( putime is 1st 5 char's of request->info )
        if ( $this->request->has('departure') ) {
            $exists = DB::table('ts_feedback')->where('date', session('ts_date'))->where('data', $this->request->info)->first();
            if ( is_null($exists) ) {
                $this->logFeedback('departure');
            }

            return 'Departure Confirmed';

        // noshow ( putime is request->due )
        } elseif ( $this->request->has('noshow') ) {
            $this->logFeedback($this->request->passenger);

            return 'No Show Confirmed';

        // signature / sms ( putime is request->putime )
        } elseif ( $this->request->has('signed') || $this->request->has('sms') ) {
            // send sms
            $result = $this->sendSms();

            // save event and get entry id
//            if ( $result != 'No data to send' ) {
                $this->request->merge(['info' => $result]);
//            }
            $id = $this->logFeedback($this->request->passenger);

            // save signature in public/images/signatures named feedback->id.jpg
            if ( $this->request->has('sig') ) {
                if ( isset($id) ) {
                    $data_uri = $this->request->sig;
                    $encoded_image = explode(",", $data_uri)[1];
                    $decoded_image = base64_decode($encoded_image);
                    Storage::disk('signatures')->put("$id.jpg", $decoded_image);
                }
            }

            return 'Drop Off Confirmed';

        // notify delays via sms
        } elseif ( $this->request->has('delay') ) {
            $bookings = Booking::with('user')->where('date', session('ts_date'))->where('putime', '>=', now()->format('H:i:s'))->get();
            foreach ( $bookings as $booking ) {
                $numbers[] = $booking->user->mobile;
            }
            $numbers = implode(',',array_unique($numbers));
            $msg = 'We are experiencing major traffic delays in Hout Bay and shuttle times will be delayed. We will keep you posted. Shuttle Bug';

            $sms = new SmsApi();
            $sms->sendSms($numbers, $msg);

            return 'sent';
        }

        return null;
    }

    /**
     * Save the event
     *
     * @param $event
     * @return int
     */
    private function logFeedback($event)
    {
        if ( $event == 'departure' ) {
            $id = 0;
        } else {
            $putime = $this->request->info == 'noshow' ? $this->request->due : $this->request->putime;
            $pass_id = session('ts_passengers')[$event];

            // the putime refers to the latest pickup, any legacy passengers on this trip will have a different putime
            $found = collect(session('ts_bookings'))->where('passenger_id', $pass_id)->where('putime', $putime)->first();
            if ( !is_null($found) ) {
                $id = $found['id'];
            } else {
                $trip = DB::table('ts_'.$this->request->vehicle)->find($this->request->tripid);
                // am trips putime is 0
                if ( $trip->putime < '09:00:00' ) {
                    $id = collect(session('ts_bookings'))->where('passenger_id', $pass_id)->where('putime', '00:00:00')->first()['id'];
                } else {
                    $id = collect(session('ts_bookings'))->where('passenger_id', $pass_id)->where('putime', $trip->putime)->first()['id'] ?? 0;
                }
            }
        }

        $log_id = DB::table('ts_feedback')->insertGetId([
            'booking_id'    => $id,
            'date'          => session('ts_date'),
            'passenger'     => $event,
            'data'          => $this->request->info,
            'duetime'       => $this->request->due,
            'acttime'       => now()->format('H:i'),
            'vehicle'       => $this->request->vehicle
        ]);

        return $log_id;
    }

    /**
     * Send drop off sms
     *
     * @return string
     */
    private function sendSms()
    {
        $table = 'ts_'.$this->request->vehicle;

        $passenger =  Children::with('user')->where(\DB::raw('CONCAT(first_name," ",last_name)'), 'LIKE', trim($this->request->passenger))->first();
        $trip = DB::table($table)->find($this->request->tripid);
        $venue = $trip->venue == '' ? 'Home' : $trip->venue ?? '';

        if ( !is_null($passenger) ) {
            $msg = 'Hi ' . $passenger->user->first_name . ', ';
            $msg .= $passenger->first_name . ' has been dropped off at ';
            $msg .= $venue . '. ' . Carbon::now()->format('H:i') . ' Shuttle Bug';

            $sms = new SmsApi();
            $send = $sms->sendSms($passenger->user->mobile, $msg);

            return $send->pass == 'success' ? $msg : $send->msg;
        }

        return 'unknown passenger';
    }

    /**
     * Remove noshow from homeheroes statement
     *
     * @param $due
     */
//    private function adjustHomeheroes($due)
//    {
//        $field = $due < '09:00:00' ? 'am_passengers' : 'day_passengers';
//        $db = DB::table('homeheroes_statement')->where('date', session('ts_date'))->first();
//        if ( !is_null($db) ) {
//            $db->update([
//                $field => $db->$field - 1
//            ]);
//        }
//
//        return;
//    }
}