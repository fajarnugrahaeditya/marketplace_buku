
<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-medium text-white/70">
                Litera Commerce
            </p>
            <h2 class="mt-1 text-2xl font-bold text-white leading-tight">
                Dashboard Admin
            </h2>
        </div>
    </x-slot>

    <div class="py-10 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            {{-- Sambutan Admin --}}
            <div class="lc-glass-card p-6 sm:p-8">
                <p class="text-sm text-gray-500">
                    Selamat datang kembali,
                </p>

                <h3 class="mt-1 text-2xl font-bold text-gray-900">
                    {{ auth()->user()->name }}
                </h3>

                <p class="mt-2 text-sm text-gray-600">
                    Pantau aktivitas pengguna dan koleksi buku di Litera Commerce.
                </p>
            </div>

            {{-- Statistik Pengguna --}}
            <section>
                <div class="mb-4">
                    <h3 class="text-lg font-bold text-white">
                        Pengguna
                    </h3>
                    <p class="mt-1 text-sm text-white/70">
                        Ringkasan jumlah pengguna berdasarkan peran.
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <x-stat-card
                        label="Pembaca"
                        :value="$roles['reader'] ?? 0"
                    />

                    <x-stat-card
                        label="Penulis"
                        :value="$roles['author'] ?? 0"
                    />

                    <x-stat-card
                        label="Admin"
                        :value="$roles['admin'] ?? 0"
                    />
                </div>
            </section>

            {{-- Statistik Buku --}}
            <section>
                <div class="mb-4">
                    <h3 class="text-lg font-bold text-white">
                        Buku
                    </h3>
                    <p class="mt-1 text-sm text-white/70">
                        Ringkasan status dan jumlah koleksi buku.
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <x-stat-card
                        label="Buku terbit"
                        :value="$bookStatus['published'] ?? 0"
                        :href="route('catalog.index')"
                    />

                    <x-stat-card
                        label="Buku di-takedown"
                        :value="$bookStatus['taken_down'] ?? 0"
                    />

                    <x-stat-card
                        label="Total buku"
                        :value="$totalBooks"
                    />
                </div>
            </section>

        </div>
    </div>
</x-app-layout>
