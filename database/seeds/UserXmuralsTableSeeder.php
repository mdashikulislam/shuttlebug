<?php

use Illuminate\Database\Seeder;

class UserXmuralsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $imports = DB::connection('mysqlsbug4')->table('user_xmural')
            ->select('id','user_id','xmural_id')
            ->orderBy('id')
            ->get();

        foreach ( $imports as $import ) {
            $input = (array) $import;
            DB::table('user_xmurals')->insert($input);
            $input = [];
        }
    }
}
