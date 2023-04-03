<?php
/**
 * Created by PhpStorm.
 * User: Ray
 * Date: 2018/11/07
 * Time: 8:18 AM
 */

namespace App\Http\Processors;


use App\Models\Booking;
use App\Models\DebtorsStatement;
use App\Models\School;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class Analytics
{
    /**
     * @var Booking
     */
    protected $booking;

    /**
     * Analytics constructor.
     *
     * @param Booking $booking
     */
    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
    }

    /**
     * Return the summary stats for the given year
     * returns stats array for each month: sales value, # bookings, month#, #days
     * and schooldays array:month#, # of schooldays
     *
     * @param int   $year
     * @return array
     */
    public function monthlyStats($year)
    {
        $selects = ['SUM(price) as value','COUNT(*) as count','MONTH(date) as month'];

        if ( $year >= now()->subYear()->year ) {
            $stats = $this->booking->selectRaw(implode(',', $selects))
                ->whereYear('date', $year)
                ->groupBy('month')
                ->orderBy('month')->get();
        } else {
            $stats = DB::connection('sbugarchive')->table('_'.$year.'_bookings')->selectRaw(implode(',', $selects))
                ->whereYear('date', $year)
                ->groupBy('month')
                ->orderBy('month')->get();
        }

        $schooldays = schoolDays($year);

        // convert collection to array & fill every month in case of zero bookings
        if ( $year >= now()->subYear()->year ) {
            $stats = $stats->toArray();
        } else {
            $stats = json_decode(json_encode($stats), true);
        }

        $end = $year == now()->year ? now()->month : 12;
        for ( $i = 1; $i <= $end; $i++ ) {
            $stat = Arr::where($stats, function ($item) use($i) {
                return $item['month'] == $i;
            });
            $cleanstats[$i] = [
                'value' => count($stat) > 0 ? array_values($stat)[0]['value'] : 0,
                'count' => count($stat) > 0 ? array_values($stat)[0]['count'] : 0,
                'month' => count($stat) > 0 ? array_values($stat)[0]['month'] : $i,
                'days'  => $schooldays[$i]
            ];
        }

        return $cleanstats;
    }

    /**
     * Return the # of bookings per day for previous, current and next month
     *
     * @return array date, bookings
     */
    public function dailyStats()
    {
        $daily['prev'] = $daily['curr'] = $daily['next'] = [];

        $selects = ['COUNT(*) as count','date'];

        $stats = $this->booking->selectRaw(implode(',',$selects))
            ->where('date', '>=', now()->subMonthNoOverflow()->startOfMonth()->toDateString())
            ->where('date', '<=', now()->addMonthNoOverflow()->lastOfMonth()->toDateString())
            ->groupBy('date')
            ->orderBy('date')->get();

        foreach ( $stats as $stat ) {
            if ( Carbon::parse($stat->date)->month == now()->subMonthNoOverflow()->month ) {
                $daily['prev'][$stat->date] = $stat->count;
            } elseif ( Carbon::parse($stat->date)->month == now()->month ) {
                $daily['curr'][$stat->date] = $stat->count;
            } else {
                $daily['next'][$stat->date] = $stat->count;
            }
        }

        return $daily;
    }

    /**
     * Return financial stats
     *
     * @param $year
     * @return \Illuminate\Support\Collection|array
     */
    public function financialStats($year)
    {
        if ( $year == now()->year ) {
            $selects = ['fin_m as month','COUNT(*) as bookings','SUM(price) as gross'];
            $stats = Booking::selectRaw(implode(',', $selects))
                ->whereYear('date', $year)
                ->where('date', '<=', Carbon::parse(now()->year.'-'.now()->month.'-28'))
                ->where('journal', '')
                ->groupBy('month')
                ->orderBy('month')->get();

            $selects = ['SUM(amount) as value','MONTH(date) as month'];
            $invoiced = DebtorsStatement::selectRaw(implode(',', $selects))
                ->whereYear('date', $year)
                ->where('transaction', 'Invoice')
                ->groupBy('month')
                ->orderBy('month')->get();

            foreach ( $stats as $stat ) {
                $nett = $invoiced->where('month', $stat->month)->first();
                if ( !is_null($nett) ) {
                    $stat->nett = $nett->value;
                } else {
                    $stat->nett = 0;
                }
            }

        } else {
            $selects = ['COUNT(*) as bookings','SUM(price) as gross'];
            $stats = Booking::selectRaw(implode(',', $selects))
                ->whereYear('date', $year)
                ->where('journal', '')
                ->get();

            $selects = ['SUM(amount) as value'];
            $invoiced = DebtorsStatement::selectRaw(implode(',', $selects))
                ->whereYear('date', $year)
                ->where('transaction', 'Invoice')
                ->get();

            $stats->first()->nett = $invoiced->first()->value;

            return $stats->toArray()[0];
        }

        $end = now()->month;
        for ( $i = 1; $i <= $end; $i++ ) {
            $stat = Arr::where($stats->toArray(), function ($item) use($i) {
                return $item['month'] == $i;
            });
            $cleanstats[$i] = [
                'month'     => count($stat) > 0 ? array_values($stat)[0]['month'] : $i,
                'bookings'  => count($stat) > 0 ? array_values($stat)[0]['bookings'] : 0,
                'gross'     => count($stat) > 0 ? array_values($stat)[0]['gross'] : 0,
                'nett'      => count($stat) > 0 ? array_values($stat)[0]['nett'] : 0,
            ];
        }

        return $cleanstats;

//        $stats = $stats->toArray();
//        array_unshift($stats, "renum");
//        unset($stats[0]);
//
//        return $stats;
    }

    /**
     * Return Homeheroes stats per month
     *
     * @param $postingMonth
     * @return mixed
     */
    public function financialHh($postingMonth)
    {
        $start = Carbon::parse(now()->subYear()->year.'-12-29');
        $db = DB::table('homeheroes_statement')
            ->whereBetween('date', [$start->toDateString(), now()->subDay()->toDateString()])
            ->orderBy('date')
            ->get();

        for ( $i = 1; $i <= now()->month; $i++ ) {
            $from = $start->copy()->addMonths($i - 1);
            $to = $from->toDateString() == $from->year.'-03-01' ? Carbon::parse($from->year.'-'.$from->month.'-28') : Carbon::parse($from)->addMonth()->subDay();
            $to = $to->month == now()->month ? now()->subDay() : $to;

            $hh[$i] = [
                'month' => $i,
                'days'  => $db->where('date', '>=', $from->toDateString())->where('date', '<=', $to->toDateString())->pluck('date')->unique()->count(),
                'pass'  => $db->where('date', '>=', $from->toDateString())->where('date', '<=', $to->toDateString())->sum('passengers'),
                'value' => $db->where('date', '>=', $from->toDateString())->where('date', '<=', $to->toDateString())->sum('value')
            ];
        }

        // average daily lifts by innova in last 10 days
        $innova = Booking::whereBetween('date', [now()->subDays(10)->toDateString(), now()->toDateString()])->where('vehicle', 102)->count()/10;

        $remaining = Booking::whereBetween('date', [now()->toDateString(), $postingMonth->end])->get();
        $rem_days = $remaining->pluck('date')->unique()->count();

        $hh_rem_lifts = $remaining->count() - ($rem_days * $innova);
        $hh_rem_lifts = $hh_rem_lifts > 0 ? $hh_rem_lifts : 0;

        $hh_acp = collect($hh)->sum('pass') > 0 ? collect($hh)->sum('value') / collect($hh)->sum('pass') : 0;
        $hh[now()->month]['days'] += $rem_days;
        $hh[now()->month]['pass'] += $hh_rem_lifts;
        $hh[now()->month]['value'] += (int) ($hh_rem_lifts * $hh_acp);

        return $hh;
    }

    /**
     * Return homeheroes cos value for previous year
     *
     * @return int
     */
    public function financialHhLastYear()
    {
        return DB::table('homeheroes_statement')
            ->whereYear('date', now()->subYear()->year)
            ->sum('value');
    }

    /**
     * Return the # bookings per school for the given year
     *
     * @param $year
     * @param $schools
     * @return \Illuminate\Support\Collection|mixed|static
     */
    public function schoolStats($year, $schools)
    {
        if ( in_array($year, [now()->year, now()->subYear()->year]) ) {
            $schools = School::withCount(['bookings' => function ($q) use ($year) {
                $q->whereYear('date', $year);
            }])->get();

        } else {
            $bookings = DB::connection('sbugarchive')->table('_'.$year.'_bookings')
                ->whereIn('puloc_id', array_column($schools->toArray(), 'id'))
                ->get();

            foreach( $schools as $school ) {
                $school->bookings_count = $bookings->where('puloc_id', $school->id)->count();
            }
        }

        $schools = $schools->filter(function($item) {
            return $item->bookings_count > 0;
        });

        $schools = json_decode(json_encode($schools), true);

        return $schools;
    }

    /**
     * Return lifts by time group
     *
     * @param array $term_ends
     * @return mixed
     */
    public function tripDemographics($term_ends)
    {
        $periods = $times = $venues = [];

        $bookings = $this->booking
            ->whereNotIn('date', $term_ends)
            ->whereMonth('date', '<=', now()->month)
            ->get();

        $years[now()->year] = collect($bookings)
            ->where('date', '>=', now()->startOfYear()->toDateString())
            ->all();
        $years[now()->subYear()->year] = collect($bookings)
            ->where('date', '<=', now()->subYear()->lastOfYear()->toDateString())
            ->all();

        foreach ( $years as $year => $data ) {
            $periods[$year] = collect($data)->count();
            $times[$year][9] = collect($data)->where('putime', '<', '09:00:00')->count();
            $times[$year][12] = collect($data)->where('putime', '>=', '09:00:00')->where('putime', '<', '12:00:00')->count();
            $times[$year][16] = collect($data)->where('putime', '>=', '12:00:00')->where('putime', '<=', '16:00:00')->count();
            $times[$year][18] = collect($data)->where('putime', '>', '16:00:00')->count();
            $venues[$year]['htos'] = collect($data)->where('puloc_type', 'user')->where('doloc_type', 'school')->count();
            $venues[$year]['stoh'] = collect($data)->where('puloc_type', 'school')->where('doloc_type', 'user')->count();
            $venues[$year]['fromx'] = collect($data)->where('puloc_type', 'xmural')->where('doloc_type', '!=', 'xmural')->count();
            $venues[$year]['tox'] = collect($data)->where('doloc_type', 'xmural')->count();
            $venues[$year]['tox'] = $venues[$year]['tox'] + collect($data)->where('puloc_type', 'school')->where('doloc_type', 'school')->count();
        }

        return [$periods, $times, $venues];
    }

    /**
     * Return number of bookings by time slots
     *
     * @param $term_ends
     * @param $year
     * @return mixed
     */
    public function trendsTimeSlots($term_ends, $year = null)
    {
        $result = [];
        $selects = ['putime', 'COUNT(putime) as count'];

        if ( is_null($year) ) {
            $bookings = $this->booking->selectRaw(implode(',', $selects))
                ->whereBetween('date', [now()->subDays(30)->toDateString(), now()->toDateString()])
                ->whereNotIn('date', $term_ends)
                ->where('putime', '>=', '12:00:00')
                ->where('puloc_type', 'school')
                ->groupBy('putime')
                ->orderBy('putime')
                ->get();

        } else {
            $bookings = $this->booking->selectRaw(implode(',', $selects))
                ->whereYear('date', $year)
                ->whereNotIn('date', $term_ends)
                ->where('putime', '>=', '12:00:00')
                ->where('puloc_type', 'school')
                ->groupBy('putime')
                ->orderBy('putime')
                ->get();
        }

        $adj_bookings = $this->adjustTimeSlots($bookings);
        $stats = collect($adj_bookings)
            ->groupBy('putime')
            ->sortBy('putime')
            ->toArray();

        foreach ( $stats as $time => $lifts ) {
            $result[$time] = collect($lifts)->sum('count');
        }
        ksort($result);

        return $result;
    }

    /**
     * Return bookings with adjusted pickup times
     *
     * @param $bookings
     * @return mixed
     */
    private function adjustTimeSlots($bookings)
    {
        foreach ( $bookings as $booking ) {
            // 12 -> 12:19 = 12:00
            if ( $booking->putime >= '12:00:00' && $booking->putime < '12:20:00' ) {
                $booking->putime = '12:00:00';
            // 12:20 -> 12:29 = 12:20
            } elseif ( $booking->putime >= '12:20:00' && $booking->putime < '12:30:00' ) {
                $booking->putime = '12:20:00';
            // 12:30 -> 16:00
            } elseif ( $booking->putime >= '12:30:00' && $booking->putime <= '16:00:00' ) {
                $time = Carbon::parse($booking->putime)->startOfHour()->toTimeString();
                while ( $time <= '16:00:00' ) {
                    $end = Carbon::parse($time)->addMinutes(15)->toTimeString();
                    if ( $booking->putime >= $time && $booking->putime < $end ) {
                        $booking->putime = $time;
                        $time = '16:01:00';
                    } else {
                        $time = $end;
                    }
                }
                // after 16:00
            } else {
                $booking->putime = '16:30:00';
            }
        }

        return $bookings;
    }

    /**
     * Return # bookings by school for 2 years plus current month
     *
     * @param $term_ends
     * @return array
     */
    public function trendsSchools($term_ends)
    {
        $schools = School::whereIn('suburb', ['Llandudno','Hout Bay'])->pluck('name', 'id')->all();

        $bookings = $this->booking
            ->whereIn('puloc_id', array_keys($schools))
            ->whereNotIn('date', $term_ends)
            ->whereMonth('date', '<=', now()->month)
            ->whereBetween('putime', ['12:00:00','16:00:00'])
            ->get();

        foreach ( $schools as $id => $name ) {
            $stats[] = [
                'school'                => $name,
                now()->subYear()->year  => $bookings->where('puloc_id', $id)->where('date', '<', now()->firstOfYear()->toDateString())->count(),
                now()->year             => $bookings->where('puloc_id', $id)->where('date', '>=', now()->firstOfYear()->toDateString())->count(),
                'current'               => $bookings->where('puloc_id', $id)->where('date', '>=', now()->firstOfMonth()->toDateString())->count(),
            ];
        }

        return $stats;
    }

    /**
     * Return customer booking trends
     *
     * @return array
     */
    public function statsCustomers()
    {
        $selects = ['user_id', 'COUNT(user_id) as count'];

        $bookings[now()->subYear()->year] = $this->booking->selectRaw(implode(',', $selects))
            ->whereYear('date', now()->subYear()->year)
            ->groupBy('user_id')
            ->get()->sortByDesc('count')->pluck('count', 'user_id')->all();

        $bookings[now()->year] = $this->booking->selectRaw(implode(',', $selects))
            ->whereYear('date', now()->year)
            ->whereMonth('date', '<=', now()->month)
            ->groupBy('user_id')
            ->get()->sortByDesc('count')->pluck('count', 'user_id')->all();

        $bookings['current'] = $this->booking->selectRaw(implode(',', $selects))
            ->whereYear('date', now()->year)
            ->whereMonth('date', now()->month)
            ->groupBy('user_id')
            ->get()
            ->sortByDesc('count')->pluck('count', 'user_id')->all();

        return $bookings;
    }

    /**
     * Return bookings by vehicle by month
     *
     * @param $start
     * @return \Illuminate\Support\Collection
     */
    public function statsVehicles($start)
    {
        $selects = ['vehicle','MONTH(date) as month','COUNT(*) as bookings'];
        $bookings = $this->booking->selectRaw(implode(',', $selects))
            ->whereBetween('date', [$start, now()->toDateString()])
            ->where('journal', '')
            ->groupBy('month','vehicle')
            ->get();

        // move any unallocated bookings to innova
        $invalid = $bookings->where('vehicle', 0)->all();
        foreach ( $invalid as $reject ) {
            $booking = $bookings->where('vehicle', 0)->where('month', $reject->month)->first();
            if ( !is_null($booking) ) {
                $booking->bookings += $reject->bookings;
            }
        }
        $stats = $bookings->filter(function ($item) {
            return !empty($item->vehicle);
        });

        return $stats;
    }

    /**
     * Return days per month for the period
     *
     * @return array
     */
    public function monthDays($period, $start)
    {
        $days = [];
        $smonth = Carbon::parse($start)->month;

        if ( Carbon::parse($start)->year < now()->year ) {
            $sdays[now()->subYear()->year] = schoolDays(now()->subYear()->year);
            $split = now()->subMonthsNoOverflow($period)->month;
            $days = array_slice($sdays[now()->subYear()->year], $split - 13, $split, true);
            $sdays[now()->year] = schoolDays(now()->year);

            for ( $n = 1; $n < now()->month; $n++ ) {
                $days[$n] = $sdays[now()->year][$n];
            }
            $days[now()->month] = schoolDaysBetween(now()->firstOfMonth()->toDateString(), now()->toDateString());

        } else {
            $limit = now()->year == 2019 ? $smonth : now()->subMonthsNoOverflow($period)->month;
            $sdays[now()->year] = schoolDays(now()->year);

            for ( $n = $limit; $n < now()->month; $n++ ) {
                $days[$n] = $sdays[now()->year][$n];
            }
            $days[now()->month] = schoolDaysBetween(now()->firstOfMonth()->toDateString(), now()->toDateString());
        }

        return $days;
    }

    /**
     * Return array of the vehicles that entered or left service in stats period
     *
     * @param $vehicles
     * @return mixed
     */
    public function vehicleDays($vehicles, $start)
    {
        $exvehicles = collect($vehicles)->where('id', '>=', 101)->where('id', '<=', 107)->pluck('id')->all();

        $history = $this->booking
            ->whereBetween('date', [$start, now()->toDateString()])
            ->whereIn('vehicle', $exvehicles)
            ->orderBy('date', 'desc')
            ->get();

        foreach ( $history->pluck('vehicle')->unique()->all() as $vehicle ) {
            $last = $history->where('vehicle', $vehicle)->first()->date;
            $days[$vehicle] = [
                'end'   => $last,
                'month' => Carbon::parse($last)->month,
                'days'  => schoolDaysBetween(Carbon::parse($last)->firstOfMonth()->toDateString(), $last)
            ];
        }

        $bookings = $this->booking
            ->whereBetween('date', [$start, now()->toDateString()])
            ->whereNotIn('vehicle', $history->pluck('vehicle')->unique()->all())
            ->orderBy('date')
            ->groupBy('vehicle', 'date')
            ->get();

        foreach ( $bookings->pluck('vehicle')->unique()->all() as $vid ) {
            $first = $bookings->where('vehicle', $vid)->first()->date;
            if ( $first > $start ) {
                $days[$vid] = [
                    'start' => $first,
                    'month' => Carbon::parse($first)->month,
                    'days'  => schoolDaysBetween(Carbon::parse($first)->toDateString(), now()->toDateString())
                ];
            }
        }

        return $days;
    }

    /**
     * Return the vehicle mileage for each vehicle by month
     *
     * @param $start
     * @return array
     */
    public function vehicleKm($start)
    {
        $selects = ['reg','MONTH(date) as month','SUM(km) as km'];
        $mileage = DB::table('_log_shuttle_mileage')->selectRaw(implode(',', $selects))
            ->whereBetween('date', [$start, now()->toDateString()])
            ->groupBy('month','reg')
            ->get();

        return $mileage;
    }
}
