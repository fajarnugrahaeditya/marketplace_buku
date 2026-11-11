<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Library extends Model
{
    // nama tabel di ERD adalah "library" (bukan bentuk jamak "libraries")
    protected $table = 'library';

    protected $fillable = ['user_id', 'book_id', 'order_id', 'download_count'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }
}
