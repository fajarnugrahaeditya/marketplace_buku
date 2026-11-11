
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold leading-tight text-gray-900">
                    Buku Saya
                </h2>
                <p class="mt-1 text-sm text-gray-600">
                    Kelola dan pantau status publikasi buku Anda.
                </p>
            </div>

            <a href="{{ route('author.buku.create') }}"
                class="inline-flex items-center justify-center rounded-xl border border-white/20 bg-[#1F2937] px-5 py-3 text-xs font-semibold uppercase tracking-widest text-white shadow-lg transition-all duration-300 hover:-translate-y-0.5 hover:bg-[#304F78] hover:shadow-xl">
                Tambah Buku
            </a>
        </div>
    </x-slot>

    <div class="py-10 sm:py-12">
        <div class="mx-auto max-w-7xl space-y-5 px-4 sm:px-6 lg:px-8">

            {{-- Notification --}}
            @if (session('status'))
                <div class="rounded-2xl border border-green-200 bg-green-50/90 p-4 text-sm text-green-800 shadow-sm">
                    {{ session('status') }}
                </div>
            @endif

            {{-- Book Table --}}
            <div class="overflow-hidden rounded-2xl border border-white/70 bg-white/60 shadow-lg backdrop-blur-xl">

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-gray-700">
                        <thead class="border-b border-white/70 bg-white/50 text-xs uppercase tracking-wider text-gray-600">
                            <tr>
                                <th class="whitespace-nowrap px-6 py-4">Cover</th>
                                <th class="w-full px-6 py-4">Judul</th>
                                <th class="whitespace-nowrap px-6 py-4">Harga</th>
                                <th class="whitespace-nowrap px-6 py-4">Status</th>
                                <th class="whitespace-nowrap px-6 py-4">Aksi</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-white/70">
                            @forelse ($books as $book)
                                @php
                                    $badge = match ($book->status) {
                                        'published' => 'border-green-200 bg-green-50 text-green-700',
                                        'pending' => 'border-yellow-200 bg-yellow-50 text-yellow-700',
                                        'rejected', 'taken_down' => 'border-red-200 bg-red-50 text-red-700',
                                        default => 'border-gray-200 bg-gray-100 text-gray-700',
                                    };

                                    $editable = ! in_array($book->status, ['taken_down', 'archived'], true);
                                    $canPublish = in_array($book->status, ['draft', 'rejected'], true);
                                @endphp

                                <tr class="transition-colors hover:bg-white/40">

                                    {{-- Cover --}}
                                    <td class="px-6 py-4 align-middle">
                                        <img
                                            src="{{ asset('storage/'.$book->cover) }}"
                                            alt="Cover {{ $book->title }}"
                                            class="h-20 w-14 rounded-lg object-cover shadow-sm"
                                        >
                                    </td>

                                    {{-- Title --}}
                                    <td class="px-6 py-4 align-middle">
                                        <div class="font-semibold text-gray-900">
                                            {{ $book->title }}
                                        </div>

                                        @if ($book->status === 'rejected' && $book->rejected_reason)
                                            <div class="mt-2 text-xs leading-relaxed text-red-600">
                                                <span class="font-semibold">Alasan penolakan:</span>
                                                {{ $book->rejected_reason }}
                                            </div>
                                        @endif

                                        @if ($book->status === 'taken_down' && $book->takedown_reason)
                                            <div class="mt-2 text-xs leading-relaxed text-red-600">
                                                <span class="font-semibold">Alasan takedown:</span>
                                                {{ $book->takedown_reason }}
                                            </div>
                                        @endif
                                    </td>

                                    {{-- Price --}}
                                    <td class="whitespace-nowrap px-6 py-4 align-middle font-medium text-gray-700">
                                        {{ $book->price === 0 ? 'Gratis' : 'Rp'.number_format($book->price, 0, ',', '.') }}
                                    </td>

                                    {{-- Status --}}
                                    <td class="whitespace-nowrap px-6 py-4 align-middle">
                                        <span class="inline-flex rounded-full border px-3 py-1.5 text-xs font-semibold {{ $badge }}">
                                            {{ $book->statusLabel() }}
                                        </span>
                                    </td>

                                    {{-- Actions --}}
                                    <td class="px-6 py-4 align-middle">
                                        <div class="flex min-w-32 flex-wrap items-center gap-x-3 gap-y-2">

                                            @if ($editable)
                                                <a href="{{ route('author.buku.edit', $book) }}"
                                                    class="text-sm font-medium text-blue-700 underline hover:text-blue-800">
                                                    Edit
                                                </a>
                                            @endif

                                            @if ($canPublish && $book->price === 0)
                                                <form method="POST" action="{{ route('author.buku.publish', $book) }}">
                                                    @csrf
                                                    <button class="text-sm font-medium text-green-700 underline hover:text-green-800">
                                                        Terbitkan
                                                    </button>
                                                </form>
                                            @endif

                                            @if ($canPublish && $book->price > 0)
                                                <form method="POST" action="{{ route('author.buku.submit', $book) }}">
                                                    @csrf
                                                    <button class="text-sm font-medium text-yellow-700 underline hover:text-yellow-800">
                                                        Ajukan tinjau
                                                    </button>
                                                </form>
                                            @endif

                                            @if ($book->status === 'published')
                                                <form method="POST" action="{{ route('author.buku.archive', $book) }}"
                                                    onsubmit="return confirm('Arsipkan buku ini? Buku tidak akan tampil di katalog.')">
                                                    @csrf
                                                    <button class="text-sm font-medium text-gray-600 underline hover:text-gray-800">
                                                        Arsipkan
                                                    </button>
                                                </form>
                                            @endif

                                            @if ($book->status === 'archived')
                                                <form method="POST" action="{{ route('author.buku.restore', $book) }}">
                                                    @csrf
                                                    <button class="text-sm font-medium text-green-700 underline hover:text-green-800">
                                                        Terbitkan kembali
                                                    </button>
                                                </form>
                                            @endif

                                            @if ($canPublish)
                                                <form method="POST" action="{{ route('author.buku.destroy', $book) }}"
                                                    onsubmit="return confirm('Hapus buku ini beserta filenya?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="text-sm font-medium text-red-600 underline hover:text-red-700">
                                                        Hapus
                                                    </button>
                                                </form>
                                            @endif

                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center">
                                        <p class="font-semibold text-gray-800">
                                            Belum ada buku.
                                        </p>
                                        <p class="mt-1 text-sm text-gray-500">
                                            Klik tombol Tambah Buku untuk mulai menerbitkan karya Anda.
                                        </p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Pagination --}}
            <div class="pt-2">
                {{ $books->links() }}
            </div>

        </div>
    </div>
</x-app-layout>
