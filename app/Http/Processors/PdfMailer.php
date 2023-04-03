<?php
/**
 * Created by PhpStorm.
 * User: Ray
 * Date: 2018/03/16
 * Time: 8:47 AM
 */

namespace App\Http\Processors;

/*
    |--------------------------------------------------------------------------
    | PdfMailer
    |--------------------------------------------------------------------------
    |
    | Called when emailing pdf documents to customer.
    | Extracts statement entries from debtors_statement and
    | invoice entries from BuildInvoice to create pdf view and send email.
    |
    | if $request is array, it will contain single customer request with $doc, $id, $date = null
    | else if $request is monthend, send to all customers who have an invoice in this month
    | else if $request is followup, send to all customers with an outstanding balance > 0
    |
    | Mail:SendPdf handles all versions of pdf emails.
    |
    */

use App\Mail\SendPdf;
use App\Models\Booking;
use App\Models\Children;
use App\Models\DebtorsJournal;
use App\Models\DebtorsStatement;
use App\Models\Price;
use App\Models\User;
use App\Notifications\WebmasterNotes;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use PDF;

class PdfMailer
{
    /**
     * Create pdf documents and email to customers
     *
     * @param string|array $request
     * @return int
     */
    public function handle($request)
    {
        $count = 0;

        // single customer request
        if ( is_array($request) ) {

            // requested statement
            if ( $request['doc'] == 'stat' ) {
                $params = $this->prepareStatEmail($request);

            // requested invoice and statement
            } elseif ( $request['doc'] == 'inv' ) {
                $params = $this->prepareInvEmail($request);
            }
            $user = User::find($request['id']);
            $email = $user->inv_email > '' ? $user->inv_email : $user->email;

            // send email
            Mail::to($email)->send(new SendPdf($user, $params));

        // multiple customer requests
        } else {
            // month-end run for invoice & statement
            if ( $request == 'monthend' ) {
                $count = $this->runMonthend();
                $data = ['subj' => 'Month-end Statements', 'msg' => $count.' Emails sent at '.Carbon::now()->toDateTimeString()];

            // follow up run for statement only
            } else {
                $count = $this->runMonthlyFollowup();
                $data = ['subj' => 'Monthly Follow up', 'msg' => $count.' Emails sent at '.Carbon::now()->toDateTimeString()];
            }
            Notification::route('mail', 'webmaster@shuttlebug.co.za')->notify(new WebmasterNotes($data));
        }

        return $count;
    }

    /**
     * Mail pdf documents to each customer
     * uses own functions optimised to reduce hits on the database
     *
     * @return int
     */
    private function runMonthEnd()
    {
        // when using emergency-run the posting month is set by controller
        if ( session()->has('emergency') ) {
            $postingMonth = session('emergency');
        } else {
            $postingMonth = (object) [
                'end'   => now()->toDateString(),
                'start' => now()->subMonth()->addDay()->toDateString()
            ];
        }

        // normal month-end
        $customers = Booking::whereBetween('date', [$postingMonth->start, $postingMonth->end])
            ->where('journal', '')->get()->pluck('user_id','user_id')->all();

        $users = User::whereIn('id', $customers)->get();

        // pdf data
        $statements = $this->getMultipleStatements($customers, 'monthend');
        $invoices = $this->getMultipleInvoices($customers, $postingMonth->end);

        // create each customer's pdf & params and mail
        $count = 0;
        foreach ( $customers as $customer ) {
            set_time_limit(0);
            $data = array_merge($statements[$customer], $invoices[$customer]);
            $user = $data['customer'] = $users->where('id', $customer)->first();
            $email = $user->inv_email > '' ? $user->inv_email : $user->email;

            $this->createPdfInvStat($data);

            $params = [
                'doc' => 'inv',
                'ver' => 'month-end',
                'bal' => $statements[$customer]['statement']->last()->balance
            ];

            Mail::to($email)->send(new SendPdf($user, $params));
            $count ++;
        }

        return $count;
    }

    /**
     * Mail latest statement to each customer with an outstanding balance
     *
     * @return int
     */
    private function runMonthlyFollowup()
    {
        // constants
        $stat_date = Carbon::createFromDate(now()->year, now()->subMonth()->month, 28)->toDateString();
        $customers = DebtorsStatement::where('date', $stat_date)->where('balance', '>', 0)->get()
            ->pluck('user_id','user_id')->all();
        $users = User::whereIn('id', $customers)->get();

        // pdf data
        $statements = $this->getMultipleStatements($customers, 'followup');

        $count = 0;
        foreach ( $customers as $customer ) {
            $bal = $statements[$customer]['statement']->last()->balance;
            foreach ( $statements[$customer]['journals'] as $entry ) {
                $bal = $bal + $entry->amount;
            }

            if ( $bal > 0 ) {
                $data = $statements[$customer];
                $user = $data['customer'] = $users->where('id', $customer)->first();
                $email = $user->inv_email > '' ? $user->inv_email : $user->email;

                $this->createPdfStatement($data);

                $params = [
                    'doc'   => 'stat',
                    'ver'   => 'followup',
                    'bal'   => $bal
                ];

                Mail::to($email)->send(new SendPdf($user, $params));
                $count++;
            }
        }

        return $count;
    }

    /**
     * Extract the statement, create the pdf & provide the email params
     *
     * @param $request
     * @return array
     */
    private function prepareStatEmail($request)
    {
        $data = $this->getCustomerStatement($request['id']);
        $data['customer'] = User::find($request['id']);

        $this->createPdfStatement($data);

        return [
            'doc'   => 'stat',
            'ver'   => 'followup',
            'bal'   => $data['statement']->last()->balance
        ];
    }

    /**
     * Extract the statement & invoice, create the pdf & provide the email params
     *
     * @param $request
     * @return array
     */
    private function prepareInvEmail($request)
    {
        $stat = $this->getCustomerStatement($request['id']);
        $inv = $this->getCustomerInvoice($request['id'], $request['date']);

        $data = array_merge($stat, $inv);
        $data['customer'] = User::find($request['id']);

        $this->createPdfInvStat($data);

        return [
            'doc'   => 'inv',
            'ver'   => 'requested',
            'bal'   => $stat['statement']->last()->balance
        ];
    }

    /**
     * Return the statement entries for given customer
     *
     * @param $id
     * @return mixed
     */
    private function getCustomerStatement($id)
    {
        $dt = Carbon::parse(DebtorsStatement::orderBy('created_at','desc')->first()->date);
        $stat_start = $dt->subMonths(12)->addDay()->toDateString();
        return [
            'statement' => DebtorsStatement::where('user_id', $id)->where('date', '>=', $stat_start)->orderBy('created_at')->get(),
            'journals'  => DebtorsJournal::currentJournals($id)
        ];
    }

    /**
     * Return the invoice for given customer for given month
     *
     * @param $id
     * @param $date
     * @return mixed
     */
    private function getCustomerInvoice($id, $date)
    {
        $invoice = new BuildInvoice();
        $inv_lines = $invoice->invoiceLines($id, $date);
        return [
            'invoice'       => $inv_lines,
            'passengers'    => Children::whereIn('id', array_column($inv_lines, 'passenger'))->get()->pluck('name','id')->all(),
            'discount'      => $invoice->invoiceDiscount($id, $inv_lines, $date),
            'month'         => $date
        ];
    }

    /**
     * Return the statements grouped by customer
     *
     * @param array     $customers
     * @param string    $run
     * @return mixed    key: customer, value: statement
     */
    private function getMultipleStatements($customers, $run)
    {
        $data = [];
        $stat_start = $run == 'followup' ?
            Carbon::createFromDate(now()->year, now()->subMonth()->month, 28)->subMonths(12)->addDay()->toDateString() :
            now()->subMonths(12)->addDay()->toDateString();

        $stat_lines = DebtorsStatement::whereIn('user_id', $customers)->where('date', '>=', $stat_start)->orderBy('created_at')->get();
        $journal_entries = $run == 'followup' ? DebtorsJournal::currentJournals() : collect([]);

        foreach ( $customers as $customer ) {
            $data[$customer]['statement'] = collect($stat_lines->where('user_id', $customer)->all());
            $data[$customer]['journals'] = $run == 'followup' ?
                collect($journal_entries->where('user_id', $customer)->all()) :
                $journal_entries;
        }

        return $data;
    }

    /**
     * Return array of invoices grouped by customer
     *
     * @param array     $customers
     * @param string    $month_end
     * @return array    key:customer, value=invoice
     */
    private function getMultipleInvoices($customers, $month_end)
    {
        $invoices = [];

        // build the invoices
        $build = new BuildInvoice();
        list($shuttle_bookings, $event_bookings) = $build->invoiceLines(null, $month_end);

        // constants
        $children = Children::whereIn('user_id', $customers)->get();
        $ruling_disc = Price::rulingPrice($month_end)->volume_disc;

        // group invoice_lines by customer
        foreach ( $customers as $customer ) {
            $shuttles = $events = [];

            $bookings = $shuttle_bookings->where('user_id', $customer)->all();
            foreach ( $bookings as $shuttle ) {
                $shuttles[] = (object) [
                    'passenger' => $shuttle->passenger_id,
                    'date'      => $shuttle->date,
                    'trip'      => $shuttle->puloc->venue . ' -> ' . $shuttle->doloc->venue,
                    'amount'    => $shuttle->price
                ];
            }

            $bookings = $event_bookings->where('user_id', $customer)->all();
            foreach ( $event_bookings as $event ) {
                list($from, $to) = $build->eventVenues($event);
                $events[] = (object) [
                    'passenger' => 'event',
                    'date'      => $event->date,
                    'trip'      => '('.$event->passengers.'pass) '.$from.' &rarr; '.$to,
                    'amount'    => $event->tripfee
                ];
            }
            $inv_lines = $shuttles + $events;

            $invoices[$customer] = [
                'invoice'       => $inv_lines,
                'passengers'    => $children->whereIn('id', array_column($inv_lines, 'passenger'))->pluck('name','id')->all(),
                'discount'      => $build->invoiceDiscount($customer, $inv_lines, $month_end, $ruling_disc),
                'month'         => $month_end
            ];
        }

        return $invoices;
    }

    /**
     * Create pdf statement
     *
     * @param $data
     */
    private function createPdfStatement($data)
    {
        PDF::loadView('layouts.pdf-statement', $data)->save(storage_path('pdf/shuttlebug-statement.pdf'));
    }

    /**
     * Create pdf statement and and invoice
     *
     * @param $data
     */
    private function createPdfInvStat($data)
    {
        PDF::loadView('layouts.pdf-invoice', $data)->save(storage_path('pdf/shuttlebug-invoice.pdf'));
    }


}
