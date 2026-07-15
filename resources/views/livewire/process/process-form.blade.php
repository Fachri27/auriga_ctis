<div>
    <div x-data="{ locale: 'id' }" class="max-w-5xl mx-auto px-6 py-6 space-y-4">

        <!-- Header -->
        <div class="flex justify-between items-center border-b border-[color:var(--hairline)] pb-3">
            <div>
                <div class="cms-eyebrow">PROCESS</div>
                <h1 class="text-xl font-semibold text-[color:var(--ink)] tracking-tight mt-0.5">Process Form</h1>
            </div>
            <button wire:click="save" class="cms-btn cms-btn-leaf">
                Save
            </button>
        </div>

        <!-- Card -->
        <div class="cms-panel cms-rise" style="animation-delay:.04s">
            <div class="cms-panel-body" style="padding:16px 20px">

                <!-- 2 Columns -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <!-- Category -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-medium text-[color:var(--muted)] mb-1.5">Category</label>
                        <select wire:model="category_id" class="cms-input w-full">
                            <option value="">Select category...</option>

                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">
                                {{ $cat->translation('id')?->name ?? $cat->slug }}
                            </option>
                            @endforeach
                        </select>
                        @error('category_id') <p class="text-sm text-[color:var(--danger)]">{{ $message }}</p> @enderror
                    </div>

                    <!-- Order Number -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-medium text-[color:var(--muted)] mb-1.5">Order Number</label>
                        <input type="number" min="1" wire:model="order_no" class="cms-input w-full" />
                        @error('order_no') <p class="text-sm text-[color:var(--danger)]">{{ $message }}</p> @enderror
                    </div>

                    <!-- Active Switch -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-medium text-[color:var(--muted)] mb-1.5">Active</label>
                        <label class="flex items-center gap-3 cursor-pointer select-none">
                            <input type="checkbox" wire:model="is_active"
                                class="h-5 w-5 rounded border-[color:var(--hairline-2)] text-[color:var(--leaf-deep)] focus:ring-[color:var(--leaf)]">
                            <span class="text-sm text-[color:var(--ink-2)]">Enable this process</span>
                        </label>
                    </div>

                </div>

                <!-- Language Switch Tabs -->
                <div class="flex gap-3 border-b border-[color:var(--hairline)] pb-2 mt-2">
                    <button @click="locale='id'" :class="locale==='id'
                        ? 'border-[color:var(--ink)] text-[color:var(--ink)]'
                        : 'border-transparent text-[color:var(--muted)]'"
                        class="px-4 py-1.5 border-b-2 text-sm font-medium transition">
                        Indonesia
                    </button>

                    <button @click="locale='en'" :class="locale==='en'
                        ? 'border-[color:var(--ink)] text-[color:var(--ink)]'
                        : 'border-transparent text-[color:var(--muted)]'"
                        class="px-4 py-1.5 border-b-2 text-sm font-medium transition">
                        English
                    </button>
                </div>

                <!-- Translations -->
                <div class="space-y-4">

                    <!-- Indonesian -->
                    <div x-show="locale==='id'" class="space-y-1.5">
                        <label class="text-xs font-medium text-[color:var(--muted)] mb-1.5">Name (ID)</label>
                        <input wire:model="name_id" class="cms-input w-full" />
                        @error('name_id') <p class="text-sm text-[color:var(--danger)]">{{ $message }}</p> @enderror
                    </div>

                    <!-- English -->
                    <div x-show="locale==='en'" class="space-y-1.5">
                        <label class="text-xs font-medium text-[color:var(--muted)] mb-1.5">Name (EN)</label>
                        <input wire:model="name_en" class="cms-input w-full" />
                        @error('name_en') <p class="text-sm text-[color:var(--danger)]">{{ $message }}</p> @enderror
                    </div>

                </div>

            </div>
        </div>

    </div>

</div>