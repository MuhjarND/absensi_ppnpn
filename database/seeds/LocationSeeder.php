<?php

use Illuminate\Database\Seeder;
use App\Location;

class LocationSeeder extends Seeder
{
    public function run()
    {
        Location::firstOrCreate(
            ['name' => 'Kantor Pusat'],
            [
                'name' => 'Kantor Pusat',
                'address' => 'Jl. Contoh No. 1, Jakarta',
                'latitude' => -6.20000000,
                'longitude' => 106.84500000,
                'radius' => 100,
                'is_active' => true,
            ]
        );
    }
}
