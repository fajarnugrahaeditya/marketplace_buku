
<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-white leading-tight">
                Tambah Buku
            </h2>
            <p class="mt-1 text-sm text-white/70">
                Isi data buku, lalu unggah cover dan file PDF-nya.
            </p>
        </div>
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
