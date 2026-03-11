<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookCopy extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id',
        'copy_number',
        'condition',
        'condition_notes',
        'is_available',
    ];

    protected $casts = [
        'is_available' => 'boolean',
    ];

    // ─── Relationships ───────────────────────────────────────────────────────────

    /**
     * A copy belongs to a book.
     */
    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * This copy has many borrow records (history).
     */
    public function borrows()
    {
        return $this->hasMany(Borrow::class);
    }

    /**
     * The active borrow for this copy (if any).
     */
    public function activeBorrow()
    {
        return $this->hasOne(Borrow::class)->where('status', 'active');
    }

    // ─── Helpers ────────────────────────────────────────────────────────────────

    public function isDegraded(): bool
    {
        return in_array($this->condition, ['degraded', 'damaged']);
    }
}
