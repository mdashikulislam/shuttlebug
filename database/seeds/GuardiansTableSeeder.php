<?php

use Illuminate\Database\Seeder;

class GuardiansTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $imports = DB::connection('mysqlsbug4')->table('guardians')
            ->select('id','user_id','first_name','last_name','relation','phone','role')
            ->orderBy('id')
            ->get();

        foreach ( $imports as $import ) {
            $input = (array) $import;
            DB::table('guardians')->insert($input);
            $input = [];
        }
    }
}
