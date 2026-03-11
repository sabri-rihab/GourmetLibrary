<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Borrow extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'book_copy_id',
        'borrowed_at',
        'due_date',
        'returned_at',
        'status',
        'return_notes',
    ];

    protected $casts = [
        'borrowed_at' => 'date',
        'due_date'    => 'date',
        'returned_at' => 'date',
    ];

    // ─── Relationships ───────────────────────────────────────────────────────────

    /**
     * The gourmand who borrowed.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The specific physical copy borrowed.
     */
    public function bookCopy()
    {
        return $this->belongsTo(BookCopy::class);
    }

    /**
     * Shortcut to the book through its copy.
     */
    public function book()
    {
        return $this->hasOneThrough(Book::class, BookCopy::class, 'id', 'id', 'book_copy_id', 'book_id');
    }

    // ─── Helpers ────────────────────────────────────────────────────────────────

    public function isOverdue(): bool
    {
        return $this->status === 'active' && $this->due_date->isPast();
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
