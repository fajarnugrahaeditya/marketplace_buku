
<x-catalog-layout title="Pustaka Saya">
    <div class="py-10 sm:py-12">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">

            {{-- Header --}}
            <div>
                <h1 class="text-2xl font-bold text-white">
                    Pustaka Saya
                </h1>
                <p class="mt-1 text-sm text-white">
                    Kumpulan buku yang telah Anda miliki.
                </p>
            </div>

            {{-- Notifications --}}
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
                {{-- Empty State --}}
                <div class="lc-glass-card space-y-3 p-8 text-center">
                    <h2 class="text-lg font-semibold text-gray-900">
                        Pustaka Anda masih kosong.
                    </h2>

                    <p class="text-sm text-gray-600">
                        Mulai jelajahi katalog dan temukan buku yang menarik untuk dibaca.
                    </p>

                    <a href="{{ route('catalog.index') }}"
                        class="inline-flex items-center justify-center rounded-xl border border-white/20 bg-[#1F2937] px-5 py-3 text-xs font-semibold uppercase tracking-widest text-white shadow-lg transition-all duration-300 hover:-translate-y-0.5 hover:bg-[#304F78] hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        Jelajahi Katalog
                    </a>
                </div>
            @else
                {{-- Book Collection --}}
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 sm:gap-6 lg:grid-cols-3">
                    @foreach ($items as $item)
                        @php($book = $item->book)

                        <div class="flex gap-4 overflow-hidden rounded-2xl border border-white/70 bg-white/60 p-4 shadow-lg backdrop-blur-xl transition-all duration-300 hover:-translate-y-1 hover:bg-white/80 hover:shadow-xl">

                            {{-- Book Cover --}}
                            <div class="h-32 w-24 shrink-0 overflow-hidden rounded-lg bg-white/50 shadow-sm">
                                <img
                                    src="{{ asset('storage/'.$book->cover) }}"
                                    alt="Cover {{ $book->title }}"
                                    class="h-full w-full object-cover object-top"
                                >
                            </div>

                            {{-- Book Information --}}
                            <div class="flex min-w-0 flex-1 flex-col">
                                <a href="{{ route('catalog.show', ['book' => $book->slug]) }}"
                                    class="line-clamp-2 font-semibold leading-snug text-gray-900 transition-colors hover:text-blue-700 hover:underline">
                                    {{ $book->title }}
                                </a>

                                <div class="mt-1 text-sm text-gray-600">
                                    {{ $book->author?->pen_name }}
                                </div>

                                <div class="mt-2 text-xs leading-relaxed text-gray-500">
                                    Ditambahkan {{ $item->created_at?->format('d/m/Y') }}
                                    &middot;
                                    Diunduh {{ $item->download_count }} kali
                                </div>

                                <div class="mt-auto pt-3">
                                    @if ($book->status === 'taken_down')
                                        <span class="inline-flex rounded-full border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-medium text-red-700">
                                            Tidak tersedia
                                        </span>
                                        <p class="mt-1 text-xs text-gray-500">
                                            Buku ditarik dari katalog.
                                        </p>
                                    @else
                                        <a href="{{ route('library.download', ['book' => $book->id]) }}"
                                            class="lc-primary-button inline-flex items-center justify-center px-4 py-2 text-xs">
                                            Unduh PDF
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Pagination --}}
                <div class="pt-2">
                    {{ $items->links() }}
                </div>
            @endif

        </div>
    </div>
</x-catalog-layout>
