<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ===== Create Admin User =====
        $adminExists = User::where('email', 'admin@easypickup.com')->exists();
        
        if (!$adminExists) {
            User::create([
                'name' => 'Admin User',
                'id_num' => '100000000000',
                'email' => 'admin@easypickup.com',
                'password' => Hash::make('Admin@123'),
                'age' => 30,
                'gender' => 'Male',
                'address' => '123 Admin St, City',
                'role' => 'admin'
            ]);
            $this->command->info('Admin user created successfully!');
            $this->command->info('Email: admin@easypickup.com');
            $this->command->info('Password: Admin@123');
        } else {
            $this->command->info('Admin user already exists. Skipping...');
        }

        // ===== Create User Two =====
        $user2Exists = User::where('email', 'user2@example.com')->exists();
        if (!$user2Exists) {
            User::create([
                'name' => 'User Two',
                'id_num' => '222222222222',
                'email' => 'user2@example.com',
                'password' => Hash::make('password123'),
                'age' => 30,
                'gender' => 'Male',
                'address' => '123 Main St, City',
                'role' => 'admin'
            ]);
        }

        // ===== Create User Three =====
        $user3Exists = User::where('email', 'user3@example.com')->exists();
        if (!$user3Exists) {
            User::create([
                'name' => 'User Three',
                'id_num' => '333333333333',
                'email' => 'user3@example.com',
                'password' => Hash::make('password123'),
                'age' => 25,
                'gender' => 'Female',
                'address' => '456 Oak Ave, Town',
                'role' => 'user'
            ]);
        }

        // ===== Create Manager One =====
        $managerExists = User::where('email', 'manager1@example.com')->exists();
        if (!$managerExists) {
            User::create([
                'name' => 'Manager One',
                'id_num' => '111111111111',
                'email' => 'manager1@example.com',
                'password' => Hash::make('password123'),
                'age' => 35,
                'gender' => 'Male',
                'address' => '789 Pine Rd, Village',
                'role' => 'manager'
            ]);
        }

        // ===== Create John Doe =====
        $johnExists = User::where('email', 'john@example.com')->exists();
        if (!$johnExists) {
            User::create([
                'name' => 'John Doe',
                'id_num' => 'ID001',
                'email' => 'john@example.com',
                'password' => Hash::make('password123'),
                'age' => 30,
                'gender' => 'Male',
                'address' => '123 Main St, City',
                'role' => 'user'
            ]);
        }

        // ===== Create Jane Smith =====
        $janeExists = User::where('email', 'jane@example.com')->exists();
        if (!$janeExists) {
            User::create([
                'name' => 'Jane Smith',
                'id_num' => 'ID002',
                'email' => 'jane@example.com',
                'password' => Hash::make('password123'),
                'age' => 25,
                'gender' => 'Female',
                'address' => '456 Oak Ave, Town',
                'role' => 'user'
            ]);
        }

        // ===== Create Bob Johnson =====
        $bobExists = User::where('email', 'bob@example.com')->exists();
        if (!$bobExists) {
            User::create([
                'name' => 'Bob Johnson',
                'id_num' => 'ID003',
                'email' => 'bob@example.com',
                'password' => Hash::make('password123'),
                'age' => 35,
                'gender' => 'Male',
                'address' => '789 Pine Rd, Village',
                'role' => 'manager'
            ]);
        }

        $this->command->info('All users seeded successfully!');
    }
}