<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'title',
        'slug',
        'author',
        'isbn',
        'description',
        'cover_image',
        'published_year',
        'publisher',
        'language',
        'total_copies',
        'arrival_date',
    ];

    protected $casts = [
        'arrival_date'   => 'date',
        'published_year' => 'integer',
        'total_copies'   => 'integer',
    ];

    // ─── Slug auto-generation ────────────────────────────────────────────────────

    protected static function booted(): void
    {
        static::creating(function (Book $book) {
            if (empty($book->slug)) {
                $book->slug = static::generateUniqueSlug($book->title);
            }
        });

        static::updating(function (Book $book) {
            // Regenerate slug if the title changed and no explicit slug was given
            if ($book->isDirty('title') && ! $book->isDirty('slug')) {
                $book->slug = static::generateUniqueSlug($book->title, $book->id);
            }
        });
    }

    /**
     * Generate a URL-safe, unique slug from a title.
     * Appends -2, -3, ... if a collision is found.
     *
     * @param string   $title   Source text (e.g. "Les Meilleures Recettes de Pâtes")
     * @param int|null $exceptId Exclude this book ID from the uniqueness check (for updates)
     */
    public static function generateUniqueSlug(string $title, ?int $exceptId = null): string
    {
        $base = Str::slug($title);   // "les-meilleures-recettes-de-pates"
        $slug = $base;
        $i    = 1;

        while (
            static::where('slug', $slug)
                  ->when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))
                  ->exists()
        ) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }

    // ─── Relationships ───────────────────────────────────────────────────────────

    /**
     * A book belongs to a category.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * A book has many physical copies.
     */
    public function copies()
    {
        return $this->hasMany(BookCopy::class);
    }

    /**
     * Available copies (good condition and not borrowed).
     */
    public function availableCopies()
    {
        return $this->hasMany(BookCopy::class)->where('is_available', true)->whereIn('condition', ['good', 'degraded']);
    }

    /**
     * Degraded or damaged copies (for admin reporting).
     */
    public function degradedCopies()
    {
        return $this->hasMany(BookCopy::class)->whereIn('condition', ['degraded', 'damaged']);
    }

    /**
     * All borrows across all copies of this book.
     */
    public function borrows()
    {
        return $this->hasManyThrough(Borrow::class, BookCopy::class);
    }

    // ─── Helpers ────────────────────────────────────────────────────────────────

    /**
     * Total borrow count (popularity metric).
     */
    public function getTotalBorrowsAttribute(): int
    {
        return $this->borrows()->count();
    }

    /**
     * Is this book a new arrival? (arrived in the last 30 days)
     */
    public function getIsNewArrivalAttribute(): bool
    {
        return $this->arrival_date && $this->arrival_date->isAfter(now()->subDays(30));
    }
}
