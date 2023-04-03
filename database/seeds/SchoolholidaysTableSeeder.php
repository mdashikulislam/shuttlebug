<?php

use Illuminate\Database\Seeder;

class SchoolholidaysTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $imports = DB::connection('mysqlsbug4')->table('holschool_coast')->get();

        foreach ( $imports as $import ) {
            $input = (array) $import;
            DB::table('schoolholidays')->insert($input);
            $input = [];
        }
    }
}
