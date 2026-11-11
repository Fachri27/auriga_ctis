{{-- Gambar cadangan kartu artikel/berita yang tidak punya foto (juga tampil bila foto gagal dimuat) --}}
<div class="absolute inset-0 flex flex-col items-center justify-center gap-2 text-white"
    style="background:linear-gradient(135deg,#264c16 0%,#0B1E07 100%)">
    <svg class="w-10 h-10 text-[#9BDB4D]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 01-2.25 2.25M16.5 7.5V18a2.25 2.25 0 002.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 002.25 2.25h13.5M6 7.5h3v3H6v-3z" />
    </svg>
    <span class="font-data uppercase tracking-widest text-[10px] text-[#9BDB4D] px-4 text-center">{{ $label }}</span>
</div>
