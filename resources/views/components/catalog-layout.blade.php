@props(['title' => null])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ? $title.' - ' : '' }}{{ config('app.name', 'Laravel') }}</title>
        
        <link rel="icon" type="image/png"
             href="{{ asset('images/logo_literacommerce.png') }}?v=2">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen lc-page">
            @auth
                {{-- Pengguna yang sudah login memakai navbar yang sama dengan halaman dashboard --}}
                @include('layouts.navigation')
            @else
                {{-- Navbar tamu: logo, Katalog, Masuk, Daftar --}}
                <nav x-data="{ open: false }" class="lc-glass-nav relative z-50">
                    <div class="max-w-[1440px] mx-auto px-5 sm:px-8 lg:px-10">
                        <div class="flex justify-between h-24">
                            <div class="flex">
                                <div class="shrink-0 flex items-center">
                                    <a href="{{ route('catalog.index') }}">
                                        <x-brand />
                                    </a>
                                </div>

                                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                                    <x-nav-link :href="route('catalog.index')" :active="request()->routeIs('catalog.*', 'authors.*')">
                                        {{ __('Katalog') }}
                                    </x-nav-link>
                                </div>
                            </div>

                            <div class="hidden sm:flex sm:items-center sm:ms-6 gap-4 text-sm">
                                <a href="{{ route('login') }}" class="text-gray-600 hover:text-gray-900">Masuk</a>
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="text-gray-600 hover:text-gray-900">Daftar</a>
                                @endif
                            </div>

                            <div class="-me-2 flex items-center sm:hidden">
                                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden bg-white/90 backdrop-blur-xl border-t border-gray-100 shadow-lg">
                        <div class="pt-2 pb-3 space-y-1">
                            <x-responsive-nav-link :href="route('catalog.index')" :active="request()->routeIs('catalog.*', 'authors.*')">
                                {{ __('Katalog') }}
                            </x-responsive-nav-link>
                            <x-responsive-nav-link :href="route('login')">
                                {{ __('Masuk') }}
                            </x-responsive-nav-link>
                            @if (Route::has('register'))
                                <x-responsive-nav-link :href="route('register')">
                                    {{ __('Daftar') }}
                                </x-responsive-nav-link>
                            @endif
                        </div>
                    </div>
                </nav>
            @endauth

            <main>
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
