<?php
/**
 * Created by PhpStorm.
 * User: Ray
 * Date: 2018/03/12
 * Time: 11:10 AM
 */

namespace App\Http\Processors;

/*
    |--------------------------------------------------------------------------
    | BuildStatement
    |--------------------------------------------------------------------------
    |
    | Runs monthly on the 28th called by DebtorsMonthend.
    |
    | Extracts journal entries for the month and adds them to debtors_statement table.
    | Extracts nett invoice values for the month and adds them to debtors_statement table.
    |
    */

use App\Models\Booking;
use App\Models\DebtorsJournal;
use App\Models\DebtorsStatement;
use App\Models\EventBooking;
use App\Models\Price;
use App\Models\Promotion;
use Carbon\Carbon;

class BuildStatement
{
    /**
     * Update statement with this month's journal entries and invoices
     *
     * @param null $month_end
     * @return array
     */
    public function update($month_end = null)
    {
        $month_end = is_null($month_end) ? now()->toDateString() : $month_end;

        // get journal entries for the month
        $journal_entries = $this->getJournalEntries($month_end);

        // add journal entries to statements
        foreach ( $journal_entries as $entry ) {
            DebtorsStatement::create($entry);
        }

        // get invoices for the month
        $invoices = $this->getInvoices($month_end);

        // add invoices to statements
        $processed = $this->postInvoices($invoices, $month_end);

        return [count($journal_entries), $processed];
    }

    /**
     * Return journal entries for the month
     * ready for posting with updated balances
     *
     * @param $month_end
     * @return array
     */
    private function getJournalEntries($month_end)
    {
        $journals = [];
        $postingMonth = $this->postingMonth($month_end);

        // get all the journal entries for the month from earliest to latest
        $entries = DebtorsJournal::whereBetween('date', [$postingMonth->start, $postingMonth->end])
            ->orderBy('date')
            ->get();

        // get the posted entries in statement to use for avoiding duplications
        $posted = DebtorsStatement::whereBetween('date', [$postingMonth->start, $postingMonth->end])
            ->where('transaction', '!=', 'Invoice')
            ->get();

        // extract the customers with journal entries
        $customers = $entries->pluck('user_id','user_id')->all();

        // get the latest statement balance for each customer
        $statements = DebtorsStatement::whereIn('user_id',$customers)->orderBy('created_at','desc')->get();
        foreach ( $customers as $customer ) {
            $balances[$customer] = $statements->where('user_id',$customer)->first()->balance ?? 0;
        }

        // add each journal entry & update the running statement balance if not a duplicate
        // $i ensures entries on the same date have different timestamps so that balance order is correct
        $i = 0;
        foreach( $entries as $entry ) {
            $exists = $posted->where('user_id', $entry->user_id)->where('date', $entry->date)->where('amount', $entry->amount)->first();
            if ( is_null($exists) ) {
                $journals[] = [
                    'user_id'     => $entry->user_id,
                    'date'        => $entry->date,
                    'transaction' => $entry->entry,
                    'amount'      => (int) $entry->amount,
                    'balance'     => (int) ($balances[$entry->user_id] + $entry->amount),
                    'created_at'  => Carbon::parse($entry->date)->addHours(9)->addSeconds($i)->toDateTimeString()
                ];
                $i ++;
                $balances[$entry->user_id] = (int) ($balances[$entry->user_id] + $entry->amount);
            }
        }

        return $journals;
    }

    /**
     * Return nett invoice values for every customer with bookings in given month
     *
     * @param $month_end
     * @return array    key: user_id, value: nett inv value
     */
    private function getInvoices($month_end)
    {
        $invoices = [];
        $postingMonth = $this->postingMonth($month_end);

        $shuttles = $this->shuttleBookings($postingMonth);
        $events = $this->eventBookings($postingMonth);

        $customers = array_unique(array_merge(array_keys($shuttles), array_keys($events)));
        foreach ( $customers as $customer ) {
            $invoices[$customer] = (int) (($shuttles[$customer] ?? 0) + ($events[$customer] ?? 0));
        }

        return $invoices;
    }

    /**
     * Add invoices to statements
     *
     * @param $invoices
     * @param $month_end
     * @return int
     */
    private function postInvoices($invoices, $month_end)
    {
        $balances = [];
        // extract the customers from invoices
        $customers = array_keys($invoices);

        // get the latest statement balance for each customer
        // this will include the journal entries just posted
        $statements = DebtorsStatement::whereIn('user_id', $customers)->orderBy('created_at', 'desc')->get();
        foreach ( $customers as $customer ) {
            $balances[$customer] = $statements->where('user_id', $customer)->first()->balance ?? 0;
        }

        // get posted invoices in statement to avoid duplicates
        $posted = DebtorsStatement::where('transaction', 'Invoice')->where('created_at', Carbon::parse($month_end)->addHours(12)->toDateTimeString())->get();

        // add invoices to statements
        $processed = 0;
        if ( count($invoices) > 0 ) {
            foreach ( $invoices as $user => $invoice ) {
                $exists = $posted->where('user_id', $user)->where('amount', (int)$invoice)->first();
                if ( is_null($exists) ) {
                    DebtorsStatement::create([
                        'user_id'     => $user,
                        'date'        => $month_end,
                        'transaction' => 'Invoice',
                        'amount'      => (int) $invoice,
                        'balance'     => (int) $balances[$user] + $invoice,
                        'created_at'  => Carbon::parse($month_end)->addHours(12)->toDateTimeString()
                    ]);
                    $processed ++;
                }
            }
        // if no invoices in the month add a no-value dummy invoice to statement to keep it in sync
        } else {
            $exists = $posted->where('user_id', 100101)->where('amount', 0)->first();
            if ( is_null($exists) ) {
                DebtorsStatement::create([
                    'user_id'     => 100101,
                    'date'        => $month_end,
                    'transaction' => 'Invoice',
                    'amount'      => 0,
                    'balance'     => 0,
                    'created_at'  => Carbon::parse($month_end)->addHours(12)->toDateTimeString()
                ]);
                $processed ++;
            }
        }

        return $processed;
    }

    /**
     * Return start & end dates of invoicing month
     *
     * @param $month_end
     * @return object
     */
    private function postingMonth($month_end)
    {
        return (object) [
            'end'   => $month_end,
            'start' => Carbon::createFromFormat('Y-m-d', $month_end)->subMonth()->addDay()->toDateString()
        ];
    }

    /**
     * Return array of shuttle invoice values
     *
     * @param $postingMonth
     * @return array    key: customer, value: booking value
     */
    public function shuttleBookings($postingMonth)
    {
        $shuttles = [];

        // get all the bookings in the month
        $bookings = Booking::whereBetween('date', [$postingMonth->start, $postingMonth->end])
            ->where('journal', '')
            ->orderBy('user_id')
            ->get();

        // extract the customers
        $customers = $bookings->pluck('user_id','user_id')->all();

        // calculate the nett invoice value for each customer (bookings value less discount)
        $ruling_disc = Price::rulingPrice($postingMonth->end)->volume_disc;
        foreach ( $customers as $customer ) {
            $discount = $this->customerDiscount($bookings->where('user_id', $customer)->all(), $ruling_disc, $postingMonth);
            $inv_value = $bookings->where('user_id',$customer)->sum('price');
            $shuttles[$customer] = (int) ($inv_value - $discount);
        }

        return $shuttles;
    }

    /**
     * Return array of event invoice values
     *
     * @param $postingMonth
     * @return array    key: customer, value: booking value
     */
    private function eventBookings($postingMonth)
    {
        $events = [];

        // get all the event bookings in the month
        $bookings = EventBooking::whereBetween('date', [$postingMonth->start, $postingMonth->end])
            ->orderBy('user_id')
            ->get();

        // extract the customers
        $customers = $bookings->pluck('user_id','user_id')->all();

        // calculate the event bookings value for each customer
        foreach ( $customers as $customer ) {
            $events[$customer] = $bookings->where('user_id',$customer)->sum('tripfee');
        }

        return $events;
    }

    /**
     * Return the discount on customer's bookings
     *  changed sibling disc from 2019-01-01 so must use different methods
     *  depending on date
     *
     * @param $bookings
     * @param $ruling_disc
     * @param $postingMonth
     * @return float|int
     */
    private function customerDiscount($bookings, $ruling_disc, $postingMonth)
    {
        $discount = 0;

        // get the customer's passengers
        $passengers = collect($bookings)->pluck('passenger_id','passenger_id')->all();

        // volume discount
        foreach ( $passengers as $passenger ) {
            // add the discount if the passenger's number of bookings qualify
            if ( collect($bookings)->where('passenger_id',$passenger)->count() > 15 ) {
                $value = collect($bookings)->where('passenger_id',$passenger)->sum('price');
                $discount += ceil($value * ($ruling_disc / 100));
            }
        }

        // sibling discount
        $year = Carbon::parse($postingMonth->end)->year;

        if ( $year < 2019 ) {
            $promo = Promotion::isActive('Special Sibling Disc', $postingMonth->end);
            $sib_disc = $promo && in_array(collect($bookings)->first()->user_id, $promo->list) ? $promo->rate : 0;
        } else {
            $sib_disc = Price::rulingPrice($postingMonth->end)->sibling_disc;
        }

        if ( $sib_disc > 0 ) {
            $used = [];

            // get the unique trips shared by siblings
            foreach( $bookings as $booking ) {
                $shared = collect($bookings)->where('date', $booking->date)
                    ->where('putime', $booking->putime)
                    ->where('puloc_id', $booking->puloc_id)
                    ->where('doloc_id', $booking->doloc_id)
                    ->where('id', '!=', $booking->id)
                    ->whereNotIn('id', $used)
                    ->all();

                // eliminate this trip so it's not counted again
                $used[] = $booking->id;
                foreach( $shared as $dup ) {
                    $used[] = $dup->id;
                }

                // apply the discount to additional siblings who shared the trip
                if ( count($shared) > 0 ) {
                    $discount += round(count($shared) * $booking->price * ($sib_disc / 100));
                }
            }
        }

        return $discount;
    }
}
