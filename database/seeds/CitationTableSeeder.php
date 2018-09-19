<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class CitationTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker\Factory::create();        
        for ($user_id=2;$user_id<=5;$user_id++)
        {
            for ($patent_id=2;$patent_id<5;$patent_id++)
            {
                for ($i=0;$i<10;$i++)
                {
                    $days=(-1)*rand(0,100);
                    $date=Carbon::now()->addDays($days)->format('Y-m-d H:i:s');
                    DB::table('citations')->insert([
                        'user_id' => $user_id,
                        'col_num' => rand(1,2),
                        'line_num' => rand(1,150),
                        'patent_id' => $patent_id,
                        'search_text' => $faker->text(),
                        'status' => rand(1,4),
                        'created_at' => $date,
                        'updated_at' => $date,
                    ]);
                }
            }
        }
    }
}
