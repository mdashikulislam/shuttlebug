<?php

use Illuminate\Database\Seeder;

class PricesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $imports = DB::connection('mysqlsbug4')->table('prices')
            ->select('date','basicrate','siblingdisc','volumedisc')
            ->where('franchise_id', 600001)
            ->orderBy('id')
            ->get();

        foreach ( $imports as $import ) {
            foreach ( $import as $key => $value ) {
                if ( $key == 'basicrate' ) {
                    $input['basic_rate'] = $value;
                    continue;
                }
                if ( $key == 'siblingdisc' ) {
                    $input['sibling_disc'] = $value;
                    continue;
                }
                if ( $key == 'volumedisc' ) {
                    $input['volume_disc'] = $value;
                    continue;
                }
                $input[$key] = $value;
            }
            DB::table('prices')->insert($input);
            $input = [];
        }
    }
}
