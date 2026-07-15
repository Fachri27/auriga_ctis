<div>
    <div class="max-w-5xl mx-auto px-6 py-6 space-y-4 cms-rise" style="animation-delay:.04s">

        {{-- PAGE HEADER --}}
        <div class="flex items-center justify-between border-b border-[color:var(--hairline)] pb-3">
            <div>
                <div class="cms-eyebrow">TASKS</div>
                <h1 class="text-xl font-semibold text-[color:var(--ink)] tracking-tight mt-0.5">
                    {{ $taskId ? 'Edit Task' : 'Create Task' }}
                </h1>
            </div>
        </div>

        {{-- SUCCESS ALERT --}}
        @if(session('success'))
        <div class="px-4 py-3 rounded-xl bg-[color:var(--ok-soft)] text-[color:var(--ok)] border border-[color:var(--hairline)] text-sm">
            {{ session('success') }}
        </div>
        @endif

        {{-- FORM --}}
        <div class="cms-panel">
            <div class="cms-panel-head">
                <div>
                    <div class="cms-eyebrow">DETAILS</div>
                    <h2 class="cms-panel-title mt-1">Task</h2>
                </div>
            </div>
            <div class="cms-panel-body" style="padding:16px 20px">
                <form wire:submit.prevent="save" class="space-y-5">

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
                    <div x-data="{ locale: 'id' }">

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
                    <div class="flex flex-wrap items-center gap-6 pt-4 border-t border-[color:var(--hairline)]">

                        <div>
                            <label class="block text-xs font-medium text-[color:var(--muted)] mb-1.5">Due Days</label>
                            <input type="number" min="0" wire:model="due_days" class="cms-input w-32">
                        </div>

                        <label class="flex items-center gap-2 text-sm text-[color:var(--ink)]">
                            <input type="checkbox" wire:model.live="is_required" class="h-4 w-4" checked>
                            Required
                        </label>

                    </div>

                    {{-- SUBMIT --}}
                    <div class="pt-4 border-t border-[color:var(--hairline)] flex justify-end">
                        <button class="cms-btn cms-btn-leaf">
                            Save Task
                        </button>
                    </div>

                </form>
            </div>
        </div>

    </div>

</div>