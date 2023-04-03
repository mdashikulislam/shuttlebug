<?php

use Illuminate\Database\Seeder;

class DebtorsJournalTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $imports = DB::connection('mysqlsbug4')->table('debtors_journal')
            ->select('id','date','user_id','entry','trans','amount','acc')
            ->where('date', '>', '2018-03-27')  // ignore for recovery
            ->orderBy('id')
            ->get();

        foreach ( $imports as $import ) {
            foreach ( $import as $key => $value ) {
                // ignore trans
                if ( $key == 'trans' ) {
                    continue;
                }
                // change acc to type
                if ( $key == 'acc' ) {
                    $input['type'] = $value;
                    continue;
                }
                // fix entry
                if ( $key == 'entry' ) {
                    $input['entry'] = $value == 'duplicated' ? 'Reverse Duplication' : ucwords($value);
                    continue;
                }
                // adjust amount
                if ( $key == 'amount' ) {
                    $input['amount'] = $import->trans == 'cr' ? $value * -1 : $value;
                    continue;
                }
                $input[$key] = $value;
            }
            DB::table('debtors_journal')->insert($input);
            $input = [];
        }
    }
}
