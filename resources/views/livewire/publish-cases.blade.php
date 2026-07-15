<div class="flex flex-wrap gap-2">
    @if(isset($case))
        @can('requestPublish', $case)
            <button wire:click="requestPublish({{ $case->id }})" class="cms-btn cms-btn-ghost">
                Request Publish
            </button>
        @endcan

        @can('approvePublish', $case)
            <button wire:click="approveAndPublish({{ $case->id }})" class="cms-btn cms-btn-leaf">
                Approve &amp; Publish
            </button>
        @endcan

        @can('publish', $case)
            <button wire:click="publish({{ $case->id }})" class="cms-btn cms-btn-primary">
                Publish
            </button>
        @endcan

        @can('unpublish', $case)
            <button wire:click="unpublish({{ $case->id }})" class="cms-btn cms-btn-danger">
                Unpublish
            </button>
        @endcan

        @can('publishMap', $case)
            <button wire:click="publishMap({{ $case->id }})" class="cms-btn cms-btn-ghost">
                Publish Map
            </button>
        @endcan
    @endif
</div>