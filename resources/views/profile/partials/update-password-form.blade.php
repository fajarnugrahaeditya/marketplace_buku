
<section class="space-y-6">
    <header class="space-y-1">
        <h2 class="text-lg font-semibold text-gray-900">
            {{ __('Update Password') }}
        </h2>

        <p class="text-sm text-gray-600">
            {{ __('Ensure your account is using a long, random password to stay secure.') }}
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="space-y-6">
        @csrf
        @method('put')

        <!-- Current Password -->
        <div>
            <x-input-label
                for="update_password_current_password"
                :value="__('Current Password')"
                class="text-sm font-semibold text-gray-800"
            />

            <x-text-input
                id="update_password_current_password"
                name="current_password"
                type="password"
                class="lc-glass-input mt-2 block w-full rounded-xl border border-white/80 bg-white/70 text-gray-900 shadow-inner backdrop-blur-xl placeholder:text-gray-400 focus:border-blue-400 focus:bg-white/90 focus:ring-2 focus:ring-blue-200 transition-all duration-200"
                autocomplete="current-password"
            />

            <x-input-error
                :messages="$errors->updatePassword->get('current_password')"
                class="mt-2"
            />
        </div>

        <!-- New Password -->
        <div>
            <x-input-label
                for="update_password_password"
                :value="__('New Password')"
                class="text-sm font-semibold text-gray-800"
            />

            <x-text-input
                id="update_password_password"
                name="password"
                type="password"
                class="lc-glass-input mt-2 block w-full rounded-xl border border-white/80 bg-white/70 text-gray-900 shadow-inner backdrop-blur-xl placeholder:text-gray-400 focus:border-blue-400 focus:bg-white/90 focus:ring-2 focus:ring-blue-200 transition-all duration-200"
                autocomplete="new-password"
            />

            <x-input-error
                :messages="$errors->updatePassword->get('password')"
                class="mt-2"
            />
        </div>

        <!-- Confirm Password -->
        <div>
            <x-input-label
                for="update_password_password_confirmation"
                :value="__('Confirm Password')"
                class="text-sm font-semibold text-gray-800"
            />

            <x-text-input
                id="update_password_password_confirmation"
                name="password_confirmation"
                type="password"
                class="lc-glass-input mt-2 block w-full rounded-xl border border-white/80 bg-white/70 text-gray-900 shadow-inner backdrop-blur-xl placeholder:text-gray-400 focus:border-blue-400 focus:bg-white/90 focus:ring-2 focus:ring-blue-200 transition-all duration-200"
                autocomplete="new-password"
            />

            <x-input-error
                :messages="$errors->updatePassword->get('password_confirmation')"
                class="mt-2"
            />
        </div>

        <!-- Actions -->
        <div class="flex flex-wrap items-center gap-4 pt-2">
            <button
                type="submit"
                class="inline-flex items-center justify-center rounded-xl border border-white/20 bg-[#1F2937] px-6 py-3 text-sm font-semibold text-white shadow-lg transition-all duration-300 hover:-translate-y-0.5 hover:bg-[#304F78] hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2"
            >
                {{ __('Save') }}
            </button>

            @if (session('status') === 'password-updated')
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
