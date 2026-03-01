<div class="mb-4 flex flex-col md:flex-row gap-3 items-end">
    <div class="flex flex-col w-full md:w-1/4">
        <label class="text-xs text-gray-600 mb-1">{{ __('messages.search') }}</label>
        <input wire:model.defer="search" type="text" placeholder="Cari judul atau deskripsi"
            class="px-3 py-2 rounded bg-white/10 border border-white/20">
    </div>

    <div class="flex flex-col w-full md:w-1/4">
        <label class="text-xs text-gray-600 mb-1">{{ __('messages.sector') }}</label>
        <select wire:model="sector" wire:change="applyFilter"
            class="px-3 py-2 rounded bg-white/10 border border-white/20">
            <option value="" class="text-black">All sectors</option>
            @foreach(\Illuminate\Support\Facades\DB::table('categories')->get() as $s)
            <option value="{{ $s->slug }}" class="text-black">{{ $s->slug }}</option>
            @endforeach
        </select>
    </div>

    <div class="flex flex-col w-full md:w-1/4">
        <label class="text-xs text-gray-600 mb-1">{{ __('messages.status') }}</label>
        <select wire:model="status" class="px-3 py-2 rounded bg-white/10 border border-white/20">
            <option value="" class="text-black">All statuses</option>
            @foreach(\Illuminate\Support\Facades\DB::table('statuses')->get() as $st)
            <option value="{{ $st->key }}" class="text-black">{{ $st->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="flex items-center gap-2">
        <button wire:click="applyFilter" class="px-4 py-2 bg-[#024b63] text-white rounded">{{ __('messages.apply') }}</button>
        <button wire:click="resetFilter" class="px-4 py-2 bg-gray-300 rounded text-black">Reset</button>
        {{-- <button type="button" id="locateMe" class="px-3 py-2 bg-white/10 rounded">Use my location</button> --}}
    </div>
</div>