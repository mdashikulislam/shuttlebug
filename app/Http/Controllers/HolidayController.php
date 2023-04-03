<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class HolidayController extends Controller
{
    /**
     * @var Holiday
     */
    protected $term;

    /**
     * HolidayController constructor.
     *
     * @param Holiday $term
     */
    public function __construct(Holiday $term)
    {
        $this->term = $term;
    }

    /**
     * Display manage page
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show()
    {
        $data = [
            'public'    => DB::table('publicholidays')->whereYear('date', now()->year)->first(),
            'nextpub'   => DB::table('publicholidays')->whereYear('date', now()->addYear()->year)->first(),
            'school'    => $this->term->whereYear('start', now()->year)->first(),
            'nextsch'   => $this->term->whereYear('start', now()->addYear()->year)->first()
        ];

        return view('office.holidays.manage', $data);
    }

    /**
     * Display form for creating or editing school terms
     *
     * @param string|null  $year
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit($year = null)
    {
        $year = is_null($year) ? now()->year : $year;

        $terms = $this->term->whereYear('start', $year)->orderBy('start')->get();

        $data = [
            'terms' => count($terms) > 0 ? $terms : null,
            'year'  => $year
        ];

        return view('office.holidays.form', $data);
    }

    /**
     * Store the school terms and holidays
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        if ( $request->has('term') ) {
            foreach ( $request->term as $ix => $term ) {
                if ( $term['start'] > '' && $term['end'] > '' ) {
                    $this->term->where('id', $ix)->update($term);
                } else {
                    $this->term->where('id', $ix)->delete();
                }
            }
        }

        if ( $request->has('newterm') ) {
            foreach ( $request->newterm as $term ) {
                if ( $term['start'] > '' && $term['end'] > '' ) {
                    $this->term->create($term);
                }
            }
        }

        $this->createSchoolHolidays($request->year);

        return back()->with('confirm', 'Changes have been saved');
    }

    /**
     * Display form for creating or editing public holidays
     *
     * @param string|null  $year
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function editPublic($year = null)
    {
        $year = is_null($year) ? now()->year : $year;

        $holidays = DB::table('publicholidays')->whereYear('date', $year)->orderBy('date')->get();
        $days = is_null($holidays) ? DB::table('publicholidays')->whereYear('date', $year-1)->orderBy('date')->get()->pluck('day') : null;

        $data = [
            'public' => $holidays,
            'days'   => $days,
            'year'   => $year
        ];

        return view('office.holidays.public-form', $data);
    }

    /**
     * Store the public holidays
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storePublic(Request $request)
    {
        if ( $request->has('hol') ) {
            foreach ( $request->hol as $ix => $holiday ) {
                if ( $holiday['date'] > '' ) {
                    DB::table('publicholidays')->where('id', $ix)->update($holiday);
                } else {
                    DB::table('publicholidays')->where('id', $ix)->delete();
                }
            }
        }

        if ( $request->has('newhol') ) {
            foreach ( $request->newhol as $holiday ) {
                if ( $holiday['date'] > '' ) {
                    DB::table('publicholidays')->insert($holiday);
                }
            }
        }

        $this->cacheHolidays($request->year);

        return back()->with('confirm', 'Changes have been saved');
    }

    /**
     * Create the school holiday dates
     *
     * @param $year
     */
    private function createSchoolHolidays($year)
    {
        $terms = $this->term->whereYear('start', $year)->orderBy('start')->get();
        // in case of editing, existing records must be deleted
        DB::table('schoolholidays')->whereYear('date', $year)->delete();

        // get holiday date ranges
        $i = 1;
        foreach ( $terms as $term ) {
            if ( $i > 1 ) {
                $hol[$i-1]['end'] = Carbon::createFromFormat('Y-m-d', $term->start)->subDay()->toDateString();
                $hol[$i]['start'] = Carbon::createFromFormat('Y-m-d', $term->end)->addDay()->toDateString();
            } elseif ( $i == 1 ) {
                $hol[$i-1]['start'] = $year.'-01-01';
                $hol[$i-1]['end'] = Carbon::createFromFormat('Y-m-d', $term->start)->subDay()->toDateString();
                $hol[$i]['start'] = Carbon::createFromFormat('Y-m-d', $term->end)->addDay()->toDateString();
            }
            if ( $i == 4 ) {
                $hol[$i]['end'] = $year.'-12-31';
            }
            $i++;
        }

        // store holiday dates
        foreach ( $hol as $days ) {
            $this->fillDates($days['start'], $days['end']);
        }

        $this->cacheHolidays($year);

        return;
    }

    /**
     * Store the school holidays
     *
     * @param $start
     * @param $end
     */
    private function fillDates($start, $end)
    {
        $date = $start;
        while ( $date <= $end ) {
            if ( Carbon::parse($date)->isWeekday() ) {
                DB::table('schoolholidays')->insert(['date' => $date]);
            }
            $date = Carbon::parse($date)->addDay()->toDateString();
        }

        return;
    }

    /**
     * Update cache after changes
     *
     * @param $year
     */
    private function cacheHolidays($year)
    {
        Cache::forget('holidays'.$year);
        $this->term->allHolidays($year);

        return;
    }

}
