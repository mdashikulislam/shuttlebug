<?php

namespace App\Console\Commands;

/*
    |--------------------------------------------------------------------------
    | DebtorsMonthend
    |--------------------------------------------------------------------------
    |
    | Runs monthly to update debtors_statement table, and prepare & mail pdf documents.
    | Updating statement table uses BuildStatement which transfers monthly invoice and journal entries.
    | Preparing and mailing pdf documents uses PdfMailer which extracts invoice & statement,
    | converts to pdf and sends email.
    |
    | Command is scheduled to run on every 28th at noon and protects against duplicating runs
    | or posting on dates other than the 28th.
    |
    */

use App\Http\Processors\BuildStatement;
use App\Http\Processors\PdfMailer;
use App\Models\DebtorsStatement;
use App\Notifications\WebmasterNotes;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class DebtorsMonthend extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'debtors:monthend';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update statements then email';

    /**
     * The services.
     *
     * @var BuildStatement
     * @var PdfMailer
     */
    protected $statement;
    protected $mailer;

    /**
     * Create a new command instance.
     *
     * @param BuildStatement $statement
     * @param PdfMailer $mailer
     * @return void
     */
    public function __construct(BuildStatement $statement, PdfMailer $mailer)
    {
        parent::__construct();

        $this->statement = $statement;
        $this->mailer = $mailer;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $count = $journals = $invoices = 0;

        // the date of the last invoice posted
        $last_invoice = DebtorsStatement::where('transaction', 'Invoice')->orderBy('date', 'desc')->first()->date;

        // process if request is dated correctly
        if ( now()->toDateString() == Carbon::createFromFormat('Y-m-d', $last_invoice)->addMonth()->toDateString() ) {
            list($journals, $invoices) = $this->statement->update();
            $count = $this->mailer->handle('monthend');

        } else {
            $mail = [
                'subj' => 'Debtors Month-end Warning',
                'msg' => 'Month-end run requested today ('.now()->toDayDateTimeString().') : Out of Sync!'
            ];
            Notification::route('mail', 'webmaster@shuttlebug.co.za')->notify(new WebmasterNotes($mail));
        }

        // log month-end run
        DB::table('_log_debtors')->insert([
            'action'    => isset($mail) ? 'Out of Sync warning' : 'Processed',
            'journals'  => $journals,
            'invoices'  => $invoices,
            'mailed'    => $count,
            'processed' => now()->toDateTimeString()
        ]);
    }
}
