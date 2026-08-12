<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class usersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        users:: create([
            'name' => 'ori';
            'email' => 'ngwambem@gmail.com';
            'telephone' => '659189043';
            'email_verified_at' => now();
            'role' => 'dev';
            'department' => 'devops';
            'password'=> bcrypt('Louis@111')
            'status' => 'actif';

        ]);
    }
}
