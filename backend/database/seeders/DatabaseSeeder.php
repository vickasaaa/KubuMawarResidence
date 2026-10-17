<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        User::updateOrCreate(
            ['email' => 'admin@kubumawar.com'],
            [
                'name' => 'Admin Kubu Mawar',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
            ]
        );

        // Create a test user
        User::updateOrCreate(
            ['email' => 'user@kubumawar.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('user1234'),
                'role' => 'user',
            ]
        );
    }
}
