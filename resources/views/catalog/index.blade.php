@php
    $inputClass = 'mt-1 block w-full rounded-md border-gray-300 bg-white text-gray-900 text-sm shadow-sm focus:border-orange-500 focus:ring-orange-500';
    $labelClass = 'block text-sm font-medium text-gray-700';

    $adaFilter = $filters['q'] !== '' || $filters['kategori'] !== '' || $filters['jenis'] !== ''
        || $filters['harga_min'] !== null || $filters['harga_max'] !== null || $filters['urut'] !== 'terbaru';
@endphp

<x-catalog-layout title="Katalog">
    <div class="py-10">
        <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">

            @if (session('status'))
                <div class="p-4 text-sm text-green-800 bg-green-100 rounded-lg">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('error'))
                <div class="p-4 text-sm text-red-800 bg-red-100 rounded-lg">
                    {{ session('error') }}
                </div>
            @endif

            <div class="lc-glass-card px-6 py-8 sm:px-8">
    <h1 class="text-3xl font-bold text-gray-900">
        {{ config('app.name') }}
    </h1>

    <p class="mt-2 text-sm sm:text-base text-gray-600">
        Baca dan terbitkan karya penulis independen.
    </p>
</div>

            <div class="flex flex-col lg:flex-row gap-8 items-start">

                {{-- Kolom kiri: kartu pencarian dan filter --}}
                <aside class="w-full lg:w-72 shrink-0">
                    <form
                        id="filter-form"
                        method="GET"
                        action="{{ route('catalog.index') }}"
                        class="lc-glass-card p-5 space-y-4"
                    >
                        <h2 class="text-base font-semibold text-white">
                            Cari buku
                        </h2>

                        <div>
                            <label for="q" class="{{ $labelClass }}">
                                Judul atau penulis
                            </label>

                            <input
                                id="q"
                                name="q"
                                type="text"
                                value="{{ $filters['q'] }}"
                                placeholder="Contoh: Sherlock"
                                class="{{ $inputClass }}"
                            >
                        </div>

                        <div>
                            <label for="kategori" class="{{ $labelClass }}">
                                Kategori
                            </label>

                            <select id="kategori" name="kategori" class="{{ $inputClass }}">
                                <option value="">Semua kategori</option>

                                @foreach ($categories as $parent)
                                    <optgroup label="{{ $parent->name }}">
                                        <option
                                            value="{{ $parent->slug }}"
                                            @selected($filters['kategori'] === $parent->slug)
                                        >
                                            Semua {{ $parent->name }}
                                        </option>

                                        @foreach ($parent->children as $child)
                                            <option
                                                value="{{ $child->slug }}"
                                                @selected($filters['kategori'] === $child->slug)
                                            >
                                                {{ $child->name }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="jenis" class="{{ $labelClass }}">
                                Jenis
                            </label>

                            <select id="jenis" name="jenis" class="{{ $inputClass }}">
                                <option value="" @selected($filters['jenis'] === '')>
                                    Gratis dan berbayar
                                </option>

                                <option value="gratis" @selected($filters['jenis'] === 'gratis')>
                                    Gratis
                                </option>

                                <option value="berbayar" @selected($filters['jenis'] === 'berbayar')>
                                    Berbayar
                                </option>
                            </select>
                        </div>

                        <div>
                            <span class="{{ $labelClass }}">Harga (Rp)</span>

                            <div class="mt-1 grid grid-cols-2 gap-2">
                                <input
                                    name="harga_min"
                                    type="number"
                                    min="0"
                                    step="1"
                                    placeholder="Min"
                                    value="{{ $filters['harga_min'] }}"
                                    aria-label="Harga minimum"
                                    class="{{ $inputClass }}"
                                >

                                <input
                                    name="harga_max"
                                    type="number"
                                    min="0"
                                    step="1"
                                    placeholder="Maks"
                                    value="{{ $filters['harga_max'] }}"
                                    aria-label="Harga maksimum"
                                    class="{{ $inputClass }}"
                                >
                            </div>
                        </div>

                        <div class="pt-1 space-y-3">
                            <button
                                type="submit"
                               class="inline-flex items-center justify-center rounded-xl border border-white/20 bg-[#1F2937] px-5 py-2.5 text-sm font-semibold text-white shadow-lg transition-all duration-300 hover:-translate-y-1 hover:bg-[#304F78] hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2 w-full inline-flex justify-center items-center"
                            >
                                Cari
                            </button>

                            @if ($adaFilter)
                                <a
                                    href="{{ route('catalog.index') }}"
                                    class="block text-center text-sm text-gray-200 underline"
                                >
                                    Reset
                                </a>
                            @endif
                        </div>
                    </form>
                </aside>

                {{-- Kolom kanan: katalog buku --}}
                <section class="flex-1 min-w-0 w-full space-y-4">
                    <div class="flex flex-wrap items-end justify-between gap-3">
                        <div>
                            <h2 class="text-2xl font-semibold text-white">
                                Katalog
                            </h2>

                            @if ($books->total() > 0)
                                <p class="text-sm text-white">
                                    Menampilkan {{ $books->total() }} buku.
                                </p>
                            @endif
                        </div>

                        <div class="flex items-center gap-2">
                            <label for="urut" class="text-sm text-white">
                                Urutkan
                            </label>

                            <select
                                id="urut"
                                name="urut"
                                form="filter-form"
                                onchange="this.form.requestSubmit()"
                                class="rounded-md border-gray-300 bg-white text-gray-900 text-sm shadow-sm focus:border-orange-500 focus:ring-orange-500"
                            >
                                <option value="terbaru" @selected($filters['urut'] === 'terbaru')>
                                    Terbaru
                                </option>

                                <option value="termurah" @selected($filters['urut'] === 'termurah')>
                                    Harga termurah
                                </option>

                                <option value="termahal" @selected($filters['urut'] === 'termahal')>
                                    Harga termahal
                                </option>
                            </select>
                        </div>
                    </div>

                    @if ($books->isEmpty())
                        <div class="p-8 text-center bg-gray-800 text-white rounded-lg shadow">
                            @if ($adaFilter)
                                Tidak ada buku yang cocok dengan pencarian atau filter Anda.
                            @else
                                Belum ada buku yang terbit.
                            @endif
                        </div>
                    @else
                        <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-4">
                            @foreach ($books as $book)
                                @include('catalog._book-card', ['book' => $book])
                            @endforeach
                        </div>

                        {{ $books->links() }}
                    @endif
                </section>

            </div>
        </div>
    </div>
</x-catalog-layout>