@props([
    'title' => null,
    'subtitle' => null,
    'actions' => null,
])

<div {{ $attributes->merge(['class' => 'bg-white rounded-lg border border-gray-200 shadow-sm']) }}>
    @if($title || $subtitle || $actions)
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <div>
                @if($title)
                    <h3 class="text-lg font-semibold text-gray-900">{{ $title }}</h3>
                @endif
                @if($subtitle)
                    <p class="mt-1 text-sm text-gray-600">{{ $subtitle }}</p>
                @endif
            </div>
            @if($actions)
                <div class="flex items-center gap-2">
                    {{ $actions }}
                </div>
            @endif
        </div>
    @endif
    <div class="p-6">
        {{ $slot }}
    </div>
</div>









