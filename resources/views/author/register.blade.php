
@php
    $inputClass = 'mt-1 block w-full rounded-xl border border-white/70 bg-white/80 text-gray-900 shadow-sm backdrop-blur-sm focus:border-blue-500 focus:ring-blue-500';
@endphp

<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold leading-tight text-gray-900">
                Daftar sebagai Penulis
            </h2>
        </div>
    </x-slot>

    <div class="py-10 sm:py-12">
        <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">

            <div class="lc-glass-card p-5 sm:p-8">

                {{-- Introduction --}}
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-gray-900">
                        Bergabung sebagai Penulis
                    </h1>
                    <p class="mt-2 text-sm leading-relaxed text-gray-600">
                        Buat profil penulis Anda dan mulai publikasikan karya melalui Litera Commerce.
                    </p>
                </div>

                <form method="POST" action="{{ route('author.register.store') }}" class="space-y-6">
                    @csrf

                    {{-- Pen Name --}}
                    <div>
                        <label for="pen_name" class="block text-sm font-semibold text-gray-700">
                            Nama pena
                        </label>

                        <input
                            id="pen_name"
                            name="pen_name"
                            type="text"
                            value="{{ old('pen_name') }}"
                            class="{{ $inputClass }}"
                            required
                            autofocus
                        >

                        @foreach ($errors->get('pen_name') as $message)
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @endforeach
                    </div>

                    {{-- Bio --}}
                    <div>
                        <label for="bio" class="block text-sm font-semibold text-gray-700">
                            Bio singkat
                            <span class="font-normal text-gray-500">(opsional)</span>
                        </label>

                        <textarea
                            id="bio"
                            name="bio"
                            rows="4"
                            class="{{ $inputClass }}"
                            placeholder="Ceritakan sedikit tentang diri Anda sebagai penulis..."
                        >{{ old('bio') }}</textarea>

                        @foreach ($errors->get('bio') as $message)
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @endforeach
                    </div>

                    {{-- Rights Agreement --}}
                    <div class="rounded-xl border border-blue-100 bg-blue-50/70 p-4">
                        <label for="rights_agreed" class="flex items-start">
                            <input
                                id="rights_agreed"
                                type="checkbox"
                                name="rights_agreed"
                                value="1"
                                class="mt-1 rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500"
                                @checked(old('rights_agreed'))
                            >

                            <span class="ms-3 text-sm leading-relaxed text-gray-700">
                                Saya menyatakan memiliki hak untuk mendistribusikan karya yang saya unggah:
                                karya sendiri, karya berlisensi terbuka, karya domain publik, atau karya dengan
                                izin pemilik. Untuk karya selain milik sendiri, saya akan mengisi tautan sumber
                                atau bukti izin.
                            </span>
                        </label>

                        @foreach ($errors->get('rights_agreed') as $message)
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @endforeach
                    </div>

                    {{-- Actions --}}
                    <div class="flex flex-wrap items-center gap-4 border-t border-white/60 pt-5">
                        <button
                            type="submit"
                            class="inline-flex items-center justify-center rounded-xl border border-white/20 bg-[#1F2937] px-5 py-3 text-xs font-semibold uppercase tracking-widest text-white shadow-lg transition-all duration-300 hover:-translate-y-0.5 hover:bg-[#304F78] hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                        >
                            Daftar sebagai Penulis
                        </button>

                        <a href="{{ route('dashboard') }}"
                            class="text-sm font-medium text-blue-700 underline transition-colors hover:text-blue-800">
                            Batal
                        </a>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
