<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * Order matters: Users → Categories → Books → BookCopies → Borrows
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CategorySeeder::class,
            BookSeeder::class,
            BookCopySeeder::class,
            BorrowSeeder::class,
        ]);
    }
}
