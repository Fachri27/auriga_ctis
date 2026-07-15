@props([
    'variant' => 'default',
    'size' => 'md'
])

@php
    $variants = [
        'default'      => 'cms-pill-default',
        'new'          => 'cms-pill-danger',
        'investigation'=> 'cms-pill-warn',
        'published'    => 'cms-pill-ok',
        'closed'       => 'cms-pill-default',
        'success'      => 'cms-pill-ok',
        'warning'      => 'cms-pill-warn',
        'danger'       => 'cms-pill-danger',
        'info'         => 'cms-pill-info',
    ];

    $sizes = [
        'sm' => 'text-[10px] px-2 py-0.5',
        'md' => 'text-[11px] px-2.5 py-1',
        'lg' => 'text-xs px-3 py-1.5',
    ];

    $classes = ($variants[$variant] ?? $variants['default']) . ' ' . ($sizes[$size] ?? $sizes['md']) . ' inline-flex items-center gap-1.5 font-semibold rounded-full';
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    <span class="dot"></span>{{ $slot }}
</span>