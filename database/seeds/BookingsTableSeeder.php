<?php

use Illuminate\Database\Seeder;

class BookingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $imports = DB::connection('mysqlsbug4')->table('bookings')
            ->select('id','user_id','passenger_id','date','puloc','putime','doloc','dotime','price','vehicle','driver','created_at','updated_at')
            ->where('date', '>', '2018-03-31')  // ignore for recovery
            ->where('date', '<', '2018-07-17')  // ignore for recovery
            ->orderBy('id')
            ->get();

        foreach ( $imports as $import ) {
            foreach ( $import as $key => $value ) {
                // change puloc to puloc_id & add type
                if ( $key == 'puloc' ) {
                    if ( $value == 'home' ) {
                        $input['puloc_id'] = $import->user_id;
                        $input['puloc_type'] = 'user';
                    } elseif ( $value > 700000 ) {
                        $input['puloc_id'] = $value;
                        $input['puloc_type'] = 'school';
                    } else {
                        $input['puloc_id'] = $value;
                        $input['puloc_type'] = 'xmural';
                    }
                    continue;
                }

                // change doloc to doloc_id & add type
                if ( $key == 'doloc' ) {
                    if ( $value == 'home' ) {
                        $input['doloc_id'] = $import->user_id;
                        $input['doloc_type'] = 'user';
                    } elseif ( $value > 700000 ) {
                        $input['doloc_id'] = $value;
                        $input['doloc_type'] = 'school';
                    } else {
                        $input['doloc_id'] = $value;
                        $input['doloc_type'] = 'xmural';
                    }
                    continue;
                }
                $input[$key] = $value;
            }
            DB::table('bookings')->insert($input);
            $input = [];
        }
    }
}
