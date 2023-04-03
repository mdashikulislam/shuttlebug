<?php
/**
 * Created by PhpStorm.
 * User: Ray
 * Date: 2018/01/30
 * Time: 8:23 AM
 */

use App\Models\Holiday;
use Carbon\Carbon;

/**
 * Set class to active - match full path
 *       use class="{!! set_active('office/users') !!}">
 *
 * @param $path
 * @return string
 */
function set_full_active($path)
{
    $path = App::getLocale() == 'en' ? $path : App::getLocale().'/'.$path;

    return Request::is($path) ? 'active' : '';
}

/**
 * Set class to active - match start path
 *
 * @param $path
 * @return string
 */
function set_start_active($path)
{
    $path = App::getLocale() == 'en' ? $path : App::getLocale().'/'.$path;

    return Request::is($path.'*') ? 'active' : '';
}

/**
 * Set class to active - match includes path
 *
 * @param $path
 * @return string
 */
function set_incl_active($path)
{
    return Request::is('*'.$path.'*') ? 'active' : '';
}

/**
 * Sort collection on multiple columns
 *      laravel collections cannot handle this
 *      $order syntax = 'colname sortorder,colname sortorder'
 *      eg: 'time asc,pass desc'
 *
 * @param $array
 * @param $order
 * @return mixed
 */
function multiSortCollection($array, $order)
{
    $orders = explode(',', $order);
    usort($array, function($a, $b) use($orders) {
        $result = array();
        foreach ($orders as $value) {
            list($field, $sort) = array_map('trim', explode(' ', trim($value)));
            if (!(isset($a->$field) && isset($b->$field))) {
                continue;
            }
            if (strcasecmp($sort, 'desc') === 0) {
                $tmp = $a;
                $a = $b;
                $b = $tmp;
            }
            if (is_numeric($a->$field) && is_numeric($b->$field) ) {
                $result[] = $a->$field - $b->$field;
            } else {
                $result[] = strcmp($a->$field, $b->$field);
            }
        }
        return implode('', $result);
    });

    return $array;
}

/**
 * Format name
 *
 * @param $string
 * @return mixed|null|string|string[]
 */
function formatName($string)
{
    $dummy = 'zxyz ';
    $word_splitters = array(' ','.','&','-',"O'","L'","D'",'St.','Mc','/','(',')');
    $lowercase_exceptions = array('van','den','von','und','der','de','da','du','of','v ','d ','vd',"l'");
    $uppercase_exceptions = array('III','IV','VI','VII','VIII','IX','LED','SS','CE','XM','DTS','(POR)','HD','GPS','GAP','GSD');

    $string = mb_strtolower($dummy.$string);
    foreach ( $word_splitters as $delimiter ) {
        $words = explode($delimiter, $string);
        $newwords = array();
        foreach ( $words as $word ) {

            if ( in_array(mb_strtoupper($word), $uppercase_exceptions) ) {
                $word = mb_strtoupper($word);
            }
            else {
                if ( !in_array($word, $lowercase_exceptions) ) {
                    $word = ucfirst($word);
                }
            }
            $newwords[] = $word;
        }

        if ( in_array(mb_strtolower($delimiter), $lowercase_exceptions) ) {
            $delimiter = mb_strtolower($delimiter);
        }

        $string = join($delimiter, $newwords);
    }
    $string = str_replace('Zxyz ', '', $string);
    $string = str_replace(',', '', $string);
    $string = str_replace(' and ', ' & ', $string);

    return $string;
}

/**
 * Format phone number
 *
 * @param $number
 * @return null|string|string[]
 */
function formatPhone($number) {
    $number = preg_replace('/[^0-9]/', '', $number);

    if ( !empty($number) ) {
        $number = substr($number, 0, 3).' '.substr($number, 3, 3).' '.substr($number, 6, 4);
    }
    return $number;
}

/**
 * Format geo for storing in table
 *  receives format as yy.yyyyyyy,xx.xxxxxxx
 *  returns same format limited to 7 decimals
 *
 * @param $value
 * @return string
 */
function setLatLon($value) {
    if ( strlen($value) > 22 ) {
        $lat = substr($value,0,strpos($value,','));
        $lat = substr($lat,0,11);
        $lon = substr($value,strpos($value,','));
        $lon = substr($lon,0,11);
        return $lat.$lon;
    }

    return $value;
}

/**
 * Return the zone of the given geo location
 *  will return in, north, east or south
 *  where in = hout bay & llandudno,
 *  east = southern suburbs,
 *  north = atlantic seaboard & city,
 *  south = peninsula
 *
 * used to allocate vehicles to pickups and passengers
 * in trip planning
 *
 * @param $geo
 * @return string
 */
function getZone($geo) {
    $lat = floatval(substr($geo,0,strpos($geo,',')));
    $lon = floatval(substr($geo,strpos($geo,',')+1));

    if ( $lon >= 18.404431 && $lat <= -33.946165 && $lat >= -34.108278 ) {
        return 'east';
    } elseif ( $lat >= -33.999114 ) {
        return 'north';
    } elseif ( $lat <= -34.064422 ) {
        return 'south';
    } else {
        return 'in';
    }
}

/**
 * Return array of years
 *  1990 to present
 *
 * @return mixed
 */
function dobYears()
{
    for ( $y = (int) date('Y'); $y >= 1990; $y-- ) {
        $years[$y] = $y;
    }

    return $years;
}

/**
 * Return array of months
 *
 * @return mixed
 */
function dobMonths()
{
    for ( $m = 1; $m <= 12; $m++ ) {
        $months[sprintf('%02d', $m)] = sprintf('%02d', $m);
    }

    return $months;
}

/**
 * Return array of days
 *
 * @return mixed
 */
function dobDays()
{
    for ( $d = 1; $d <= 31; $d++ ) {
        $days[sprintf('%02d', $d)] = sprintf('%02d', $d);
    }

    return $days;
}

/**
 * Return the # of school days in each month of given year
 *
 * @param $year
 * @return array
 */
function schoolDays($year)
{
    $holidays = collect(Holiday::allHolidays($year))->groupBy(function ($item, $key) {
        return Carbon::parse($item)->format('m');
    })->all();

    foreach ( $holidays as $key => $dates ) {
        $month_holidays[(int) $key] = count($dates);
    }

    for ( $i = 1; $i <= 12; $i++ ) {
        $dt = Carbon::createFromDate($year, $i, 15);
        $days = $dt->copy()->startOfMonth()->diffInWeekdays($dt->copy()->endOfMonth());
        $holidays = $month_holidays[$i] ?? 0;
        $schooldays[$i] = $days - $holidays;
    }

    return $schooldays;
}

/**
 * Return the number of school days between two dates
 *
 * @param $start
 * @param $end
 * @return int
 */
function schoolDaysBetween($start, $end)
{
    $holidays = collect(Holiday::allHolidays(Carbon::parse($start)->year))->filter(function ($item) use ($start, $end) {
        return Carbon::parse($item)->toDateString() >= $start && Carbon::parse($item)->toDateString() <= $end;
    })->all();

    if ( Carbon::parse($start)->year < Carbon::parse($end)->year ) {
        $next_holidays = collect(Holiday::allHolidays(Carbon::parse($end)->year))->filter(function ($item) use ($start, $end) {
            return Carbon::parse($item)->toDateString() >= $start && Carbon::parse($item)->toDateString() <= $end;
        })->all();
        $holidays = array_unique(array_merge($holidays, $next_holidays));
    }

    $days = 0;
    for ( $i = 0; $i <= Carbon::parse($start)->diffInDays(Carbon::parse($end)); $i++ ) {
        $target = Carbon::parse($start)->addDays($i);
        $days = $target->isWeekday() && !in_array($target->toDateString(), $holidays) ? $days + 1 : $days;
    }

    return $days;
}

/**
 * Convert array of arrays to array of objects
 *
 * @param $array
 * @return array
 */
function convertArrayToObject($array) {
    $object = json_decode(json_encode($array));

    return $object;
}