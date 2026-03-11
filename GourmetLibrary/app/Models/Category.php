<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'color',
    ];

    // ─── Relationships ───────────────────────────────────────────────────────────

    /**
     * A category has many books.
     */
    public function books()
    {
        return $this->hasMany(Book::class);
    }

    // ─── Helpers ────────────────────────────────────────────────────────────────

    /**
     * Auto-generate slug from name if not provided.
     */
    protected static function booted(): void
    {
        static::creating(function (Category $category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }
}
