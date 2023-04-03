<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/** @mixin \Eloquent */
class Holiday extends Model
{
    /**
     * The table properties
     *
     * @var array
     */
    protected $table    = 'schoolterms';
    protected $fillable = ['start','end'];
    public $timestamps  = false;

    /**
     * Return school holiday dates
     *
     * @param $year
     * @return array
     */
    public static function schoolHolidays($year)
    {
        return DB::table('schoolholidays')
            ->whereYear('date', $year)
            ->whereNotIn(DB::raw("DAYOFWEEK(date)"), [1,7])
            ->orderBy('date')
            ->get()->pluck('date')->all();
    }

    /**
     * Return public holiday dates
     *
     * @param $year
     * @return array
     */
    public static function publicHolidays($year)
    {
        return DB::table('publicholidays')
            ->whereYear('date', $year)
//            ->whereNotIn(DB::raw("DAYOFWEEK(date)"), [1,7])
            ->orderBy('date')
            ->get()->pluck('date')->all();
    }

    /**
     * Return all holiday dates
     *  cache when both school & public holidays exist
     *
     * @param $year
     * @return array
     */
    public static function allHolidays($year)
    {
        $holidays = Cache::get('holidays'.$year, function() use($year) {
            $school = self::schoolHolidays($year);

            if ( count($school) > 0 ) {
                $public = self::publicHolidays($year);

                foreach ( $public as $key => $day ) {
                    if ( Carbon::parse($day)->isWeekend() ) {
                        unset($public[$key]);
                    }
                }

                if ( count($public) > 0 ) {
                    $holidays = array_merge($school, $public);
                    $holidays = array_unique($holidays);
                    sort($holidays);
                    Cache::forever('holidays' . $year, $holidays);
                } else {
                    $holidays = null;
                }
            } else {
                $holidays = null;
            }
        });

        return $holidays;
    }
}
