<div>
    <div class="max-w-7xl mx-auto px-6 py-6 space-y-4">

        <div class="flex justify-between items-center border-b border-[color:var(--hairline)] pb-3">
            <div>
                <div class="cms-eyebrow">PROCESS</div>
                <h1 class="text-xl font-semibold text-[color:var(--ink)] tracking-tight mt-0.5">Processes</h1>
            </div>
            <a href="{{ route('process.create') }}" class="cms-btn cms-btn-leaf">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                New
            </a>
        </div>

        <div class="flex flex-wrap gap-3">
            <input wire:model.live="search" type="text" placeholder="Search processes..."
                class="cms-input w-full sm:w-64">
            <select wire:model="categoryFilter" class="cms-input">
                <option value="">All categories</option>
                @foreach($categories as $c)
                <option value="{{ $c->id }}">{{ $c->translation('id')?->name ?? $c->slug }}</option>
                @endforeach
            </select>
        </div>

        <div class="cms-panel cms-rise" style="animation-delay:.04s">
            <div class="overflow-x-auto">
                <table class="cms-table w-full">
                    <thead>
                        <tr>
                            <th class="text-left">Category</th>
                            <th class="text-left">Name (ID)</th>
                            <th class="text-left">Name (EN)</th>
                            <th class="text-left">Order</th>
                            <th class="text-left">Active</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($processes as $proc)
                        <tr wire:key="proc-{{ $proc->id }}">
                            <td class="font-semibold text-[color:var(--ink)]">
                                {{ $proc->category?->translation('id')?->name ?? $proc->category?->slug }}
                            </td>
                            <td class="text-[color:var(--ink-2)]">
                                {{ $proc->translation('id')?->name ?? '-' }}
                            </td>
                            <td class="text-[color:var(--muted)]">
                                {{ $proc->translation('en')?->name ?? '-' }}
                            </td>
                            <td class="num">{{ $proc->order_no }}</td>
                            <td>
                                @if($proc->is_active)
                                    <x-internal.badge variant="ok">Active</x-internal.badge>
                                @else
                                    <x-internal.badge variant="default">Inactive</x-internal.badge>
                                @endif
                            </td>
                            <td class="text-right">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('process.edit', $proc->id) }}" class="cms-btn cms-btn-ghost" style="padding:6px 12px">Edit</a>
                                    <button wire:click="delete({{ $proc->id }})"
                                        onclick="return confirm('Delete this process?')"
                                        class="cms-btn cms-btn-danger" style="padding:6px 12px">Delete</button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-4 border-t border-[color:var(--hairline)]">
                {{ $processes->links() }}
            </div>
        </div>

    </div>
</div>