<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TsFeedbackTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $from = now()->firstOfMonth()->toDateString();
        $imports = DB::connection('mysqlsbug4')->table('ts_confirmations')
            ->select('id','passenger','sms','acttime','duetime','vehicle')
            ->where('acttime', '>=', $from)
            ->orderBy('id')
            ->get();

        foreach ( $imports as $import ) {
            foreach ( $import as $key => $value ) {
                // change sms to data
                if ( $key == 'sms' ) {
                    $input['data'] = $value;
                    continue;
                }
                // handle acttime
                if ( $key == 'acttime' ) {
                    $input['date'] = Carbon::parse($value)->toDateString();
                    $input['acttime'] = Carbon::parse($value)->format('H:i:s');
                    continue;
                }
                $input[$key] = $value;
            }
            DB::table('ts_feedback')->insert($input);
            $input = [];
        }
    }
}
