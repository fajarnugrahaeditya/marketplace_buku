<?php

namespace App\Services;

use App\Models\Library;
use App\Models\Order;
use App\Models\RoyaltyTransaction;
use Illuminate\Support\Facades\DB;

class OrderFulfillment
{
    /**
     * Tandai pesanan lunas: buku masuk Pustaka Saya dan royalti penulis dicatat.
     * Aman dipanggil berulang (cek status dari tombol dan webhook nanti): hasilnya tetap sama.
     * Kalau uang sudah masuk, pesanan diproses walau statusnya sempat expired atau cancelled.
     */
    public function markPaid(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $locked = Order::whereKey($order->id)->lockForUpdate()->first();

            if (! $locked || $locked->status === 'paid') {
                return;
            }

            $locked->load('items.book');

            foreach ($locked->items as $line) {
                Library::firstOrCreate(
                    ['user_id' => $locked->user_id, 'book_id' => $line->book_id],
                    ['order_id' => $locked->id, 'download_count' => 0]
                );

                RoyaltyTransaction::firstOrCreate(
                    ['order_item_id' => $line->id],
                    ['author_id' => $line->book->author_id, 'amount' => $line->author_share]
                );
            }

            $locked->status = 'paid';
            $locked->save();
        });

        $order->refresh();
    }
}
