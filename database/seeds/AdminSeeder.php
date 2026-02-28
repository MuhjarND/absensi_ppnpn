<?php

use Illuminate\Database\Seeder;
use App\User;
use App\Role;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run()
    {
        $adminRole = Role::where('name', 'admin')->first();

        User::firstOrCreate(
            ['email' => 'admin@absensi.com'],
            [
                'name' => 'Administrator',
                'email' => 'admin@absensi.com',
                'password' => Hash::make('password123'),
                'role_id' => $adminRole->id,
                'nip' => '000000000',
                'position' => 'Administrator Sistem',
                'is_active' => true,
            ]
        );
    }
}
