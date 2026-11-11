
@props(['label', 'value', 'href' => null])

@php
    $classes = 'group flex h-32 flex-col justify-between rounded-2xl border border-white/70 bg-white/60 p-5 shadow-lg backdrop-blur-xl transition-all duration-300 hover:-translate-y-1 hover:bg-white/80 hover:shadow-xl';

    $shown = is_numeric($value)
        ? number_format($value, 0, ',', '.')
        : $value;
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        <span class="text-sm font-medium text-gray-600 transition-colors group-hover:text-blue-700">
            {{ $label }}
        </span>

        <span class="text-3xl font-bold text-gray-900">
            {{ $shown }}
        </span>
    </a>
@else
    <div {{ $attributes->merge(['class' => $classes]) }}>
        <span class="text-sm font-medium text-gray-600">
            {{ $label }}
        </span>

        <span class="text-3xl font-bold text-gray-900">
            {{ $shown }}
        </span>
    </div>
@endif
