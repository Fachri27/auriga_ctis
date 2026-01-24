<div>
    {{-- Publish flow buttons; expects $case to be provided by parent --}}
    @if(isset($case))
    @can('requestPublish', $case)
    <button wire:click="requestPublish({{ $case->id }})" class="btn btn-outline-primary">Request Publish</button>
    @endcan

    @can('approvePublish', $case)
    <button wire:click="approveAndPublish({{ $case->id }})" class="btn btn-success">Approve & Publish</button>
    @endcan

    @can('publish', $case)
    <button wire:click="publish({{ $case->id }})" class="btn btn-primary">Publish</button>
    @endcan

    @can('unpublish', $case)
    <button wire:click="unpublish({{ $case->id }})" class="btn btn-warning">Unpublish</button>
    @endcan

    @can('publishMap', $case)
    <button wire:click="publishMap({{ $case->id }})" class="btn btn-info">Publish Map</button>
    @endcan
    @endif

    {{-- LEGACY: simple Publish button (kept for fallback)
    @if(isset($case))
    @can('publish', $case)
    <button wire:click="publish({{ $case->id }})" class="btn btn-primary">Publish</button>
    @endcan
    @endif
    --}}
</div>