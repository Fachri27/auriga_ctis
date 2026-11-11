<div class="max-w-7xl mx-auto px-6 py-6 space-y-4">
    <div class="cms-panel cms-rise" style="animation-delay:.04s">

        {{-- Header --}}
        <div class="cms-panel-head">
            <div>
                <div class="cms-eyebrow">CMS / ARTIKEL</div>
                <h1 class="text-xl font-semibold text-[color:var(--ink)] tracking-tight mt-0.5">Daftar Halaman</h1>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" wire:click="pullNews" wire:loading.attr="disabled" wire:target="pullNews" class="cms-btn cms-btn-ghost disabled:opacity-60"
                    title="Tarik berita penegakan hukum lingkungan & SDA dari Google News dan RSS media (otomatis tiap jam)">
                    <span wire:loading.remove wire:target="pullNews">Tarik berita sekarang</span>
                    <span wire:loading wire:target="pullNews">Menarik berita…</span>
                </button>
                <a href="{{ route('artikel.create') }}">
                    <button class="cms-btn cms-btn-primary">
                        + Create
                    </button>
                </a>
            </div>
        </div>

        {{-- Filters --}}
        <div class="cms-panel-body flex flex-wrap gap-4" style="padding:16px 20px">
            <input type="text" wire:model.live.debounce.100ms="search" placeholder="Search..." class="cms-input">
            <select id="statusFilter" wire:model.live.debounce.100ms="status" class="cms-input">
                <option value="">Semua Status</option>
                <option value="draft">Draft</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
            <select id="userFilter" wire:model.live.debounce.100ms="type" class="cms-input">
                <option value="">Semua Type</option>
                <option value="internal">Internal (Analisis Auriga)</option>
                <option value="eksternal">Eksternal (Berita)</option>
            </select>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table id="pageTable" class="cms-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Published-At</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Author</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pages as $data)
                    @php
                        $idTranslation = $data->translation->firstWhere('locale', 'id')->title ?? '-';
                        $idContent = $data->translation->firstWhere('locale', 'id')->excerpt ?? '-';
                    @endphp
                    <tr wire:key="artikel-{{ $data->id }}">
                        <td class="link max-w-xs truncate">{{ $idTranslation }}</td>
                        <td>
                            @if ($data->type === 'internal')
                            <x-internal.badge variant="ok">internal</x-internal.badge>
                            @else
                            <x-internal.badge variant="default">eksternal</x-internal.badge>
                            @if ($data->external_id)
                                <div class="text-[10px] text-[color:var(--muted)] mt-0.5">berita otomatis</div>
                            @endif
                            @endif
                        </td>
                        <td class="num">{{ $data->published_at }}</td>
                        <td class="max-w-[200px] truncate">{{ strip_tags($idContent) }}</td>
                        <td>
                            @if ($data->status === 'active')
                            <x-internal.badge variant="ok">active</x-internal.badge>
                            @elseif ($data->status === 'draft')
                            <x-internal.badge variant="warn">draft</x-internal.badge>
                            @else
                            <x-internal.badge variant="default">inactive</x-internal.badge>
                            @endif
                            {{-- Halaman publik hanya menampilkan berita bergambar (config news.require_image). --}}
                            @if ($data->type === 'eksternal' && blank($data->image) && config('news.require_image', true))
                            <x-internal.badge variant="warning" title="Tampil di situs setelah gambarnya ditemukan">menunggu gambar</x-internal.badge>
                            @endif
                        </td>
                        <td>{{ $data->type === 'eksternal' ? ($data->source_name ?? '-') : ($data->user?->name ?? '-') }}</td>
                        <td class="text-right">
                            <div class="flex justify-end gap-2">
                                <button wire:click="toggleActive({{ $data->id }})" class="cms-btn cms-btn-ghost">
                                    {{ $data->status === 'active' ? 'Sembunyikan' : 'Tampilkan' }}
                                </button>
                                <a href="{{ route('artikel.edit', $data->id) }}"
                                    class="cms-btn cms-btn-ghost">Edit</a>
                                <button wire:click='delete({{ $data->id }})'
                                    class="cms-btn cms-btn-danger">delete</button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-sm text-[color:var(--muted)]">No pages found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="cms-panel-body" style="padding:16px 20px">
            {{ $pages->links() }}
        </div>
    </div>

    @if (session('success'))
        <div x-data="{ show: true }" x-show="show" x-transition:enter="transition ease-out duration-500"
            x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-500" x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-2" x-init="setTimeout(() => show = false, 3000)"
            class="fixed bottom-6 right-6 bg-[color:var(--ok)] text-white px-5 py-3 rounded-xl shadow-lg text-sm font-medium">
            {{ session('success') }}
        </div>
    @endif
</div>