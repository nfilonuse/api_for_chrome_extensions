<?php

use Illuminate\Database\Seeder;

class CompaniesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('companies')->insert([
            'id' => 1,
            'name' => 'AmconSoft, Inc',
            'description' => 'Main company of system',
            'logo_path' => '',
        ]);

        DB::table('companies')->insert([
            'id' => 2,
            'name' => 'Test Company 1',
            'description' => 'Test Company 1 of system',
            'logo_path' => '',
        ]);

        DB::table('companies')->insert([
            'id' => 3,
            'name' => 'Test Company 2',
            'description' => 'Test Company 2 of system',
            'logo_path' => '',
        ]);
    }
}
