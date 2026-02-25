<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $defaultPassword = 'password123'; // ganti kalau mau

        $users = [
            [
                'name' => 'Student Demo',
                'email' => 'student@example.com',
                'role' => UserRole::Student,
            ],
            [
                'name' => 'Teacher Demo',
                'email' => 'teacher@example.com',
                'role' => UserRole::Teacher,
            ],
            [
                'name' => 'Admin Demo',
                'email' => 'admin@example.com',
                'role' => UserRole::Admin,
            ],
            [
                'name' => 'Developer Demo',
                'email' => 'dev@example.com',
                'role' => UserRole::Developer,
            ],
        ];

        foreach ($users as $u) {
            User::updateOrCreate(
                ['email' => $u['email']],
                [
                    'name' => $u['name'],
                    'password' => Hash::make($defaultPassword),
                    'role' => $u['role'],
                    'email_verified_at' => now(), // biar nggak ketahan verify email saat dev
                    // opsional:
                    // 'avatar' => 'https://i.pravatar.cc/150?u='.$u['email'],
                ]
            );
        }
    }
}
