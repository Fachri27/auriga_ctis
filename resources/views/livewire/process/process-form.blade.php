<div>
    <div x-data="{ locale: 'id' }" class="max-w-4xl mx-auto mt-10 space-y-6">

        <!-- Header -->
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-semibold tracking-tight">Process Form</h1>
            <button wire:click="save" class="px-5 py-2.5 bg-black text-white rounded-xl hover:opacity-90 transition">
                Save
            </button>
        </div>

        <!-- Card -->
        <div class="bg-white border rounded-2xl shadow-sm p-6 space-y-8">

            <!-- 2 Columns -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <!-- Category -->
                <div class="space-y-2">
                    <label class="text-sm font-medium text-gray-700">Category</label>
                    <select wire:model="category_id"
                        class="w-full border rounded-xl p-3 bg-gray-50 focus:ring-black focus:border-black">
                        <option value="">Select category...</option>

                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">
                            {{ $cat->translation('id')?->name ?? $cat->slug }}
                        </option>
                        @endforeach
                    </select>
                    @error('category_id') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                </div>

                <!-- Order Number -->
                <div class="space-y-2">
                    <label class="text-sm font-medium text-gray-700">Order Number</label>
                    <input type="number" min="1" wire:model="order_no"
                        class="w-full border rounded-xl p-3 bg-gray-50 focus:ring-black focus:border-black" />
                    @error('order_no') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                </div>

                <!-- Active Switch -->
                <div class="space-y-2">
                    <label class="text-sm font-medium text-gray-700">Active</label>
                    <label class="flex items-center gap-3 cursor-pointer select-none">
                        <input type="checkbox" wire:model="is_active"
                            class="h-5 w-5 rounded border-gray-300 focus:ring-black">
                        <span class="text-sm text-gray-700">Enable this process</span>
                    </label>
                </div>

            </div>

            <!-- Language Switch Tabs -->
            <div class="flex gap-3 border-b pb-2">
                <button @click="locale='id'" :class="locale==='id' 
                    ? 'border-black text-black' 
                    : 'border-transparent text-gray-500'"
                    class="px-4 py-1.5 border-b-2 text-sm font-medium transition">
                    Indonesia
                </button>

                <button @click="locale='en'" :class="locale==='en' 
                    ? 'border-black text-black' 
                    : 'border-transparent text-gray-500'"
                    class="px-4 py-1.5 border-b-2 text-sm font-medium transition">
                    English
                </button>
            </div>

            <!-- Translations -->
            <div class="space-y-4">

                <!-- Indonesian -->
                <div x-show="locale==='id'" class="space-y-2">
                    <label class="text-sm font-medium text-gray-700">Name (ID)</label>
                    <input wire:model="name_id"
                        class="w-full p-3 border rounded-xl bg-gray-50 focus:ring-black focus:border-black" />
                    @error('name_id') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                </div>

                <!-- English -->
                <div x-show="locale==='en'" class="space-y-2">
                    <label class="text-sm font-medium text-gray-700">Name (EN)</label>
                    <input wire:model="name_en"
                        class="w-full p-3 border rounded-xl bg-gray-50 focus:ring-black focus:border-black" />
                    @error('name_en') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                </div>

            </div>

        </div>

    </div>

</div>