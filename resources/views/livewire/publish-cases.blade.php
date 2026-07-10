<div class="flex flex-wrap gap-2">
    @if(isset($case))
        @can('requestPublish', $case)
            <button wire:click="requestPublish({{ $case->id }})"
                class="px-4 py-2 text-sm font-medium text-blue-700 bg-blue-100 rounded-lg hover:bg-blue-200 transition-colors">
                Request Publish
            </button>
        @endcan

        @can('approvePublish', $case)
            <button wire:click="approveAndPublish({{ $case->id }})"
                class="px-4 py-2 text-sm font-medium text-green-700 bg-green-100 rounded-lg hover:bg-green-200 transition-colors">
                Approve &amp; Publish
            </button>
        @endcan

        @can('publish', $case)
            <button wire:click="publish({{ $case->id }})"
                class="px-4 py-2 text-sm font-medium text-white bg-black rounded-lg hover:bg-gray-800 transition-colors">
                Publish
            </button>
        @endcan

        @can('unpublish', $case)
            <button wire:click="unpublish({{ $case->id }})"
                class="px-4 py-2 text-sm font-medium text-red-700 bg-red-100 rounded-lg hover:bg-red-200 transition-colors">
                Unpublish
            </button>
        @endcan

        @can('publishMap', $case)
            <button wire:click="publishMap({{ $case->id }})"
                class="px-4 py-2 text-sm font-medium text-indigo-700 bg-indigo-100 rounded-lg hover:bg-indigo-200 transition-colors">
                Publish Map
            </button>
        @endcan
    @endif
</div>