
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Tambah Buku
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @include('author.books._form', [
                'action' => route('author.buku.store'),
                'method' => 'POST',
                'book' => null,
            ])
        </div>
    </div>
</x-app-layout>
