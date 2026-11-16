<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-white leading-tight">
                Detail Tinjauan
            </h2>
            <p class="mt-1 text-sm text-white/70">
                Periksa kelengkapan buku sebelum menyetujui atau menolak.
            </p>
        </div>
    </x-slot>

    @php
        $licenseLabels = [
            'own_work' => 'Karya sendiri',
            'creative_commons' => 'Creative Commons',
            'public_domain' => 'Domain publik',
            'with_permission' => 'Dengan izin pemilik',
        ];

        $badge = match ($book->status) {
            'published' => 'border-green-200 bg-green-50 text-green-700',
            'pending' => 'border-yellow-200 bg-yellow-50 text-yellow-700',
            'rejected', 'taken_down' => 'border-red-200 bg-red-50 text-red-700',
            default => 'border-gray-200 bg-gray-100 text-gray-700',
        };

        $hasPreview = $book->files->contains('type', 'preview');
        $hasFull = $book->files->contains('type', 'full');
        $sourceIsLink = $book->source_url && preg_match('#^https?://#i', $book->source_url);
        $input = 'w-full rounded-xl border border-gray-300 bg-white/80 px-3 py-2 text-sm text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500';
    @endphp

    <div class="py-10 sm:py-12">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">

            <a href="{{ route('admin.books.index', ['status' => $book->status === 'pending' ? 'pending' : 'all']) }}"
                class="inline-flex items-center gap-2 text-sm font-medium text-white/90 hover:text-white transition-colors">
                &larr; Kembali ke daftar
            </a>

            @if (session('status'))
                <div class="rounded-2xl border border-green-200 bg-green-50/90 p-4 text-sm text-green-800 shadow-sm">
                    {{ session('status') }}
                </div>
            @endif

            <div class="lc-glass-card flex flex-col gap-8 p-6 md:flex-row md:p-8">
                <div class="w-full shrink-0 md:w-56">
                    <img src="{{ asset('storage/'.$book->cover) }}"
                        alt="Cover {{ $book->title }}"
                        class="w-full rounded-2xl object-contain shadow-md">
                </div>

                <div class="min-w-0 flex-1 space-y-4">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">{{ $book->title }}</h1>
                            <p class="mt-1 text-sm text-gray-600">
                                oleh {{ $book->author?->pen_name }}
                                @if ($book->author?->user)
                                    ({{ $book->author->user->email }})
                                @endif
                            </p>
                        </div>
                        <span class="inline-flex rounded-full border px-3 py-1.5 text-xs font-semibold {{ $badge }}">
                            {{ $book->statusLabel() }}
                        </span>
                    </div>

                    <dl class="grid grid-cols-1 gap-3 text-sm sm:grid-cols-2">
                        <div>
                            <dt class="text-gray-500">Harga</dt>
                            <dd class="font-semibold text-gray-900">
                                {{ $book->price === 0 ? 'Gratis' : 'Rp'.number_format($book->price, 0, ',', '.') }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Kategori</dt>
                            <dd class="font-semibold text-gray-900">
                                {{ $book->categories->pluck('name')->join(', ') ?: '-' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Status hak distribusi</dt>
                            <dd class="font-semibold text-gray-900">{{ $licenseLabels[$book->license_type] ?? $book->license_type }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Tautan sumber / bukti izin</dt>
                            <dd class="break-words font-semibold text-gray-900">
                                @if ($sourceIsLink)
                                    <a href="{{ $book->source_url }}" target="_blank" rel="noopener noreferrer nofollow"
                                        class="text-blue-700 underline hover:text-blue-800">{{ $book->source_url }}</a>
                                @else
                                    {{ $book->source_url ?: '-' }}
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Persetujuan hak penulis</dt>
                            <dd class="font-semibold text-gray-900">
                                {{ $book->author?->rights_agreed_at?->format('d M Y H:i') ?? 'Belum tercatat' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Laporan terbuka</dt>
                            <dd class="font-semibold text-gray-900">{{ $openReports }}</dd>
                        </div>
                    </dl>

                    @if ($book->synopsis)
                        <div class="border-t border-gray-300/50 pt-4">
                            <h2 class="text-sm font-bold text-gray-900">Sinopsis</h2>
                            <p class="mt-2 whitespace-pre-line text-sm leading-relaxed text-gray-600">{{ $book->synopsis }}</p>
                        </div>
                    @endif

                    <div class="flex flex-wrap gap-x-5 gap-y-2 border-t border-gray-300/50 pt-4 text-sm font-medium">
                        @if ($hasPreview)
                            <a href="{{ route('admin.books.file', ['book' => $book, 'type' => 'preview']) }}" target="_blank"
                                class="text-blue-700 underline hover:text-blue-800">Buka pratinjau (PDF)</a>
                        @else
                            <span class="text-gray-500">Tidak ada pratinjau</span>
                        @endif

                        @if ($hasFull)
                            <a href="{{ route('admin.books.file', ['book' => $book, 'type' => 'full']) }}" target="_blank"
                                class="text-blue-700 underline hover:text-blue-800">Buka PDF lengkap</a>
                        @else
                            <span class="text-red-600">PDF lengkap tidak ditemukan</span>
                        @endif

                        @if ($book->status === 'published')
                            <a href="{{ route('catalog.show', ['book' => $book->slug]) }}" target="_blank"
                                class="text-blue-700 underline hover:text-blue-800">Lihat di katalog</a>
                        @endif
                    </div>

                    @if ($book->status === 'rejected' && $book->rejected_reason)
                        <p class="rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                            <span class="font-semibold">Alasan penolakan:</span> {{ $book->rejected_reason }}
                        </p>
                    @endif

                    @if ($book->status === 'taken_down' && $book->takedown_reason)
                        <p class="rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                            <span class="font-semibold">Alasan takedown:</span> {{ $book->takedown_reason }}
                        </p>
                    @endif
                </div>
            </div>

            {{-- Tindakan admin --}}
            @if ($book->status === 'pending')
                <div class="grid gap-6 md:grid-cols-2">
                    <div class="lc-glass-card space-y-3 p-6">
                        <h2 class="font-bold text-gray-900">Setujui</h2>
                        <p class="text-sm text-gray-600">Buku langsung tampil di katalog dan bisa dibeli.</p>
                        <form method="POST" action="{{ route('admin.books.approve', $book) }}"
                            onsubmit="return confirm('Setujui dan terbitkan buku ini?')">
                            @csrf
                            <button type="submit"
                                class="inline-flex items-center justify-center rounded-xl bg-green-600 px-5 py-3 text-sm font-semibold text-white hover:bg-green-700">
                                Setujui dan terbitkan
                            </button>
                        </form>
                    </div>

                    <div class="lc-glass-card space-y-3 p-6">
                        <h2 class="font-bold text-gray-900">Tolak</h2>
                        <form method="POST" action="{{ route('admin.books.reject', $book) }}" class="space-y-3">
                            @csrf
                            <label for="reject-reason" class="block text-sm text-gray-600">
                                Alasan (akan dibaca penulis)
                            </label>
                            <textarea id="reject-reason" name="reason" rows="4" required minlength="10" maxlength="1000"
                                class="{{ $input }}">{{ old('reason') }}</textarea>
                            @error('reason')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <button type="submit"
                                class="inline-flex items-center justify-center rounded-xl bg-red-600 px-5 py-3 text-sm font-semibold text-white hover:bg-red-700">
                                Tolak buku
                            </button>
                        </form>
                    </div>
                </div>
            @elseif ($book->status === 'published')
                <div class="lc-glass-card space-y-3 p-6">
                    <h2 class="font-bold text-gray-900">Takedown</h2>
                    <p class="text-sm text-gray-600">
                        Buku disembunyikan dari katalog dan pemilik tidak bisa mengunduhnya lagi.
                        Laporan terbuka untuk buku ini otomatis ditandai ditinjau.
                    </p>
                    <form method="POST" action="{{ route('admin.books.takedown', $book) }}" class="space-y-3"
                        onsubmit="return confirm('Takedown buku ini?')">
                        @csrf
                        <textarea name="reason" rows="3" required minlength="10" maxlength="1000"
                            placeholder="Alasan takedown"
                            class="{{ $input }}">{{ old('reason') }}</textarea>
                        @error('reason')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <button type="submit"
                            class="inline-flex items-center justify-center rounded-xl bg-red-600 px-5 py-3 text-sm font-semibold text-white hover:bg-red-700">
                            Takedown buku
                        </button>
                    </form>
                </div>
            @elseif ($book->status === 'taken_down')
                <div class="lc-glass-card space-y-3 p-6">
                    <h2 class="font-bold text-gray-900">Pulihkan</h2>
                    <p class="text-sm text-gray-600">Buku tampil lagi di katalog dan pemilik bisa mengunduhnya.</p>
                    <form method="POST" action="{{ route('admin.books.restore', $book) }}"
                        onsubmit="return confirm('Pulihkan buku ini?')">
                        @csrf
                        <button type="submit"
                            class="inline-flex items-center justify-center rounded-xl bg-green-600 px-5 py-3 text-sm font-semibold text-white hover:bg-green-700">
                            Pulihkan buku
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
