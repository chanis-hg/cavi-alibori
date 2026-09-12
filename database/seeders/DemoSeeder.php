<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        // Compte admin pour le panel Filament
        User::firstOrCreate(
            ['email' => 'admin@cavi.bj'],
            [
                'name' => 'Admin ATDA',
                'password' => Hash::make('cavi2026'),
                'role' => 'agronome_atda',
            ]
        );

        User::firstOrCreate(
            ['email' => 'dev@cavi.bj'],
            [
                'name' => 'Dev CAVI',
                'password' => Hash::make('cavi2026'),
                'role' => 'dev',
            ]
        );
    }
}