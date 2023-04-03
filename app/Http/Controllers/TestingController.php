<?php

namespace App\Http\Controllers;

use App\Console\Commands\DebtorsMonthend;
use App\Http\Processors\BuildInvoice;
use App\Http\Processors\PriceUpdates;
use App\Http\Processors\SmsApi;
use App\Models\Booking;
use App\Models\Children;
use App\Models\DebtorsStatement;
use App\Models\Holiday;
use App\Models\PlanningReport;
use App\Models\Price;
use App\Models\Promotion;
use App\Models\School;
use App\Models\TripSettings;
use App\Models\User;
use App\Models\Vehicle;
use App\TripPlanning\RouteMappers;
use App\TripPlanning\TripBuilder;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class TestingController extends Controller
{
    /**
     * Display test results
     *      To see debugbar use view else dump or return results
     *
     * @params Request  $request
     * @return mixed
     */
    public function show(Request $request)
    {
        //dd(phpinfo());
//        $sms = new SmsApi();
//        dd((string)$sms->checkCredits());

//        $this->booking = new \App\Models\Booking;

//        $this->processDebtorsTransactions();
//        $this->dropoffDuration();
//        return $this->planForDay('2020-02-21');
//        $this->moveBookings(2017);
//        $this->changeBookingsTable();
//        $this->debtorsStatement();
//        return $this->hhTrips();
//        dd(config());








dd('done');
        // 1301 files (4/1/18) 101 files (5/1/18)
//        $num = 0;
//        foreach ( Storage::disk('signatures')->allFiles() as $file ) {
//            $num++;
//        }
//        dd($num);

//        $live = env('DB_DATABASE', 'sbug5');
//        $archive = env('DB2_DATABASE', 'sbugarchive');
//        $year = now()->subYear(2)->year;
//        $table = '_' . $year . '_bookings';
//
//        if ( !Schema::connection('sbugarchive')->hasTable($table) && Schema::hasTable('bookings') ) {
//            DB::statement("CREATE TABLE $archive.$table LIKE $live.bookings");
//
//            $cutoff = Carbon::parse('last day of December ' . $year)->toDateString();
//
//            DB::statement("INSERT INTO $archive.$table SELECT * FROM $live.bookings WHERE date <= '$cutoff'");
//
////            if ( DB::connection('sbugarchive')->table($table)->count() > 0 ) {
////                DB::table('bookings')->where('date', '<=', $cutoff)->delete();
////            }
//        }
//dd(DB::connection('sbugarchive')->table($table)->count());


        $request->merge([
            'date'      => '2018-12-03',
            'period'    => 'day',
            'day_vehicles' => [4,6,5]
        ]);
        // build and save the plan

        $builder = new TripBuilder();
        $builder->handle($request);
        dd('done');

        // inactive friends
//        $friends = Children::withCount('bookings')->where('friend','friend')->where('status', 'active')->get();
//        dump('active friends= '.count($friends));
//        $inactive = [];
//        foreach ( $friends as $friend ) {
//            if ( $friend->bookings_count == 0 ) {
//                $inactive[] = $friend->id;
//            }
//        }
//        dd('inactive friends= '.count($inactive));

        // active users without bookings
//        $change = [];
//        $users = User::withCount('bookings')->where('status', 'active')->where('role', '!=', 'admin')->orderBy('last_name')->get();
//        foreach ( $users as $user ) {
//            if ( $user->bookings_count == 0 ) {
//                dump($user->id.' '.$user->name . ' = ' . $user->bookings_count.' ('.$user->joindate.')');
//                if ( $user->joindate < '2018-01-01' ) {
//                    $change[] = $user->id.' '.$user->name;  // these should be updated to 'inactive'
//                }
//            }
//        }
//        dd($change);

//        $distances = DB::table('map_distances')->orderBy('from')->get();
//        foreach ( $distances as $distance ) {
//            $matches = $distances->where('from', $distance->from)->where('to', $distance->to)->all();
//            if ( count($matches) > 1 ) {
//                echo $distance->from.' -> '.$distance->to.'<br>';
//            }
//        }
//        dd('done');

// convert array of arrays to array of objects
//    $object = array_map(function($element) {
//        return (object) $element;
//    }, $route);

        /*
        // test integrity of debtors_statement table
        $statements = DB::table('debtors_statement')->orderBy('date')->get();
        $customers = $statements->pluck('user_id','user_id');

        foreach ($customers as $customer) {
            $statement = $statements->where('user_id', $customer);

            $opbal = ($statement->first()->balance - $statement->first()->amount);
            $trans = $statement->sum('amount');
            $calc = $opbal + $trans;
            $clbal = $statement->last()->balance;
            $diff = 0;

            if ( $calc != $clbal ) {
                echo 'Customer: ' . $customer . '<br>-------------------<br>';
                echo 'Opening Balance = ' . $opbal . '<br>';
                echo 'transactions = ' . $trans . '<br>';
                echo 'calculated balance = ' . $calc . '<br>';
                echo 'DB Balance = ' . $clbal.'<br><br>';
                $diff += $clbal - $calc;
            }
        }
        dd($diff);
        */

        /*
        // friends with bookings
        $friends = Children::where('friend', 'friend')->orderBy('first_name')->get();

        foreach( $friends as $friend ) {
            $bookings = DB::connection('mysqlsbug4')->table('bookings')
                ->where('passenger_id', $friend->id)
                ->count();

            if ( $bookings > 0 ) {
                $trips[] = $friend->id . ': ' . $friend->name . ' = ' . $bookings;
            }
        }

        return $trips;
        */

        /*
        // list children whose user_id does not match a customer
        $users = User::orderBy('id')->get()->pluck('name','id')->all();
        $children = Children::orderBy('user_id')->get();
        foreach ( $children as $child ) {
            if ( !isset($users[$child->user_id]) ) {
                echo $child->user_id.' does not exist for child '.$child->name.' (id = '.$child->id.')<br>';
                $kids = Children::where('id', '!=', $child->id)->where('first_name', $child->first_name)->where('last_name', $child->last_name)->get();
                if ( count($kids) > 0 ) {
                    echo '----------------------------------------------<br>';
                }
                foreach ( $kids as $kid ) {
                    echo $kid->user_id.' does exist for child '.$child->name.' (id = '.$kid->id.')<br>';
                }
                if ( count($kids) > 0 ) {
                    echo '----------------------------------------------<br>';
                }
                echo '<br>';
            }
        }
        */
    }

    public function hhTrips()
    {
//        $trips = $this->planForDay('2020-02-21');
//        foreach ($trips as $trip) {
//            if(key($trip['free']) > 102) {
//                $hh[] = [
//                    'time' => $trip['pickup']['time'],
//                    'venue' => $trip['pickup']['venue'],
//                    'pax' => $trip['']
//            }
//        }
//        return $trips;

        $trips = Booking::
//            whereBetween('date', ['2020-01-01','2020-02-21'])
            where('date', '2020-02-21')
            ->where('vehicle', '>', 102)
            ->get();

        foreach ( $trips as $trip )


        return $trips;
    }

    /**
     * checks that all entries in debtors journal have been posted to debtors statement
     * for current year up to last statement issued.
     * also that all closing balances are correct.
     */
    private function debtorsStatement()
    {
        $customers = Booking::with('user')->whereYear('date', now()->year)->get()->pluck('user.name', 'user_id')->all();
        $upto = now()->year.'-'.(now()->month-1).'-28';
        $counter = 0;

        $journal = DB::table('debtors_journal')
            ->whereYear('date', now()->year)
            ->where('date', '<=', $upto)
            ->orderBy('date')
            ->get();

        $statement = DB::table('debtors_statement')
            ->whereYear('date', now()->year)
            ->where('transaction', '!=', 'Invoice')
            ->orderBy('date')
            ->get();

        foreach ( $customers as $id => $name ) {
            $user_stat = collect($statement->where('user_id', $id)->all());
            $user_jour = collect($journal->where('user_id', $id)->all());

            if ( $user_stat->sum('amount') != $user_jour->sum('amount') ) {
                $counter++;
                dump($id.':'.$name . ': statement: ' . $user_stat->sum('amount') . ' == journal: ' . $user_jour->sum('amount'));
                foreach ( $user_jour as $payment ) {
                    if ( $payment->amount != ($user_stat->where('date', $payment->date)->first()->amount ?? 0) ) {
                        echo 'payment: ' . $payment->date . ' : ' . $payment->amount . ' statement: ' . ($user_stat->where('date', $payment->date)->first()->amount ?? 'nada') . '<br>';
                    }
                }
            }
        }

        if ( $counter == 0 ) {
            dump('All journal entries have been posted');
        }

        $statement = DB::table('debtors_statement')
            ->whereYear('date', now()->year)
            ->orderBy('date')
            ->get();

        $counter = 0;
        foreach ( $customers as $id => $name ) {
            $user_stat = collect($statement->where('user_id', $id)->all());

            if ( count($user_stat) > 0 ) {
                $opening_balance = $user_stat->first()->balance - $user_stat->first()->amount;
                $closing_balance = $user_stat->last()->balance;
                $check = $opening_balance + $user_stat->sum('amount');
                if ( $check != $closing_balance ) {
                    $counter ++;
                    dump($id . ':' . $name . ': statement bal = ' . $closing_balance . ' == check = ' . $check);
                }
            }
        }

        if ( $counter == 0 ) {
            dump('All balances check ok');
        }
    }

    private function moveBookings($year)
    {
        $table = '_' . $year . '_bookings';

        if ( !Schema::connection('sbugarchive')->hasTable($table) ) {

            DB::statement("CREATE TABLE sbugarchive.$table LIKE bookings;");
            $date = Carbon::parse('last day of December ' . $year)->toDateString();
            DB::statement("INSERT INTO sbugarchive.$table SELECT * FROM bookings WHERE date <= '$date'");
            if ( DB::connection('sbugarchive')->table('_' . $year . '_bookings')->count() > 0 ) {
                DB::table('bookings')->where('date', '<=', $date)->delete();
            }
            /*
            Schema::connection('sbugarchive')->create('_' . $year . '_bookings', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('user_id')->index();
                $table->unsignedInteger('passenger_id')->index();
                $table->date('date')->index();
                $table->unsignedInteger('puloc_id');
                $table->time('putime');
                $table->unsignedInteger('doloc_id');
                $table->time('dotime');
                $table->unsignedSmallInteger('price');
                $table->string('vehicle', 3);
                $table->unsignedSmallInteger('driver');
                $table->string('puloc_type', 10);
                $table->string('doloc_type', 10);
                $table->string('journal', 10);
                $table->unsignedSmallInteger('fin_m');
                $table->timestamps();
            });

            $imports = DB::table('bookings')
                ->whereBetween('date', [Carbon::parse('first day of January ' . $year)->toDateString(), Carbon::parse('last day of December ' . $year)->toDateString()])
                ->orderBy('id')
                ->get();

            foreach ( $imports as $import ) {
                $input = (array) $import;
                DB::connection('sbugarchive')->table('_' . $year . '_bookings')->insert($input);
                $input = [];
            }

            if ( DB::connection('sbugarchive')->table('_' . $year . '_bookings')->count() > 0 ) {
                DB::table('bookings')
                    ->whereBetween('date', [Carbon::parse('first day of January ' . $year)->toDateString(), Carbon::parse('last day of December ' . $year)->toDateString()])
                    ->delete();
            }
            */
        }
    }

    /**
     * return the trip plan
     *
     * @param $date
     * @return mixed
     */
    private function planForDay($date)
    {
        return PlanningReport::where('date', $date)->first()->day;
    }

    /**
     * Return trip time averages
     *
     */
    public function dropoffDuration()
    {
        $start = '2020-02-15';
        $end = '2020-03-30';

//        $target_time = '15:30';
//        $target_school = 'Llandudno Prim';
        $limit = 0;

        $plans = PlanningReport::whereBetween('date', [$start, $end])->get()->pluck('day')->all();
        foreach ( $plans as $plan ) {
            foreach ( $plan as $pickup ) {

                if ( !isset($pickup['error']) ) {
                    if ( $limit > 0 && Arr::first($pickup['pass']) != $limit ) {
                        continue;
                    }
                    // $pos = strpos($pickup['head'], ':')+2+5;
                    $time = substr($pickup['head'], strpos($pickup['head'], ':') + 2, 5);
                    // $venue = substr($pickup['head'],strpos($pickup['head'], ' at ')+4,strlen($pickup['head'])-$pos - 17);
                    // $venue = str_replace('.','',$venue);

                    // if ( $time == $target_time && $venue == $target_school ) {
                    $duration = Carbon::parse($time)->diffInMinutes(Arr::first($pickup['free']));
                    $frees[Arr::first($pickup['pass'])][] = $duration;
//                    $stat[] = Arr::first($pickup['pass']) . ' = ' . $duration;
                    // }
                }
            }
        }
//        dump($target_time.' @ '.$target_school);
//        dump($stat ?? '?');

        ksort($frees);
        foreach ( $frees as $passengers => $free ) {
            $values = array_count_values($free);
            $mode = array_search(max($values), $values);
            $avg = round(array_sum($free) / count($free), 0);
            $max = max($free);
            dump($passengers . ' passengers');
            echo 'avg = ' . $avg . '<br>';
            echo 'max = ' . $max . '<br>';
            echo 'mode = ' . $mode . '<br>';
            echo 'mean = ' . round(($avg + $max + $mode) / 3, 0) . '<br>';
//            dump('----------------------------------------');
        }
        dd('done');
    }




    /**
     * Re-build debtors statements
     * bu table exists for recovery
     * remove all statement entries dated after 2017-01-28
     * (accept that the remaining entries represent accurate opening transactions and balances)
     * adds months to statement from 2017-01-29 up to 2017-12-28 resulting in an accurate record
     * of 2017 transactions and closing balance at 2017-12-28.
     */
    public function buildStatements()
    {
        /*
        $stat_2016 = DB::connection('sbugarchive')->table('_2016_debtors_statements')->orderBy('date', 'desc')->get();
        $used = [];
        foreach($stat_2016 as $stat) {
            if ( !in_array($stat->user_id, $used) ) {
                if ( $stat->balance > 0 ) {
                    $op_bals[$stat->user_id] = $stat->balance;
                }
                $used[] = $stat->user_id;
            }
        }
//
        DebtorsStatement::query()->truncate();
        foreach ( $op_bals as $id => $balance ) {
            if ( !in_array($id, [100135,100628,100702]) ) {
                DebtorsStatement::create([
                    'user_id'     => $id,
                    'date'        => '2017-01-01',
                    'transaction' => 'Opening Balance',
                    'amount'      => 0,
                    'balance'     => $balance,
                    'created_at'  => Carbon::parse('2017-01-01')->toDateTimeString()
                ]);
            }
        }

        $recons = DB::table('_build_recons')->orderBy('date')->get();
        $recons = json_decode(json_encode($recons), true);
        foreach ( $recons as $recon ) {
            DB::table('debtors_journal')->insert($recon);
        }
//
        $db_dt = Carbon::parse('2016-12-28');
        $upto_dt = Carbon::parse('2017-12-28');

        while ( $db_dt->toDateString() < $upto_dt->toDateString() ) {
            $date = $db_dt->addMonth()->toDateString();
            app('App\Http\Controllers\DebtorsController')->updateStatement($date);
        }
    */

        $statements = DB::table('debtors_statement')->orderBy('created_at','desc')->get();
        $customers = User::whereIn('id', $statements->pluck('user_id')->unique()->all())->get()->sortBy('alpha_name', SORT_NATURAL|SORT_FLAG_CASE)->pluck('alpha_name', 'id')->all();
        foreach ( $customers as $id => $name ) {
            $balances[$id]['built'] = collect($statements)->where('user_id', $id)->first()->balance;
        }

        $origstats = DB::table('_bu_debtors_statement')->where('date', '<', '2017-12-29')->orderBy('created_at', 'desc')->get();
        $origcustomers = User::whereIn('id', $origstats->pluck('user_id')->unique()->all())->get()->sortBy('alpha_name', SORT_NATURAL|SORT_FLAG_CASE)->pluck('alpha_name', 'id')->all();
        foreach ( $origcustomers as $id => $name ) {
            if ( isset($balances[$id]) ) {
                $balances[$id]['orig'] = collect($origstats)->where('user_id', $id)->first()->balance;
            } else {
                $balances[$id]['built'] = 0;
                $balances[$id]['orig'] = collect($origstats)->where('user_id', $id)->first()->balance;
            }
        }
        $customers = $customers + $origcustomers;
        foreach ($customers as $id => $name ) {
            if ( !isset($balances[$id]['orig']) ) {
                $balances[$id]['orig'] = 0;
            }
        }

        $data = [
            'customers'     => $customers,
            'balances'      => $balances
        ];

        return view('office.built-debtors', $data);
    }

    private function changeBookingsTable()
    {
        Booking::where('price', 154)->update(['promo' => 29]);
        Booking::where('price', 151)->update(['promo' => 30]);
        Booking::where('price', 125)->update(['promo' => 28]);
        Booking::where('price', 121)->update(['promo' => 22]);
        Booking::where('price', 106)->update(['promo' => 19]);
        Booking::where('price', 104)->update(['promo' => 23]);
        Booking::where('price', 67)->update(['promo' => 24]);
        Booking::where('price', 58)->update(['promo' => 21]);
        Booking::where('price', 50)->update(['promo' => 17]);
    }

    /**
     * Emergency re-processing of debtors statement entries if scheduled run fails
     * will not duplicate entries that are already posted
     * only posts entries to statement, does not send emails to clients.
     *
     */
    private function processDebtorsTransactions()
    {
        $last_invoice = DebtorsStatement::where('transaction', 'Invoice')->orderBy('date', 'desc')->first()->date;
        $next_invoice = Carbon::createFromFormat('Y-m-d', $last_invoice)->addMonth()->toDateString();

        dump('Processing '.$next_invoice);

        if ( $next_invoice < now()->toDateString() ) {
            $this->statement = new \App\Http\Processors\BuildStatement;
            list($journals, $invoices) = $this->statement->update($next_invoice);
            dd('posted '.$journals.' journals and '.$invoices.' invoices');
        } else {
            dd("Can't process $next_invoice - it's not yet due");
        }

        dd('done');
    }

}
