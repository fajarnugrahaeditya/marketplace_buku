
<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-medium text-white/70">
                Litera Commerce
            </p>
            <h2 class="mt-1 text-2xl font-bold text-white leading-tight">
                Dashboard Reader
            </h2>
        </div>
    </x-slot>

    <div class="py-10 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Notifikasi --}}
            @if (session('status'))
                <div class="lc-glass-card p-4 text-sm text-green-700">
                    {{ session('status') }}
                </div>
            @endif

            {{-- Statistik --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <x-stat-card
                    label="Buku di Pustaka"
                    :value="$libraryCount"
                    :href="route('library.index')"
                />

                <x-stat-card
                    label="Total unduhan"
                    :value="$downloadTotal"
                    :href="route('library.index')"
                />

                <x-stat-card
                    label="Penulis diikuti"
                    :value="$followingCount"
                    href="#dari-penulis"
                />
            </div>

            {{-- Aktivitas Reader --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                {{-- Buku terakhir di pustaka --}}
                <section class="lc-glass-card p-5 sm:p-6 space-y-5">
                    <div class="flex items-center justify-between gap-3">
                        <h3 class="font-bold text-gray-900">
                            Terakhir di Pustaka Saya
                        </h3>

                        <a href="{{ route('library.index') }}"
                            class="text-sm font-semibold text-blue-700 hover:text-blue-800 hover:underline shrink-0">
                            Lihat semua &rarr;
                        </a>
                    </div>

                    @forelse ($recentLibrary as $item)
                        @php($book = $item->book)

                        <div class="flex items-center gap-3 rounded-xl border border-white/60 bg-white/40 p-3">
                            <img
                                src="{{ asset('storage/'.$book->cover) }}"
                                alt="Cover {{ $book->title }}"
                                class="w-12 h-16 object-cover rounded-lg shadow-sm shrink-0"
                            >

                            <div class="flex-1 min-w-0">
                                <a href="{{ route('catalog.show', ['book' => $book->slug]) }}"
                                    class="block font-semibold text-sm text-gray-900 hover:text-blue-700 hover:underline truncate">
                                    {{ $book->title }}
                                </a>

                                <div class="mt-1 text-xs text-gray-500">
                                    {{ $book->author?->pen_name }}
                                </div>
                            </div>

                            @if ($book->status !== 'taken_down')
                                <a href="{{ route('library.download', ['book' => $book->id]) }}"
                                    class="inline-flex items-center justify-center px-3 py-2 rounded-lg bg-[#1F2937] text-white text-xs font-semibold hover:bg-gray-700 transition-colors shrink-0">
                                    Unduh
                                </a>
                            @endif
                        </div>
                    @empty
                        <div class="rounded-xl border border-white/60 bg-white/40 p-5 text-center">
                            <p class="text-sm text-gray-600">
                                Pustaka Anda masih kosong.
                            </p>

                            <a href="{{ route('catalog.index') }}"
                                class="inline-block mt-2 text-sm font-semibold text-blue-700 hover:text-blue-800 hover:underline">
                                Jelajahi katalog &rarr;
                            </a>
                        </div>
                    @endforelse
                </section>

                {{-- Buku dari penulis yang diikuti --}}
                <section id="dari-penulis"
                    class="lc-glass-card p-5 sm:p-6 space-y-5">

                    <h3 class="font-bold text-gray-900">
                        Terbaru dari penulis yang Anda ikuti
                    </h3>

                    @if ($followingCount === 0)
                        <div class="rounded-xl border border-white/60 bg-white/40 p-5">
                            <p class="text-sm leading-relaxed text-gray-600">
                                Anda belum mengikuti penulis. Buka profil penulis dari halaman buku, lalu tekan Ikuti.
                            </p>
                        </div>

                    @elseif ($fromFollowed->isEmpty())
                        <div class="rounded-xl border border-white/60 bg-white/40 p-5">
                            <p class="text-sm text-gray-600">
                                Penulis yang Anda ikuti belum menerbitkan buku.
                            </p>
                        </div>

                    @else
                        @foreach ($fromFollowed as $book)
                            <div class="flex items-center gap-3 rounded-xl border border-white/60 bg-white/40 p-3">
                                <img
                                    src="{{ asset('storage/'.$book->cover) }}"
                                    alt="Cover {{ $book->title }}"
                                    class="w-12 h-16 object-cover rounded-lg shadow-sm shrink-0"
                                >

                                <div class="flex-1 min-w-0">
                                    <a href="{{ route('catalog.show', ['book' => $book->slug]) }}"
                                        class="block font-semibold text-sm text-gray-900 hover:text-blue-700 hover:underline truncate">
                                        {{ $book->title }}
                                    </a>

                                    <div class="mt-1 text-xs text-gray-500">
                                        {{ $book->author?->pen_name }}
                                    </div>
                                </div>

                                <span class="px-3 py-1.5 text-xs font-semibold rounded-full shrink-0
                                    {{ $book->price === 0
                                        ? 'bg-green-50 text-green-700 border border-green-200'
                                        : 'bg-orange-50 text-orange-700 border border-orange-200' }}">
                                    {{ $book->price === 0 ? 'Gratis' : 'Rp'.number_format($book->price, 0, ',', '.') }}
                                </span>
                            </div>
                        @endforeach
                    @endif
                </section>
            </div>

            {{-- Ajakan menjadi penulis --}}
            <div class="lc-glass-card p-6 sm:p-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-5">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">
                        Punya karya sendiri?
                    </h3>

                    <p class="mt-2 text-sm text-gray-600">
                        Daftar sebagai penulis dan terbitkan buku Anda, gratis atau berbayar.
                    </p>
                </div>

                <a href="{{ route('author.register') }}"
                    class="inline-flex items-center justify-center px-5 py-3 rounded-xl bg-[#1F2937] text-white text-sm font-semibold hover:bg-gray-700 transition-colors shrink-0">
                    Jadi penulis &rarr;
                </a>
            </div>

        </div>
    </div>
</x-app-layout>
