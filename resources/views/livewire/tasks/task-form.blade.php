<div>
    <div class="max-w-4xl mx-auto p-6">

        {{-- TITLE --}}
        <h1 class="text-2xl font-semibold mb-8">
            {{ $taskId ? 'Edit Task' : 'Create Task' }}
        </h1>

        {{-- SUCCESS ALERT --}}
        @if(session('success'))
        <div class="p-3 bg-green-100 text-green-700 border border-green-300 mb-6">
            {{ session('success') }}
        </div>
        @endif

        {{-- FORM --}}
        <form wire:submit.prevent="save" class="space-y-8">

            {{-- PROCESS --}}
            <div>
                <label class="block mb-1 text-sm font-medium">Process</label>
                <select wire:model="process_id"
                    class="w-full border bg-gray-50 px-3 py-2 focus:border-black outline-none">
                    <option value="">Select process...</option>
                    @foreach($processes as $p)
                    <option value="{{ $p->id }}">
                        {{ $p->translation('id')?->name ?? $p->slug }}
                    </option>
                    @endforeach
                </select>
                @error('process_id')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- LANGUAGE SWITCHER --}}
            <div x-data="{ locale: 'id' }" class="mt-6">

                <div class="flex gap-2 mb-3">
                    <button type="button" @click="locale='id'"
                        :class="locale==='id' ? 'bg-black text-white' : 'bg-gray-200 text-gray-700'"
                        class="px-4 py-1 text-sm">
                        ID
                    </button>

                    <button type="button" @click="locale='en'"
                        :class="locale==='en' ? 'bg-black text-white' : 'bg-gray-200 text-gray-700'"
                        class="px-4 py-1 text-sm">
                        EN
                    </button>
                </div>

                {{-- INDONESIA --}}
                <div x-show="locale === 'id'" class="space-y-5">

                    <div>
                        <label class="block mb-1 text-sm">Name (ID)</label>
                        <input wire:model="name_id"
                            class="w-full border bg-white px-3 py-2 focus:border-black outline-none">
                        @error('name_id')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block mb-1 text-sm">Description (ID)</label>
                        <textarea wire:model="desc_id"
                            class="w-full border bg-white px-3 py-2 h-32 focus:border-black outline-none"></textarea>
                        @error('desc_id')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                </div>

                {{-- ENGLISH --}}
                <div x-show="locale === 'en'" class="space-y-5">

                    <div>
                        <label class="block mb-1 text-sm">Name (EN)</label>
                        <input wire:model="name_en"
                            class="w-full border bg-white px-3 py-2 focus:border-black outline-none">
                        @error('name_en')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block mb-1 text-sm">Description (EN)</label>
                        <textarea wire:model="desc_en"
                            class="w-full border bg-white px-3 py-2 h-32 focus:border-black outline-none"></textarea>
                        @error('desc_en')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                </div>

            </div>

            {{-- ROW: DUE / REQUIRED / ACTIVE --}}
            <div class="flex flex-wrap items-center gap-6 pt-4 border-t">

                <div>
                    <label class="block mb-1 text-sm">Due Days</label>
                    <input type="number" min="0" wire:model="due_days"
                        class="px-3 py-2 border bg-white w-32 focus:border-black outline-none">
                </div>

                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" wire:model.live="is_required" class="h-4 w-4" checked>
                    Required
                </label>

            </div>

            {{-- SUBMIT --}}
            <div class="pt-6 border-t flex justify-end">
                <button class="px-6 py-2 bg-black text-white text-sm hover:bg-gray-900">
                    Save Task
                </button>
            </div>

        </form>

    </div>

</div>