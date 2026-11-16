<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-white leading-tight">
                Laporan Pembaca
            </h2>
            <p class="mt-1 text-sm text-white/70">
                Laporan terhadap buku yang terbit. Tinjau bukunya, takedown bila perlu.
            </p>
        </div>
    </x-slot>

    @php
        $tabs = [
            'open' => 'Baru',
            'reviewed' => 'Ditinjau',
            'closed' => 'Ditutup',
            'all' => 'Semua',
        ];
    @endphp

    <div class="py-10 sm:py-12">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">


            @if (session('status'))
                <div class="rounded-2xl border border-green-200 bg-green-50/90 p-4 text-sm text-green-800 shadow-sm">
                    {{ session('status') }}
                </div>
            @endif

            <div class="flex flex-wrap gap-2">
                @foreach ($tabs as $key => $label)
                    @php
                        $count = $key === 'all' ? $counts->sum() : ($counts[$key] ?? 0);
                        $active = $status === $key;
                    @endphp
                    <a href="{{ route('admin.reports.index', ['status' => $key]) }}"
                        class="rounded-full border px-4 py-2 text-sm font-medium transition-colors
                            {{ $active ? 'border-white bg-white text-gray-900' : 'border-white/40 bg-white/10 text-white hover:bg-white/20' }}">
                        {{ $label }} ({{ $count }})
                    </a>
                @endforeach
            </div>

            @if ($reports->isEmpty())
                <div class="lc-glass-card p-8 text-center text-gray-700">
                    Tidak ada laporan pada kategori ini.
                </div>
            @else
                <div class="lc-glass-card divide-y divide-white/70">
                    @foreach ($reports as $report)
                        <div class="space-y-3 p-5">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <a href="{{ route('admin.books.show', $report->book_id) }}"
                                        class="font-semibold text-gray-900 hover:underline">
                                        {{ $report->book?->title ?? 'Buku dihapus' }}
                                    </a>
                                    <p class="text-xs text-gray-600">
                                        Dilaporkan {{ $report->user?->name ?? 'pengguna dihapus' }}
                                        &middot; {{ $report->created_at->format('d M Y H:i') }}
                                    </p>
                                </div>
                                <span class="inline-flex rounded-full border border-gray-200 bg-gray-100 px-3 py-1.5 text-xs font-semibold text-gray-700">
                                    {{ $report->statusLabel() }}
                                </span>
                            </div>

                            <p class="whitespace-pre-line text-sm text-gray-700">{{ $report->reason }}</p>

                            @if ($report->status !== 'closed')
                                <div class="flex flex-wrap items-center gap-4">
                                    @if ($report->status === 'open')
                                        <form method="POST" action="{{ route('admin.reports.update', $report) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="reviewed">
                                            <button class="text-sm font-medium text-blue-700 underline hover:text-blue-800">
                                                Tandai ditinjau
                                            </button>
                                        </form>
                                    @endif

                                    <form method="POST" action="{{ route('admin.reports.update', $report) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="closed">
                                        <button class="text-sm font-medium text-gray-600 underline hover:text-gray-800">
                                            Tutup laporan
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                {{ $reports->links() }}
            @endif
        </div>
    </div>
</x-app-layout>
