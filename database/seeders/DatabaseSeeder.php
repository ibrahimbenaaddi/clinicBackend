<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()
            ->count(20)
            ->state([
                'role' => 'doctor',
            ])
            ->hasDoctor()
            ->create();

        User::factory()
            ->count(20)
            ->state([
                'role' => 'patient',
            ])
            ->hasPatient()
            ->create();

        User::create([
            'firstname' => 'admin',
            'lastname' => 'admin',
            'email' => 'admin@email.com',
            'role' => 'admin',
            'password' => Hash::make('password1234'),
        ])->admin()->create();
    }
}
