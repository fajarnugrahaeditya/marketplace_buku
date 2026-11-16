<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-white leading-tight">
                Keranjang
            </h2>
            <p class="mt-1 text-sm text-white/70">
                Buku berbayar yang siap Anda pesan.
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

            @if ($items->isEmpty())
                <div class="lc-glass-card p-8 text-center">
                    <p class="text-gray-700">Keranjang Anda masih kosong.</p>
                    <a href="{{ route('catalog.index') }}"
                        class="mt-4 inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold lc-primary-button">
                        Jelajahi katalog
                    </a>
                </div>
            @else
                <div class="lc-glass-card divide-y divide-white/70">
                    @foreach ($items as $item)
                        @php($book = $item->book)

                        <div class="flex flex-wrap items-center gap-4 p-5">
                            <img src="{{ asset('storage/'.$book->cover) }}"
                                alt="Cover {{ $book->title }}"
                                class="h-24 w-16 shrink-0 rounded-lg object-cover shadow-sm">

                            <div class="min-w-0 flex-1">
                                <a href="{{ route('catalog.show', ['book' => $book->slug]) }}"
                                    class="font-semibold text-gray-900 hover:underline">
                                    {{ $book->title }}
                                </a>
                                <p class="mt-1 text-sm text-gray-600">
                                    {{ $book->author?->pen_name }}
                                </p>

                                @if ($item->block_reason)
                                    <p class="mt-2 text-xs font-medium text-red-600">
                                        {{ $item->block_reason }} Hapus dari keranjang untuk melanjutkan.
                                    </p>
                                @endif
                            </div>

                            <div class="text-right">
                                <p class="font-semibold text-gray-900 {{ $item->block_reason ? 'line-through opacity-60' : '' }}">
                                    Rp{{ number_format($book->price, 0, ',', '.') }}
                                </p>

                                <form method="POST" action="{{ route('cart.destroy', ['book' => $book->slug]) }}" class="mt-2">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-sm font-medium text-red-600 underline hover:text-red-700">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="lc-glass-card flex flex-wrap items-center justify-between gap-4 p-5">
                    <div>
                        <p class="text-sm text-gray-600">Total</p>
                        <p class="text-2xl font-bold text-gray-900">Rp{{ number_format($total, 0, ',', '.') }}</p>
                    </div>

                    @if ($items->contains(fn ($i) => $i->block_reason))
                        <p class="text-sm text-gray-600">Hapus buku yang bermasalah dulu, lalu lanjut ke checkout.</p>
                    @else
                        <a href="{{ route('checkout.show') }}"
                            class="inline-flex items-center justify-center rounded-xl px-5 py-3 text-sm font-semibold lc-primary-button">
                            Lanjut ke checkout
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
