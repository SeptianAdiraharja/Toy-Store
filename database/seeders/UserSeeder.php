<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Owner Account
        User::updateOrCreate(
            ['username' => 'owner'],
            [
                'nama' => 'H. Faisal (Owner)',
                'email' => 'owner@serba123.com',
                'password' => Hash::make('password'),
                'role' => 'owner',
            ]
        );

        // Admin Account
        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'nama' => 'Admin Store',
                'email' => 'admin@serba123.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        // Kasir Account
        User::updateOrCreate(
            ['username' => 'kasir'],
            [
                'nama' => 'Siti Nurhaliza (Kasir)',
                'email' => 'baca',
                'password' => Hash::make('password'),
                'role' => 'kasir',
            ]
        );
    }
}
