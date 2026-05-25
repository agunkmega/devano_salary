<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@example.com',
                'password' => bcrypt('password'),
                'role' => User::ROLE_SUPER_ADMIN,
                'is_active' => true,
            ],
            [
                'name' => 'HR User',
                'email' => 'hr@example.com',
                'password' => bcrypt('password'),
                'role' => User::ROLE_HR,
                'is_active' => true,
            ],
            [
                'name' => 'Manager User',
                'email' => 'manager@example.com',
                'password' => bcrypt('password'),
                'role' => User::ROLE_MANAGER,
                'is_active' => true,
            ],
            [
                'name' => 'Staff User',
                'email' => 'staff@example.com',
                'password' => bcrypt('password'),
                'role' => User::ROLE_STAFF,
                'is_active' => true,
            ],
        ];

        foreach ($users as $userData) {
            User::firstOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }
    }
}
