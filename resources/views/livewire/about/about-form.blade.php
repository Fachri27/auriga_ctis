<div>
    <div class="my-6" x-data="{ lang: 'id' }">
        <div class="max-w-7xl mx-auto bg-white py-8 mb-20 px-8 rounded-xl shadow-md">

            <nav class="text-sm text-gray-600 mb-6 flex items-center gap-2">
                <a href="" class="text-gray-800 hover:text-blue-600 font-medium">About</a>
                <span class="text-gray-400">›</span>
                <span class="text-blue-600 font-semibold">Edit About Page</span>
            </nav>

            <h1 class="text-2xl font-bold mb-8 text-gray-700">Edit About Page</h1>

            <form wire:submit.prevent="save">
                <div class="grid grid-cols-12 gap-6">

                    <div class="col-span-12 lg:col-span-4">
                        <div class="bg-gray-50 border rounded-xl p-5 space-y-4 sticky top-6">

                            <div>
                                <label class="font-medium">Bahasa</label>
                                <select x-model="lang" class="w-full border rounded-lg px-3 py-2 mt-1">
                                    <option value="id">Indonesia</option>
                                    <option value="en">English</option>
                                </select>
                            </div>

                            <div x-show="lang === 'id'">
                                <label class="block text-sm font-medium text-gray-600 mb-1">Title (ID)</label>
                                <input type="text" wire:model="title_id" class="w-full border border-gray-500 rounded p-2">
                                @error('title_id') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                            </div>

                            <div x-show="lang === 'en'">
                                <label class="block text-sm font-medium text-gray-600 mb-1">Title (EN)</label>
                                <input type="text" wire:model="title_en" class="w-full border border-gray-500 rounded p-2">
                                @error('title_en') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                            </div>

                        </div>
                    </div>

                    <div class="col-span-12 lg:col-span-8 space-y-6">

                        {{-- Content --}}
                        <div class="bg-white border rounded-xl p-4">
                            <h3 class="font-semibold mb-3">Content</h3>
                            <div x-show="lang === 'id'">
                                @include('front.components.tinymce-about-content-id')
                            </div>
                            <div x-show="lang === 'en'">
                                @include('front.components.tinymce-about-content-en')
                            </div>
                        </div>

                        {{-- Vision --}}
                        <div class="bg-white border rounded-xl p-4">
                            <h3 class="font-semibold mb-3">Vision</h3>
                            <div x-show="lang === 'id'">
                                @include('front.components.tinymce-about-vision-id')
                            </div>
                            <div x-show="lang === 'en'">
                                @include('front.components.tinymce-about-vision-en')
                            </div>
                        </div>

                        {{-- Mission --}}
                        <div class="bg-white border rounded-xl p-4">
                            <h3 class="font-semibold mb-3">Mission</h3>
                            <div x-show="lang === 'id'">
                                @include('front.components.tinymce-about-mission-id')
                            </div>
                            <div x-show="lang === 'en'">
                                @include('front.components.tinymce-about-mission-en')
                            </div>
                        </div>

                    </div>

                    <div class="col-span-12 sticky bottom-0 bg-white border-t p-4 flex justify-end">
                        <button type="submit" wire:loading.attr="disabled" class="relative bg-blue-600 hover:bg-blue-700 disabled:opacity-60 disabled:cursor-not-allowed text-white px-6 py-2 rounded-lg font-medium flex items-center gap-2">
                            <svg wire:loading wire:target="save" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
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

    @if (session('success'))
        <div x-data="{ show: true }" x-show="show" x-transition:enter="transition ease-out duration-500"
            x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-500" x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-2" x-init="setTimeout(() => show = false, 3000)"
            class="fixed bottom-6 right-6 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg">
            {{ session('success') }}
        </div>
    @endif
</div>
