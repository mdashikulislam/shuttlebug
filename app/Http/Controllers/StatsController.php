<?php

namespace App\Http\Controllers;

use App\Http\Processors\Analytics;
use App\Http\Processors\BuildStatement;
use App\Models\Booking;
use App\Models\School;
use App\Models\User;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class StatsController extends Controller
{
    /**
     * @var Booking
     * @var Analytics
     */
    protected $booking;
    protected $process;

    /**
     * StatsController constructor.
     *
     * @param Booking $booking
     * @param Analytics $process
     */
    public function __construct(Booking $booking, Analytics $process)
    {
        $this->booking = $booking;
        $this->process = $process;
    }

    /**
     * Display the current year's bookings
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show()
    {
        $stats = $this->process->monthlyStats(now()->year);

        $data = [
            'mstats'    => collect($stats)->where('month', '<=', Carbon::now()->month)->all(),
            'dstats'    => $this->process->dailyStats(),
            'days'      => schoolDays(now()->year),
            'hols'      => Cache::get('holidays'.now()->year),
            'months'    => [
                'prev' => now()->subMonthNoOverflow()->firstOfMonth()->toDateString(),
                'curr' => now()->firstOfMonth()->toDateString(),
                'next' => now()->addMonthNoOverflow()->firstOfMonth()->toDateString()
            ]
        ];

        return view('office.analytics.summary', $data);
    }

    /**
     * Display the financial stats
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function financials()
    {
        $invoices = new BuildStatement();
        $month_end = now()->year.'-'.now()->month.'-28';
        $postingMonth = (object) ['end' => $month_end, 'start' => Carbon::createFromFormat('Y-m-d', $month_end)->subMonth()->addDay()->toDateString()];
        $nett_income = array_sum($invoices->shuttleBookings($postingMonth));

        $data = [
            'stats'     => $this->process->financialStats(now()->year),
            'curr_nett' => $nett_income,
            'lyear'     => $this->process->financialStats(now()->subYear()->year),
            'hh'        => $this->process->financialHh($postingMonth),
            'hhly'      => $this->process->financialHhLastYear()
        ];

        return view('office.analytics.financials', $data);
    }

    /**
     * Display trip stats
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function trips()
    {
        $term_ends = DB::table('schoolterms')
            ->whereYear('start', '>=', now()->subYear()->year)
            ->get()->pluck('end')->all();

        // demographics
        list($periods, $times, $venues) = $this->process->tripDemographics($term_ends);
        $days[now()->year] = array_sum(Arr::where(schoolDays(now()->year), function($value, $key) {
            return $key <= Carbon::now()->month;
        }));
        $days[now()->subYear()->year] = array_sum(schoolDays(now()->subYear()->year));

        // peak time trends
        $peak_times['current'] = $this->process->trendsTimeSlots($term_ends);
        $peak_times[now()->year] = $this->process->trendsTimeSlots($term_ends, now()->year);
        $peak_times[now()->subYear()->year] = $this->process->trendsTimeSlots($term_ends, now()->subYear()->year);
        $days['current'] = schoolDaysBetween(now()->subDays(30)->toDateString(), now()->toDateString());

        $time = '12:00:00';
        while ( $time <= '16:00:00' ) {
            $end = Carbon::parse($time)->addMinutes(15)->toTimeString();
            $ptimes[] = $time == '12:15:00' ? '12:20:00' : $time;
            $time = $end;
        }
        $ptimes[] = '16:30:00';

        // school trends
        $schools = $this->process->trendsSchools($term_ends);

        $data = [
            'times'         => $times,
            'periods'       => $periods,
            'venues'        => $venues,
            'days'          => $days,
            'peak_times'    => $peak_times,
            'ptimes'        => $ptimes,
            'schools'       => $schools
        ];

        return view('office.analytics.trips', $data);
    }

    /**
     * Display customers stats
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function customers()
    {
        $stats = $this->process->statsCustomers();

        foreach ( $stats as $data ) {
            $users[] = array_keys($data);
        }
        $users = array_unique(Arr::flatten($users));
        $customers = User::whereIn('id', $users)->get()->pluck('name', 'id')->all();
        $ranked_customers = [];
        foreach($stats[now()->subYear()->year] as $ix => $value) {
            if ( array_key_exists($ix, $customers) ) {
                $ranked_customers[$ix] = $customers[$ix];
            }
        }

        $sorted_customers = [];
        foreach($stats['current'] as $ix => $value) {
            if ( array_key_exists($ix, $customers) ) {
                $sorted_customers[$ix] = $customers[$ix];
            }
        }

        $customers = array_replace_recursive($sorted_customers, $ranked_customers);

        $days[now()->year] = array_sum(Arr::where(schoolDays(now()->year), function($value, $key) {
            return $key <= Carbon::now()->month;
        }));
        $days[now()->subYear()->year] = array_sum(schoolDays(now()->subYear()->year));
        $days['current'] = schoolDays(now()->year)[now()->month];

        foreach ( $stats as $year => $data ) {
            $total[$year] = 0;
            foreach ( $stats[$year] as $id => $count ) {
                if ( isset($stats['current'][$id]) && $stats['current'][$id] > $days['current'] * .6 ) {
                    $total[$year] += $count;
                }
            }
        }

        $data = [
            'stats'     => $stats,
            'total'     => $total,
            'customers' => $customers,
            'days'      => $days
        ];

        return view('office.analytics.customers', $data);
    }

    /**
     * Display vehicle stats
     *
     * @todo group all 2 & 3 seaters into a single vehicle
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function vehicles()
    {
        // show 6 months stats limited to start of 2019
        $period = 5;
        $start = now()->subMonthsNoOverflow($period)->firstOfMonth()->toDateString();
        // apply limit
        $period = Carbon::parse($start)->year == 2018 ? now()->month : $period;
        $start = Carbon::parse($start)->year == 2018 ? '2019-01-01' : $start;

        $selects = ['MONTH(date) as month','COUNT(*) as bookings'];
        $bookings = $this->booking->selectRaw(implode(',', $selects))
            ->whereBetween('date', [$start, now()->toDateString()])
            ->where('journal', '')
            ->groupBy('month')
            ->get()->pluck('bookings', 'month');

        $stats = $this->process->statsVehicles($start);

        $vehicles = array_unique(array_column($stats->toArray(), 'vehicle'));
        sort($vehicles);

        $days = $this->process->monthDays($period, $start);
        $vehicle_days = $this->process->vehicleDays($vehicles, $start);

        $mileage = $this->process->vehicleKm($start);

        $data = [
            'bookings'  => $bookings,
            'stats'     => $stats,
            'days'      => array_reverse($days, true),
            'vehicles'  => $vehicles,
            'mileage'   => $mileage,
            'vdays'     => $vehicle_days,
            'fleet'     => Vehicle::whereIn('id', $vehicles)->get()
        ];

        return view('office.analytics.vehicles', $data);
    }

    /**
     * Display bookings history stats
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function history()
    {
        if ( now() >= now()->firstOfYear() && now() < now()->firstOfYear()->addDays(3) ) {
            $data = ['delayed' => true];
            return view('office.analytics.history', $data);
        }

        $year = now()->subYears(3)->year;
        $ceil = now()->year;
        while ( $year <= $ceil ) {
            $stats[$year] = $this->process->monthlyStats($year);
            $year++;
        }

        $stats[now()->year] = collect($stats[now()->year])->where('month', '<=', Carbon::now()->month)->all();
        krsort($stats);

        $data = [
            'stats' => $stats
        ];

        return view('office.analytics.history', $data);
    }

    /**
     * Display school history stats
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function schools()
    {
        if ( now() >= now()->firstOfYear() && now() < now()->firstOfYear()->addDays(3) ) {
            $data = ['delayed' => true];
            return view('office.analytics.schools', $data);
        }

        $schools = School::where('name', '!=', 'none')->get();

        $year = now()->subYears(3)->year;
        $ceil = now()->year;
        while ( $year <= $ceil ) {
            $stats[$year] = $this->process->schoolStats($year, $schools);
            $days[$year]  = array_sum(schoolDays($year));
            $year++;
        }

        $days['ytd'] = array_sum(array_slice(schoolDays(now()->year), 0, now()->month));

        $stats[now()->year] = collect($stats[now()->year])->where('month', '<=', Carbon::now()->month)->all();
        krsort($stats);

        foreach ( $stats as $year ) {
            foreach ( $year as $item ) {
                $used[$item['id']] = $item['name'];
            }
        }

        $data = [
            'schools'   => $used,
            'stats'     => $stats,
            'days'      => $days
        ];

        return view('office.analytics.schools', $data);
    }
}
