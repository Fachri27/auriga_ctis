<div>
    <div class="max-w-4xl mx-auto px-6 py-10" x-data="{ tab: 'id' }">

        <h1 class="text-2xl font-semibold mb-6">
            {{ $categoryId ? 'Edit Category' : 'Create Category' }}
        </h1>

        <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-8 space-y-10">

            <!-- ICON + SLUG + ACTIVE -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                <!-- Icon -->
                <div>
                    <label class="text-sm font-medium">Icon</label>
                    <input type="text" wire:model="icon" class="w-full mt-1 rounded-xl border-gray-300 bg-gray-50 px-4 py-2.5
                       focus:ring-black focus:bg-white">
                </div>

                <!-- Active Toggle -->
                <div>
                    <label class="text-sm font-medium">Active</label>

                    <div class="mt-2 flex items-center gap-3 cursor-pointer"
                        wire:click="$set('is_active', {{ $is_active ? 'false' : 'true' }})">

                        <div class="w-12 h-6 rounded-full relative transition
                                {{ $is_active ? 'bg-black' : 'bg-gray-300' }}">
                            <div class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transform transition
                                    {{ $is_active ? 'translate-x-6' : '' }}">
                            </div>
                        </div>

                        <span class="text-sm">
                            {{ $is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                </div>

            </div>

            <!-- LANGUAGE TABS -->
            <div class="flex items-center gap-3 border-b pb-3">
                <button type="button" @click="tab = 'id'"
                    :class="tab === 'id' ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-700'"
                    class="px-4 py-1.5 rounded-lg text-sm font-medium">
                    🇮🇩 Indonesia
                </button>

                <button type="button" @click="tab = 'en'"
                    :class="tab === 'en' ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-700'"
                    class="px-4 py-1.5 rounded-lg text-sm font-medium">
                    🇺🇸 English
                </button>
            </div>

            <!-- INDONESIA TAB -->
            <div x-show="tab === 'id'" class="space-y-4">
                <h3 class="font-semibold">Indonesia (ID)</h3>

                <div x-data="{
                title: @entangle('name_id'),
                slug: @entangle('slug'),
                makeSlug(t){
                    return t.toLowerCase().replace(/[^a-z0-9]+/g,'-').replace(/^-+|-+$/g,'')
                }
            }" x-init="if (title && !slug) { slug = makeSlug(title) }">

                    <label class="block text-sm font-medium mb-1">Name</label>
                    <input type="text" x-model="title" @input="slug = makeSlug(title)" class="w-full mt-1 rounded-xl bg-gray-50 border-gray-300 px-4 py-2.5
                       focus:ring-black focus:bg-white">

                    <label class="block text-sm font-medium mt-4 mb-1">Slug</label>
                    <input type="text" x-model="slug" readonly class="w-full mt-1 rounded-xl bg-gray-50 border-gray-300 px-4 py-2.5
                       focus:ring-black focus:bg-white">

                    <label class="block text-sm font-medium mt-4 mb-1">Description</label>
                    <textarea wire:model="desc_id" rows="5" class="w-full mt-1 rounded-xl bg-gray-50 border-gray-300 px-4 py-2.5
                       focus:ring-black focus:bg-white"></textarea>

                </div>
            </div>

            <!-- ENGLISH TAB -->
            <div x-show="tab === 'en'" class="space-y-4">
                <h3 class="font-semibold">English (EN)</h3>

                <div>
                    <label class="text-sm">Name</label>
                    <input type="text" wire:model="name_en" class="w-full mt-1 rounded-xl bg-gray-50 border-gray-300 px-4 py-2.5
                       focus:ring-black focus:bg-white">
                </div>

                <div>
                    <label class="text-sm">Description</label>
                    <textarea wire:model="desc_en" rows="4" class="w-full mt-1 rounded-xl bg-gray-50 border-gray-300 px-4 py-2.5
                          focus:ring-black focus:bg-white"></textarea>
                </div>
            </div>

            <!-- SUBMIT -->
            <button wire:click="save" class="mt-8 px-6 py-3 bg-black text-white rounded hover:bg-gray-800">
                {{ $categoryId ? 'Update' : 'Save' }}
            </button>

        </div>

    </div>
</div>