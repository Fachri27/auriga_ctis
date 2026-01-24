<div>
    {{-- SELECTED LOCATION --}}
    <div x-data="mapComponent()" x-init="initMap()">

        <label class="block font-medium mb-1">
            {{ $locale === 'id' ? 'Lokasi Kejadian' : 'Location' }}
        </label>

        <div wire:ignore id="map" class="w-full h-64 rounded border"></div>
        <div x-data="{ open: false }" class="relative w-full">

            <label class="block text-sm font-medium text-gray-700 mb-1">
                Lokasi Kejadian
            </label>

            <input type="text" wire:model.live="searchLocation" @focus="open = true" @keydown.escape="open = false"
                placeholder="Cari desa / kelurahan..."
                class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:ring-black focus:border-black" />

            {{-- DROPDOWN --}}
            @if(count($results) > 0)
            <div x-show="open" x-transition x-cloak @click.away="open = false" class="absolute left-0 right-0 z-[9999] mt-1
                   bg-white border border-gray-300
                   rounded-lg shadow-lg max-h-60 overflow-auto">
                @foreach($results as $item)
                <div wire:click="select('{{ $item['id'] }}','{{ $item['text'] }}')" @click="open = false"
                    class="px-4 py-2 hover:bg-gray-100 cursor-pointer text-sm">
                    {{ $item['text'] }}
                </div>
                @endforeach
            </div>
            @endif
        </div>

        <input type="hidden" x-model="lat" wire:model="lat">
        <input type="hidden" x-model="lng" wire:model="lng">

        <template x-if="lat && lng">
            <p class="mt-2 text-sm text-gray-600">
                Lokasi: <strong x-text="lat + ', ' + lng"></strong>
            </p>
        </template>
    </div>
</div>