<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class UserSubscriptionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker\Factory::create();        
        for ($i=0;$i<=1;$i++)
        {
            for ($user_id=2;$user_id<=5;$user_id++)
            {
                if ($i==0)
                    $trial_ends_at=Carbon::now()->addDays(-3)->format('Y-m-d H:i:s');
                else
                    $trial_ends_at=null;
                $ends_at=Carbon::now()->addDays($i*60)->format('Y-m-d H:i:s');
                DB::table('user_subscriptions')->insert([
                    'user_id' => $user_id,
                    'name' => 'name of subscription',
                    'stripe_id' => rand(1,150),
                    'stripe_plan' => 'subscription plan 3 month',
                    'quantity' => 1,
                    'trial_ends_at' => $trial_ends_at,
                    'ends_at' => $ends_at,
                    'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                    'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                ]);
            }
        }
    }
}
