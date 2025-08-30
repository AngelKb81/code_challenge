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
        // ==========================================
        // UTENTI DI TEST - Password: "password"
        // ==========================================

        // Crea un utente admin
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Crea un utente normale
        User::create([
            'name' => 'Normal User',
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);

        // Crea altri utenti di esempio con password "password"
        $users = [
            ['name' => 'Marco Rossi', 'email' => 'marco.rossi@example.com'],
            ['name' => 'Giulia Bianchi', 'email' => 'giulia.bianchi@example.com'],
            ['name' => 'Luca Ferrari', 'email' => 'luca.ferrari@example.com'],
            ['name' => 'Sara Romano', 'email' => 'sara.romano@example.com'],
            ['name' => 'Andrea Russo', 'email' => 'andrea.russo@example.com'],
            ['name' => 'Chiara Marino', 'email' => 'chiara.marino@example.com'],
            ['name' => 'Federico Greco', 'email' => 'federico.greco@example.com'],
            ['name' => 'Elena Bruno', 'email' => 'elena.bruno@example.com'],
            ['name' => 'Matteo Gallo', 'email' => 'matteo.gallo@example.com'],
            ['name' => 'Francesca Villa', 'email' => 'francesca.villa@example.com'],
        ];

        foreach ($users as $userData) {
            User::create([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => Hash::make('password'), // Tutti con password "password"
                'role' => 'user',
            ]);
        }
    }
}
