<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;



/** @mixin \Eloquent */
class PlanningReport extends Model
{
    /**
     * The table properties
     *
     * @var array
     */
    protected $table    = 'planning_reports';
    protected $fillable = ['unused','date','am','day','warnings','am_vehicles','day_vehicles','updated_am','updated_day'];
    public $timestamps  = false;
    protected $casts = [
        'am' => 'array',
        'day' => 'array',
        'warnings' => 'array',
        'am_vehicles' => 'array',
        'day_vehicles' => 'array',
    ];

    /**
     * Return the period report for a given date
     * if the used vehicles are the same as selected and
     * if the bookings have not been revised after the plan.
     *
     * @param object    $request
     * @return mixed
     */
    public function getExisting($request)
    {
        $factor = $request->period == 'am' ? '<' : '>=';
        $veh_field = $request->period == 'am' ? 'am_vehicles' : 'day_vehicles';
        $upd_field = $request->period == 'am' ? 'updated_am' : 'updated_day';

        // get the bookings
        $booking = Booking::where('date', $request->date)
            ->where('putime', $factor, '09:00:00')
            ->orderBy('updated_at', 'desc')
            ->first();

        // return false if there are no bookings
        if ( is_null($booking) ) {
            return [false, null];

        // get the date of the latest booking update
        // obsolete when using sync
//        } else {
//            $log = DB::table('_log_cancelled_bookings')->where('date', $request->date)->first();
//            $log_dt = !is_null($log) ? Carbon::parse($log->$upd_field) : now()->subYear();
//
//            $book_dt = $booking->updated_at ?? now()->subYear();
//            $updated = max($log_dt, $book_dt)->toDateTimeString();
        }

        // get the saved plan if exists
        $report = self::select('id', 'date', $request->period, 'warnings', $veh_field, $upd_field)
            ->where('date', $request->date)
            ->where($request->period, '>', '')
            ->first();

        // return null if there is no report or there are no vehicles
        if ( is_null($report) || empty($report->$veh_field) ) {
            return [null, null];
        }

        // return the report with warning if vehicles used in the saved report are different
        foreach ( $report->$veh_field as $id => $vehicle ) {
            if ( $vehicle['seats'] > 3 && !in_array($id, $request->day_vehicles) ) {
                return [$report, 'warning'];
            }
        }

        return [$report, null];
    }
}
