@props([
    'variant' => 'default',
    'size' => 'md'
])

@php
    $variants = [
        'default' => 'bg-gray-100 text-gray-800',
        'new' => 'bg-red-100 text-red-800',
        'investigation' => 'bg-yellow-100 text-yellow-800',
        'published' => 'bg-green-100 text-green-800',
        'closed' => 'bg-gray-100 text-gray-600',
        'success' => 'bg-green-100 text-green-800',
        'warning' => 'bg-yellow-100 text-yellow-800',
        'danger' => 'bg-red-100 text-red-800',
        'info' => 'bg-blue-100 text-blue-800',
    ];

    $sizes = [
        'sm' => 'px-2 py-0.5 text-xs',
        'md' => 'px-2.5 py-1 text-xs',
        'lg' => 'px-3 py-1.5 text-sm',
    ];

    $classes = ($variants[$variant] ?? $variants['default']) . ' ' . ($sizes[$size] ?? $sizes['md']) . ' inline-flex items-center font-medium rounded-full';
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</span>








