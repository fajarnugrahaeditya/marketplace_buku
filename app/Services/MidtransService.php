<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Str;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction;
use Throwable;

/** Pembungkus tipis untuk library midtrans/midtrans-php. */
class MidtransService
{
    public function configured(): bool
    {
        return filled(config('services.midtrans.server_key'))
            && filled(config('services.midtrans.client_key'));
    }

    public function clientKey(): string
    {
        return trim((string) config('services.midtrans.client_key'));
    }

    public function isProduction(): bool
    {
        return (bool) config('services.midtrans.is_production');
    }

    public function snapJsUrl(): string
    {
        return $this->isProduction()
            ? 'https://app.midtrans.com/snap/snap.js'
            : 'https://app.sandbox.midtrans.com/snap/snap.js';
    }

    private function boot(): void
    {
        Config::$serverKey = trim((string) config('services.midtrans.server_key'));
        Config::$clientKey = $this->clientKey();
        Config::$isProduction = $this->isProduction();
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    /** Minta Snap token untuk satu pesanan. $midtransOrderId harus unik di Midtrans. */
    public function createSnapToken(Order $order, User $user, string $midtransOrderId): string
    {
        $this->boot();

        $order->loadMissing('items.book');

        $seconds = (int) now()->diffInSeconds($order->expires_at, false);
        $minutes = max(1, (int) ceil($seconds / 60));

        $params = [
            'transaction_details' => [
                'order_id' => $midtransOrderId,
                'gross_amount' => (int) $order->total,
            ],
            'item_details' => $order->items->map(fn ($line) => [
                'id' => (string) $line->book_id,
                'price' => (int) $line->price,
                'quantity' => 1,
                'name' => Str::limit($line->book->title, 50, ''),
            ])->values()->all(),
            'customer_details' => [
                'first_name' => Str::limit($user->name, 100, ''),
                'email' => $user->email,
            ],
            // token berlaku selama sisa batas bayar pesanan
            'expiry' => [
                'unit' => 'minute',
                'duration' => $minutes,
            ],
        ];

        return (string) Snap::getSnapToken($params);
    }

    /**
     * Status transaksi dari Midtrans. Mengembalikan null kalau transaksinya belum ada
     * (404, misalnya pembeli belum membuka popup pembayaran).
     */
    public function status(string $midtransOrderId): ?object
    {
        $this->boot();

        try {
            return Transaction::status($midtransOrderId);
        } catch (Throwable $e) {
            if ((int) $e->getCode() === 404) {
                return null;
            }

            throw $e;
        }
    }

    /** Batalkan transaksi yang masih pending di Midtrans. */
    public function cancel(string $midtransOrderId): void
    {
        $this->boot();

        Transaction::cancel($midtransOrderId);
    }
}
