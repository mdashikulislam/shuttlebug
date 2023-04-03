<?php

namespace App\Http\Controllers;

/*
    |--------------------------------------------------------------------------
    | Debtors
    |--------------------------------------------------------------------------
    |
    | Invoices are compiled from the bookings tables.
    | Adjustments are posted to the debtors_journal table.
    | Statements are summarised in the debtors_statement table.
    |
    | Bookings can only be modified in the current invoice period (29th to 28th),
    | Previous month booking modifications are posted to the journal.
    | Journal entries can only be dated in the current invoice period.
    | 28th is reserved for invoices only, so journal entries posted on 28th are dated 29th.
    | On the 28th current month invoice and journals are posted to the statement.
    |
    | This process ensures the integrity of the running 'balance' carried in the statement.
    */

use App\Http\Processors\BuildInvoice;
use App\Http\Processors\BuildStatement;
use App\Http\Processors\PdfMailer;
use App\Models\Booking;
use App\Models\Children;
use App\Models\DebtorsJournal;
use App\Models\DebtorsStatement;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DebtorsController extends Controller
{
    /**
     * @var DebtorsJournal
     */
    protected $statement;
    protected $journal;

    /**
     * DebtorsController constructor.
     *
     * @param DebtorsStatement $statement
     * @param DebtorsJournal $journal
     */
    public function __construct(DebtorsStatement $statement, DebtorsJournal $journal)
    {
        $this->statement = $statement;
        $this->journal = $journal;
    }

    /**
     * Display manage page
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show()
    {
        $ids = $this->statement->statementCustomers();

        $data = [
            'customers'     => User::whereIn('id', $ids)->orderBy('last_name')->get()->pluck('alpha_name','id')->all(),
            'passengers'    => [],
            'monthend'      => DB::table('_log_debtors')->orderBy('processed', 'desc')->limit(3)->get()
        ];

        return view('office.debtors.manage', $data);
    }

    /**
     * Display form for posting journal entries
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function createJournal()
    {
        $users = User::whereHas('bookings')->with('children')->orderBy('last_name')->get();

        $data = [
            'customers'     => $users->pluck('alpha_name','id')->all(),
            'children'      => Children::childrenWithDifferentNames($users),
            'entries'       => $this->journal->availableEntries(),
            'transactions'  => $this->journal->with('user')->where('date', '>', now()->subMonths(2))->orderBy('date','desc')->get(),
            'invMonth'      => $this->statement->invMonth()
        ];

        return view('office.debtors.journal', $data);
    }

    /**
     * Store a journal entry
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeJournal(Request $request)
    {
        if ( in_array($request->entry, $this->journal->creditEntries()) ) {
            $request->merge(['amount' => $request->amount * -1]);
        }

        $this->journal->create($request->all());

        return back()->with('confirm', 'Transaction has been saved');
    }

    /**
     * Return the customer's latest statement balance
     * called by journal.blade
     *
     * @param $id
     * @return mixed
     */
    public function getLatestBalance($id)
    {
        return $this->statement->where('user_id', $id)->orderBy('created_at','desc')->first()->balance ?? 0;
    }

    /**
     * Display the statement/invoice page
     *
     * @param      $id
     * @param null $pdf
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|mixed
     */
    public function showFinancials($id, $pdf = null)
    {
        $dt = Carbon::parse($this->statement->orderBy('created_at','desc')->first()->date);
        for ( $i = 0; $i < 12; $i++ ) {
            $months[$dt->copy()->subMonths($i)->toDateString()] = $dt->copy()->subMonths($i)->format('F');
        }
        $stat_start = $dt->subMonths(12)->addDay()->toDateString();

        $data = [
            'customer'  => User::find($id),
            'months'    => $months,
            'statement' => $this->statement->where('user_id',$id)->where('date', '>=', $stat_start)->orderBy('created_at', 'desc')->get(),
            'journals'  => $this->journal->currentJournals($id)
        ];

        $balance = $data['statement']->first()->balance ?? 0;
        $journals = $this->journal->currentJournals($id);
        foreach ( $journals as $entry ) {
            $balance = $balance + $entry->amount;
            $entry->balance = $balance;
        }
        $data['journals'] = $journals->reverse();

        // return data to customerPdf
        if ( !is_null($pdf) ) {
            return $data;
        }

        if ( $id == Auth::user()->id ) {
            return view('myaccount.billing', $data);
        }

        return view('office.debtors.financials', $data);
    }

    /**
     * Display outstanding debtors, current or at end of given month
     *
     * @param null $date
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showOutstanding($date = null)
    {
        $outstanding = $this->statement->outstanding($date);

        $data = [
            'month'     => $date,
            'balances'  => $outstanding,
            'customers' => User::whereIn('id', array_keys($outstanding))->orderBy('last_name')->get(),
            'dates'     => $this->statement->statementDates()
        ];

        return view('office.debtors.outstanding', $data);
    }

    /**
     * Load the invoice for the given month
     *
     * @param $id   user-id
     * @param $date month-end
     * @param $pdf
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|mixed
     */
    public function getInvoice($id, $date, $pdf = null)
    {
        $process = new BuildInvoice();

        $inv_lines = $process->invoiceLines($id, $date);

        $data = [
            'invoice'       => $inv_lines,
            'passengers'    => Children::whereIn('id', array_column($inv_lines, 'passenger'))->get()->pluck('name','id')->all(),
            'discount'      => $process->invoiceDiscount($id, $inv_lines, $date),
            'customer'      => User::find($id),
            'month'         => $date
        ];

        // return data to customerPdf
        if ( !is_null($pdf) ) {
            return $data;
        }

        return view('layouts.invoice', $data);
    }

    /**
     * Show the passenger deliveries page
     *
     * @param $child
     * @param $month
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function deliveries($child, $month)
    {
        $passenger = Children::find($child);
        $bookings = Booking::monthBookings($child, $month);

        $deliveries = DB::table('ts_feedback')
            ->where('date', 'like', $month.'%')
            ->where('passenger', $passenger->name)
            ->orderBy('date')->orderBy('duetime')
            ->get()->all();

        foreach ( $deliveries as $delivery ) {
            if ( $delivery->booking_id > 0 ) {
                $booking = $bookings->where('id', $delivery->booking_id)->first();
                $booking->delivery = $delivery;
            }
        }

        $data = [
            'month'         => Carbon::parse($month)->format('F'),
            'passenger'     => $passenger,
            'bookings'      => $bookings->where('date', '<=', now()->toDateString())->all()
        ];

        return view('office.debtors.deliveries', $data);
    }

    /**
     * Create & send pdf document to customer
     *
     * @param $doc
     * @param $id
     * @param $date
     */
    public function customerPdf($doc, $id, $date = null)
    {
        $process = new PdfMailer();
        $process->handle(['doc' => $doc, 'id' => $id, 'date' => $date]);

        return;
    }

    /**
     * Emergency re-processing of debtors statement entries if scheduled run fails
     * can be run on any date for selected month-end.
     * will not duplicate entries that are already posted
     * only posts entries to statement, does not send emails to clients.
     *
     * @param $month_end
     * @return \Illuminate\Http\RedirectResponse
     */
    public function emergencyReprocess($month_end)
    {


        return back()->with('confirm', 'Transactions Processed for '.$month_end);
    }

    /**
     * Emergency re-mailing of month-end invoice if scheduled run fails
     * Only sends last month's pdf invoice & statement to all customers
     * assumes buildStatement successfully completed
     * to re-run entire process ssh into server, cd laravel, and run 'php artisan debtors:monthend'
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function emergencyMonthEnd()
    {
        $process = new PdfMailer();

        $year = now()->month == 1 && now()->day < 28 ? now()->year - 1 : now()->year;
        $prev = now()->month == 1 ? 12 : now()->month - 1;
        $end = now()->day < 28 ? Carbon::parse($year.'-'.$prev.'-28')->toDateString() : Carbon::parse($year.'-'.now()->month.'-28')->toDateString();
        $postingMonth = (object) [
            'end'   => $end,
            'start' => Carbon::parse($end)->subMonth()->addDay()->toDateString()
        ];
        session()->put('emergency', $postingMonth);
        set_time_limit(0);

        $process->handle('monthend');
        session()->put('emergency', $postingMonth);

        return back()->with('confirm', 'Invoices sent');
    }

    /**
     * Update Statements at month_end
     * used by seeding to bring statements up to date
     *
     * @param                $date
     * @return string
     */
    public function updateStatement($date)
    {
        $process = new BuildStatement();

        // prevent this process from duplicating entries in the statement
        if ( $this->statement->orderBy('created_at', 'desc')->first()->date < $date ) {
            $process->update($date);

            // log month-end run
            DB::table('_log_debtors')->insert([
                'action'    => 'Processed',
                'processed' => now()->toDateString()
            ]);
        }

        return 'done';
    }
}
