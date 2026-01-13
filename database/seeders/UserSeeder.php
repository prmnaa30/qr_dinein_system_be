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
        User::create([
            'name' => 'Super Admin',
            'email' => 'admin@gmail.com',
            'username' => 'myadmin123',
            'password' => Hash::make('password'),
            'role' => 'admin'
        ]);

        User::create([
            'name' => 'My Kitchen',
            'email' => 'mykitchen@gmail.com',
            'username' => 'mykitchen123',
            'password' => Hash::make('password'),
            'role' => 'kitchen'
        ]);

        User::create([
            'name' => 'My Cashier',
            'email' => 'mycashier@gmail.com',
            'username' => 'mycashier123',
            'password' => Hash::make('password'),
            'role' => 'cashier'
        ]);
    }
}
