
<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-white leading-tight">
                {{ __('Profile') }}
            </h2>
            <p class="mt-1 text-sm text-white/70">
                Kelola informasi akun dan keamanan Anda.
            </p>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-5xl space-y-6 px-4 sm:px-6 lg:px-8">

            <!-- Profile Information -->
            <div class="rounded-3xl border border-white/70 bg-white/55 p-6 shadow-xl backdrop-blur-2xl sm:p-8">
                <div class="max-w-3xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <!-- Update Password -->
            <div class="rounded-3xl border border-white/70 bg-white/55 p-6 shadow-xl backdrop-blur-2xl sm:p-8">
                <div class="max-w-3xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <!-- Delete Account -->
            <div class="rounded-3xl border border-white/70 bg-white/55 p-6 shadow-xl backdrop-blur-2xl sm:p-8">
                <div class="max-w-3xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
