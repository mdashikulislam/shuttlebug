<?php

use Illuminate\Database\Seeder;

class VehicleLogTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $last_seed = DB::table('vehicle_log')->orderBy('id', 'desc')->first()->id;
        $imports = DB::connection('mysqlsbug4')->table('vehicle_logs')
            ->select('id','reg','date','milage')
            ->where('id', '>', $last_seed)
            ->orderBy('id')
            ->get();

        foreach ( $imports as $import ) {
            foreach ( $import as $key => $value ) {
                // change milage to mileage
                if ( $key == 'milage' ) {
                    $input['mileage'] = $value;
                    continue;
                }
                $input[$key] = $value;
            }
            DB::table('vehicle_log')->insert($input);
            $input = [];
        }
    }
}
