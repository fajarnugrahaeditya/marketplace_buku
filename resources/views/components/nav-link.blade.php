@props(['active'])

@php
    $isActive = $active ?? false;
    $classes = $isActive ? 'lc-nav-link lc-nav-link--active' : 'lc-nav-link';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }} @if ($isActive) aria-current="page" @endif>
    {{ $slot }}
</a>
