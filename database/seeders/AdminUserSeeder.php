<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if admin user already exists
        if (!User::where('email', 'admin@ikost.com')->exists()) {
            User::create([
                'name' => 'Administrator',
                'email' => 'admin@ikost.com',
                'phone' => '081234567890',
                'occupation' => 'System Administrator',
                'address' => 'Jl. Admin No. 1, Jakarta',
                'ktp_path' => null,
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'status' => 'active',
                'email_verified_at' => now(),
            ]);
        }
    }
}
