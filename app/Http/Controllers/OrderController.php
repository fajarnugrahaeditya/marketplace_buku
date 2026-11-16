<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Services\MidtransService;
use App\Services\PaymentSync;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class OrderController extends Controller
{
    public function __construct(
        private MidtransService $midtrans,
        private PaymentSync $sync,
    ) {
    }

    public function index(Request $request): View
    {
        Order::expireDue($request->user()->id);

        $orders = Order::where('user_id', $request->user()->id)
            ->withCount('items')
            ->latest('id')
            ->paginate(10);

        return view('orders.index', compact('orders'));
    }

    public function show(Request $request, Order $order): View
    {
        $this->own($request, $order);

        Order::expireDue($request->user()->id);
        $order->refresh();

        $order->load(['items.book.author:user_id,pen_name', 'payments']);

        return view('orders.show', [
            'order' => $order,
            'paymentReady' => $this->midtrans->configured(),
            'clientKey' => $this->midtrans->clientKey(),
            'snapJsUrl' => $this->midtrans->snapJsUrl(),
            'paidPayment' => $order->payments->firstWhere('paid_at', '!=', null),
        ]);
    }

    /** Pembeli membatalkan pesanan yang belum dibayar. */
    public function cancel(Request $request, Order $order): RedirectResponse
    {
        $this->own($request, $order);
        abort_unless($order->status === 'pending', 403);

        // sebelum batal, pastikan pembeli belum terlanjur membayar, lalu tutup transaksi di Midtrans
        if ($this->midtrans->configured()) {
            try {
                foreach (Payment::where('order_id', $order->id)->where('status', 'pending')->get() as $payment) {
                    $status = $this->midtrans->status($payment->midtrans_order_id);

                    if ($status === null) {
                        continue;
                    }

                    if ($this->sync->apply($payment, $status)) {
                        return redirect()->route('orders.show', ['order' => $order->order_number])
                            ->with('error', 'Pesanan ini sudah dibayar, jadi tidak bisa dibatalkan.');
                    }

                    if ($payment->status === 'pending') {
                        $this->midtrans->cancel($payment->midtrans_order_id);
                        $payment->status = 'cancel';
                        $payment->save();
                    }
                }
            } catch (Throwable $e) {
                // pembatalan lokal tetap jalan; kalau uang sempat masuk, pengecekan status akan menandai pesanan lunas
                Log::warning('Gagal menutup transaksi Midtrans saat batal', ['order' => $order->order_number, 'error' => $e->getMessage()]);
            }
        }

        $order->refresh();
        abort_unless($order->status === 'pending', 403);

        $order->status = 'cancelled';
        $order->save();

        return redirect()->route('orders.index')->with('status', 'Pesanan '.$order->order_number.' dibatalkan.');
    }

    private function own(Request $request, Order $order): void
    {
        // pesanan orang lain dibuat seolah tidak ada
        abort_unless((int) $order->user_id === (int) $request->user()->id, 404);
    }
}
