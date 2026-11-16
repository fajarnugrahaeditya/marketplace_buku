<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    /** Ringkasan sebelum pesanan dibuat. */
    public function show(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        $cart = Cart::where('user_id', $user->id)->first();

        $items = $cart
            ? $cart->items()->with('book.author:user_id,pen_name')->orderBy('id')->get()
            : collect();

        if ($items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Keranjang masih kosong.');
        }

        // ada item yang tidak bisa dibeli lagi: kembali ke keranjang agar pengguna bisa merapikannya
        if ($items->contains(fn ($item) => $item->book->purchaseBlockReason($user) !== null)) {
            return redirect()->route('cart.index')
                ->with('error', 'Ada buku di keranjang yang tidak bisa dibeli lagi. Hapus dulu sebelum checkout.');
        }

        return view('orders.checkout', [
            'items' => $items,
            'total' => (int) $items->sum(fn ($item) => $item->book->price),
            'expiryHours' => $this->expiryHours(),
        ]);
    }

    /** Buat pesanan dari isi keranjang. Pembayaran Midtrans menyusul di minggu 7. */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        Order::expireDue($user->id);

        $result = DB::transaction(function () use ($user) {
            // kunci baris keranjang supaya klik ganda tidak membuat dua pesanan
            $cart = Cart::where('user_id', $user->id)->lockForUpdate()->first();

            $items = $cart
                ? $cart->items()->with('book')->orderBy('id')->get()
                : collect();

            if ($items->isEmpty()) {
                return ['error' => 'Keranjang masih kosong.', 'to' => 'cart.index'];
            }

            if ($items->contains(fn ($item) => $item->book->purchaseBlockReason($user) !== null)) {
                return ['error' => 'Ada buku di keranjang yang tidak bisa dibeli lagi. Hapus dulu sebelum checkout.', 'to' => 'cart.index'];
            }

            // satu buku tidak boleh ada di dua pesanan yang sama-sama menunggu pembayaran
            $bookIds = $items->pluck('book_id');
            $dupe = OrderItem::whereIn('book_id', $bookIds)
                ->whereHas('order', fn ($q) => $q->where('user_id', $user->id)
                    ->where('status', 'pending')
                    ->where('expires_at', '>', now()))
                ->with('book:id,title')
                ->first();

            if ($dupe) {
                return [
                    'error' => 'Buku "'.$dupe->book->title.'" sudah ada di pesanan yang belum dibayar. Bayar atau batalkan pesanan itu dulu.',
                    'to' => 'orders.index',
                ];
            }

            $percent = $this->royaltyPercent();

            $order = new Order();
            $order->user_id = $user->id;
            $order->order_number = $this->newOrderNumber();
            $order->total = (int) $items->sum(fn ($item) => $item->book->price);
            $order->status = 'pending';
            $order->expires_at = now()->addHours($this->expiryHours());
            $order->save();

            foreach ($items as $item) {
                $price = (int) $item->book->price;
                // pembulatan ke bawah untuk penulis; sisanya menjadi bagian platform
                $authorShare = intdiv($price * $percent, 100);

                $line = new OrderItem();
                $line->order_id = $order->id;
                $line->book_id = $item->book_id;
                $line->price = $price;
                $line->royalty_percent = $percent;
                $line->author_share = $authorShare;
                $line->platform_share = $price - $authorShare;
                $line->save();
            }

            $cart->items()->delete();

            return ['order' => $order];
        });

        if (isset($result['error'])) {
            return redirect()->route($result['to'])->with('error', $result['error']);
        }

        return redirect()->route('orders.show', ['order' => $result['order']->order_number])
            ->with('status', 'Pesanan dibuat. Selesaikan pembayaran sebelum batas waktu.');
    }

    private function newOrderNumber(): string
    {
        do {
            $number = 'LC'.now()->format('ymd').'-'.Str::upper(Str::random(6));
        } while (Order::where('order_number', $number)->exists());

        return $number;
    }

    private function royaltyPercent(): int
    {
        $percent = (int) Setting::get('royalty_author_percent', 90);

        return max(0, min(100, $percent));
    }

    private function expiryHours(): int
    {
        return max(1, (int) Setting::get('order_expiry_hours', 24));
    }
}
