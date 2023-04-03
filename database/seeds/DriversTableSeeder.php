<?php

use Illuminate\Database\Seeder;

class DriversTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $imports = DB::connection('mysqlsbug4')->table('drivers')
            ->select('id','first_name','last_name','from','to','status')
            ->orderBy('id')
            ->get();

        foreach ( $imports as $import ) {
            $input = (array) $import;
            DB::table('drivers')->insert($input);
            $input = [];
        }
    }
}
