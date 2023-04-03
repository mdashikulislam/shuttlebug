<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/** @mixin \Eloquent */
class DebtorsStatement extends Model
{
    /**
     * The table properties
     *
     * @var array
     */
    protected $table    = 'debtors_statement';
    protected $fillable = ['user_id','date','transaction','amount','balance','created_at'];
    public $timestamps  = false;

    /**
     * Return the start and end date of the invoicing month in use today.
     * this returns the month ending on the NEXT 28th.
     * for TRANSACTIONS (bookings & journals), on the 28th the 28th of the NEXT month is returned
     * as the 28th is a reserved date in statements for invoices ONLY.
     *
     * @return object
     */
    public static function invMonth()
    {
        $dt = now();                                                                // 2018-01-28 : 2018-01-31 : 2018-01-15
        $monthend = Carbon::createFromDate($dt->year, $dt->month, 28);              // 2018-01-28

        if ( $dt->toDateString() >= $monthend->toDateString() ) {
            $invMonth = (object) [
                'start' => $monthend->copy()->addDay()->toDateString(),             // 2018-01-29
                'end'   => $monthend->copy()->addMonth()->toDateString()            // 2018-02-28
            ];
        } else {
            $invMonth = (object) [
                'start' => $monthend->copy()->subMonth()->addDay()->toDateString(), // 2017-12-29
                'end'   => $monthend->copy()->toDateString()                        // 2018-01-28
            ];
        }

        return $invMonth;
    }

    /**
     * Return the start and end date of the posting month in use today.
     * this returns:
     *      the 28th of the current month if now is before Noon of the 28th of the current month
     *      else the 28th of the next month.
     *
     * @return object
     */
    public static function postingMonth()
    {
        $dt = now();                                                                // 2018-01-28 : 2018-01-31 : 2018-01-15
        $monthend = Carbon::create($dt->year, $dt->month, 28, '12');                // 2018-01-28

        if ( $dt->toDateTimeString() >= $monthend->toDateTimeString() ) {
            $invMonth = (object) [
                'start' => $monthend->copy()->addDay()->toDateString(),             // 2018-01-29
                'end'   => $monthend->copy()->addMonth()->toDateString()            // 2018-02-28
            ];
        } else {
            $invMonth = (object) [
                'start' => $monthend->copy()->subMonth()->addDay()->toDateString(), // 2017-12-29
                'end'   => $monthend->copy()->toDateString()                        // 2018-01-28
            ];
        }

        return $invMonth;
    }

    /**
     * Return array of customer ids
     *
     * @return array
     */
    public function statementCustomers()
    {
        return self::orderBy('id')->get()->pluck('user_id','user_id')->all();
    }

    /**
     * Return array of outstanding balances at given date
     * includes only non zero balances
     *
     * @param $date
     * @return array    key: user_id value: balance
     */
    public function outstanding($date)
    {
        $date = is_null($date) ? now()->toDateString()  : $date;
        $balances = [];

        // last posted invoice
        $stat_dt = self::where('date', '<=', $date)->where('transaction', 'Invoice')->orderBy('created_at','desc')->first()->date;
        $jnl_dt = $date;

        $statements = self::where('date', '<=', $stat_dt)->orderBy('created_at','desc')->get();
        $customers = $statements->pluck('user_id','user_id')->all();
        foreach ( $customers as $customer ) {
            $balances[$customer] = $statements->where('user_id',$customer)->first()->balance;
        }

        $journals = DebtorsJournal::whereBetween('date', [$stat_dt, $jnl_dt])->get();

        foreach ( $journals as $journal ) {
            $balances[$journal->user_id] = ($balances[$journal->user_id] ?? 0) + $journal->amount;
        }

        return array_filter($balances);
    }

    /**
     * Return array of month-end dates for statement selection
     * includes completed months in current year plus last financial year-end
     *
     * @return array
     */
    public function statementDates()
    {
        for ( $m = now()->month; $m > 0; $m-- ) {
            $dt = Carbon::createFromDate(now()->year, $m, 28);
            if ( $m == now()->month ) {
                $dates[null] = 'Today';
            } else {
                $dates[$dt->toDateString()] = $dt->format('F Y');
            }
        }

        $prev_fin_year = Carbon::createFromDate(now()->subYear()->year, 2, 28);
        $dates[$prev_fin_year->toDateString()] = $prev_fin_year->format('F Y');

        return $dates;
    }

}
