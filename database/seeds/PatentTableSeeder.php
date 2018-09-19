<?php

use Illuminate\Database\Seeder;

class PatentTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('patents')->insert([
            'id' => 1,
            'user_id' => 3,
            'patent_number' => 'US8765432',
            'patent_name' => 'Patent 1',
            'patent_count_page' => 20,
            'patent_count_column' => 2,
            'patent_number_sys_id' => '07be0de8-d606-466b-9efc-084aeefb3570',
        ]);

        DB::table('patents')->insert([
            'id' => 2,
            'user_id' => 2,
            'patent_number' => 'US8765433',
            'patent_name' => 'Patent 2',
            'patent_count_page' => 20,
            'patent_count_column' => 2,
            'patent_number_sys_id' => '09e999dc-35d5-47b3-9b9c-2b5a05006bf8',
        ]);

        DB::table('patents')->insert([
            'id' => 3,
            'user_id' => 2,
            'patent_number' => 'US8765434',
            'patent_name' => 'Patent 3',
            'patent_count_page' => 20,
            'patent_count_column' => 2,
            'patent_number_sys_id' => '0bfbb78f-e276-407c-a217-3048ade30a8e',
        ]);

        DB::table('patents')->insert([
            'id' => 4,
            'user_id' => 5,
            'patent_number' => 'US8765435',
            'patent_name' => 'Patent 4',
            'patent_count_page' => 20,
            'patent_count_column' => 2,
            'patent_number_sys_id' => '0ce4b66d-a31a-4716-840c-f26b58cc249f',
        ]);
    }
}
