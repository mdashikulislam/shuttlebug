<?php

use Illuminate\Database\Seeder;

class TripSettingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $imports = DB::connection('mysqlsbug4')->table('route_settings')
            ->select('id','busroute','pref_outer_am','passenger_limit','hold_vehicle','home_delay','school_dodelay','school_pudelay','buffer','pre_allocate')
            ->get();

        foreach ( $imports as $import ) {
            foreach ( $import as $key => $value ) {
                // change bus_route
                if ( $key == 'busroute' ) {
                    $input['bus_route'] = 0;
                    continue;
                }
                // change hold_vehicle
                if ( $key == 'hold_vehicle' ) {
                    $input['vehicle_wait'] = 900;
                    continue;
                }
                // change pref_vehicle
                if ( $key == 'pref_outer_am' ) {
                    $input['pref_am_vehicle'] = 103;
                    continue;
                }
                $input[$key] = $value;
            }
            DB::table('trip_settings')->insert($input);
            $input = [];
        }
    }
}
