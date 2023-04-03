<?php

use Illuminate\Database\Seeder;

class SchooltermsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $imports = DB::connection('mysqlsbug4')->table('schoolterms')
            ->select('id','start','end')
            ->where('region','coast')
            ->orderBy('id')
            ->get();

        foreach ( $imports as $import ) {
            $input = (array) $import;
            DB::table('schoolterms')->insert($input);
            $input = [];
        }
    }
}
