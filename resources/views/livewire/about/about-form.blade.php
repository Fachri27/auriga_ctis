<div x-data="{ lang: 'id' }">
    <div class="max-w-7xl mx-auto px-6 py-6 space-y-4">

        <div class="cms-panel cms-rise" style="animation-delay:.04s">
            <div class="cms-panel-head">
                <div>
                    <div class="cms-eyebrow">ABOUT</div>
                    <h1 class="text-xl font-semibold text-[color:var(--ink)] tracking-tight mt-0.5">Edit About Page</h1>
                </div>
            </div>
            <div class="cms-panel-body" style="padding:16px 20px">

                <form wire:submit.prevent="save">
                    <div class="grid grid-cols-12 gap-4">

                        <div class="col-span-12 lg:col-span-4">
                            <div class="cms-panel sticky top-6">
                                <div class="cms-panel-head">
                                    <div class="cms-panel-title">Bahasa</div>
                                </div>
                                <div class="cms-panel-body space-y-4" style="padding:16px 20px">

                                    <div>
                                        <label class="block text-xs font-medium text-[color:var(--muted)] mb-1.5">Bahasa</label>
                                        <select x-model="lang" class="cms-input w-full">
                                            <option value="id">Indonesia</option>
                                            <option value="en">English</option>
                                        </select>
                                    </div>

                                    <div x-show="lang === 'id'">
                                        <label class="block text-xs font-medium text-[color:var(--muted)] mb-1.5">Title (ID)</label>
                                        <input type="text" wire:model="title_id" class="cms-input w-full">
                                        @error('title_id') <p class="text-sm text-[color:var(--danger)]">{{ $message }}</p> @enderror
                                    </div>

                                    <div x-show="lang === 'en'">
                                        <label class="block text-xs font-medium text-[color:var(--muted)] mb-1.5">Title (EN)</label>
                                        <input type="text" wire:model="title_en" class="cms-input w-full">
                                        @error('title_en') <p class="text-sm text-[color:var(--danger)]">{{ $message }}</p> @enderror
                                    </div>

                                </div>
                            </div>
                        </div>

                        <div class="col-span-12 lg:col-span-8 space-y-3">

                            {{-- Content --}}
                            <div class="cms-panel">
                                <div class="cms-panel-head">
                                    <div class="cms-panel-title">Content</div>
                                </div>
                                <div class="cms-panel-body" style="padding:16px 20px">
                                    <div x-show="lang === 'id'">
                                        @include('front.components.tinymce-about-content-id')
                                    </div>
                                    <div x-show="lang === 'en'">
                                        @include('front.components.tinymce-about-content-en')
                                    </div>
                                </div>
                            </div>

                            {{-- Vision --}}
                            <div class="cms-panel">
                                <div class="cms-panel-head">
                                    <div class="cms-panel-title">Vision</div>
                                </div>
                                <div class="cms-panel-body" style="padding:16px 20px">
                                    <div x-show="lang === 'id'">
                                        @include('front.components.tinymce-about-vision-id')
                                    </div>
                                    <div x-show="lang === 'en'">
                                        @include('front.components.tinymce-about-vision-en')
                                    </div>
                                </div>
                            </div>

                            {{-- Mission --}}
                            <div class="cms-panel">
                                <div class="cms-panel-head">
                                    <div class="cms-panel-title">Mission</div>
                                </div>
                                <div class="cms-panel-body" style="padding:16px 20px">
                                    <div x-show="lang === 'id'">
                                        @include('front.components.tinymce-about-mission-id')
                                    </div>
                                    <div x-show="lang === 'en'">
                                        @include('front.components.tinymce-about-mission-en')
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="col-span-12 sticky bottom-0 bg-[color:var(--surface)] border-t border-[color:var(--hairline)] py-3 flex justify-end">
                            <button type="submit" wire:loading.attr="disabled" class="cms-btn cms-btn-leaf">
                                <svg wire:loading wire:target="save" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                                </svg>
                                <span wire:loading.remove wire:target="save">Simpan</span>
                                <span wire:loading wire:target="save">Saving...</span>
                            </button>
                        </div>

                    </div>
                </form>

            </div>
        </div>

    </div>

    @if (session('success'))
        <div x-data="{ show: true }" x-show="show" x-transition:enter="transition ease-out duration-500"
            x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-500" x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-2" x-init="setTimeout(() => show = false, 3000)"
            class="fixed bottom-6 right-6 bg-[color:var(--leaf-deep)] text-white px-6 py-3 rounded-lg shadow-lg">
            {{ session('success') }}
        </div>
    @endif
</div>