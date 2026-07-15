<div class="cms-panel cms-rise">
    <div class="cms-panel-head">
        <div>
            <div class="cms-eyebrow">Kolaborasi</div>
            <h3 class="cms-panel-title">Diskusi Tim</h3>
        </div>
    </div>
    <div class="cms-panel-body" style="padding:16px 20px">
        <div class="space-y-3">
            @forelse($discussions as $item)
            <div class="border border-[color:var(--hairline)] rounded-[10px] p-3 bg-[color:var(--paper)]">
                <p class="text-xs font-semibold text-[color:var(--ink)]">{{ $item->name }}</p>
                <p class="text-sm text-[color:var(--ink-2)] mt-1 break-words">{{ $item->message }}</p>
                @if($item->attachments)
                <div class="flex flex-wrap gap-2 mt-2">
                    @foreach(json_decode($item->attachments) as $file)
                    <a href="{{ asset('storage/'.$file) }}" target="_blank" class="inline-block">
                        <img src="{{ asset('storage/'.$file) }}" alt="" class="w-[60px] h-[60px] object-cover rounded-md border border-[color:var(--hairline)]">
                    </a>
                    @endforeach
                </div>
                @endif
            </div>
            @empty
            <p class="py-8 text-sm text-[color:var(--muted)] text-center">Belum ada diskusi.</p>
            @endforelse
        </div>

        <div class="mt-4 pt-4 border-t border-[color:var(--hairline)] space-y-2">
            <input type="text" wire:model="message" class="cms-input w-full" placeholder="Tulis pesan...">
            <input type="file" wire:model="attachments" multiple class="cms-input w-full">
            <div class="flex justify-end">
                <button wire:click="send" class="cms-btn cms-btn-leaf">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12l14-7-7 14-2-5-5-2z" /></svg>
                    Kirim
                </button>
            </div>
        </div>
    </div>
</div>