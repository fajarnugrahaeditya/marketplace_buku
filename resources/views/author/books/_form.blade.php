
@php
    $selected = $book ? $book->categories->pluck('id')->map(fn ($id) => (int) $id)->all() : [];
    $selected = array_map('intval', (array) old('category_ids', $selected));

    $inputClass = 'mt-1 block w-full rounded-xl border border-white/80 bg-white/70 text-gray-900 shadow-inner backdrop-blur-xl placeholder:text-gray-400 focus:border-blue-400 focus:bg-white/90 focus:ring-2 focus:ring-blue-200 transition-all duration-200';
    $labelClass = 'block text-sm font-semibold text-gray-800';
    $helpClass = 'mt-1.5 text-xs leading-relaxed text-gray-600';
    $fileClass = 'mt-2 block w-full cursor-pointer rounded-xl border border-white/80 bg-white/50 p-3 text-sm text-gray-700 backdrop-blur-lg file:mr-4 file:rounded-lg file:border-0 file:bg-[#1F2937] file:px-4 file:py-2 file:text-xs file:font-semibold file:uppercase file:tracking-wider file:text-white hover:file:bg-[#304F78]';
    $sectionClass = 'space-y-5';
@endphp
<form method="POST" action="{{ $action }}"
    enctype="multipart/form-data"
    class="w-full space-y-8 rounded-3xl border border-white/70 bg-white/55 p-6 shadow-2xl backdrop-blur-2xl sm:p-10"
    x-data="{ license: '{{ old('license_type', $book?->license_type ?? 'own_work') }}' }">

    @csrf

    @if ($method !== 'POST')
        @method($method)
    @endif

    {{-- Informasi Buku --}}
    <section class="{{ $sectionClass }} space-y-5">
        <div class="border-b border-white/70 pb-3">
            <h3 class="text-lg font-bold text-gray-900">Informasi Buku</h3>
            <p class="mt-1 text-sm text-gray-600">
                Masukkan informasi dasar mengenai buku yang akan diterbitkan.
            </p>
        </div>

        <div>
            <label for="title" class="{{ $labelClass }}">Judul</label>
            <input id="title" name="title" type="text"
                value="{{ old('title', $book?->title) }}"
                class="{{ $inputClass }}" required autofocus>

            @foreach ($errors->get('title') as $message)
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @endforeach
        </div>

        <div>
            <label for="synopsis" class="{{ $labelClass }}">Sinopsis</label>
            <textarea id="synopsis" name="synopsis" rows="5"
                class="{{ $inputClass }}"
                placeholder="Tuliskan ringkasan atau gambaran isi buku...">{{ old('synopsis', $book?->synopsis) }}</textarea>

            @foreach ($errors->get('synopsis') as $message)
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @endforeach
        </div>

        <div>
            <label for="price" class="{{ $labelClass }}">Harga (rupiah)</label>
            <input id="price" name="price" type="number" min="0" step="1"
                value="{{ old('price', $book?->price ?? 0) }}"
                class="{{ $inputClass }}" required>

            <p class="{{ $helpClass }}">
                Isi 0 untuk buku gratis, atau minimal Rp{{ number_format($minPrice, 0, ',', '.') }}
                untuk buku berbayar. Buku gratis terbit langsung, buku berbayar ditinjau admin terlebih dahulu.
            </p>

            @foreach ($errors->get('price') as $message)
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @endforeach
        </div>
    </section>

    {{-- Informasi Lisensi --}}
    <section class="{{ $sectionClass }} space-y-5">
        <div class="border-b border-white/70 pb-3">
            <h3 class="text-lg font-bold text-gray-900">Informasi Lisensi</h3>
            <p class="mt-1 text-sm text-gray-600">
                Tentukan jenis lisensi dan sertakan sumber atau bukti izin jika diperlukan.
            </p>
        </div>

        <div>
            <label for="license_type" class="{{ $labelClass }}">Jenis lisensi</label>
            <select id="license_type" name="license_type"
                x-model="license" class="{{ $inputClass }}">
                @foreach ([
                    'own_work' => 'Karya sendiri',
                    'creative_commons' => 'Creative Commons',
                    'public_domain' => 'Domain publik',
                    'with_permission' => 'Dengan izin pemilik'
                ] as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>

            @foreach ($errors->get('license_type') as $message)
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @endforeach
        </div>

        <div>
            <label for="source_url" class="{{ $labelClass }}">
                Tautan sumber atau bukti izin
                <span x-show="license !== 'own_work'" class="font-semibold text-red-600">(wajib)</span>
            </label>

            <input id="source_url" name="source_url" type="url"
                placeholder="https://"
                value="{{ old('source_url', $book?->source_url) }}"
                class="{{ $inputClass }}">

            <p class="{{ $helpClass }}">
                Wajib diisi jika karya bukan milik sendiri. Boleh berupa tautan ke halaman sumber
                atau tautan dokumen izin dari pemilik karya.
            </p>

            @foreach ($errors->get('source_url') as $message)
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @endforeach
        </div>
    </section>

    {{-- Kategori Buku --}}
    <section class="{{ $sectionClass }} space-y-5">
        <div class="border-b border-white/70 pb-3">
            <h3 class="text-lg font-bold text-gray-900">Kategori Buku</h3>
            <p class="mt-1 text-sm text-gray-600">
                Pilih minimal satu kategori yang sesuai dengan isi buku.
            </p>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            @foreach ($categories as $parent)
                <fieldset class="rounded-xl border border-white/80 bg-white/40 px-4 pb-4 pt-5 shadow-sm backdrop-blur-lg">
                <legend class="relative top-5 px-2 py-1 text-sm font-bold text-gray-800">
                {{ $parent->name }}
                </legend>

                    <div class="mt-3 grid grid-cols-2 gap-x-3 gap-y-3">
                        @foreach ($parent->children as $child)
                            <label class="inline-flex cursor-pointer items-center text-sm text-gray-700">
                                <input type="checkbox" name="category_ids[]"
                                    value="{{ $child->id }}"
                                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500"
                                    @checked(in_array($child->id, $selected, true))>

                                <span class="ms-2">{{ $child->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>
            @endforeach
        </div>

        @foreach ($errors->get('category_ids') as $message)
            <p class="text-sm text-red-600">{{ $message }}</p>
        @endforeach

        @foreach ($errors->get('category_ids.*') as $messages)
            @foreach ((array) $messages as $message)
                <p class="text-sm text-red-600">{{ $message }}</p>
            @endforeach
        @endforeach
    </section>

    {{-- File dan Dokumen --}}
    <section class="{{ $sectionClass }} space-y-5">
        <div class="border-b border-white/70 pb-3">
            <h3 class="text-lg font-bold text-gray-900">File dan Dokumen Buku</h3>
            <p class="mt-1 text-sm text-gray-600">
                Unggah cover, file lengkap, dan pratinjau buku.
            </p>
        </div>

        {{-- Cover --}}
        <div>
            <label for="cover" class="{{ $labelClass }}">
                Cover Buku
                <span class="font-normal text-gray-500">(JPG, PNG, atau WEBP, maksimal 2 MB)</span>
            </label>

            @if ($book && $book->cover)
                <div class="mt-3 flex items-center gap-4 rounded-xl border border-white/80 bg-white/40 p-3 backdrop-blur-lg">
                    <img src="{{ asset('storage/'.$book->cover) }}"
                        alt="Cover saat ini"
                        class="h-28 w-20 rounded-lg object-cover shadow-md">

                    <div>
                        <p class="text-sm font-semibold text-gray-800">Cover saat ini</p>
                        <p class="{{ $helpClass }}">
                            Unggah file baru hanya jika ingin mengganti cover.
                        </p>
                    </div>
                </div>
            @endif

            <input id="cover" name="cover" type="file"
                accept="image/png,image/jpeg,image/webp"
                class="{{ $fileClass }}">

            @foreach ($errors->get('cover') as $message)
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @endforeach
        </div>

        {{-- File Buku Lengkap --}}
        <div>
            <label for="full_file" class="{{ $labelClass }}">
                File Buku Lengkap
                <span class="font-normal text-gray-500">(PDF, maksimal 20 MB)</span>
            </label>

            @if ($book && $book->fullFile)
                <div class="mt-2 rounded-xl border border-white/80 bg-white/40 p-3 backdrop-blur-lg">
                    <p class="text-sm font-medium text-gray-800">
                        File saat ini:
                        {{ number_format($book->fullFile->size / 1024, 0, ',', '.') }} KB.
                    </p>

                    <p class="{{ $helpClass }}">
                        Unggah file baru hanya jika ingin menggantinya.

                        @if ($book->price > 0)
                            Mengganti file buku berbayar yang sudah terbit membutuhkan tinjau ulang admin.
                        @endif
                    </p>
                </div>
            @endif

            <input id="full_file" name="full_file" type="file"
                accept="application/pdf"
                class="{{ $fileClass }}">

            @foreach ($errors->get('full_file') as $message)
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @endforeach
        </div>

        {{-- File Pratinjau --}}
        <div>
            <label for="preview_file" class="{{ $labelClass }}">
                File Pratinjau
                <span class="font-normal text-gray-500">(PDF, opsional, maksimal 5 MB)</span>
            </label>

            @if ($book && $book->previewFile)
                <div class="mt-2 rounded-xl border border-white/80 bg-white/40 p-3 backdrop-blur-lg">
                    <p class="text-sm font-medium text-gray-800">
                        File pratinjau saat ini:
                        {{ number_format($book->previewFile->size / 1024, 0, ',', '.') }} KB.
                    </p>
                </div>
            @endif

            <input id="preview_file" name="preview_file" type="file"
                accept="application/pdf"
                class="{{ $fileClass }}">

            @foreach ($errors->get('preview_file') as $message)
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @endforeach
        </div>
    </section>

    {{-- Tombol Aksi --}}
    <div class="flex flex-wrap items-center gap-4 border-t border-white/60 pt-6">
        <button type="submit"
            class="inline-flex items-center justify-center rounded-xl border border-white/20 bg-[#1F2937] px-6 py-3 text-xs font-bold uppercase tracking-widest text-white shadow-lg transition-all duration-300 hover:-translate-y-1 hover:bg-[#304F78] hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2">
            {{ $book ? 'Simpan Perubahan' : 'Simpan sebagai Draft' }}
        </button>

        <a href="{{ route('author.buku.index') }}"
            class="inline-flex items-center justify-center rounded-xl border border-white/70 bg-white/50 px-6 py-3 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm backdrop-blur-lg transition-all duration-300 hover:-translate-y-0.5 hover:bg-white/80 hover:shadow-md">
            Batal
        </a>
    </div>
</form>
