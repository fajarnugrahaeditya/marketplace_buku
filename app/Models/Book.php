<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Book extends Model
{
    // author_id, slug, status, dan kolom tanggal sengaja diisi lewat kode yang eksplisit
    protected $fillable = ['title', 'synopsis', 'price', 'license_type', 'source_url', 'cover'];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'published_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(AuthorProfile::class, 'author_id', 'user_id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(BookFile::class);
    }

    public function fullFile(): HasOne
    {
        return $this->hasOne(BookFile::class)->where('type', 'full');
    }

    public function previewFile(): HasOne
    {
        return $this->hasOne(BookFile::class)->where('type', 'preview');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'book_category', 'book_id', 'category_id');
    }

    public function isFree(): bool
    {
        return $this->price === 0;
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'draft' => 'Draft',
            'pending' => 'Menunggu tinjau',
            'published' => 'Terbit',
            'rejected' => 'Ditolak',
            'taken_down' => 'Di-takedown',
            'archived' => 'Diarsipkan',
            default => $this->status,
        };
    }
}
