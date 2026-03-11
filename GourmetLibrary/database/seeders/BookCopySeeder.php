<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\BookCopy;
use Illuminate\Database\Seeder;

class BookCopySeeder extends Seeder
{
    public function run(): void
    {
        $books = Book::all();

        foreach ($books as $book) {
            for ($i = 1; $i <= $book->total_copies; $i++) {
                $copyNumber = 'COPY-' . str_pad($i, 3, '0', STR_PAD_LEFT);

                // Distribute conditions realistically across copies
                // First copies are always good; last ones might be degraded
                $condition = match (true) {
                    $i === $book->total_copies && $book->total_copies >= 3 && rand(0, 1) => 'degraded',
                    $i === $book->total_copies && $book->total_copies >= 4 && rand(0, 2) === 0 => 'damaged',
                    default => 'good',
                };

                $conditionNotes = match ($condition) {
                    'degraded' => 'Pages légèrement tachées, reliure usée mais lisible.',
                    'damaged'  => 'Reliure très abîmée, plusieurs pages déchirées. À réparer ou remplacer.',
                    default    => null,
                };

                BookCopy::firstOrCreate(
                    ['book_id' => $book->id, 'copy_number' => $copyNumber],
                    [
                        'condition'       => $condition,
                        'condition_notes' => $conditionNotes,
                        'is_available'    => true, // will be updated by BorrowSeeder
                    ]
                );
            }
        }
    }
}
