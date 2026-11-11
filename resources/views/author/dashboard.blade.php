<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Dashboard Penulis
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="p-4 text-sm text-green-800 bg-green-100 rounded-lg">
                    {{ session('status') }}
                </div>
            @endif

            <div class="p-6 bg-gray-800 text-white shadow sm:rounded-lg space-y-4">
                <p class="text-white">
                    Selamat datang,
                    <strong class="text-white">{{ auth()->user()->authorProfile?->pen_name ?? auth()->user()->name }}</strong>.
                </p>
                <a href="{{ route('author.buku.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-orange-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-400">
                    Kelola buku saya
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
