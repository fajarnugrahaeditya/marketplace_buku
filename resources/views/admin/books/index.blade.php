<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-white leading-tight">
                Tinjau Buku
            </h2>
            <p class="mt-1 text-sm text-white/70">
                Setujui atau tolak buku berbayar, dan takedown buku yang melanggar.
            </p>
        </div>
    </x-slot>

    @php
        $tabs = [
            'pending' => 'Menunggu tinjau',
            'published' => 'Terbit',
            'rejected' => 'Ditolak',
            'taken_down' => 'Di-takedown',
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

            {{-- Filter status --}}
            <div class="flex flex-wrap gap-2">
                @foreach ($tabs as $key => $label)
                    @php
                        $count = $key === 'all' ? $counts->sum() : ($counts[$key] ?? 0);
                        $active = $status === $key;
                    @endphp
                    <a href="{{ route('admin.books.index', ['status' => $key]) }}"
                        class="rounded-full border px-4 py-2 text-sm font-medium transition-colors
                            {{ $active ? 'border-white bg-white text-gray-900' : 'border-white/40 bg-white/10 text-white hover:bg-white/20' }}">
                        {{ $label }} ({{ $count }})
                    </a>
                @endforeach
            </div>

            @if ($books->isEmpty())
                <div class="lc-glass-card p-8 text-center text-gray-700">
                    Tidak ada buku pada kategori ini.
                </div>
            @else
                <div class="overflow-hidden rounded-2xl border border-white/70 bg-white/60 shadow-lg backdrop-blur-xl">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-gray-700">
                            <thead class="border-b border-white/70 bg-white/50 text-xs uppercase tracking-wider text-gray-600">
                                <tr>
                                    <th class="whitespace-nowrap px-6 py-4">Cover</th>
                                    <th class="w-full px-6 py-4">Judul</th>
                                    <th class="whitespace-nowrap px-6 py-4">Harga</th>
                                    <th class="whitespace-nowrap px-6 py-4">Status</th>
                                    <th class="whitespace-nowrap px-6 py-4">Diperbarui</th>
                                    <th class="px-6 py-4"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/70">
                                @foreach ($books as $book)
                                    @php
                                        $badge = match ($book->status) {
                                            'published' => 'border-green-200 bg-green-50 text-green-700',
                                            'pending' => 'border-yellow-200 bg-yellow-50 text-yellow-700',
                                            'rejected', 'taken_down' => 'border-red-200 bg-red-50 text-red-700',
                                            default => 'border-gray-200 bg-gray-100 text-gray-700',
                                        };
                                    @endphp
                                    <tr class="transition-colors hover:bg-white/40">
                                        <td class="px-6 py-4 align-middle">
                                            <img src="{{ asset('storage/'.$book->cover) }}"
                                                alt="Cover {{ $book->title }}"
                                                class="h-20 w-14 rounded-lg object-cover shadow-sm">
                                        </td>
                                        <td class="px-6 py-4 align-middle">
                                            <div class="font-semibold text-gray-900">{{ $book->title }}</div>
                                            <div class="mt-1 text-xs text-gray-600">{{ $book->author?->pen_name }}</div>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 align-middle font-medium">
                                            {{ $book->price === 0 ? 'Gratis' : 'Rp'.number_format($book->price, 0, ',', '.') }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 align-middle">
                                            <span class="inline-flex rounded-full border px-3 py-1.5 text-xs font-semibold {{ $badge }}">
                                                {{ $book->statusLabel() }}
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 align-middle">
                                            {{ $book->updated_at->format('d M Y H:i') }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 align-middle">
                                            <a href="{{ route('admin.books.show', $book) }}"
                                                class="text-sm font-medium text-blue-700 underline hover:text-blue-800">
                                                {{ $book->status === 'pending' ? 'Tinjau' : 'Detail' }}
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{ $books->links() }}
            @endif
        </div>
    </div>
</x-app-layout>
