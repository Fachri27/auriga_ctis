<div>
    <div x-data="{ open: false }" x-on:open-task-modal.window="open = true" x-on:close-task-modal.window="open = false" x-show="open" x-cloak>

        {{-- Overlay --}}
        <div class="fixed inset-0 bg-black/40 z-40" @click="open = false"></div>

        {{-- Modal --}}
        <div class="fixed inset-0 z-50 flex justify-center items-start p-6 overflow-y-auto">
            <div class="w-full max-w-2xl cms-panel">

                {{-- Header --}}
                <div class="cms-panel-head">
                    <div>
                        <div class="cms-eyebrow">TASKS</div>
                        <h3 class="cms-panel-title mt-1">Create Task</h3>
                    </div>
                    <button @click="open=false" class="cms-btn cms-btn-ghost">Close</button>
                </div>

                {{-- Body --}}
                <div class="cms-panel-body" style="padding:16px 20px">
                    {{-- PROCESS --}}
                    <div>
                        <label class="block text-xs font-medium text-[color:var(--muted)] mb-1.5">Process</label>
                        <select wire:model="process_id" class="cms-input w-full">
                            <option value="">Select process...</option>
                            @foreach($processes as $p)
                            <option value="{{ $p->id }}">
                                {{ $p->translation('id')?->name ?? $p->slug }}
                            </option>
                            @endforeach
                        </select>
                        @error('process_id')
                        <p class="text-[color:var(--danger)] text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- LANGUAGE SWITCHER --}}
                    <div x-data="{ locale: 'id' }" class="mt-5">

                        <div class="flex gap-2 mb-3">
                            <button type="button" @click="locale='id'"
                                :class="locale==='id' ? 'cms-btn cms-btn-primary' : 'cms-btn cms-btn-ghost'">
                                ID
                            </button>

                            <button type="button" @click="locale='en'"
                                :class="locale==='en' ? 'cms-btn cms-btn-primary' : 'cms-btn cms-btn-ghost'">
                                EN
                            </button>
                        </div>

                        {{-- INDONESIA --}}
                        <div x-show="locale === 'id'" class="space-y-4">

                            <div>
                                <label class="block text-xs font-medium text-[color:var(--muted)] mb-1.5">Name (ID)</label>
                                <input wire:model="name_id" class="cms-input w-full">
                                @error('name_id')
                                <p class="text-[color:var(--danger)] text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-[color:var(--muted)] mb-1.5">Description (ID)</label>
                                <textarea wire:model="desc_id" class="cms-input w-full h-32"></textarea>
                                @error('desc_id')
                                <p class="text-[color:var(--danger)] text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                        </div>

                        {{-- ENGLISH --}}
                        <div x-show="locale === 'en'" class="space-y-4">

                            <div>
                                <label class="block text-xs font-medium text-[color:var(--muted)] mb-1.5">Name (EN)</label>
                                <input wire:model="name_en" class="cms-input w-full">
                                @error('name_en')
                                <p class="text-[color:var(--danger)] text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-[color:var(--muted)] mb-1.5">Description (EN)</label>
                                <textarea wire:model="desc_en" class="cms-input w-full h-32"></textarea>
                                @error('desc_en')
                                <p class="text-[color:var(--danger)] text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                        </div>

                    </div>

                    {{-- ROW: DUE / REQUIRED --}}
                    <div class="flex flex-wrap items-center gap-6 pt-4 mt-4 border-t border-[color:var(--hairline)]">

                        <div>
                            <label class="block text-xs font-medium text-[color:var(--muted)] mb-1.5">Due Days</label>
                            <input type="number" min="0" wire:model="due_days" class="cms-input w-32">
                        </div>

                        <label class="flex items-center gap-2 text-sm text-[color:var(--ink)]">
                            <input type="checkbox" wire:model.live="is_required" class="h-4 w-4">
                            Required
                        </label>

                        {{-- Actions --}}
                        <div class="flex justify-end gap-3 ml-auto">
                            <button @click="open=false" class="cms-btn cms-btn-ghost">Cancel</button>
                            <button wire:click="save" class="cms-btn cms-btn-leaf">
                                Save
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

</div>