
<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-white leading-tight">
                Edit Buku
            </h2>
            <p class="mt-1 text-sm text-white/70">
                Perbarui data atau file buku Anda.
            </p>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
            @if ($book->status === 'rejected' && $book->rejected_reason)
                <div class="p-4 text-sm text-red-800 bg-red-100 rounded-lg">
                    Buku ini ditolak. Alasan: {{ $book->rejected_reason }}
                </div>
            @endif

            @include('author.books._form', [
                'action' => route('author.buku.update', $book),
                'method' => 'PUT',
                'book' => $book,
            ])
        </div>
    </div>
</x-app-layout>
