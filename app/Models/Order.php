<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    // user_id, order_number, total, status, dan expires_at diisi lewat kode yang eksplisit (CheckoutController)
    protected $fillable = [];

    protected function casts(): array
    {
        return [
            'total' => 'integer',
            'expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'pending' => 'Menunggu pembayaran',
            'paid' => 'Lunas',
            'expired' => 'Kedaluwarsa',
            'cancelled' => 'Dibatalkan',
            default => $this->status,
        };
    }

    /** Pesanan pending yang lewat batas bayar otomatis menjadi expired (dipanggil saat halaman dibuka). */
    public static function expireDue(?int $userId = null): void
    {
        static::query()
            ->where('status', 'pending')
            ->where('expires_at', '<', now())
            ->when($userId, fn ($q) => $q->where('user_id', $userId))
            ->update(['status' => 'expired']);
    }
}
