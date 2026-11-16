<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    protected $fillable = ['user_id', 'book_id', 'reason'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'open' => 'Baru',
            'reviewed' => 'Ditinjau',
            'closed' => 'Ditutup',
            default => $this->status,
        };
    }
}
