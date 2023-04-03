<?php

use Illuminate\Database\Seeder;

class PromotionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $imports = DB::connection('mysqlsbug4')->table('promotions')
            ->select('id','type','view','name','description','rate','start','expire')
            ->orderBy('id')
            ->get();

        foreach ( $imports as $import ) {
            foreach ( $import as $key => $value ) {
                if ( $key == 'start' ) {
                    if ( $value < date('Y-m-d') ) {
                        $input['expire'] = $import->expire;
                    }
                }
                if ( $key == 'expire' ) {
                    continue;
                }
                $input[$key] = $value;
            }
            DB::table('promotions')->insert($input);
            $input = [];
        }
    }
}
