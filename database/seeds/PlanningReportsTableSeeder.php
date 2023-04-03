<?php

use Illuminate\Database\Seeder;

class PlanningReportsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $imports = DB::connection('mysqlsbug4')->table('ts_reports')
            ->select('id','date','aml','day','updated_at')
            ->get();

        foreach ( $imports as $import ) {
            foreach ( $import as $key => $value ) {
                // change aml
                if ( $key == 'aml' ) {
                    $input['am'] = $value;
                    continue;
                }
                $input[$key] = $value;
            }
            DB::table('planning_reports')->insert($input);
            $input = [];
        }
    }
}
