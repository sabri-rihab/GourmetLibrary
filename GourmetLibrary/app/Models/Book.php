<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'title',
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
