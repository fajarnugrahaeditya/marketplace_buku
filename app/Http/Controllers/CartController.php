<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Cart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(Request $request): View
    {
        $cart = Cart::firstOrCreate(['user_id' => $request->user()->id]);

        $items = $cart->items()
            ->with('book.author:user_id,pen_name')
            ->orderBy('id')
            ->get()
            ->map(function ($item) use ($request) {
                // item yang sudah tidak bisa dibeli (ditarik, sudah dimiliki, dsb.) diberi catatan
                $item->block_reason = $item->book->purchaseBlockReason($request->user());

                return $item;
            });

        $total = (int) $items->whereNull('block_reason')->sum(fn ($item) => $item->book->price);

        return view('cart.index', compact('items', 'total'));
    }

    public function store(Request $request, Book $book): RedirectResponse
    {
        $reason = $book->purchaseBlockReason($request->user());

        if ($reason) {
            return redirect()->route('catalog.show', ['book' => $book->slug])->with('error', $reason);
        }

        $cart = Cart::firstOrCreate(['user_id' => $request->user()->id]);

        $item = $cart->items()->firstOrCreate(['book_id' => $book->id]);

        return redirect()->route('catalog.show', ['book' => $book->slug])
            ->with('status', $item->wasRecentlyCreated
                ? 'Buku dimasukkan ke keranjang.'
                : 'Buku ini sudah ada di keranjang.');
    }

    public function destroy(Request $request, Book $book): RedirectResponse
    {
        $cart = Cart::where('user_id', $request->user()->id)->first();

        if ($cart) {
            $cart->items()->where('book_id', $book->id)->delete();
        }

        return redirect()->route('cart.index')->with('status', 'Buku dihapus dari keranjang.');
    }
}
