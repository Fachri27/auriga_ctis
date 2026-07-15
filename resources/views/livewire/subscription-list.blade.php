<div class="max-w-7xl mx-auto px-6 py-6 space-y-4">
    <div class="cms-panel cms-rise" style="animation-delay:.04s">
        <div class="cms-panel-head">
            <div>
                <div class="cms-eyebrow">Langganan</div>
                <h1 class="text-xl font-semibold text-[color:var(--ink)] tracking-tight mt-0.5">Langganan Kasus</h1>
            </div>
            <div class="flex items-center gap-3">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari email..." class="cms-input w-64">
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="cms-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Email</th>
                        <th>Kasus</th>
                        <th>Tanggal</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($subscriptions as $index => $data)
                    <tr wire:key="subscription-{{ $data->id }}">
                        <td class="num">{{ ($subscriptions->currentPage() - 1) * $subscriptions->perPage() + $index + 1 }}</td>
                        <td class="num">{{ $data->email }}</td>
                        <td>
                            @if ($data->case)
                                <a href="{{ route('case.detail', $data->case_id) }}" class="link">{{ $data->case->case_number }}</a>
                            @else
                                <x-internal.badge variant="info">Semua kasus baru</x-internal.badge>
                            @endif
                        </td>
                        <td>{{ $data->created_at->format('d M Y H:i') }}</td>
                        <td class="text-right">
                            <button wire:click="delete({{ $data->id }})"
                                    wire:confirm="Hapus langganan ini?"
                                    class="cms-btn cms-btn-danger">Hapus</button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-12 text-center text-sm text-[color:var(--muted)]">Tidak ada langganan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="cms-panel-body" style="padding:16px 20px">
            {{ $subscriptions->links() }}
        </div>
    </div>
    @if (session('success'))
    <div x-data="{ show: true }" x-show="show" x-transition:enter="transition ease-out duration-500"
        x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-500" x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-2" x-init="setTimeout(() => show = false, 3000)" class="fixed bottom-6 right-6 bg-[color:var(--ok)] text-white px-5 py-3 rounded-lg shadow-lg text-sm">
        {{ session('success') }}
    </div>
    @endif
</div>