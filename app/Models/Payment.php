<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'order_id',
        'midtrans_order_id',
        'snap_token',
        'method',
        'status',
        'paid_at',
        'payload',
    ];

    protected function casts(): array
    {
        return [
            'paid_at' => 'datetime',
            'payload' => 'array',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /** Status Midtrans yang berarti percobaan bayar ini sudah gagal dan tidak bisa dipakai lagi. */
    public function isFinishedWithoutPayment(): bool
    {
        return in_array($this->status, ['deny', 'cancel', 'expire', 'failure'], true);
    }
}
