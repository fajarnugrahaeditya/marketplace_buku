
<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-medium text-white/70">
                Litera Commerce
            </p>
            <h2 class="mt-1 text-2xl font-bold text-white leading-tight">
                Dashboard Penulis
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

            {{-- Sambutan dan menu utama --}}
            <div class="lc-glass-card p-6 sm:p-7 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                <div>
                    <p class="text-sm text-gray-500">
                        Selamat datang kembali,
                    </p>

                    <h3 class="mt-1 text-2xl font-bold text-gray-900">
                        {{ $penName }}
                    </h3>

                    <p class="mt-2 text-sm text-gray-600">
                        Kelola karya dan pantau perkembangan buku Anda di Litera Commerce.
                    </p>
                </div>

                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('author.buku.create') }}"
                        class="inline-flex items-center justify-center rounded-xl border border-white/20 bg-[#1F2937] px-5 py-2.5 text-sm font-semibold text-white shadow-lg transition-all duration-300 hover:-translate-y-1 hover:bg-[#304F78] hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2">
                        + Tambah buku
                    </a>

                    <a href="{{ route('author.buku.index') }}"
                        class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl border border-white/80 bg-white/60 text-gray-800 text-sm font-semibold hover:bg-white/90 transition-colors">
                        Kelola buku
                    </a>

                    <a href="{{ route('authors.show', ['authorProfile' => auth()->id()]) }}"
                        class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl border border-white/80 bg-white/60 text-gray-800 text-sm font-semibold hover:bg-white/90 transition-colors">
                        Profil publik
                    </a>

                    <a href="{{ route('author.profile.edit') }}"
                        class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl border border-white/80 bg-white/60 text-gray-800 text-sm font-semibold hover:bg-white/90 transition-colors">
                        Edit profil
                    </a>
                </div>
            </div>

            {{-- Statistik buku --}}
            <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4">
                <x-stat-card
                    label="Draft"
                    :value="$statusCounts['draft'] ?? 0"
                    :href="route('author.buku.index')"
                />

                <x-stat-card
                    label="Menunggu tinjau"
                    :value="$statusCounts['pending'] ?? 0"
                    :href="route('author.buku.index')"
                />

                <x-stat-card
                    label="Terbit"
                    :value="$statusCounts['published'] ?? 0"
                    :href="route('author.buku.index')"
                />

                <x-stat-card
                    label="Ditolak"
                    :value="$statusCounts['rejected'] ?? 0"
                    :href="route('author.buku.index')"
                />

                <x-stat-card
                    label="Total unduhan"
                    :value="$downloadTotal"
                    href="#terpopuler"
                />

                <x-stat-card
                    label="Pengikut"
                    :value="$followers"
                    :href="route('authors.show', ['authorProfile' => auth()->id()])"
                />
            </div>

            {{-- Informasi buku --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                {{-- Perlu perhatian --}}
                <section class="lc-glass-card p-5 sm:p-6 space-y-5">
                    <h3 class="font-bold text-gray-900">
                        Perlu perhatian
                    </h3>

                    @forelse ($attention as $book)
                        <div class="rounded-xl border border-white/60 bg-white/40 p-4 space-y-2">
                            <div class="flex items-center justify-between gap-3">
                                <span class="font-semibold text-sm text-gray-900 truncate">
                                    {{ $book->title }}
                                </span>

                                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-red-50 text-red-700 border border-red-200 shrink-0">
                                    {{ $book->statusLabel() }}
                                </span>
                            </div>

                            <p class="text-sm leading-relaxed text-gray-600">
                                @if ($book->status === 'rejected')
                                    Alasan penolakan:
                                    {{ $book->rejected_reason ?: 'tidak dicantumkan' }}.

                                    <a href="{{ route('author.buku.edit', $book) }}"
                                        class="font-semibold text-blue-700 hover:text-blue-800 hover:underline">
                                        Perbaiki buku
                                    </a>
                                @else
                                    Alasan takedown:
                                    {{ $book->takedown_reason ?: 'tidak dicantumkan' }}.
                                @endif
                            </p>
                        </div>
                    @empty
                        <div class="rounded-xl border border-white/60 bg-white/40 p-5 text-center">
                            <p class="text-sm text-gray-600">
                                Tidak ada buku yang perlu diperhatikan.
                            </p>
                        </div>
                    @endforelse
                </section>

                {{-- Buku paling banyak diunduh --}}
                <section id="terpopuler" class="lc-glass-card p-5 sm:p-6 space-y-5">
                    <h3 class="font-bold text-gray-900">
                        Buku paling banyak diunduh
                    </h3>

                    @forelse ($topBooks as $book)
                        <div class="flex items-center gap-3 rounded-xl border border-white/60 bg-white/40 p-3">
                            <img
                                src="{{ asset('storage/'.$book->cover) }}"
                                alt="Cover {{ $book->title }}"
                                class="w-10 h-14 object-cover rounded-lg shadow-sm shrink-0"
                            >

                            <span class="flex-1 min-w-0 font-semibold text-sm text-gray-900 truncate">
                                {{ $book->title }}
                            </span>

                            <span class="text-xs font-medium text-gray-500 shrink-0">
                                {{ number_format($book->downloads, 0, ',', '.') }} unduhan
                            </span>
                        </div>
                    @empty
                        <div class="rounded-xl border border-white/60 bg-white/40 p-5">
                            <p class="text-sm leading-relaxed text-gray-600">
                                Belum ada unduhan. Terbitkan buku gratis agar pembaca bisa mengambilnya.
                            </p>
                        </div>
                    @endforelse
                </section>
            </div>

        </div>
    </div>
</x-app-layout>
