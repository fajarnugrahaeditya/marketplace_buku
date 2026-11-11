<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Dashboard Admin
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="p-6 bg-gray-800 text-white shadow sm:rounded-lg">
                <p class="text-white">
                    Selamat datang, <strong class="text-white">{{ auth()->user()->name }}</strong>.
                    Panel tinjau buku, laporan, dan penarikan akan dibuat di minggu 8.
                </p>
            </div>
        </div>
    </div>
</x-app-layout>
