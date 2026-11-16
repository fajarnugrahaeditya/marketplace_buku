<?php

namespace App\Services;

use App\Models\Payment;
use Illuminate\Support\Str;

/** Menerapkan status transaksi dari Midtrans ke data payment dan order. */
class PaymentSync
{
    public function __construct(private OrderFulfillment $fulfillment)
    {
    }

    /** Mengembalikan true kalau transaksi ini sudah dibayar. */
    public function apply(Payment $payment, object $status): bool
    {
        $transaction = (string) ($status->transaction_status ?? '');
        $fraud = (string) ($status->fraud_status ?? '');

        if ($transaction !== '') {
            $payment->status = Str::limit($transaction, 20, '');
        }

        if (! empty($status->payment_type)) {
            $payment->method = Str::limit((string) $status->payment_type, 30, '');
        }

        $payment->payload = json_decode(json_encode($status), true);

        $paid = $transaction === 'settlement'
            || ($transaction === 'capture' && ! in_array($fraud, ['challenge', 'deny'], true));

        if ($paid && ! $payment->paid_at) {
            $payment->paid_at = now();
        }

        $payment->save();

        if ($paid) {
            $this->fulfillment->markPaid($payment->order);
        }

        return $paid;
    }
}
