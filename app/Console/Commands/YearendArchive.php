<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Illuminate\Database\Schema\Blueprint;

use Illuminate\Support\Carbon;

use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Schema;

class YearendArchive extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'yearend:archive';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Move data to archive';

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
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $live = config('database.connections.mysql.database');
        $archive = config('database.connections.sbugarchive.database');

        /*
         * archive bookings
         */
        $year = now()->subYears(2)->year;
        $table = '_' . $year . '_bookings';

        if ( !Schema::connection('sbugarchive')->hasTable($table) && Schema::hasTable('bookings') ) {
            DB::statement("CREATE TABLE $archive.$table LIKE $live.bookings");
            $cutoff = Carbon::parse('last day of December ' . $year)->toDateString();

            DB::statement("INSERT INTO $archive.$table SELECT * FROM $live.bookings WHERE date <= '$cutoff'");

            if ( DB::connection('sbugarchive')->table($table)->count() > 0 ) {
                DB::table('bookings')->where('date', '<=', $cutoff)->delete();
            }
        }

        /*
         * archive event bookings
         */
        $year = now()->subYears(2)->year;
        $table = '_' . $year . '_event_bookings';

        if ( !Schema::connection('sbugarchive')->hasTable($table) && Schema::hasTable('event_bookings') ) {
            DB::statement("CREATE TABLE $archive.$table LIKE $live.event_bookings");
            $cutoff = Carbon::parse('last day of December ' . $year)->toDateString();

            DB::statement("INSERT INTO $archive.$table SELECT * FROM $live.event_bookings WHERE date <= '$cutoff'");

            if ( DB::connection('sbugarchive')->table($table)->count() > 0 ) {
                DB::table('event_bookings')->where('date', '<=', $cutoff)->delete();
            }
        }

        /*
         * archive debtors journal
         */
        $year = now()->subYears(2)->year;
        $table = '_' . $year . '_debtors_journal';

        if ( !Schema::connection('sbugarchive')->hasTable($table) && Schema::hasTable('debtors_journal') ) {
            DB::statement("CREATE TABLE $archive.$table LIKE $live.debtors_journal");
            $cutoff = Carbon::parse('last day of December ' . $year)->toDateString();

            DB::statement("INSERT INTO $archive.$table SELECT * FROM $live.debtors_journal WHERE date <= '$cutoff'");

            if ( DB::connection('sbugarchive')->table($table)->count() > 0 ) {
                DB::table('debtors_journal')->where('date', '<=', $cutoff)->delete();
            }
        }

        /*
         * archive debtors statements
         */
        $year = now()->subYears(2)->year;
        $table = '_' . $year . '_debtors_statement';

        if ( !Schema::connection('sbugarchive')->hasTable($table) && Schema::hasTable('debtors_statement') ) {
            DB::statement("CREATE TABLE $archive.$table LIKE $live.debtors_statement");
            $cutoff = Carbon::parse('last day of December ' . $year)->toDateString();

            DB::statement("INSERT INTO $archive.$table SELECT * FROM $live.debtors_statement WHERE date <= '$cutoff'");

            if ( DB::connection('sbugarchive')->table($table)->count() > 0 ) {
                DB::table('debtors_statement')->where('date', '<=', $cutoff)->delete();
            }
        }

        /*
         * archive vehicle log
         */
        $year = now()->subYears(2)->year;
        $table = '_' . $year . '_vehicle_log';

        if ( !Schema::connection('sbugarchive')->hasTable($table) && Schema::hasTable('vehicle_log') ) {
            DB::statement("CREATE TABLE $archive.$table LIKE $live.vehicle_log");
            $cutoff = Carbon::parse('last day of December ' . $year)->toDateString();

            DB::statement("INSERT INTO $archive.$table SELECT * FROM $live.vehicle_log WHERE date <= '$cutoff'");

            if ( DB::connection('sbugarchive')->table($table)->count() > 0 ) {
                DB::table('vehicle_log')->where('date', '<=', $cutoff)->delete();
            }
        }

        /*
         * archive shuttle mileage log
         */
        $year = now()->subYears(2)->year;
        $table = '_' . $year . '_log_shuttle_mileage';

        if ( !Schema::connection('sbugarchive')->hasTable($table) && Schema::hasTable('_log_shuttle_mileage') ) {
            DB::statement("CREATE TABLE $archive.$table LIKE $live._log_shuttle_mileage");
            $cutoff = Carbon::parse('last day of December ' . $year)->toDateString();

            DB::statement("INSERT INTO $archive.$table SELECT * FROM $live._log_shuttle_mileage WHERE date <= '$cutoff'");

            if ( DB::connection('sbugarchive')->table($table)->count() > 0 ) {
                DB::table('_log_shuttle_mileage')->where('date', '<=', $cutoff)->delete();
            }
        }
    }
}

