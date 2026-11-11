<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AuthorProfile extends Model
{
    // Kunci utama tabel ini adalah user_id (satu pengguna, satu profil penulis)
    protected $primaryKey = 'user_id';

    public $incrementing = false;

    protected $keyType = 'int';

    // balance, user_id, dan rights_agreed_at sengaja tidak boleh diisi massal
    protected $fillable = ['pen_name', 'bio', 'photo'];

    protected function casts(): array
    {
        return [
            'rights_agreed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function books(): HasMany
    {
        return $this->hasMany(Book::class, 'author_id', 'user_id');
    }
}
