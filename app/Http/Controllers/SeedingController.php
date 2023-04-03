<?php

namespace App\Http\Controllers;

use App\Models\Children;
use App\Models\DebtorsStatement;
use App\Models\Promotion;
use App\Models\School;
use App\Models\User;
use App\Models\Xmural;
use BookingsTableSeeder;
use Carbon\Carbon;
use ChildrenTableSeeder;
use DebtorsJournalTableSeeder;
use DriversTableSeeder;
use EventBookingsTableSeeder;
use GuardiansTableSeeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PricesTableSeeder;
use PromotionsTableSeeder;
use SchoolsTableSeeder;
use SchooltermsTableSeeder;
use TsFeedbackTableSeeder;
use UsersTableSeeder;
use UserXmuralsTableSeeder;
use VehicleLogTableSeeder;
use VehiclesTableSeeder;
use XmuralsTableSeeder;

class SeedingController extends Seeder
{
    /* This runs the database seeders so that the sequence can be controlled
        to allow for functions that manipulate seeding results and depend on
        other seeded tables.

        Add all seeders here as the seeding is not run from db seed.
        It should be called after running the full migration.

        Where tables are not entirely re-seeded the base date is 2018-03-31
        and these will be seeded with entries after the base date.
    */

    public function seedTables()
    {
        $this->seeding('users');
            DB::table('users')->truncate();
            $seeder = new UsersTableSeeder();
            $seeder->run();
        $this->seeded();

        $this->seeding('children');
            DB::table('children')->truncate();
            $seeder = new ChildrenTableSeeder();
            $seeder->run();
        $this->seeded();

        $this->seeding('guardians');
            DB::table('guardians')->truncate();
            $seeder = new GuardiansTableSeeder();
            $seeder->run();
        $this->seeded();

        $this->seeding('schools');
            DB::table('schools')->truncate();
            $seeder = new SchoolsTableSeeder();
            $seeder->run();
        $this->seeded();

        $this->seeding('xmurals');
            Schema::dropIfExists('xmurals');
            Schema::create('xmurals', function (Blueprint $table) {
                $table->increments('id');
                $table->string('activity', 25);
                $table->string('venue', 30)->index();
                $table->string('unit', 30);
                $table->string('street', 40);
                $table->string('suburb', 25);
                $table->string('city', 25);
                $table->string('view', 3);
                $table->string('geo', 25);
            });
            $seeder = new XmuralsTableSeeder();
            $seeder->run();
        $this->seeded();

        $this->seeding('user_xmurals');
            DB::table('user_xmurals')->truncate();
            $seeder = new UserXmuralsTableSeeder();
            $seeder->run();
        $this->seeded();

        // new entries only
        $this->seeding('bookings');
            DB::table('bookings')->where('date', '>', '2018-03-31')->delete();
            $seeder = new BookingsTableSeeder();    // revise after recovery
            $seeder->run();
        $this->seeded();

        // new entries only
        $this->seeding('event_bookings');
            DB::table('event_bookings')->where('date', '>', '2018-03-31')->delete();
            $seeder = new EventBookingsTableSeeder();    // revise after recovery
            $seeder->run();
        $this->seeded();

        // once we have the xmurals, user_xmurals & bookings tables we can run fixXmurals
        // which also updates user_xmurals & bookings
        $this->seeding('xmural fixes');
            $this->fixXmurals();
        $this->seeded();

        // once we have bookings we can update inactive users
        $this->seeding('users fixes');
            $this->fixUsers();
        $this->seeded();

        // once we have bookings we can clean out friends from the children table
        $this->seeding('cleaning friends');
            $this->cleanFriends();
        $this->seeded();

        // only entries after 2018-03-27
        $this->seeding('debtors_journal');
            DB::table('debtors_journal')->where('date', '>', '2018-03-27')->delete();
            $seeder = new DebtorsJournalTableSeeder();    // revise after recovery
            $seeder->run();
        $this->seeded();

        // add transactions after 2018-03-28
        $this->seeding('statements');
            $this->seedStatements();
        $this->seeded();

        // only new entries
        $this->seeding('vehicle_log');
            $seeder = new VehicleLogTableSeeder();
            $seeder->run();
            $this->seedVehicleLog();
        $this->seeded();

        // only the current month
        $this->seeding('ts_feedback');
            DB::table('ts_feedback')->truncate();
            $seeder = new TsFeedbackTableSeeder();
            $seeder->run();
        $this->seeded();

        /**
         * These tables need to be truncated
         */
        DB::table('planning_reports')->truncate();
        DB::table('trip_hacks')->truncate();
        DB::table('ts_102')->truncate();
        DB::table('ts_103')->truncate();
        DB::table('ts_105')->truncate();
        DB::table('_log_debtors')->truncate();

        /** these tables are not re-seeded
         * drivers              // re-seed for recovery
         * map_distances
         * password_resets
         * planning_reports
         * prices               // re-seed for recovery
         * promotions           // re-seed for recovery
         * publicholidays
         * schoolholidays
         * schoolterms          // re-seed for recovery
         * sessions
         * trip_hacks
         * trip_settings
         * vehicles             // re-seed for recovery
         */

/* for recovery */
//        $this->seeding('drivers');
//            $seeder = new DriversTableSeeder();
//            $seeder->run();
//        $this->seeded();
//
//        $this->seeding('prices');
//            $seeder = new PricesTableSeeder();
//            $seeder->run();
//        $this->seeded();
//
//        $this->seeding('promotions');
//            $seeder = new PromotionsTableSeeder();
//            $seeder->run();
//        $this->seeded();
//
//        $this->seeding('schoolterms');
//            $seeder = new SchooltermsTableSeeder();
//            $seeder->run();
//        $this->seeded();
//
//        $this->seeding('vehicles');
//            $seeder = new VehiclesTableSeeder();
//            $seeder->run();
//        $this->seeded();
/* end for recovery */

        return "'Tis Done Sir !";
    }

    private function seeding($table)
    {
        echo 'seeding '.$table.' ...';
        ob_flush();
        flush();
    }

    private function seeded()
    {
        echo ' seeded<br>';
        ob_flush();
        flush();
    }

    /**
     * Clean up vehicle_log table
     * Retains first daily mileage & removes any others on that date
     */
    public function seedVehicleLog()
    {
        $logs = DB::table('vehicle_log')->orderBy('reg')->orderBy('date')->get();
        $regs = $logs->pluck('reg')->unique()->values()->all();

        foreach ( $regs as $reg ) {
            $used = [];
            foreach ( $logs->where('reg', $reg)->all() as $log ) {
                $date = Carbon::parse($log->date)->toDateString();
                if ( !in_array($date, $used) ) {
                    DB::table('vehicle_log')->where('reg', $reg)->whereDate('date', $date)->where('id', '!=', $log->id)->delete();
                    $used[] = $date;
                }
            }
        }

        return;
    }

    /**
     * Seed debtors statements
     * adds months to debtors_statement from April up to end of prev invoicing month
     */
    public function seedStatements()
    {
        DebtorsStatement::where('date', '>', '2018-03-28')->delete();
        $db_dt = Carbon::parse(DebtorsStatement::orderBy('created_at','desc')->first()->date);
        $invMonth = DebtorsStatement::invMonth();
        $upto_dt = Carbon::createFromFormat('Y-m-d',$invMonth->end)->subMonth();

        while ( $db_dt->toDateString() < $upto_dt->toDateString() ) {
            $date = $db_dt->addMonth()->toDateString();
            app('App\Http\Controllers\DebtorsController')->updateStatement($date);
        }

        return;
    }

    /**
     * Seed vip promotions
     * add helga schoeman to latest vip
     */
    public function seedPromotionsVip()
    {
        $promotion = Promotion::where('name', 'Vip')->orderBy('start', 'desc')->first();
        $promotion->update(['customers' => ["100181"]]);

        return;
    }

    /**
     * Update inactive users
     * there are no event bookings that change these results
     *
     */
    public function fixUsers()
    {
        $users = User::withCount('bookings')->where('status', 'active')->where('role', '!=', 'admin')->get();
        foreach ( $users as $user ) {
            if ( $user->bookings_count == 0 && $user->joindate < '2018-01-01' ) {
                $inactive[] = $user->id;
            }
        }
        User::whereIn('id', $inactive)->update(['status' => 'inactive']);

        return;
    }

    /**
     * Seed xmural suburbs
     *
     */

    /**
     * Remove friends without bookings
     * previously just marked inactive.
     */
    private function cleanFriends()
    {
        $friends = Children::withCount('bookings')->where('friend','friend')->get();
        $inactive = [];
        foreach ( $friends as $friend ) {
            if ( $friend->bookings_count == 0 ) {
                $inactive[] = $friend->id;
            }
        }
        Children::whereIn('id', $inactive)->delete();
//        Children::whereIn('id', $inactive)->update(['status' => 'inactive']);

        return;
    }

    /**
     * Clean up xmural table
     *  also updates the imported bookings & user_xmurals
     *
     */
    private function fixXmurals()
    {
        // make all pvt views a social activity
        $xmurals = Xmural::where('view','pvt')->where('activity', '!=', 'Social')->orderBy('id', 'desc')->get();
        if ( !is_null($xmurals) ) {
            foreach ( $xmurals as $xmural ) {
                Xmural::find($xmural->id)->update(['activity' => 'Social']);
            }
        }

        // fix venues that should be pvt
        $xmurals = Xmural::where('view',  '!=', 'pvt')
            ->where('activity', 'Social')
            ->orWhere('venue', '=', 'Friend')
            ->orWhere('venue', 'like', '%Granny%')
            ->orWhere('venue', 'like', '%Mom%')
            ->orWhere('venue', 'like', '%Physio%')
            ->orWhere('venue', 'like', '%Optician%')
            ->orWhere('venue', '=', 'Remedial')
            ->orWhere('venue', '=', 'Lessons')
            ->orWhere('venue', '=', "Zac's House")
            ->orderBy('id', 'desc')->get();
        if ( !is_null($xmurals) ) {
            foreach ( $xmurals as $xmural ) {
                Xmural::find($xmural->id)->update(['view' => 'pvt']);
            }
        }

        // change first record to obsolete xmural for use in archived bookings
        $xmural = Xmural::find(300001);
        $xmural->update([
            'venue'     => 'Unknown Xmural',
            'unit'      => '',
            'street'    => 'Brighton St',
            'suburb'    => 'Hout Bay',
            'city'      => 'Cape Town',
            'view'      => 'pvt',
            'geo'       => '-34.041892,18.352491'
        ]);

        // clean ups & duplicates
        $xmurals = Xmural::where('venue','Mainstream Shopping Centre')->orderBy('id', 'desc')->get();
        if ( !is_null($xmurals) ) {
            $ids = $xmurals->pluck('id')->all();
            foreach ( $xmurals as $xmural ) {
                Xmural::find($xmural->id)->update([
                    'venue'  => 'Mom',
                    'unit'   => 'Mainstream Shopping Centre',
                    'street' => 'Princess St',
                    'view'   => 'pvt'
                ]);
            }
            $this->processUpdates($ids);
        }

        if (Schema::hasColumn('xmurals', 'activity')) {
            $xmurals = Xmural::where('street', 'Beach Cres')->where('activity', 'Sport')->orderBy('id', 'desc')->get();
            if ( !is_null($xmurals) ) {
                $ids = $xmurals->pluck('id')->all();
                foreach ( $xmurals as $xmural ) {
                    Xmural::find($xmural->id)->update([
                        'venue' => 'Edge Gym',
                        'unit'  => 'Village Gate Centre',
                        'geo'   => '-34.045406,18.360488'
                    ]);
                }
                $this->processUpdates($ids);
            }
        }

        $xmurals = Xmural::where('venue','Beach')->orderBy('id', 'desc')->get();
        if ( !is_null($xmurals) ) {
            $ids = $xmurals->pluck('id')->all();
            foreach ( $xmurals as $xmural ) {
                Xmural::find($xmural->id)->update([
                    'venue'  => 'Hout Bay Beach',
                    'unit'   => '',
                    'street' => '12 Beach Cres',
                    'geo'    => '-34.046391,18.359128'
                ]);
            }
            $this->processUpdates($ids);
        }

        $xmurals = Xmural::where('venue','like','Nanny And %')->orderBy('id', 'desc')->get();
        if ( !is_null($xmurals) ) {
            $ids = $xmurals->pluck('id')->all();
            foreach ( $xmurals as $xmural ) {
                Xmural::find($xmural->id)->update(['venue' => "Nanny 'n Me"]);
            }
            $this->processUpdates($ids);
        }

        $xmurals = Xmural::where('street','1 Gilquin Cres')->orderBy('id', 'desc')->get();
        if ( !is_null($xmurals) ) {
            $ids = $xmurals->pluck('id')->all();
            foreach ( $xmurals as $xmural ) {
                Xmural::find($xmural->id)->update(['venue' => "Gilquin Cres", 'geo' => '-34.040172,18.346919']);
            }
            $this->processUpdates($ids);
        }

        $xmurals = Xmural::where('street','1 Milner Rd')->orderBy('id', 'desc')->get();
        if ( !is_null($xmurals) ) {
            $ids = $xmurals->pluck('id')->all();
            $this->processUpdates($ids);
        }

        $xmurals = Xmural::where('street','11 Andrews Rd')->orderBy('id', 'desc')->get();
        if ( !is_null($xmurals) ) {
            $ids = $xmurals->pluck('id')->all();
            $this->processUpdates($ids);
        }

        $xmurals = Xmural::where('street','12 Gilquin Cres')->orderBy('id', 'desc')->get();
        if ( !is_null($xmurals) ) {
            $ids = $xmurals->pluck('id')->all();
            foreach ( $xmurals as $xmural ) {
                Xmural::find($xmural->id)->update(['venue' => "Granny", 'geo' => '-34.039859,18.346687']);
            }
            $this->processUpdates($ids);
        }

        $xmurals = Xmural::where('street', 'like', '138 Albert%')->orderBy('id', 'desc')->get();
        if ( !is_null($xmurals) ) {
            $ids = $xmurals->pluck('id')->all();
            foreach ( $xmurals as $xmural ) {
                Xmural::find($xmural->id)->update(['venue' => "Cooking Club", 'street' => '138 Albert Rd', 'geo' => '-34.033274,18.347420']);
            }
            $this->processUpdates($ids);
        }

        $xmurals = Xmural::where('street', '21 Lategan St')->orderBy('id', 'desc')->get();
        if ( !is_null($xmurals) ) {
            $ids = $xmurals->pluck('id')->all();
            foreach ( $xmurals as $xmural ) {
                Xmural::find($xmural->id)->update(['venue' => "Wendy Swim School", 'geo' => '-34.033500,18.356744']);
            }
            $this->processUpdates($ids);
        }

        $xmurals = Xmural::where('street','3 Gully Rd')->orderBy('id', 'desc')->get();
        if ( !is_null($xmurals) ) {
            $ids = $xmurals->pluck('id')->all();
            $this->processUpdates($ids);
        }

        $xmurals = Xmural::where('street','3 Spinner Close')->orderBy('id', 'desc')->get();
        if ( !is_null($xmurals) ) {
            $ids = $xmurals->pluck('id')->all();
            $this->processUpdates($ids);
        }

        $xmurals = Xmural::where('venue', 'Hout Bay Montessori')->orderBy('id', 'desc')->get();
        if ( !is_null($xmurals) ) {
            $ids = $xmurals->pluck('id')->all();
            foreach ( $xmurals as $xmural ) {
                Xmural::find($xmural->id)->update(['street' => "4459 Main Rd", 'geo' => '-34.018997,18.370885']);
            }
            $this->processUpdates($ids);
        }

        $xmurals = Xmural::where('street', '42 Soetvlei Ave')->orderBy('id', 'desc')->get();
        if ( !is_null($xmurals) ) {
            $ids = $xmurals->pluck('id')->all();
            $this->processUpdates($ids);
        }

        $xmurals = Xmural::where('street', 'Andrews Rd')->orderBy('id', 'desc')->get();
        if ( !is_null($xmurals) ) {
            $ids = $xmurals->pluck('id')->all();
            foreach ( $xmurals as $xmural ) {
                Xmural::find($xmural->id)->update(['venue' => 'Museum Hall', 'street' => "3 Andrews Rd", 'geo' => '-34.040380,18.360006']);
            }
            $this->processUpdates($ids);
        }

        $xmurals = Xmural::where('street', 'Baviaanskloof Rd')->where('unit', '')->orderBy('id', 'desc')->get();
        if ( !is_null($xmurals) ) {
            $ids = $xmurals->pluck('id')->all();
            foreach ( $xmurals as $xmural ) {
                Xmural::find($xmural->id)->update(['venue' => 'Community Hall', 'street' => "Baviaanskloof Rd", 'geo' => '-34.041990,18.360192']);
            }
            $this->processUpdates($ids);
        }

        $xmurals = Xmural::where('street', 'like', '%Foresters%')->orderBy('id', 'desc')->get();
        if ( !is_null($xmurals) ) {
            $ids = $xmurals->pluck('id')->all();
            foreach ( $xmurals as $xmural ) {
                Xmural::find($xmural->id)->update(['venue' => 'Oasis Swimming Academy', 'street' => "1 Foresters Close", 'geo' => '-34.022638,18.367510']);
            }
            $this->processUpdates($ids);
        }

        $xmurals = Xmural::where('venue', 'Kronendal Music Academy')->get();
        if ( !is_null($xmurals) ) {
            $ids = $xmurals->pluck('id')->all();
            foreach ( $xmurals as $xmural ) {
                Xmural::find($xmural->id)->update(['street' => "153 Empire Ave", 'geo' => '-34.030803,18.348348']);
            }
            $this->processUpdates($ids);
        }

        $xmurals = Xmural::where('venue', 'Victoria Mall')->get();
        if ( !is_null($xmurals) ) {
            $ids = $xmurals->pluck('id')->all();
            foreach ( $xmurals as $xmural ) {
                Xmural::find($xmural->id)->update(['venue' => 'Samurai Karate', 'unit' => '1st Floor Victoria Mall','street' => "cnr Victoria and Empire Ave", 'geo' => '-34.031313,18.350558']);
            }
            $this->processUpdates($ids);
        }

        $xmurals = Xmural::where('venue', 'Museum Hall')->get();
        if ( !is_null($xmurals) ) {
            $ids = $xmurals->pluck('id')->all();
            foreach ( $xmurals as $xmural ) {
                Xmural::find($xmural->id)->update(['street' => "3 Andrews Rd", 'geo' => '-34.040380,18.360006']);
            }
            $this->processUpdates($ids);
        }

        $xmurals = Xmural::where('venue', 'Hout Bay International Prim')->orderBy('id', 'desc')->get();
        if ( !is_null($xmurals) ) {
            $ids = $xmurals->pluck('id')->all();
            foreach ( $xmurals as $xmural ) {
                Xmural::find($xmural->id)->update(['street' => "60 Main Rd"]);
            }
            $this->processUpdates($ids);
        }

        $xmurals = Xmural::where('street', 'Melkhout Cres')->orderBy('id', 'desc')->get();
        if ( !is_null($xmurals) ) {
            $ids = $xmurals->pluck('id')->all();
            foreach ( $xmurals as $xmural ) {
                Xmural::find($xmural->id)->update(['venue' => 'Gym Minis', 'unit' => "Checkers Centre", 'geo' => '-34.044499,18.358455']);
            }
            $this->processUpdates($ids);
        }

        // replace bookings for xmural school venues with school venue
        $schools = School::get()->pluck('name','id')->all();
        $xmurals = Xmural::whereIn('venue', $schools)->get();
        if ( !is_null($xmurals) ) {
            // update bookings
            foreach ( $xmurals as $xmural ) {
                $school = array_search($xmural->venue, $schools);
                DB::table('bookings')->where('puloc_id', $xmural->id)->update(['puloc_id' => $school, 'puloc_type' => 'school']);
                DB::table('bookings')->where('doloc_id', $xmural->id)->update(['doloc_id' => $school, 'doloc_type' => 'school']);
                // archives
//                DB::connection('sbugarchive')->table('archive_bookings')->where('puloc', $xmural->id)->update(['puloc' => $school]);
//                DB::connection('sbugarchive')->table('archive_bookings')->where('doloc', $xmural->id)->update(['doloc' => $school]);
            }
            // remove from user_xmurals
            $ids = $xmurals->pluck('id')->all();
            DB::table('user_xmurals')->whereIn('xmural_id', $ids)->delete();
            // remove from xmurals
            Xmural::whereIn('id', $ids)->delete();
        }

        // remove obsolete records (this sb run annually)
        // @todo if set up as annual run these should also be removed from trips
        $xmurals = Xmural::where('view', 'pvt')->where('id', '!=', 300001)->orderBy('id')->get();
        $ids = $xmurals->pluck('id')->all();
        $pups = DB::table('bookings')->whereIn('puloc_id', $ids)->get()->pluck('puloc_id','puloc_id')->all();
        $dofs = DB::table('bookings')->whereIn('doloc_id', $ids)->get()->pluck('doloc_id','doloc_id')->all();
        $bookings = array_unique(array_merge($pups,$dofs));
        $unused = array_diff($ids, $bookings);

        if ( count($unused) > 0 ) {
            // replace all of these in bookings with id of 'archived xmural'
//            DB::connection('sbugarchive')->table('archive_bookings')->whereIn('puloc', $unused)->update(['puloc' => 300001]);
//            DB::connection('sbugarchive')->table('archive_bookings')->whereIn('doloc', $unused)->update(['doloc' => 300001]);
            // remove unused from user_xmurals
            DB::table('user_xmurals')->whereIn('xmural_id', $unused)->delete();
            // remove unused from xmurals
            DB::table('xmurals')->whereIn('id', $unused)->delete();
        }

        // crash saver: replace bookings for unknown xmurals with archive xmural
        $xmurals = Xmural::orderBy('id')->get()->pluck('id')->all();
        $pups = DB::table('bookings')->where('puloc_id','like','300%')->get()->pluck('puloc_id','puloc_id')->all();
        $dofs = DB::table('bookings')->where('doloc_id','like','300%')->get()->pluck('doloc_id','doloc_id')->all();
        $bookings = array_unique(array_merge($pups,$dofs));
        $unknown = array_diff($bookings, $xmurals);
        DB::table('bookings')->whereIn('puloc_id', $unknown)->update(['puloc_id' => 300001]);
        DB::table('bookings')->whereIn('doloc_id', $unknown)->update(['doloc_id' => 300001]);

        // drop activity column
        if (Schema::hasColumn('xmurals', 'activity')) {
            DB::statement('ALTER TABLE xmurals DROP activity');
        }

        return;
    }

    /**
     * fixXmural helper
     *
     * @param $ids
     * @throws \Exception
     */
    private function processUpdates($ids)
    {
        $keep = array_pop($ids);
        $this->updateBookings($ids, $keep);
        Xmural::whereIn('id', $ids)->delete();

        return;
    }

    /**
     * fixXmural helper
     *
     * @param $ids
     * @param $keep
     */
    private function updateBookings($ids, $keep)
    {
        $bookings = DB::table('bookings')->whereIn('puloc_id',$ids)->get();
        foreach($bookings as $booking) {
            DB::table('bookings')->where('id',$booking->id)->update(['puloc_id' => $keep]);
        }
        $bookings = DB::table('bookings')->whereIn('doloc_id',$ids)->get();
        foreach($bookings as $booking) {
            DB::table('bookings')->where('id',$booking->id)->update(['doloc_id' => $keep]);
        }
        // archives
//        $bookings = DB::connection('sbugarchive')->table('archive_bookings')->whereIn('puloc',$ids)->get();
//        foreach($bookings as $booking) {
//            DB::connection('sbugarchive')->table('archive_bookings')->where('id',$booking->id)->update(['puloc' => $keep]);
//        }
//        $bookings = DB::connection('sbugarchive')->table('archive_bookings')->whereIn('doloc',$ids)->get();
//        foreach($bookings as $booking) {
//            DB::connection('sbugarchive')->table('archive_bookings')->where('id',$booking->id)->update(['doloc' => $keep]);
//        }

        // update user_xmurals
        $links = DB::table('user_xmurals')->whereIn('xmural_id',$ids)->get();
        $users = $links->pluck('user_id','user_id')->all();
        foreach($users as $user) {
            $keep_link = DB::table('user_xmurals')->where('user_id',$user)->where('xmural_id',$keep)->first();
            if ( is_null($keep_link) ) {
                DB::table('user_xmurals')->insert(['user_id' => $user, 'xmural_id' => $keep]);
            }
            DB::table('user_xmurals')->where('user_id',$user)->whereIn('xmural_id', $ids)->delete();
        }

        return;
    }

}
