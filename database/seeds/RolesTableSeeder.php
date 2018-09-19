<?php

use Illuminate\Database\Seeder;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('roles')->insert([
            'id' => 957,
            'name' => 'superadmin',
        ]);

        DB::table('roles')->insert([
            'id' => 100,
            'name' => 'companyadmin',
        ]);

        DB::table('roles')->insert([
            'id' => 200,
            'name' => 'companyuser',
        ]);
    }
}
