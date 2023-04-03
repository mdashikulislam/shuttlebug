<?php

namespace App\Console\Commands;

/*
    |--------------------------------------------------------------------------
    | DebtorsFollowup
    |--------------------------------------------------------------------------
    |
    | Runs monthly to prepare & mail pdf statement to all customers with outstanding balances.
    | Preparing and mailing pdf documents uses PdfMailer to extract statement,
    | convert to pdf and send email.
    |
    | Command is scheduled to run on 7th of month at 5am.
    |
    */

use App\Http\Processors\PdfMailer;
use Illuminate\Console\Command;

class DebtorsFollowup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'debtors:followup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'email outstanding debtors';

    /**
     * The pdf mailer service.
     *
     * @var PdfMailer
     */
    protected $mailer;

    /**
     * Create a new command instance.
     *
     * @param PdfMailer $mailer
     * @return void
     */
    public function __construct(PdfMailer $mailer)
    {
        parent::__construct();

        $this->mailer = $mailer;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->mailer->handle('followup');
    }
}
