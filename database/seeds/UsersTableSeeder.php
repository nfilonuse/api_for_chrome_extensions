<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $trial_ends_at=Carbon::now()->addDays(3)->format('Y-m-d H:i:s');
        DB::table('users')->insert([
            'id' => 1,
            'email' => 'superadmin@flip.taxi',
            'password' => bcrypt('superadmin'),
            'company_id' => 1,
            'is_active' => 1,
            'role_id' => 957,
        ]);

        DB::table('users')->insert([
            'id' => 2,
            'email' => 'admincompany1@flip.taxi',
            'password' => bcrypt('admincompany1'),
            'company_id' => 2,
            'card_brand' => 'Visa',
            'card_last_four' => '4242',
            'trial_ends_at' => $trial_ends_at,
            'stripe_id' => rand(1000,9999),
            'is_active' => 1,
            'role_id' => 100,
        ]);

        DB::table('users')->insert([
            'id' => 3,
            'email' => 'usercompany1@flip.taxi',
            'password' => bcrypt('usercompany1'),
            'company_id' => 2,
            'card_brand' => 'Visa',
            'card_last_four' => '4243',
            'trial_ends_at' => $trial_ends_at,
            'stripe_id' => rand(1000,9999),
            'is_active' => 1,
            'role_id' => 200,
        ]);

        DB::table('users')->insert([
            'id' => 4,
            'email' => 'admincompany2@flip.taxi',
            'password' => bcrypt('admincompany2'),
            'company_id' => 3,
            'card_brand' => 'Visa',
            'card_last_four' => '4244',
            'trial_ends_at' => $trial_ends_at,
            'stripe_id' => rand(1000,9999),
            'is_active' => 1,
            'role_id' => 100,
        ]);

        DB::table('users')->insert([
            'id' => 5,
            'email' => 'usercompany2@flip.taxi',
            'password' => bcrypt('usercompany2'),
            'company_id' => 3,
            'card_brand' => 'Visa',
            'card_last_four' => '4245',
            'trial_ends_at' => $trial_ends_at,
            'stripe_id' => rand(1000,9999),
            'is_active' => 1,
            'role_id' => 200,
        ]);
    }
}
