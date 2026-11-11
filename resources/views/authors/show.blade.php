
<x-catalog-layout :title="$authorProfile->pen_name">
    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            {{-- Notifikasi --}}
            @if (session('status'))
                <div class="rounded-xl border border-green-200 bg-green-50/80 p-4 text-sm text-green-800 shadow-sm backdrop-blur-lg">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('error'))
                <div class="rounded-xl border border-red-200 bg-red-50/80 p-4 text-sm text-red-800 shadow-sm backdrop-blur-lg">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Profil Penulis --}}
            <div class="rounded-3xl border border-white/70 bg-white/55 p-6 shadow-2xl backdrop-blur-2xl sm:p-10">

                <div class="flex flex-col gap-6 sm:flex-row sm:items-center">

                    {{-- Foto Profil --}}
                    @if ($authorProfile->photo)
                        <img src="{{ asset('storage/'.$authorProfile->photo) }}"
                            alt="Foto {{ $authorProfile->pen_name }}"
                            class="h-28 w-28 shrink-0 rounded-full border-4 border-white/80 object-cover shadow-lg">
                    @else
                        <div class="flex h-28 w-28 shrink-0 items-center justify-center rounded-full border-4 border-white/80 bg-[#304F78] text-4xl font-bold text-white shadow-lg">
                            {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($authorProfile->pen_name, 0, 1)) }}
                        </div>
                    @endif

                    {{-- Informasi Penulis --}}
                    <div class="min-w-0 flex-1">
                        <h1 class="text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl">
                            {{ $authorProfile->pen_name }}
                        </h1>

                        <p class="mt-2 text-sm font-medium text-gray-600">
                            {{ $followers }} pengikut
                            <span class="mx-1 text-gray-400">&middot;</span>
                            {{ $books->total() }} buku terbit
                        </p>

                        @if ($authorProfile->bio)
                            <p class="mt-4 whitespace-pre-line text-sm leading-relaxed text-gray-700">
                                {{ $authorProfile->bio }}
                            </p>
                        @endif
                    </div>
                </div>

                {{-- Tombol Aksi --}}
                <div class="mt-7 flex flex-wrap items-center gap-3 border-t border-white/70 pt-6">

                    @guest
                        <a href="{{ route('login') }}"
                            class="inline-flex items-center justify-center rounded-xl border border-white/20 bg-[#1F2937] px-6 py-3 text-xs font-bold uppercase tracking-widest text-white shadow-lg transition-all duration-300 hover:-translate-y-0.5 hover:bg-[#304F78] hover:shadow-xl">
                            Masuk untuk Mengikuti
                        </a>
                    @else
                        @if ($isSelf)
                            <a href="{{ route('author.profile.edit') }}"
                                class="inline-flex items-center justify-center rounded-xl border border-white/20 bg-[#1F2937] px-6 py-3 text-xs font-bold uppercase tracking-widest text-white shadow-lg transition-all duration-300 hover:-translate-y-0.5 hover:bg-[#304F78] hover:shadow-xl">
                                Edit Profil
                            </a>

                        @elseif ($canFollow && $isFollowing)
                            <form method="POST" action="{{ route('authors.unfollow', ['authorProfile' => $authorProfile->user_id]) }}">
                                @csrf
                                @method('DELETE')

                                <button type="submit"
                                    class="inline-flex items-center justify-center rounded-xl border border-white/80 bg-white/60 px-6 py-3 text-xs font-bold uppercase tracking-widest text-gray-700 shadow-sm backdrop-blur-lg transition-all duration-300 hover:-translate-y-0.5 hover:bg-white/90 hover:shadow-md">
                                    Berhenti Mengikuti
                                </button>
                            </form>

                        @elseif ($canFollow)
                            <form method="POST" action="{{ route('authors.follow', ['authorProfile' => $authorProfile->user_id]) }}">
                                @csrf

                                <button type="submit"
                                    class="inline-flex items-center justify-center rounded-xl border border-white/20 bg-[#1F2937] px-6 py-3 text-xs font-bold uppercase tracking-widest text-white shadow-lg transition-all duration-300 hover:-translate-y-0.5 hover:bg-[#304F78] hover:shadow-xl">
                                    Ikuti Penulis
                                </button>
                            </form>
                        @endif
                    @endguest

                </div>
            </div>

            {{-- Daftar Buku --}}
            <div class="space-y-5">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h2 class="text-xl font-bold text-white sm:text-2xl">
                        Buku oleh {{ $authorProfile->pen_name }}
                    </h2>

                    <span class="text-sm text-gray-500">
                        {{ $books->total() }} buku
                    </span>
                </div>

                @if ($books->isEmpty())
                    <div class="rounded-2xl border border-white/70 bg-white/55 p-10 text-center shadow-lg backdrop-blur-xl">
                        <h3 class="text-lg font-semibold text-gray-800">
                            Belum Ada Buku
                        </h3>
                        <p class="mt-2 text-sm text-gray-600">
                            Penulis ini belum punya buku yang terbit.
                        </p>
                    </div>
                @else
                    <div class="grid grid-cols-2 gap-4 sm:gap-6 md:grid-cols-3 lg:grid-cols-4">
                        @foreach ($books as $book)
                            @include('catalog._book-card', ['book' => $book])
                        @endforeach
                    </div>

                    <div class="pt-3">
                        {{ $books->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-catalog-layout>
