<?php

use Illuminate\Database\Seeder;

class EventBookingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $imports = DB::connection('mysqlsbug4')->table('event_bookings')
            ->select('id','user_id','date','puloc','putime','doloc','dotime','passengers','tripfee','vehicle','driver',DB::raw('asText(pugeo) as pugeo'),DB::raw('asText(dogeo) as dogeo'),'created_at','updated_at')
            ->where('date', '>', '2018-03-31')  // ignore for recovery
            ->orderBy('id')
            ->get();

        foreach ( $imports as $import ) {
            foreach ( $import as $key => $value ) {
                // change spatial geos to string geos
                if ( $key == 'pugeo' ) {
                    if ( !is_null($value) ) {
                        $text = preg_replace('/[^0-9-\s,.]/', '', trim($value));
                        $geo = preg_replace('/\s/', ',', $text);
                        $lat = substr($geo,0,strpos($geo,','));
                        $lat = substr($lat,0,11);
                        $lon = substr($geo,strpos($geo,','));
                        $lon = substr($lon,0,11);
                        $input['pugeo'] = $lat.$lon;
                    }
                    continue;
                }
                if ( $key == 'dogeo' ) {
                    if ( !is_null($value) ) {
                        $text = preg_replace('/[^0-9-\s,.]/', '', trim($value));
                        $geo = preg_replace('/\s/', ',', $text);
                        $lat = substr($geo,0,strpos($geo,','));
                        $lat = substr($lat,0,11);
                        $lon = substr($geo,strpos($geo,','));
                        $lon = substr($lon,0,11);
                        $input['dogeo'] = $lat.$lon;
                    }
                    continue;
                }
                $input[$key] = $value;
            }
            DB::table('event_bookings')->insert($input);
            $input = [];
        }
    }
}
