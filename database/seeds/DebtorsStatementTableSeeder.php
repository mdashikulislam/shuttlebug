<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DebtorsStatementTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $imports = DB::connection('mysqlsbug4')->table('debtors_statements')
            ->select('id','user_id','date','type','transaction','val','balance')
            ->where('date', '<=', '2017-12-28')
            ->orderBy('id')
            ->get();

        $i = 0;
        foreach ( $imports as $import ) {
            foreach ( $import as $key => $value ) {
                // ignore type
                if ( $key == 'type' ) {
                    continue;
                }
                // fix val
                if ( $key == 'val' ) {
                    $input['amount'] = $import->type == 'cr' ? $value * -1 : $value;
                    continue;
                }
                // add created_at
                if ( $key == 'date' ) {
                    $input['date'] = $value;
                    $input['created_at'] = Carbon::parse($value)->addHours(9)->addSeconds($i)->toDateTimeString();
                    continue;
                }
                $input[$key] = $value;
            }
            DB::table('_bu_debtors_statement')->insert($input);
            $input = [];
            $i++;
        }
    }
}
