
<section class="space-y-6">
    <header class="space-y-1">
        <h2 class="text-lg font-semibold text-gray-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="text-sm text-gray-600">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="space-y-6">
        @csrf
        @method('patch')

        <!-- Name -->
        <div>
            <x-input-label
                for="name"
                :value="__('Name')"
                class="text-sm font-semibold text-gray-800"
            />

            <x-text-input
                id="name"
                name="name"
                type="text"
                class="lc-glass-input mt-2 block w-full rounded-xl border border-white/80 bg-white/70 text-gray-900 shadow-inner backdrop-blur-xl placeholder:text-gray-400 focus:border-blue-400 focus:bg-white/90 focus:ring-2 focus:ring-blue-200 transition-all duration-200"
                :value="old('name', $user->name)"
                required
                autofocus
                autocomplete="name"
            />

            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <!-- Email -->
        <div>
            <x-input-label
                for="email"
                :value="__('Email')"
                class="text-sm font-semibold text-gray-800"
            />

            <x-text-input
                id="email"
                name="email"
                type="email"
                class="lc-glass-input mt-2 block w-full rounded-xl border border-white/80 bg-white/70 text-gray-900 shadow-inner backdrop-blur-xl placeholder:text-gray-400 focus:border-blue-400 focus:bg-white/90 focus:ring-2 focus:ring-blue-200 transition-all duration-200"
                :value="old('email', $user->email)"
                required
                autocomplete="username"
            />

            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-4 rounded-xl border border-amber-200/80 bg-amber-50/70 p-4 backdrop-blur-lg">
                    <p class="text-sm text-gray-700">
                        {{ __('Your email address is unverified.') }}
                    </p>

                    <button
                        form="send-verification"
                        class="mt-2 text-sm font-medium text-blue-700 underline transition-colors hover:text-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2 rounded-md"
                    >
                        {{ __('Click here to re-send the verification email.') }}
                    </button>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-3 text-sm font-medium text-green-700">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <!-- Actions -->
        <div class="flex flex-wrap items-center gap-4 pt-2">
            <button
                type="submit"
                class="inline-flex items-center justify-center rounded-xl border border-white/20 bg-[#1F2937] px-6 py-3 text-sm font-semibold text-white shadow-lg transition-all duration-300 hover:-translate-y-0.5 hover:bg-[#304F78] hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2"
            >
                {{ __('Save') }}
            </button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm font-medium text-green-700"
                >
                    {{ __('Saved.') }}
                </p>
            @endif
        </div>
    </form>
</section>
