@php
    $role = auth()->user()->role;
    $homeRoute = match ($role) {
        'reader' => 'reader.dashboard',
        'author' => 'author.dashboard',
        'admin' => 'admin.dashboard',
        default => null,
    };
@endphp

<nav x-data="{ open: false }" class="lc-glass-nav relative z-50">
    <!-- Primary Navigation Menu -->
    <div class="max-w-[1440px] mx-auto px-5 sm:px-8 lg:px-10">
        <div class="flex justify-between h-24">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('catalog.index') }}">
                        <x-brand />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('catalog.index')" :active="request()->routeIs('catalog.*', 'authors.*')">
                        {{ __('Katalog') }}
                    </x-nav-link>

                    @if (in_array($role, ['reader', 'author'], true))
                        <x-nav-link :href="route('library.index')" :active="request()->routeIs('library.*')">
                            {{ __('Pustaka Saya') }}
                        </x-nav-link>
                    @endif

                    @if ($homeRoute)
                        <x-nav-link :href="route($homeRoute)" :active="request()->routeIs($homeRoute)">
                            {{ __('Dashboard') }}
                        </x-nav-link>
                    @endif

                    @if ($role === 'author')
                        <x-nav-link :href="route('author.buku.index')" :active="request()->routeIs('author.buku.*')">
                            {{ __('Kelola Buku') }}
                        </x-nav-link>
                    @endif

                    @if ($role === 'reader')
                        <x-nav-link :href="route('author.register')" :active="request()->routeIs('author.register')">
                            {{ __('Jadi Penulis') }}
                        </x-nav-link>
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>
                        @if ($role === 'author')
                         <x-dropdown-link :href="route('author.profile.edit')">
                         {{ __('Edit Profil Penulis') }}
                         </x-dropdown-link>
                        @endif
                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
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

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden bg-white/90 backdrop-blur-xl border-t border-gray-100 shadow-lg">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('catalog.index')" :active="request()->routeIs('catalog.*', 'authors.*')">
                {{ __('Katalog') }}
            </x-responsive-nav-link>

            @if (in_array($role, ['reader', 'author'], true))
                <x-responsive-nav-link :href="route('library.index')" :active="request()->routeIs('library.*')">
                    {{ __('Pustaka Saya') }}
                </x-responsive-nav-link>
            @endif

            @if ($homeRoute)
                <x-responsive-nav-link :href="route($homeRoute)" :active="request()->routeIs($homeRoute)">
                    {{ __('Dashboard') }}
                </x-responsive-nav-link>
            @endif

            @if ($role === 'author')
                <x-responsive-nav-link :href="route('author.buku.index')" :active="request()->routeIs('author.buku.*')">
                    {{ __('Kelola Buku') }}
                </x-responsive-nav-link>
            @endif

            @if ($role === 'reader')
                <x-responsive-nav-link :href="route('author.register')" :active="request()->routeIs('author.register')">
                    {{ __('Jadi Penulis') }}
                </x-responsive-nav-link>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>
                @if ($role === 'author')
                <x-responsive-nav-link :href="route('author.profile.edit')">
                {{ __('Edit Profil Penulis') }}
                </x-responsive-nav-link>
                @endif
                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
