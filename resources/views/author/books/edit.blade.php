
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Buku
        </h2>
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
