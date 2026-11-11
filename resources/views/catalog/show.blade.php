
@php
    $licenseLabels = [
        'own_work' => 'Karya sendiri',
        'creative_commons' => 'Creative Commons',
        'public_domain' => 'Domain publik',
        'with_permission' => 'Dengan izin pemilik',
    ];

    $isAdmin = auth()->check() && auth()->user()->role === 'admin';

    $btn = 'inline-flex items-center justify-center px-4 py-2 rounded-xl font-semibold text-sm transition-all duration-200';
@endphp

<x-catalog-layout :title="$book->title">
    <div class="py-10">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-5">

            {{-- Kembali ke katalog --}}
            <a href="{{ route('catalog.index') }}"
                class="inline-flex items-center gap-2 text-sm font-medium text-white/90 hover:text-white transition-colors">
                &larr; Kembali ke katalog
            </a>

            {{-- Notifikasi --}}
            @if (session('status'))
                <div class="lc-glass-card p-4 text-sm text-green-700">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('error'))
                <div class="lc-glass-card p-4 text-sm text-red-700">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Detail buku --}}
            <div class="lc-glass-card p-6 md:p-8 flex flex-col md:flex-row gap-8">

                {{-- Cover buku --}}
                <div class="w-full md:w-64 shrink-0">
                    <div class="overflow-hidden rounded-2xl bg-white/50 shadow-md">
                        <img
                            src="{{ asset('storage/'.$book->cover) }}"
                            alt="Cover {{ $book->title }}"
                            class="w-full h-auto object-contain"
                        >
                    </div>
                </div>

                {{-- Informasi buku --}}
                <div class="flex-1 min-w-0 space-y-5">

                    {{-- Judul dan penulis --}}
                    <div>
                        <span class="lc-glass-badge">
                            Detail Buku
                        </span>

                        <h1 class="mt-4 text-2xl sm:text-3xl font-bold leading-tight text-gray-900">
                            {{ $book->title }}
                        </h1>

                        @if ($book->author)
                            <p class="mt-2 text-sm text-gray-600">
                                oleh
                                <a href="{{ route('authors.show', ['authorProfile' => $book->author_id]) }}"
                                    class="font-semibold text-blue-700 hover:text-blue-800 hover:underline">
                                    {{ $book->author->pen_name }}
                                </a>
                            </p>
                        @endif
                    </div>

                    {{-- Kategori --}}
                    @if ($book->categories->isNotEmpty())
                        <div class="flex flex-wrap gap-2">
                            @foreach ($book->categories as $category)
                                <a href="{{ route('catalog.index', ['kategori' => $category->slug]) }}"
                                    class="inline-flex items-center px-3 py-1.5 rounded-full
                                           border border-white/70 bg-white/60
                                           text-xs font-medium text-gray-700
                                           hover:bg-white/90 transition-colors">
                                    {{ $category->name }}
                                </a>
                            @endforeach
                        </div>
                    @endif

                    {{-- Harga --}}
                    <div class="rounded-2xl border border-white/70 bg-white/50 p-4">
                        <p class="text-xs font-medium text-gray-500">
                            Harga Buku
                        </p>

                        <div class="mt-1 text-2xl font-bold text-gray-900">
                            {{ $book->price === 0 ? 'Gratis' : 'Rp'.number_format($book->price, 0, ',', '.') }}
                        </div>
                    </div>

                    {{-- Informasi lisensi --}}
                    <div class="text-sm text-gray-600">
                        <span class="font-semibold text-gray-800">Lisensi:</span>

                        <span class="inline-flex items-center ml-1 px-2.5 py-1 rounded-full
                                     bg-blue-50 text-blue-700 font-medium text-xs">
                            {{ $licenseLabels[$book->license_type] ?? $book->license_type }}
                        </span>

                        @if ($book->license_type !== 'own_work' && $book->source_url)
                            <div class="mt-2">
                                <a href="{{ $book->source_url }}"
                                    target="_blank"
                                    rel="noopener noreferrer nofollow"
                                    class="text-blue-700 hover:text-blue-800 hover:underline font-medium">
                                    Lihat sumber atau bukti izin &rarr;
                                </a>
                            </div>
                        @endif
                    </div>

                    {{-- Sinopsis --}}
                    @if ($book->synopsis)
                        <div class="border-t border-gray-300/50 pt-4">
                            <h2 class="text-base font-bold text-gray-900">
                                Sinopsis
                            </h2>

                            <p class="mt-2 text-sm leading-relaxed text-gray-600 whitespace-pre-line">
                                {{ $book->synopsis }}
                            </p>
                        </div>
                    @endif

                    {{-- Tombol aksi --}}
                    <div class="pt-2 space-y-4">

                        @if ($book->isFree())
                            @guest
                                <a href="{{ route('login') }}"
                                    class="{{ $btn }} lc-primary-button">
                                    Masuk untuk mengambil buku gratis
                                </a>
                            @else
                                @if ($isAdmin)
                                    <p class="text-sm text-gray-500">
                                        Akun admin tidak dapat mengambil buku.
                                    </p>
                                @elseif ($isOwnBook)
                                    <p class="text-sm text-gray-500">
                                        Ini buku Anda.
                                    </p>
                                @elseif ($owned)
                                    <a href="{{ route('library.index') }}"
                                        class="{{ $btn }} bg-green-100 text-green-700 hover:bg-green-200">
                                        Sudah ada di Pustaka Saya
                                    </a>
                                @else
                                    <form method="POST"
                                        action="{{ route('library.claim', ['book' => $book->slug]) }}">
                                        @csrf

                                        <button type="submit"
                                            class="{{ $btn }} lc-primary-button">
                                            Ambil buku gratis
                                        </button>
                                    </form>
                                @endif
                            @endguest
                        @else
                            <button type="button"
                                disabled
                                class="{{ $btn }} bg-gray-300 text-gray-500 cursor-not-allowed opacity-80">
                                Beli buku (segera hadir)
                            </button>
                        @endif

                        {{-- Pratinjau PDF --}}
                        @if ($book->previewFile)
                            <div>
                                @guest
                                    <a href="{{ route('login') }}"
                                        class="text-sm font-medium text-[#EA5924] hover:underline">
                                        Masuk untuk mengunduh pratinjau &rarr;
                                    </a>
                                @else
                                    <a href="{{ route('catalog.preview', ['book' => $book->slug]) }}"
                                        target="_blank"
                                        class="text-sm font-medium text-blue-700 hover:text-blue-800 hover:underline">
                                        Buka pratinjau (PDF) &rarr;
                                    </a>
                                @endguest
                            </div>
                        @endif

                        {{-- Salin tautan --}}
                        <div x-data="{
                            copied: false,
                            copy() {
                                const url = @js(route('catalog.show', ['book' => $book->slug]));
                                const done = () => {
                                    this.copied = true;
                                    setTimeout(() => this.copied = false, 2000);
                                };

                                if (navigator.clipboard && window.isSecureContext) {
                                    navigator.clipboard.writeText(url).then(done);
                                } else {
                                    const t = document.createElement('textarea');
                                    t.value = url;
                                    t.style.position = 'fixed';
                                    t.style.opacity = '0';
                                    document.body.appendChild(t);
                                    t.select();

                                    try {
                                        document.execCommand('copy');
                                        done();
                                    } catch (e) {}

                                    document.body.removeChild(t);
                                }
                            }
                        }">
                            <button type="button"
                                @click="copy()"
                                class="{{ $btn }} border border-gray-300/70 bg-white/60 text-gray-700 hover:bg-white/90">
                                <span x-text="copied ? 'Tautan disalin!' : 'Salin tautan'">
                                    Salin tautan
                                </span>
                            </button>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</x-catalog-layout>
