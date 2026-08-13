<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Ori',
            'email' => 'ngwambem@gmail.com',
            'telephone' => '659189043',
            'email_verified_at' => now(),
            'role' => 'dev',
            'department' => 'devops',
            'password' => Hash::make('Louis@111'),
            'status' => 'actif',
        ]);
    }
}