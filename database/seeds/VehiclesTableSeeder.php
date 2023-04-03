<?php

use Illuminate\Database\Seeder;

class VehiclesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $imports = DB::connection('mysqlsbug4')->table('vehicles')
            ->select('id','model','reg','seats','licence','status',DB::raw('asText(geoloc) as geoloc'))
            ->orderBy('id')
            ->get();

        foreach ( $imports as $import ) {
            foreach ( $import as $key => $value ) {
                // change spatial geoloc to string geo
                if ( $key == 'geoloc' ) {
                    if ( $import->model == 'Verso' ) {
                        $input['geo'] = '-34.017509,18.367309';
                    } elseif ( $import->model == 'Innova' ) {
                        $input['geo'] = '-34.041899,18.352533';
                    } else {
                        $input['geo'] = '-34.043456,18.357016';
                    }
                    continue;
                }
                // add driver_id
                if ( $key == 'id' ) {
                    $input['id'] = $value;
                    if ( $value == 105 ) {
                        $input['driver_id'] = 1005;
                    } elseif ( $value == 103 ) {
                        $input['driver_id'] = 1005;
                    } elseif ( $value == 102 ) {
                        $input['driver_id'] = 1002;
                    }
                    continue;
                }
                $input[$key] = $value;
            }
            DB::table('vehicles')->insert($input);
            $input = [];
        }
    }
}
