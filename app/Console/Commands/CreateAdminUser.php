<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-admin-user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create admin user for the application';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Check if admin user already exists
        if (User::where('email', 'admin@ikost.com')->exists()) {
            $this->info('Admin user already exists!');
            return;
        }

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

        $this->info('Admin user created successfully!');
        $this->info('Email: admin@ikost.com');
        $this->info('Password: admin123');
    }
}
