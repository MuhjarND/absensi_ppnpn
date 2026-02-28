<?php

use Illuminate\Database\Seeder;
use App\Shift;

class ShiftSeeder extends Seeder
{
    public function run()
    {
        $shifts = [
            [
                'name' => 'Reguler',
                'start_time' => '08:00:00',
                'end_time' => '15:00:00',
                'is_overnight' => false,
            ],
            [
                'name' => 'Security Pagi',
                'start_time' => '06:00:00',
                'end_time' => '14:00:00',
                'is_overnight' => false,
            ],
            [
                'name' => 'Security Siang',
                'start_time' => '14:00:00',
                'end_time' => '22:00:00',
                'is_overnight' => false,
            ],
            [
                'name' => 'Security Malam',
                'start_time' => '22:00:00',
                'end_time' => '06:00:00',
                'is_overnight' => true,
            ],
        ];

        foreach ($shifts as $shift) {
            Shift::firstOrCreate(['name' => $shift['name']], $shift);
        }
    }
}
