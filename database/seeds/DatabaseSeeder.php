<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(CountriesTableSeeder::class);
        $this->call(StatesTableSeeder::class);
        $this->call(States1TableSeeder::class);
        $this->call(States2TableSeeder::class);
        $this->call(States3TableSeeder::class);
        $this->call(CompaniesTableSeeder::class);
        $this->call(RolesTableSeeder::class);
        $this->call(UsersTableSeeder::class);
        $this->call(PatentTableSeeder::class);
        $this->call(CitationTableSeeder::class);
        $this->call(SubscriptionTableSeeder::class);
        $this->call(UserSubscriptionTableSeeder::class);
    }
}
