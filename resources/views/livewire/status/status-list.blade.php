<div>
    <div class="max-w-7xl mx-auto px-6 py-6 space-y-4">

        <div class="cms-panel cms-rise" style="animation-delay:.04s">
            <div class="cms-panel-head">
                <div>
                    <div class="cms-eyebrow">Configuration</div>
                    <h1 class="text-xl font-semibold text-[color:var(--ink)] tracking-tight mt-0.5">Statuses</h1>
                </div>
                <div class="flex items-center gap-3">
                    <input class="cms-input w-64" placeholder="Search case number..." wire:model.live="search">
                    <button class="cms-btn cms-btn-primary" @click="$dispatch('open-status-modal')">
                        + New Status
                    </button>
                </div>
            </div>

            <div class="cms-panel-body" style="padding:0">
                <div class="overflow-x-auto">
                    <table class="cms-table w-full">
                        <thead>
                            <tr>
                                <th class="text-left">Key</th>
                                <th class="text-left">Name</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($statuses as $s)
                            <tr wire:key="status-{{ $s->id }}">
                                <td class="num">{{ $s->key }}</td>
                                <td>{{ $s->name }}</td>
                                <td class="text-right">
                                    <button class="link"
                                        @click="$dispatch('edit-status-modal', { statusId: {{ $s->id }} })">
                                        Edit
                                    </button>

                                    <button wire:click="delete({{ $s->id }})"
                                        onclick="confirm('Delete status?') || event.stopImmediatePropagation()"
                                        class="cms-btn cms-btn-danger ml-3">
                                        Delete
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="px-4 py-3 border-t border-[color:var(--hairline)]">
                    {{ $statuses->links() }}
                </div>
            </div>
        </div>

        <livewire:status.status-modal>

    </div>
</div>