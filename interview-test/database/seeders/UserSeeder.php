<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         User::create([
            'name' => 'John Doe',
            'id_num' => 'ID001',
            'email' => 'john@example.com',
            'age' => 30,
            'gender' => 'Male',
            'address' => '123 Main St, City',
            'role' => 'admin'
        ]);
        User::create([
            'name' => 'Jane Smith',
            'id_num' => 'ID002',
            'email' => 'jane@example.com',
            'age' => 25,
            'gender' => 'Female',
            'address' => '456 Oak Ave, Town',
            'role' => 'user'
        ]);

        User::create([
            'name' => 'Bob Johnson',
            'id_num' => 'ID003',
            'email' => 'bob@example.com',
            'age' => 35,
            'gender' => 'Male',
            'address' => '789 Pine Rd, Village',
            'role' => 'manager'
        ]);
    }
}
