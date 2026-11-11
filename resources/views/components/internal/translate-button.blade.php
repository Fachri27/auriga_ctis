{{-- Tombol auto translate ID → EN untuk komponen Livewire yang memakai App\Livewire\Concerns\AutoTranslates --}}
<button type="button"
    wire:click="autoTranslate"
    wire:confirm="Field English akan diisi (dan ditimpa bila sudah ada) dengan terjemahan DeepL dari versi Indonesia. Lanjutkan?"
    wire:loading.attr="disabled"
    wire:target="autoTranslate"
    {{ $attributes->merge(['class' => 'cms-btn cms-btn-ghost inline-flex items-center gap-2 disabled:opacity-60 disabled:cursor-wait']) }}>
    <svg wire:loading.remove wire:target="autoTranslate" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129" />
    </svg>
    <svg wire:loading wire:target="autoTranslate" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24" aria-hidden="true">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
    </svg>
    <span wire:loading.remove wire:target="autoTranslate">Terjemahkan ID → EN</span>
    <span wire:loading wire:target="autoTranslate">Menerjemahkan…</span>
</button>
