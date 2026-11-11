
@props(['variant' => 'inline'])

@if ($variant === 'stacked')
    {{-- Logo untuk halaman login, daftar, dan lupa password --}}
    <div {{ $attributes->merge(['class' => 'flex flex-col items-center text-center']) }}>
        <x-application-logo class="block h-20 w-auto max-w-full" />
        <span class="mt-3 text-2xl font-semibold text-gray-800">
            {{ config('app.name') }}
        </span>
        <span class="text-sm text-gray-500">
            e-commerce buku digital
        </span>
    </div>
@else
    {{-- Logo untuk navbar --}}
    <div {{ $attributes->merge(['class' => 'flex items-center gap-3']) }}>
        <x-application-logo class="block h-10 w-10 shrink-0" />
        <div class="block leading-tight">
            <div class="text-lg font-semibold text-gray-800">
                {{ config('app.name') }}
            </div>
            <div class="text-xs text-gray-500">
                e-commerce buku digital
            </div>
        </div>
    </div>
@endif
