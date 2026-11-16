<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-white leading-tight">
                Checkout
            </h2>
            <p class="mt-1 text-sm text-white/70">
                Periksa pesanan Anda sebelum dibuat.
            </p>
        </div>
    </x-slot>

    <div class="py-10 sm:py-12">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">


            @if (session('error'))
                <div class="rounded-2xl border border-red-200 bg-red-50/90 p-4 text-sm text-red-800 shadow-sm">
                    {{ session('error') }}
                </div>
            @endif

            <div class="lc-glass-card divide-y divide-white/70">
                @foreach ($items as $item)
                    <div class="flex items-center justify-between gap-4 p-5">
                        <div class="min-w-0">
                            <p class="truncate font-semibold text-gray-900">{{ $item->book->title }}</p>
                            <p class="text-sm text-gray-600">{{ $item->book->author?->pen_name }}</p>
                        </div>
                        <p class="shrink-0 font-semibold text-gray-900">
                            Rp{{ number_format($item->book->price, 0, ',', '.') }}
                        </p>
                    </div>
                @endforeach

                <div class="flex items-center justify-between p-5">
                    <p class="font-semibold text-gray-700">Total</p>
                    <p class="text-2xl font-bold text-gray-900">Rp{{ number_format($total, 0, ',', '.') }}</p>
                </div>
            </div>

            <div class="lc-glass-card space-y-4 p-5">
                <p class="text-sm text-gray-700">
                    Pesanan harus dibayar dalam <strong>{{ $expiryHours }} jam</strong> setelah dibuat.
                    Lewat dari itu, pesanan kedaluwarsa otomatis dan Anda bisa memesan ulang.
                </p>

                <div class="flex flex-wrap items-center gap-4">
                    <form method="POST" action="{{ route('checkout.store') }}">
                        @csrf
                        <button type="submit"
                            class="inline-flex items-center justify-center rounded-xl px-5 py-3 text-sm font-semibold lc-primary-button">
                            Buat pesanan
                        </button>
                    </form>

                    <a href="{{ route('cart.index') }}" class="text-sm font-medium text-gray-700 underline hover:text-gray-900">
                        Kembali ke keranjang
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
