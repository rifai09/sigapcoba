<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin Demo',
                'email' => 'admin@demo.test',
                'password' => Hash::make('123456789'),
                'role' => 'admin',
            ],
            [
                'name' => 'Staf Demo',
                'email' => 'staf@demo.test',
                'password' => Hash::make('123456789'),
                'role' => 'staf',
            ],
            [
                'name' => 'Kepala Unit Demo',
                'email' => 'kepunit@demo.test',
                'password' => Hash::make('123456789'),
                'role' => 'kepala_unit',
            ],
            [
                'name' => 'Katimker Demo',
                'email' => 'katimker@demo.test',
                'password' => Hash::make('123456789'),
                'role' => 'katimker',
            ],
            [
                'name' => 'Kabid Demo',
                'email' => 'kabid@demo.test',
                'password' => Hash::make('123456789'),
                'role' => 'kabid',
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']], 
                $user 
            );
        }
    }
}