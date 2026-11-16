<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Services\MidtransService;
use App\Services\PaymentSync;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class PaymentController extends Controller
{
    public function __construct(
        private MidtransService $midtrans,
        private PaymentSync $sync,
    ) {
    }

    /** Minta Snap token untuk pesanan (dipanggil tombol "Bayar sekarang"). */
    public function snap(Request $request, Order $order): JsonResponse
    {
        $user = $request->user();
        abort_unless((int) $order->user_id === (int) $user->id, 404);

        Order::expireDue($user->id);
        $order->refresh();

        if (! $this->midtrans->configured()) {
            return response()->json(['message' => 'Pembayaran belum dikonfigurasi. Hubungi admin.'], 503);
        }

        if ($order->status === 'paid') {
            return response()->json(['message' => 'Pesanan ini sudah dibayar.', 'paid' => true], 409);
        }

        if ($order->status !== 'pending') {
            return response()->json(['message' => 'Pesanan ini sudah tidak bisa dibayar ('.$order->statusLabel().').'], 409);
        }

        try {
            $result = DB::transaction(function () use ($order, $user) {
                // kunci pesanan supaya klik ganda tidak membuat dua transaksi
                $locked = Order::whereKey($order->id)->lockForUpdate()->first();

                if (! $locked || $locked->status !== 'pending') {
                    return ['error' => 'Pesanan ini sudah tidak bisa dibayar.'];
                }

                $payments = Payment::where('order_id', $locked->id)->orderByDesc('id')->get();

                // pakai ulang token lama selama masih hidup di Midtrans
                foreach ($payments as $payment) {
                    if (! $payment->snap_token || $payment->status !== 'pending') {
                        continue;
                    }

                    $status = $this->midtrans->status($payment->midtrans_order_id);

                    if ($status === null) {
                        return ['token' => $payment->snap_token];
                    }

                    if ($this->sync->apply($payment, $status)) {
                        return ['paid' => true];
                    }

                    if ($payment->status === 'pending') {
                        return ['token' => $payment->snap_token];
                    }
                    // sudah gagal atau kedaluwarsa di Midtrans: buat percobaan baru di bawah
                }

                $midtransOrderId = $locked->order_number.'-'.($payments->count() + 1);
                $token = $this->midtrans->createSnapToken($locked, $user, $midtransOrderId);

                Payment::create([
                    'order_id' => $locked->id,
                    'midtrans_order_id' => $midtransOrderId,
                    'snap_token' => $token,
                    'status' => 'pending',
                ]);

                return ['token' => $token];
            });
        } catch (Throwable $e) {
            Log::error('Gagal membuat Snap token', ['order' => $order->order_number, 'error' => $e->getMessage()]);

            return response()->json(['message' => 'Gagal menghubungi Midtrans. Coba lagi sebentar.'], 502);
        }

        if (isset($result['paid'])) {
            return response()->json(['message' => 'Pesanan ini sudah dibayar.', 'paid' => true], 409);
        }

        if (isset($result['error'])) {
            return response()->json(['message' => $result['error']], 409);
        }

        return response()->json(['token' => $result['token']]);
    }

    /** Cek status pembayaran ke Midtrans lalu perbarui pesanan. Berguna selama webhook belum aktif. */
    public function check(Request $request, Order $order): RedirectResponse
    {
        abort_unless((int) $order->user_id === (int) $request->user()->id, 404);

        $back = redirect()->route('orders.show', ['order' => $order->order_number]);

        if (! $this->midtrans->configured()) {
            return $back->with('error', 'Pembayaran belum dikonfigurasi. Hubungi admin.');
        }

        $payments = Payment::where('order_id', $order->id)->orderByDesc('id')->get();

        if ($payments->isEmpty()) {
            return $back->with('error', 'Belum ada pembayaran yang dibuat. Klik "Bayar sekarang" dulu.');
        }

        $paid = false;
        $pending = false;
        $failedStatus = null;

        try {
            foreach ($payments as $payment) {
                $status = $this->midtrans->status($payment->midtrans_order_id);

                if ($status === null) {
                    continue; // popup pembayaran belum pernah dibuka
                }

                if ($this->sync->apply($payment, $status)) {
                    $paid = true;
                    break;
                }

                if ($payment->status === 'pending') {
                    $pending = true;
                } elseif ($payment->isFinishedWithoutPayment()) {
                    $failedStatus ??= $payment->status;
                }
            }
        } catch (Throwable $e) {
            Log::error('Gagal cek status pembayaran', ['order' => $order->order_number, 'error' => $e->getMessage()]);

            return $back->with('error', 'Gagal menghubungi Midtrans. Coba lagi sebentar.');
        }

        if ($paid) {
            return $back->with('status', 'Pembayaran diterima. Buku sudah masuk ke Pustaka Saya.');
        }

        if ($pending) {
            return $back->with('status', 'Pembayaran masih menunggu. Selesaikan pembayaran lalu cek lagi.');
        }

        if ($failedStatus) {
            return $back->with('error', 'Pembayaran belum berhasil (status: '.$failedStatus.'). Silakan coba bayar lagi.');
        }

        return $back->with('status', 'Belum ada transaksi di Midtrans untuk pesanan ini. Klik "Bayar sekarang" untuk memulai.');
    }
}
