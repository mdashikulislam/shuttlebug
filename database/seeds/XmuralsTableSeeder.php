<?php

use Illuminate\Database\Seeder;

class XmuralsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $imports = DB::connection('mysqlsbug4')->table('xmurals')
            ->select('id','activity','venue','address','view', DB::raw('asText(geoloc) as geoloc'))
            ->orderBy('id')
            ->get();

        foreach ( $imports as $import ) {
            foreach ( $import as $key => $value ) {
                // move address to new columns
                if ( $key == 'address' ) {
                    if ( strlen($value) > 15 ) {
                        $address = explode(',', $value);
                        $input['city'] = array_pop($address);
                        $input['suburb'] = array_pop($address);
                        $input['street'] = array_pop($address);
                        $input['unit'] = count($address) > 0 ? array_pop($address) : '';
                        if ( $input['street'] == '' ) {
                            $input['street'] = 'n/a';
                        }
                    } else {
                        $input['city'] = 'n/a';
                        $input['suburb'] = 'n/a';
                        $input['street'] = 'n/a';
                        $input['unit'] = 'n/a';
                    }
                    continue;
                }
                // change spatial geoloc to string geo
                if ( $key == 'geoloc' ) {
                    if ( !is_null($value) ) {
                        $text = preg_replace('/[^0-9-\s,.]/', '', trim($value));
                        $geo = preg_replace('/\s/', ',', $text);
                        $lat = substr($geo, 0, strpos($geo, ','));
                        $lat = substr($lat, 0, 10);
                        $lon = substr($geo, strpos($geo, ','));
                        $lon = substr($lon, 0, 10);
                        $input['geo'] = $lat . $lon;
                    }
                    continue;
                }
                $input[$key] = $value;
            }
            DB::table('xmurals')->insert($input);
            $input = [];
        }
    }
}
