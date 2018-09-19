<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class SubscriptionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker\Factory::create();        
        DB::table('subscriptions')->insert([
            'id' => 1,
            'name' => 'Subscription 1 month',
            'price' => 5,
            'price_vat' => 1,
            'trial_days' => 3,
            'ends_days' => 30,
            'stripe_plan' => 's_monly',
        ]);

        DB::table('subscriptions')->insert([
            'id' => 2,
            'name' => 'Subscription 3 month',
            'price' => 15,
            'price_vat' => 1,
            'trial_days' => 3,
            'ends_days' => 90,
            'stripe_plan' => 's_3month',
        ]);

        DB::table('subscriptions')->insert([
            'id' => 3,
            'name' => 'Subscription 6 month',
            'price' => 25,
            'price_vat' => 3,
            'trial_days' => 3,
            'ends_days' => 180,
            'stripe_plan' => 's_6month',
        ]);

        DB::table('subscriptions')->insert([
            'id' => 4,
            'name' => 'Subscription 1 year',
            'price' => 50,
            'price_vat' => 3,
            'trial_days' => 3,
            'ends_days' => 365,
            'stripe_plan' => 's_yearly',
        ]);

    }
}
