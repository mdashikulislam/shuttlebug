<?php

use Illuminate\Database\Seeder;

class SuburbsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
//        $users = DB::table('users')
//            ->where('suburb', '!=', 'n/a')
//            ->where('suburb', '>', '')
//            ->groupBy('suburb')
//            ->get()->pluck('city','suburb')->all();
//
//        $schools = DB::table('schools')
//            ->where('suburb', '!=', 'n/a')
//            ->where('suburb', '>', '')
//            ->groupBy('suburb')
//            ->get()->pluck('city','suburb')->all();
//
//        $xmurals = DB::table('xmurals')
//            ->where('suburb', '!=', 'n/a')
//            ->where('suburb', '>', '')
//            ->groupBy('suburb')
//            ->get()->pluck('city','suburb')->all();
//
//        $collection = collect($users)->merge($schools)->merge($xmurals);
//        $suburbs = $collection->toArray();
//        ksort($suburbs);
//
//        foreach ( $suburbs as $suburb => $city ) {
//            $input['name'] = $suburb;
//            $input['city'] = $city;
//            DB::table('suburbs')->insert($input);
//            $input = [];
//        }
    }
}
