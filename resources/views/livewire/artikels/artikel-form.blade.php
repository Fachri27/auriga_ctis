<div class="max-w-7xl mx-auto px-6 py-6 space-y-4" x-data="{ lang: 'id' }">
    <div x-data="{ type: @entangle('type') }" class="cms-rise" style="animation-delay:.04s">

        {{-- Page header --}}
        <div class="cms-panel-head">
            <div>
                <div class="cms-eyebrow">CMS / ARTIKEL</div>
                <h1 class="text-xl font-semibold text-[color:var(--ink)] tracking-tight mt-0.5">
                    {{ $artikel ? 'Edit Artikel' : 'Tambah Artikel' }}
                </h1>
            </div>
            <a href="" class="cms-btn cms-btn-ghost">Artikel</a>
        </div>

        <form wire:submit.prevent="save">
            <div class="grid grid-cols-12 gap-4">

                {{-- ================= LEFT COLUMN ================= --}}
                <div class="col-span-12 lg:col-span-4">
                    <div class="cms-panel sticky top-6">
                        <div class="cms-panel-head">
                            <div class="cms-panel-title">Pengaturan</div>
                        </div>
                        <div class="cms-panel-body space-y-4" style="padding:16px 20px">

                            {{-- Language --}}
                            <div>
                                <label class="block text-xs font-medium text-[color:var(--muted)] mb-1.5">Bahasa</label>
                                <select x-model="lang" class="cms-input w-full">
                                    <option value="id">Indonesia</option>
                                    <option value="en">English</option>
                                </select>
                            </div>

                            {{-- Title & Slug ID --}}
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
                                    <label class="block text-xs font-medium text-[color:var(--muted)] mb-1.5">Title (ID)</label>
                                    <input type="text" wire:model="titleId" name="title_id" x-model="title"
                                        @input="slug = makeSlug(title)"
                                        class="cms-input w-full">
                                    @error('title_id') <p class="text-sm text-[color:var(--danger)]">{{ $message }}</p> @enderror

                                    <label class="block text-xs font-medium text-[color:var(--muted)] mb-1.5 mt-3">Slug</label>
                                    <input type="text" name="slug" x-model="slug" readonly
                                        class="cms-input w-full bg-[color:var(--paper-2)]">
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
                                    <label class="block text-xs font-medium text-[color:var(--muted)] mb-1.5">Title (EN)</label>
                                    <input type="text" wire:model="titleEn" name="title_en" x-model="title"
                                        @input="slug = makeSlug(title)"
                                        class="cms-input w-full">
                                    @error('title_en') <p class="text-sm text-[color:var(--danger)]">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            {{-- Publish + Page Type --}}
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-[color:var(--muted)] mb-1.5">Publish</label>
                                    <input type="date" wire:model="publishedAt"
                                        class="cms-input w-full">
                                </div>

                                <div>
                                    <label class="block text-xs font-medium text-[color:var(--muted)] mb-1.5">Type</label>
                                    <select wire:model="type" class="cms-input w-full">
                                        <option value="internal">Internal</option>
                                        <option value="eksternal">Eksternal</option>
                                    </select>
                                </div>
                            </div>

                            <div x-show="type === 'eksternal'">
                                {{-- Link --}}
                                <div>
                                    <label class="block text-xs font-medium text-[color:var(--muted)] mb-1.5">Link</label>
                                    <input type="text" wire:model="link"
                                        class="cms-input w-full"
                                        placeholder="https://contoh.com/resource">
                                </div>
                            </div>

                            {{-- Featured Image --}}
                            <div>
                                <label class="block text-xs font-medium text-[color:var(--muted)] mb-1.5">Featured Image</label>
                                <input type="file" wire:model="image" class="cms-input w-full">

                                <div class="mt-3">
                                    @if ($image)
                                    <img src="{{ $image->temporaryUrl() }}"
                                        class="w-20 h-20 rounded-lg object-cover border border-[color:var(--hairline)]">
                                    @elseif ($oldImage)
                                    <img src="{{ asset('storage/' . $oldImage) }}"
                                        class="w-20 h-20 rounded-lg object-cover border border-[color:var(--hairline)]">
                                    @endif
                                </div>
                            </div>

                            {{-- categories --}}
                            <div>
                                <label class="block text-xs font-medium text-[color:var(--muted)] mb-1.5">Sector</label>
                                <select wire:model="categoryId" class="cms-input w-full">
                                    <option value="">Select Sector</option>
                                    @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name ?? $cat->slug }}</option>
                                    @endforeach
                                </select>
                                @error('category_id') <p class="text-sm text-[color:var(--danger)]">{{ $message }}</p> @enderror
                            </div>

                            {{-- Status --}}
                            <div>
                                <label class="block text-xs font-medium text-[color:var(--muted)] mb-1.5">Status</label>
                                <select wire:model="status" class="cms-input w-full">
                                    <option value="draft">Draft</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>

                        </div>
                    </div>
                </div>

                {{-- ================= RIGHT COLUMN ================= --}}
                <div class="col-span-12 lg:col-span-8 space-y-4">

                    {{-- Excerpt --}}
                    <div class="cms-panel cms-rise" style="animation-delay:.10s">
                        <div class="cms-panel-head">
                            <div class="cms-panel-title">Excerpt</div>
                        </div>
                        <div class="cms-panel-body" style="padding:16px 20px">
                            <div x-show="lang === 'id'">
                                {{-- excerpt_editor_id --}}
                                @includeWhen(true,'front.components.tinymce-excerpt-id')
                            </div>

                            <div x-show="lang === 'en'">
                                {{-- excerpt_editor_en --}}
                                @includeWhen(true,'front.components.tinymce-excerpt-en')
                            </div>
                        </div>
                    </div>

                    {{-- Content --}}
                    <div class="cms-panel cms-rise" style="animation-delay:.16s">
                        <div class="cms-panel-head">
                            <div class="cms-panel-title">Content</div>
                        </div>
                        <div class="cms-panel-body" style="padding:16px 20px">
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
                </div>

                {{-- SAVE --}}
                <div class="col-span-12 sticky bottom-0 bg-[color:var(--surface)] border-t border-[color:var(--hairline)] p-4 flex justify-end">
                    <button type="submit" wire:loading.attr="disabled" class="cms-btn cms-btn-leaf disabled:opacity-60 disabled:cursor-not-allowed">

                        {{-- Spinner --}}
                        <svg wire:loading wire:target="save" class="animate-spin h-5 w-5"
                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                        </svg>

                        {{-- Text --}}
                        <span wire:loading.remove wire:target="save">
                            {{ $artikel ? 'Update' : 'Create' }}
                        </span>

                        <span wire:loading wire:target="save">
                            Saving...
                        </span>
                    </button>

                </div>

            </div>
        </form>
    </div>

    {{-- @include('front.components.floating') --}}
</div>