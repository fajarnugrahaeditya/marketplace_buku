<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-white leading-tight">
                Detail Pesanan
            </h2>
            <p class="mt-1 text-sm text-white/70">
                {{ $order->order_number }}
            </p>
        </div>
    </x-slot>

    @php
        $badge = match ($order->status) {
            'paid' => 'border-green-200 bg-green-50 text-green-700',
            'pending' => 'border-yellow-200 bg-yellow-50 text-yellow-700',
            default => 'border-gray-200 bg-gray-100 text-gray-600',
        };
    @endphp

    <div class="py-10 sm:py-12">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">

            <a href="{{ route('orders.index') }}"
                class="inline-flex items-center gap-2 text-sm font-medium text-white/90 hover:text-white transition-colors">
                &larr; Semua pesanan
            </a>

            @if (session('status'))
                <div class="rounded-2xl border border-green-200 bg-green-50/90 p-4 text-sm text-green-800 shadow-sm">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('error'))
                <div class="rounded-2xl border border-red-200 bg-red-50/90 p-4 text-sm text-red-800 shadow-sm">
                    {{ session('error') }}
                </div>
            @endif

            <div class="lc-glass-card space-y-3 p-6">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h1 class="text-xl font-bold text-gray-900">{{ $order->order_number }}</h1>
                    <span class="inline-flex rounded-full border px-3 py-1.5 text-xs font-semibold {{ $badge }}">
                        {{ $order->statusLabel() }}
                    </span>
                </div>

                <p class="text-sm text-gray-600">
                    Dibuat {{ $order->created_at->format('d M Y H:i') }}
                </p>

                @if ($order->status === 'pending')
                    <p class="text-sm font-medium text-yellow-800">
                        Bayar sebelum {{ $order->expires_at->format('d M Y H:i') }}.
                    </p>
                @elseif ($order->status === 'expired')
                    <p class="text-sm text-gray-600">
                        Batas bayar sudah lewat. Masukkan bukunya ke keranjang lagi untuk memesan ulang.
                    </p>
                @endif
            </div>

            <div class="lc-glass-card divide-y divide-white/70">
                @foreach ($order->items as $line)
                    <div class="flex items-center justify-between gap-4 p-5">
                        <div class="min-w-0">
                            <p class="truncate font-semibold text-gray-900">{{ $line->book->title }}</p>
                            <p class="text-sm text-gray-600">{{ $line->book->author?->pen_name }}</p>
                        </div>
                        <p class="shrink-0 font-semibold text-gray-900">
                            Rp{{ number_format($line->price, 0, ',', '.') }}
                        </p>
                    </div>
                @endforeach

                <div class="flex items-center justify-between p-5">
                    <p class="font-semibold text-gray-700">Total</p>
                    <p class="text-2xl font-bold text-gray-900">Rp{{ number_format($order->total, 0, ',', '.') }}</p>
                </div>
            </div>

            @if ($order->status === 'pending')
                <div class="lc-glass-card space-y-4 p-5">
                    @if (! $paymentReady)
                        <p class="text-sm text-red-700">
                            Pembayaran belum dikonfigurasi (kunci Midtrans belum diisi). Hubungi admin.
                        </p>
                    @else
                        <div class="flex flex-wrap items-center gap-4">
                            <button type="button" id="pay-btn"
                                data-url="{{ route('orders.pay', ['order' => $order->order_number]) }}"
                                class="inline-flex items-center justify-center rounded-xl px-5 py-3 text-sm font-semibold lc-primary-button disabled:cursor-not-allowed disabled:opacity-60">
                                Bayar sekarang
                            </button>

                            <form method="POST" id="check-form"
                                action="{{ route('orders.check', ['order' => $order->order_number]) }}">
                                @csrf
                                <button class="text-sm font-medium text-blue-700 underline hover:text-blue-800">
                                    Cek status pembayaran
                                </button>
                            </form>

                            <form method="POST" action="{{ route('orders.cancel', ['order' => $order->order_number]) }}"
                                onsubmit="return confirm('Batalkan pesanan ini?')">
                                @csrf
                                <button class="text-sm font-medium text-red-600 underline hover:text-red-700">
                                    Batalkan pesanan
                                </button>
                            </form>
                        </div>

                        <p id="pay-message" class="hidden text-sm text-red-700" role="alert"></p>

                        <p class="text-xs text-gray-500">
                            Sudah membayar tapi status belum berubah? Klik "Cek status pembayaran".
                        </p>
                    @endif
                </div>
            @elseif ($order->status === 'paid')
                <div class="lc-glass-card space-y-2 p-5">
                    @if ($paidPayment)
                        <p class="text-sm text-gray-700">
                            Dibayar via <strong>{{ $paidPayment->method ?? 'Midtrans' }}</strong>
                            pada {{ $paidPayment->paid_at->format('d M Y H:i') }}.
                        </p>
                    @endif

                    <a href="{{ route('library.index') }}" class="text-sm font-medium text-blue-700 underline hover:text-blue-800">
                        Buka Pustaka Saya &rarr;
                    </a>
                </div>
            @endif
        </div>
    </div>

    @if ($order->status === 'pending' && $paymentReady)
        <script src="{{ $snapJsUrl }}" data-client-key="{{ $clientKey }}"></script>
        <script>
            (function () {
                const button = document.getElementById('pay-btn');
                const message = document.getElementById('pay-message');
                const checkForm = document.getElementById('check-form');
                const csrf = document.querySelector('meta[name="csrf-token"]').content;

                function showError(text) {
                    message.textContent = text;
                    message.classList.remove('hidden');
                }

                // setelah popup ditutup atau pembayaran selesai, cek statusnya ke server
                function syncStatus() {
                    checkForm.submit();
                }

                button.addEventListener('click', async function () {
                    message.classList.add('hidden');
                    button.disabled = true;

                    try {
                        const response = await fetch(button.dataset.url, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrf,
                                'Accept': 'application/json',
                            },
                        });
                        const data = await response.json().catch(function () { return {}; });

                        if (data.paid) {
                            syncStatus();
                            return;
                        }

                        if (! response.ok) {
                            throw new Error(data.message || 'Gagal memulai pembayaran.');
                        }

                        window.snap.pay(data.token, {
                            onSuccess: syncStatus,
                            onPending: syncStatus,
                            onError: function () {
                                showError('Pembayaran gagal. Silakan coba lagi.');
                                button.disabled = false;
                            },
                            onClose: function () {
                                button.disabled = false;
                            },
                        });
                    } catch (error) {
                        showError(error.message);
                        button.disabled = false;
                    }
                });
            })();
        </script>
    @endif
</x-app-layout>
