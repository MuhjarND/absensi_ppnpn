<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            RoleSeeder::class,
            ShiftSeeder::class,
            LocationSeeder::class,
            AdminSeeder::class,
        ]);
    }
}
