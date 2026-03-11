<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\BookCopy;
use App\Models\Borrow;
use App\Models\User;
use Illuminate\Database\Seeder;

class BorrowSeeder extends Seeder
{
    public function run(): void
    {
        $gourmands = User::where('role', 'gourmand')->get();
        $copies    = BookCopy::with('book')->where('condition', '!=', 'lost')->get();

        if ($gourmands->isEmpty() || $copies->isEmpty()) {
            return;
        }

        // ── Past borrows (returned) ──────────────────────────────────────────────
        // Create ~40 historical borrows spread across multiple users and books
        $returnedBorrowsData = [
            // [user_index, copy_index, borrowed_days_ago, borrow_duration_days]
            [0, 0, 180, 14], [1, 2, 170, 10], [2, 5, 160, 7],
            [3, 8, 155, 14], [0, 10, 150, 21], [1, 12, 140, 14],
            [2, 15, 130, 10], [3, 1, 125, 7],  [0, 3, 120, 14],
            [1, 6, 115, 21], [2, 9, 110, 14],  [3, 11, 100, 10],
            [0, 14, 90, 7],  [1, 16, 85, 14],  [2, 18, 80, 21],
            [3, 20, 75, 14], [0, 22, 70, 10],  [1, 24, 65, 7],
            [2, 2, 60, 14],  [3, 4, 55, 21],   [0, 7, 50, 14],
            [1, 13, 45, 10], [2, 19, 40, 7],   [3, 21, 35, 14],
            [0, 25, 30, 21], [1, 0, 28, 14],   [2, 3, 25, 10],
            [3, 6, 22, 7],   [0, 9, 20, 14],   [1, 15, 18, 21],
            // Popular book (index 13 = "5 Ingrédients Jamie Oliver") — borrowed many times
            [2, 13, 200, 14], [3, 13, 185, 10], [0, 13, 165, 14],
            [1, 13, 145, 7],  [2, 13, 100, 14], [3, 13, 75, 21],
            [0, 13, 50, 14],  [1, 13, 20, 7],
        ];

        foreach ($returnedBorrowsData as [$ui, $ci, $daysAgo, $duration]) {
            $user = $gourmands->get($ui % $gourmands->count());
            $copy = $copies->get($ci % $copies->count());

            if (!$user || !$copy) continue;

            $borrowedAt  = now()->subDays($daysAgo);
            $dueDate     = $borrowedAt->copy()->addDays($duration);
            $returnedAt  = $dueDate->isFuture() ? $dueDate : $dueDate->copy()->subDays(rand(0, 3));

            Borrow::firstOrCreate(
                [
                    'user_id'      => $user->id,
                    'book_copy_id' => $copy->id,
                    'borrowed_at'  => $borrowedAt->toDateString(),
                ],
                [
                    'due_date'    => $dueDate->toDateString(),
                    'returned_at' => $returnedAt->toDateString(),
                    'status'      => 'returned',
                ]
            );
        }

        // ── Active borrows (currently checked out — marks copy unavailable) ─────
        $activeBorrows = [
            [0, 1, 10, 14],
            [1, 4, 5, 21],
            [2, 7, 8, 14],
            [3, 11, 3, 7],
            [0, 17, 12, 21],
        ];

        foreach ($activeBorrows as [$ui, $ci, $daysAgo, $duration]) {
            $user = $gourmands->get($ui % $gourmands->count());
            $copy = $copies->get($ci % $copies->count());

            if (!$user || !$copy) continue;

            $borrowedAt = now()->subDays($daysAgo);
            $dueDate    = $borrowedAt->copy()->addDays($duration);

            $borrow = Borrow::firstOrCreate(
                [
                    'user_id'      => $user->id,
                    'book_copy_id' => $copy->id,
                    'borrowed_at'  => $borrowedAt->toDateString(),
                ],
                [
                    'due_date'    => $dueDate->toDateString(),
                    'returned_at' => null,
                    'status'      => $dueDate->isPast() ? 'overdue' : 'active',
                ]
            );

            // Mark the copy as unavailable
            $copy->update(['is_available' => false]);
        }

        // ── One overdue borrow ───────────────────────────────────────────────────
        $user = $gourmands->first();
        $copy = $copies->get(23 % $copies->count());
        if ($user && $copy && $copy->is_available) {
            Borrow::firstOrCreate(
                [
                    'user_id'      => $user->id,
                    'book_copy_id' => $copy->id,
                    'borrowed_at'  => now()->subDays(30)->toDateString(),
                ],
                [
                    'due_date'    => now()->subDays(16)->toDateString(),
                    'returned_at' => null,
                    'status'      => 'overdue',
                ]
            );
            $copy->update(['is_available' => false]);
        }
    }
}
