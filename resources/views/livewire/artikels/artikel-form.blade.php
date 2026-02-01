<div>
    <div class="my-6" x-data="{ lang: 'id' }">
        <div class="max-w-7xl mx-auto bg-white py-8 mb-20 px-8 rounded-xl shadow-md"
            x-data="{ type: @entangle('type') }">

            {{-- Breadcrumb --}}
            <nav class="text-sm text-gray-600 mb-6 flex items-center gap-2">
                <a href="" class="text-gray-800 hover:text-blue-600 font-medium">
                    Artikel
                </a>
                <span class="text-gray-400">›</span>
                <span class="text-blue-600 font-semibold">
                    {{ $artikel ? '✏️ Edit Artikel' : 'Tambah Artikel' }}
                </span>
            </nav>

            <h1 class="text-2xl font-bold mb-8 text-gray-700">
                {{ $artikel ? '✏️ Edit Artikel' : '➕ Add Artikel' }}
            </h1>

            <form wire:submit.prevent="save">
                <div class="grid grid-cols-12 gap-6">

                    {{-- ================= LEFT COLUMN ================= --}}
                    <div class="col-span-12 lg:col-span-4">
                        <div class="bg-gray-50 border rounded-xl p-5 space-y-4 sticky top-6">

                            {{-- Language --}}
                            <div>
                                <label class="font-medium">🌐 Bahasa</label>
                                <select x-model="lang" class="w-full border rounded-lg px-3 py-2 mt-1">
                                    <option value="id">Indonesia</option>
                                    <option value="en">English</option>
                                </select>
                            </div>

                            {{-- Title & Slug ID --}}
                            {{-- Title --}}
                            <div x-show="lang === 'id'">
                                <div x-data="{
                        title: @js(old('titleId', $titleId ?? '')),
                        slug: @js(old('slug', $slug ?? '')),
                        makeSlug(text) {
                            return text
                                .toLowerCase()
                                .replace(/[^a-z0-9]+/g, '-')
                                .replace(/^-+|-+$/g, '');
                        }
                    }" x-init="if(title && !slug){ slug = makeSlug(title) }">
                                    <label class="block text-sm font-medium text-gray-600 mb-1">Title (ID)</label>
                                    <input type="text" wire:model="titleId" name="title_id" x-model="title"
                                        @input="slug = makeSlug(title)"
                                        class="w-full border border-gray-500 rounded p-2">
                                    @error('title_id') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror

                                    <label class="block font-medium mb-1 mt-3">Slug</label>
                                    <input type="text" name="slug" x-model="slug" readonly
                                        class="w-full border border-gray-500 rounded p-2 bg-gray-100">
                                </div>
                            </div>

                            {{-- Title & Slug (EN) --}}
                            <div x-show="lang === 'en'">
                                <div x-data="{
                        title: @js(old('titleEn', $titleEn ?? '')),
                        makeSlug(text) {
                            return text
                                .toLowerCase()
                                .replace(/[^a-z0-9]+/g, '-')
                                .replace(/^-+|-+$/g, '');
                        }
                    }" x-init="if(title && !slug){ slug = makeSlug(title) }">
                                    <label class="block text-sm font-medium text-gray-600 mb-1">Title (EN)</label>
                                    <input type="text" wire:model="titleEn" name="title_en" x-model="title"
                                        @input="slug = makeSlug(title)"
                                        class="w-full border border-gray-500 rounded p-2">
                                    @error('title_en') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            {{-- Publish + Page Type --}}
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="text-sm">📅 Publish</label>
                                    <input type="date" wire:model="publishedAt"
                                        class="w-full border rounded-lg px-2 py-2">
                                </div>

                                <div>
                                    <label class="text-sm">Type</label>
                                    <select wire:model="type" class="w-full border rounded-lg px-2 py-2">
                                        <option value="internal">Internal</option>
                                        <option value="eksternal">Eksternal</option>
                                    </select>
                                </div>
                            </div>

                            <div x-show="type === 'eksternal'">
                                {{-- Link --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-600 mb-1">Link</label>
                                    <input type="text" wire:model="link"
                                        class="w-full border border-gray-500 rounded p-2"
                                        placeholder="https://contoh.com/resource">
                                </div>
                            </div>

                            {{-- Featured Image --}}
                            <div>
                                <label class="font-medium">Featured Image</label>
                                <input type="file" wire:model="image" class="w-full border rounded-lg px-2 py-2 mt-1">

                                <div class="mt-3">
                                    @if ($image)
                                    <img src="{{ $image->temporaryUrl() }}"
                                        class="w-20 h-20 rounded-lg object-cover border">
                                    @elseif ($oldImage)
                                    <img src="{{ asset('storage/' . $oldImage) }}"
                                        class="w-20 h-20 rounded-lg object-cover border">
                                    @endif
                                </div>
                            </div>

                            {{-- categories --}}
                            <div>
                                <label class="text-sm">Sector</label>
                                <select wire:model="categoryId" class="w-full border px-3 py-2">
                                    <option value="">Select Sector</option>
                                    @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name ?? $cat->slug }}</option>
                                    @endforeach
                                </select>
                                @error('category_id') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                            </div>

                            {{-- Status --}}
                            <div>
                                <label class="font-medium">Status</label>
                                <select wire:model="status" class="w-full border rounded-lg px-3 py-2">
                                    <option value="draft">Draft</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>

                        </div>
                    </div>

                    {{-- ================= RIGHT COLUMN ================= --}}
                    <div class="col-span-12 lg:col-span-8 space-y-6">

                        {{-- Excerpt --}}
                        <div class="bg-white border rounded-xl p-4">
                            <h3 class="font-semibold mb-3">Excerpt</h3>

                            <div x-show="lang === 'id'">
                                {{-- excerpt_editor_id --}}
                                @includeWhen(true,'front.components.tinymce-excerpt-id')
                            </div>

                            <div x-show="lang === 'en'">
                                {{-- excerpt_editor_en --}}
                                @includeWhen(true,'front.components.tinymce-excerpt-en')
                            </div>
                        </div>

                        {{-- Content --}}
                        <div class="bg-white border rounded-xl p-4">
                            <h3 class="font-semibold mb-3">Content</h3>

                            <div x-show="lang === 'id'">
                                {{-- editor_id --}}
                                @includeWhen(true,'front.components.tinymce-content-id')
                            </div>

                            <div x-show="lang === 'en'">
                                {{-- editor_en --}}
                                @includeWhen(true,'front.components.tinymce-content-en')
                            </div>
                        </div>
                    </div>

                    {{-- SAVE --}}
                    <div class="col-span-12 sticky bottom-0 bg-white border-t p-4 flex justify-end">
                        <button type="submit" wire:loading.attr="disabled" class="relative bg-blue-600 hover:bg-blue-700 disabled:opacity-60 disabled:cursor-not-allowed
               text-white px-6 py-2 rounded-lg font-medium flex items-center gap-2">

                            {{-- Spinner --}}
                            <svg wire:loading wire:target="save" class="animate-spin h-5 w-5 text-white"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4" />
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                            </svg>

                            {{-- Text --}}
                            <span wire:loading.remove wire:target="save">
                                {{ $artikel ? '💾 Update' : '🚀 Create' }}
                            </span>

                            <span wire:loading wire:target="save">
                                Saving...
                            </span>
                        </button>

                    </div>

                </div>
            </form>
        </div>
    </div>

    {{-- @include('front.components.floating') --}}
</div>