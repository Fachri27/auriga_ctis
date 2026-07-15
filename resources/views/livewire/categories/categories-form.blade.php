<div>
    <div class="max-w-5xl mx-auto px-6 py-6 space-y-4" x-data="{ tab: 'id' }">

        <!-- Page header -->
        <div class="flex items-center justify-between border-b border-[color:var(--hairline)] pb-3">
            <div>
                <div class="cms-eyebrow">Catalog</div>
                <h1 class="text-xl font-semibold text-[color:var(--ink)] tracking-tight mt-0.5">
                    {{ $categoryId ? 'Edit Category' : 'Create Category' }}
                </h1>
            </div>
        </div>

        <div class="cms-panel cms-rise" style="animation-delay:.04s">
            <div class="cms-panel-head">
                <div>
                    <div class="cms-panel-title">Category details</div>
                    <div class="cms-panel-sub">Set the active state and translations</div>
                </div>
            </div>

            <div class="cms-panel-body space-y-6" style="padding:20px">

                <!-- ACTIVE TOGGLE -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-[color:var(--muted)] mb-1.5">Active</label>

                        <div class="mt-1 flex items-center gap-3 cursor-pointer"
                            wire:click="$set('is_active', {{ $is_active ? 'false' : 'true' }})">

                            <div class="w-12 h-6 rounded-full relative transition"
                                    style="background:{{ $is_active ? 'var(--ink)' : 'var(--hairline-2)' }}">
                                <div class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transform transition"
                                        :class="{{ $is_active ? 'translate-x-6' : '' }}">
                                </div>
                            </div>

                            <span class="text-sm text-[color:var(--ink)]">
                                {{ $is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- LANGUAGE TABS -->
                <div class="flex items-center gap-2 border-b border-[color:var(--hairline)] pb-3">
                    <button type="button" @click="tab = 'id'"
                        :class="tab === 'id' ? 'cms-btn-primary' : 'cms-btn-ghost'"
                        class="cms-btn">
                        Indonesia
                    </button>

                    <button type="button" @click="tab = 'en'"
                        :class="tab === 'en' ? 'cms-btn-primary' : 'cms-btn-ghost'"
                        class="cms-btn">
                        English
                    </button>
                </div>

                <!-- INDONESIA TAB -->
                <div x-show="tab === 'id'" class="space-y-4">
                    <div class="cms-eyebrow">Indonesia (ID)</div>

                    <div x-data="{
                    title: @entangle('name_id'),
                    slug: @entangle('slug'),
                    makeSlug(t){
                        return t.toLowerCase().replace(/[^a-z0-9]+/g,'-').replace(/^-+|-+$/g,'')
                    }
                }" x-init="if (title && !slug) { slug = makeSlug(title) }">

                        <div>
                            <label class="block text-xs font-medium text-[color:var(--muted)] mb-1.5">Name</label>
                            <input type="text" x-model="title" @input="slug = makeSlug(title)" class="cms-input w-full">
                        </div>

                        <div class="mt-4">
                            <label class="block text-xs font-medium text-[color:var(--muted)] mb-1.5">Slug</label>
                            <input type="text" x-model="slug" readonly class="cms-input w-full">
                        </div>

                    </div>
                </div>

                <!-- ENGLISH TAB -->
                <div x-show="tab === 'en'" class="space-y-4">
                    <div class="cms-eyebrow">English (EN)</div>

                    <div>
                        <label class="block text-xs font-medium text-[color:var(--muted)] mb-1.5">Name</label>
                        <input type="text" wire:model="name_en" class="cms-input w-full">
                    </div>
                </div>

                <!-- SUBMIT -->
                <div class="flex justify-end pt-2">
                    <button wire:click="save" class="cms-btn cms-btn-leaf">
                        {{ $categoryId ? 'Update' : 'Save' }}
                    </button>
                </div>

            </div>
        </div>

    </div>
</div>