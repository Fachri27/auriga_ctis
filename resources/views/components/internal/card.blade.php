@props([
    'title' => null,
    'subtitle' => null,
    'eyebrow' => null,
    'actions' => null,
])

<div {{ $attributes->merge(['class' => 'cms-panel']) }}>
    @if($title || $subtitle || $actions || $eyebrow)
        <div class="cms-panel-head">
            <div>
                @if($eyebrow)
                    <div class="cms-eyebrow">{{ $eyebrow }}</div>
                @endif
                @if($title)
                    <h3 class="cms-panel-title {{ $eyebrow ? 'mt-1' : '' }}">{{ $title }}</h3>
                @endif
                @if($subtitle)
                    <p class="cms-panel-sub">{{ $subtitle }}</p>
                @endif
            </div>
            @if($actions)
                <div class="flex items-center gap-2">
                    {{ $actions }}
                </div>
            @endif
        </div>
    @endif
    <div class="cms-panel-body">
        {{ $slot }}
    </div>
</div>