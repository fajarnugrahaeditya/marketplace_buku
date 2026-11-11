
<a href="{{ route('catalog.show', ['book' => $book->slug]) }}"
    class="group flex h-full flex-col overflow-hidden rounded-2xl
           border border-white/70 bg-white/60 shadow-lg
           backdrop-blur-xl transition-all duration-300
           hover:-translate-y-1 hover:bg-white/80 hover:shadow-xl">

    {{-- Cover buku --}}
    <div class="w-full overflow-hidden bg-white/40"
         style="aspect-ratio: 3 / 4;">

        <img
            src="{{ asset('storage/'.$book->cover) }}"
            alt="Cover {{ $book->title }}"
            class="block h-full w-full object-cover object-top
                   transition-transform duration-500
                   group-hover:scale-105"
        >
    </div>

    {{-- Informasi buku --}}
    <div class="flex flex-1 flex-col gap-2 p-4">

        <div
            class="text-sm font-bold leading-snug text-gray-900"
            title="{{ $book->title }}"
        >
            {{ \Illuminate\Support\Str::limit($book->title, 50) }}
        </div>

        <div class="text-xs text-gray-500">
            {{ $book->author?->pen_name }}
        </div>

        {{-- Harga --}}
        <div class="mt-auto pt-2">
            @if ($book->price === 0)
                <span class="inline-flex items-center rounded-full
                             border border-green-200 bg-green-50
                             px-3 py-1 text-xs font-semibold text-green-700">
                    Gratis
                </span>
            @else
                <span class="inline-flex items-center rounded-full
                             border border-orange-200 bg-orange-50
                             px-3 py-1 text-xs font-semibold text-orange-700">
                    Rp{{ number_format($book->price, 0, ',', '.') }}
                </span>
            @endif
        </div>

    </div>
</a>
