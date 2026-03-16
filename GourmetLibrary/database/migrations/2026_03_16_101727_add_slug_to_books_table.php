<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Add a unique slug column to the books table.
     * The slug is generated from the book title and is used to build
     * human-readable URLs like: /cuisine-italienne/livres/les-meilleures-recettes-de-pates
     */
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            // Place the slug right after the title column
            $table->string('slug')->nullable()->unique()->after('title');
        });

        // Backfill slugs for any books that already exist in the database
        \App\Models\Book::all()->each(function (\App\Models\Book $book) {
            $base = Str::slug($book->title);
            $slug = $base;
            $i    = 1;
            // Ensure uniqueness: append -2, -3, ... if the slug already exists
            while (\App\Models\Book::where('slug', $slug)->where('id', '!=', $book->id)->exists()) {
                $slug = "{$base}-{$i}";
                $i++;
            }
            $book->updateQuietly(['slug' => $slug]);
        });

        // Now make the column non-nullable now that all rows are filled
        Schema::table('books', function (Blueprint $table) {
            $table->string('slug')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
