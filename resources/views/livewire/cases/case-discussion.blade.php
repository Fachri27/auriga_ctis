<div>
    <h3 class="font-semibold mb-2">Discussion</h3>

    <div class="space-y-4">
        @foreach($discussions as $item)
        <div class="p-3 border rounded bg-gray-50">
            <p class="font-semibold">{{ $item->name }}</p>
            <p>{{ $item->message }}</p>
            @if($item->attachments)
            <div class="text-sm mt-2">
                @foreach(json_decode($item->attachments) as $file)
                <a href="{{ asset('storage/'.$file) }}" class="text-blue-600 underline">
                    <img src="{{ asset('storage/'.$file) }}" alt="" class="w-[70px] h-[70px]">
                </a><br>
                @endforeach
            </div>
            @endif
        </div>
        @endforeach
    </div>

    <div class="mt-4">
        <input type="text" wire:model="message" class="border w-full px-3 py-2" placeholder="Type message...">
        <input type="file" wire:model="attachments" multiple class="border w-full px-3 py-2 mt-2">
        <button wire:click="send" class="px-4 py-2 bg-black text-white mt-2">Send</button>
    </div>
</div>