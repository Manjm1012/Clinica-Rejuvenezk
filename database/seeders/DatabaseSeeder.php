<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $client = Client::firstOrCreate(
            ['slug' => 'clinica-rejuvenezk'],
            [
                'name' => 'Clinica Rejuvenezk',
                'contact_email' => 'contacto@rejuvenezk.com',
                'is_active' => true,
            ]
        );

        $admin = User::firstOrCreate(
            ['email' => 'admin@rejuvenezk.com'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('password'),
                'role' => 'superadmin',
                'email_verified_at' => now(),
            ]
        );

        $doctorUser = User::firstOrCreate(
            ['email' => 'medico@rejuvenezk.com'],
            [
                'client_id' => $client->id,
                'name' => 'Dr. Demo',
                'password' => Hash::make('password'),
                'role' => 'doctor',
                'email_verified_at' => now(),
            ]
        );

        Doctor::firstOrCreate(
            ['user_id' => $doctorUser->id],
            [
                'client_id' => $client->id,
                'full_name' => 'Dr. Demo Rejuvenezk',
                'specialty' => 'Medicina Estética',
                'is_active' => true,
            ]
        );
    }
}

