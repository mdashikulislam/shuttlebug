<?php

namespace App\Console\Commands;

use App\Models\Vehicle;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class TableCleanup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'table:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up tables';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Remove entries older than 3 months
     *
     * @return void
     */
    public function handle()
    {
        $cutoff = now()->firstOfMonth()->subMonths(3)->toDateString();

        if ( Schema::hasTable('planning_reports') ) {
            DB::table('planning_reports')->where('date', '<', $cutoff)->delete();
        }

        if ( Schema::hasTable('trip_hacks') ) {
            DB::table('trip_hacks')->where('date', '<', $cutoff)->delete();
        }

        if ( Schema::hasTable('ts_feedback') ) {
            DB::table('ts_feedback')->where('date', '<', $cutoff)->delete();
        }

        $existing = Vehicle::where('status', '!=', 'history')->get()->pluck('id')->all();
        for ( $i = Arr::first($existing); $i <= Arr::last($existing) + 5; $i++ ) {
            $table = 'ts_' . $i;
            if ( Schema::hasTable($table) ) {
                DB::table($table)->where('date', '<', $cutoff)->delete();
            }
        }

        /*
         * Remove signature images older than 2 months
         */
        $cutoff = now()->firstOfMonth()->subMonths(2)->toDateString();

        foreach ( Storage::disk('signatures')->allFiles() as $file ) {
            if ( date('Y-m-d', Storage::disk('signatures')->lastModified($file)) < $cutoff ) {
                Storage::disk('signatures')->delete($file);
            }
        }
    }
}
