<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create superadmin user
        User::create([
            'name' => 'Super Administrator',
            'email' => 'superadmin@example.com',
            'password' => Hash::make('password'),
            'role' => 'superadmin',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Create admin user
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Create BSP user
        User::create([
            'name' => 'BSP User',
            'email' => 'bsp@example.com',
            'password' => Hash::make('password'),
            'role' => 'bsp',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Create DAU user
        User::create([
            'name' => 'DAU User',
            'email' => 'dau@example.com',
            'password' => Hash::make('password'),
            'role' => 'dau',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Create Unit Kerja user
        User::create([
            'name' => 'Unit Kerja User',
            'email' => 'unitkerja@example.com',
            'password' => Hash::make('password'),
            'role' => 'unit_kerja',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Create KKF user
        User::create([
            'name' => 'KKF User',
            'email' => 'kkf@example.com',
            'password' => Hash::make('password'),
            'role' => 'kkf',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Create additional test users
        User::factory(10)->create();
    }
}