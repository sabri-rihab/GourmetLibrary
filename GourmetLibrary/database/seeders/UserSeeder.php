<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // ── Admin (Chef Bibliothécaire) ─────────────────────────────────────────
        User::firstOrCreate(
            ['email' => 'admin@gourmetlibrary.fr'],
            [
                'name'     => 'Chef Bibliothécaire',
                'password' => Hash::make('password'),
                'role'     => 'admin',
            ]
        );

        // ── Gourmands (lecteurs) ─────────────────────────────────────────────────
        $gourmands = [
            ['name' => 'Alice Dupont',     'email' => 'alice@example.fr'],
            ['name' => 'Bruno Lefevre',    'email' => 'bruno@example.fr'],
            ['name' => 'Claire Martin',    'email' => 'claire@example.fr'],
            ['name' => 'David Moreau',     'email' => 'david@example.fr'],
            ['name' => 'Emma Bernard',     'email' => 'emma@example.fr'],
            ['name' => 'Fabien Rousseau',  'email' => 'fabien@example.fr'],
            ['name' => 'Gabrielle Simon',  'email' => 'gabrielle@example.fr'],
            ['name' => 'Hugo Laurent',     'email' => 'hugo@example.fr'],
        ];

        foreach ($gourmands as $gourmand) {
            User::firstOrCreate(
                ['email' => $gourmand['email']],
                [
                    'name'     => $gourmand['name'],
                    'password' => Hash::make('password'),
                    'role'     => 'gourmand',
                ]
            );
        }
    }
}
