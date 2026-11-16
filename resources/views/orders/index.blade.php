<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-white leading-tight">
                Pesanan Saya
            </h2>
            <p class="mt-1 text-sm text-white/70">
                Riwayat dan status pesanan Anda.
            </p>
        </div>
    </x-slot>

    <div class="py-10 sm:py-12">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">


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

            @if ($orders->isEmpty())
                <div class="lc-glass-card p-8 text-center text-gray-700">
                    Belum ada pesanan.
                </div>
            @else
                <div class="overflow-hidden rounded-2xl border border-white/70 bg-white/60 shadow-lg backdrop-blur-xl">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-gray-700">
                            <thead class="border-b border-white/70 bg-white/50 text-xs uppercase tracking-wider text-gray-600">
                                <tr>
                                    <th class="whitespace-nowrap px-6 py-4">Nomor</th>
                                    <th class="whitespace-nowrap px-6 py-4">Tanggal</th>
                                    <th class="whitespace-nowrap px-6 py-4">Buku</th>
                                    <th class="whitespace-nowrap px-6 py-4">Total</th>
                                    <th class="whitespace-nowrap px-6 py-4">Status</th>
                                    <th class="px-6 py-4"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/70">
                                @foreach ($orders as $order)
                                    @php
                                        $badge = match ($order->status) {
                                            'paid' => 'border-green-200 bg-green-50 text-green-700',
                                            'pending' => 'border-yellow-200 bg-yellow-50 text-yellow-700',
                                            'expired', 'cancelled' => 'border-gray-200 bg-gray-100 text-gray-600',
                                            default => 'border-gray-200 bg-gray-100 text-gray-700',
                                        };
                                    @endphp
                                    <tr class="transition-colors hover:bg-white/40">
                                        <td class="whitespace-nowrap px-6 py-4 font-semibold text-gray-900">
                                            {{ $order->order_number }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4">
                                            {{ $order->created_at->format('d M Y H:i') }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4">{{ $order->items_count }} buku</td>
                                        <td class="whitespace-nowrap px-6 py-4 font-medium">
                                            Rp{{ number_format($order->total, 0, ',', '.') }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4">
                                            <span class="inline-flex rounded-full border px-3 py-1.5 text-xs font-semibold {{ $badge }}">
                                                {{ $order->statusLabel() }}
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4">
                                            <a href="{{ route('orders.show', ['order' => $order->order_number]) }}"
                                                class="text-sm font-medium text-blue-700 underline hover:text-blue-800">
                                                Detail
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{ $orders->links() }}
            @endif
        </div>
    </div>
</x-app-layout>
