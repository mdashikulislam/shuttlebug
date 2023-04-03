<?php

namespace App\Console\Commands;

use App\Http\Processors\TestCron;
use Illuminate\Console\Command;

class CronTester extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:tester';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'email webmaster as test';

    /**
     * The tester service.
     *
     * @var TestCron
     */
    protected $tester;

    /**
     * Create a new command instance
     *
     * @param TestCron $tester
     * @return void
     */
    public function __construct(TestCron $tester)
    {
        parent::__construct();

        $this->tester = $tester;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->tester->handle();

        return;
    }
}
