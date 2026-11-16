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

    /**
     * Alasan buku ini tidak bisa dimasukkan ke keranjang atau dipesan oleh pengguna tertentu.
     * Mengembalikan null kalau boleh dibeli.
     */
    public function purchaseBlockReason(User $user): ?string
    {
        if ($this->status !== 'published') {
            return 'Buku ini sedang tidak tersedia.';
        }

        if ($this->isFree()) {
            return 'Buku gratis tidak perlu dibeli, ambil langsung dari halaman buku.';
        }

        // AB-07: penulis tidak bisa membeli bukunya sendiri
        if ((int) $this->author_id === (int) $user->id) {
            return 'Anda tidak bisa membeli buku milik sendiri.';
        }

        // AB-06: buku yang sama tidak bisa dimiliki dua kali
        if (Library::where('user_id', $user->id)->where('book_id', $this->id)->exists()) {
            return 'Buku ini sudah ada di Pustaka Saya.';
        }

        return null;
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
