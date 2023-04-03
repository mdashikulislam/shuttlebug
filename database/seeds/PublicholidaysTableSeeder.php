<?php

use Illuminate\Database\Seeder;

class PublicholidaysTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $imports = DB::connection('mysqlsbug4')->table('holpublic')
            ->select('id','date','day')
            ->orderBy('id')
            ->get();

        foreach ( $imports as $import ) {
            $input = (array) $import;
            DB::table('publicholidays')->insert($input);
            $input = [];
        }
    }
}
