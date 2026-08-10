<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = [
        'name'=> 'emilie',
        'email'=> 'ngwambem@gmail.cm',
        'password'=> bcrypt("12345t"),
        'telephone'=> '124567899',
        'departement'=> 'Devops',
        'statut'=> 'actif',
        ];
        User::create($user);
    }
}
