<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    public function run(): void
    {
        // Диспетчеры
        User::create([
            'name' => 'Диспетчер 1',
            'email' => 'dispatcher1@example.com',
            'password' => Hash::make('password'),
            'role' => 'dispatcher',
        ]);
        User::create([
            'name' => 'Диспетчер 2',
            'email' => 'dispatcher2@example.com',
            'password' => Hash::make('password'),
            'role' => 'dispatcher',
        ]);

        // Мастера
        User::create([
            'name' => 'Мастер 1',
            'email' => 'master1@example.com',
            'password' => Hash::make('password'),
            'role' => 'master',
        ]);
        User::create([
            'name' => 'Мастер 2',
            'email' => 'master2@example.com',
            'password' => Hash::make('password'),
            'role' => 'master',
        ]);
    }
}
