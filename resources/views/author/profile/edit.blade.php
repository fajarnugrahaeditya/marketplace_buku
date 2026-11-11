
@php
    $inputClass = 'mt-1 block w-full rounded-xl border border-white/80 bg-white/70 text-gray-900 shadow-inner backdrop-blur-xl placeholder:text-gray-400 focus:border-blue-400 focus:bg-white/90 focus:ring-2 focus:ring-blue-200 transition-all duration-200';

    $labelClass = 'block text-sm font-semibold text-gray-800';

    $fileClass = 'mt-2 block w-full cursor-pointer rounded-xl border border-white/80 bg-white/50 p-3 text-sm text-gray-700 backdrop-blur-lg file:mr-4 file:rounded-lg file:border-0 file:bg-[#1F2937] file:px-4 file:py-2 file:text-xs file:font-semibold file:uppercase file:tracking-wider file:text-white hover:file:bg-[#304F78]';
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Profil Penulis
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-5">

            {{-- Notifikasi --}}
            @if (session('status'))
                <div class="rounded-xl border border-green-200 bg-green-50/80 p-4 text-sm text-green-800 shadow-sm backdrop-blur-lg">
                    {{ session('status') }}
                </div>
            @endif

            {{-- Form Profil --}}
            <div class="rounded-3xl border border-white/70 bg-white/55 p-6 shadow-2xl backdrop-blur-2xl sm:p-10">

                <div class="mb-8 border-b border-white/70 pb-4">
                    <h3 class="text-lg font-bold text-gray-900">
                        Informasi Profil Penulis
                    </h3>
                    <p class="mt-1 text-sm text-gray-800">
                        Kelola foto, nama pena, dan informasi singkat yang ditampilkan pada profil publikmu.
                    </p>
                </div>

                <form method="POST"
                    action="{{ route('author.profile.update') }}"
                    enctype="multipart/form-data"
                    class="space-y-8">

                    @csrf
                    @method('PUT')

                    {{-- Foto Profil --}}
                    <div class="space-y-3">
                        <div>
                            <span class="{{ $labelClass }}">Foto Profil</span>
                            <p class="mt-1 text-xs text-black">
                                Gunakan foto yang merepresentasikan identitas penulismu.
                            </p>
                        </div>

                        <div class="flex flex-col gap-5 rounded-2xl border border-white/70 bg-white/30 p-5 backdrop-blur-lg sm:flex-row sm:items-center">

                            @if ($profile->photo)
                                <img src="{{ asset('storage/'.$profile->photo) }}"
                                    alt="Foto {{ $profile->pen_name }}"
                                    class="h-24 w-24 shrink-0 rounded-full border-4 border-white/80 object-cover shadow-lg">
                            @else
                                <div class="flex h-24 w-24 shrink-0 items-center justify-center rounded-full border-4 border-white/80 bg-[#304F78] text-3xl font-bold text-white shadow-lg">
                                    {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($profile->pen_name, 0, 1)) }}
                                </div>
                            @endif

                            <div class="min-w-0 flex-1">
                                <label for="photo" class="{{ $labelClass }}">
                                    Unggah Foto Baru
                                </label>

                                <input id="photo" name="photo" type="file"
                                    accept="image/png,image/jpeg,image/webp"
                                    class="{{ $fileClass }}">

                                <p class="mt-2 text-xs text-gray-500">
                                    Format JPG, PNG, atau WEBP. Ukuran maksimal 2 MB.
                                </p>
                            </div>
                        </div>

                        @if ($profile->photo)
                            <label for="remove_photo"
                                class="inline-flex cursor-pointer items-center text-sm text-gray-700">

                                <input id="remove_photo" name="remove_photo" type="checkbox" value="1"
                                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">

                                <span class="ms-2">Hapus foto saat ini</span>
                            </label>
                        @endif

                        @foreach ($errors->get('photo') as $message)
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @endforeach
                    </div>

                    {{-- Nama Pena --}}
                    <div>
                        <label for="pen_name" class="{{ $labelClass }}">
                            Nama Pena
                        </label>

                        <input id="pen_name" name="pen_name" type="text"
                            value="{{ old('pen_name', $profile->pen_name) }}"
                            class="{{ $inputClass }}" required>

                        @foreach ($errors->get('pen_name') as $message)
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @endforeach
                    </div>

                    {{-- Bio --}}
                    <div>
                        <label for="bio" class="{{ $labelClass }}">
                            Bio Singkat
                        </label>

                        <textarea id="bio" name="bio" rows="5"
                            class="{{ $inputClass }}"
                            placeholder="Ceritakan sedikit tentang dirimu sebagai penulis...">{{ old('bio', $profile->bio) }}</textarea>

                        <p class="mt-1.5 text-xs text-black">
                            Tuliskan deskripsi singkat yang akan membantu pembaca mengenalmu.
                        </p>

                        @foreach ($errors->get('bio') as $message)
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @endforeach
                    </div>

                    {{-- Tombol Aksi --}}
                    <div class="flex flex-wrap items-center gap-4 border-t border-white/60 pt-6">

                        <button type="submit"
                            class="inline-flex items-center justify-center rounded-xl border border-white/20 bg-[#1F2937] px-6 py-3 text-xs font-bold uppercase tracking-widest text-white shadow-lg transition-all duration-300 hover:-translate-y-1 hover:bg-[#304F78] hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2">
                            Simpan Perubahan
                        </button>

                        <a href="{{ route('authors.show', ['authorProfile' => $profile->user_id]) }}"
                            class="inline-flex items-center justify-center rounded-xl border border-white/70 bg-white/50 px-6 py-3 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm backdrop-blur-lg transition-all duration-300 hover:-translate-y-0.5 hover:bg-white/80 hover:shadow-md">
                            Lihat Profil Publik
                        </a>

                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
