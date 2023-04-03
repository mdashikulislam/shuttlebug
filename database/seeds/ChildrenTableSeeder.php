<?php

use Illuminate\Database\Seeder;

class ChildrenTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $imports = DB::connection('mysqlsbug4')->table('children')
            ->select('id','user_id','school_id','first_name','last_name','dob','gender','phone','friend','medical')
            ->orderBy('id')
            ->get();

        $inactive = DB::table('users')->where('status', 'inactive')->get()->pluck('id')->all();

        foreach ( $imports as $import ) {
            $input = (array) $import;
            if ( in_array($import->user_id, $inactive) ) {
                $input['status'] = 'inactive';
            }
            DB::table('children')->insert($input);
            $input = [];
        }
    }
}
