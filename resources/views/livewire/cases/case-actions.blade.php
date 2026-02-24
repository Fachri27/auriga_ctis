<div>
    <div x-data="{ open: false }" class="relative">

        <div class="flex justify-between items-center mb-4">
            <h2 class="font-semibold text-lg">Tugas</h2>

            <button @click="open = true" class="px-4 py-2 bg-black text-white rounded hover:bg-gray-800">
                + Tambah Tugas
            </button>
        </div>

        <!-- MODAL -->
        <div x-show="open" x-cloak class="fixed inset-0 flex items-center justify-center bg-black/50 z-50">

            <div @click.away="open = false" class="bg-white w-full max-w-lg rounded-lg shadow-lg p-6">

                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold">Tambah Tugas Manual</h3>
                    <button @click="open = false" class="text-gray-500">✕</button>
                </div>

                <!-- FORM -->
                <div>

                    <input type="text" wire:model="title" placeholder="Judul Tugas"
                        class="w-full border p-2 mb-2 rounded">

                    @error('title')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror


                    <textarea wire:model="description" placeholder="Deskripsi" class="w-full border p-2 mb-2 rounded">
                </textarea>


                    <input type="file" wire:model="files" multiple class="w-full border p-2 mb-3 rounded">

                    @error('files.*')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror

                    <div class="flex justify-end gap-2">
                        <button @click="open = false" class="px-4 py-2 border rounded">
                            Batal
                        </button>

                        <button wire:click="createAction" @click="open = false"
                            class="px-4 py-2 bg-black text-white rounded">
                            Simpan
                        </button>
                    </div>

                </div>
            </div>
        </div>


        <!-- LIST ACTIONS -->
        @forelse($actions as $action)
        <div class="border p-4 rounded mb-3 bg-white shadow-sm">

            <div class="flex justify-between items-center">
                <h4 class="font-semibold">
                    {{ $action->title }}
                </h4>
                <div class="flex gap-2">
                    <button wire:click="editAction({{ $action->id }})"
                        class="px-3 py-1 bg-yellow-400 text-white rounded text-xs hover:bg-yellow-500">Edit</button>
                    <button wire:click="deleteAction({{ $action->id }})"
                        class="px-3 py-1 bg-red-500 text-white rounded text-xs hover:bg-red-600"
                        onclick="return confirm('Hapus tugas ini?')">Delete</button>
                </div>
            </div>

            <p class="text-sm text-gray-600 mt-2">
                {{ $action->description }}
            </p>

            @if($action->due_date)
            <p class="text-xs text-gray-500 mt-1">
                Deadline: {{ \Carbon\Carbon::parse($action->due_date)->format('d M Y') }}
            </p>
            @endif

            <!-- FILES -->
            @if($action->files->count())
            <div class="mt-3">
                <p class="text-sm font-medium">Dokumen:</p>
                @foreach($action->files as $file)
                <div class="text-sm">
                    <a href="{{ asset('storage/'.$file->file_path) }}" target="_blank" class="text-blue-600 underline">
                        {{ $file->file_name }}
                    </a>
                </div>
                @endforeach
            </div>
            @endif

        </div>
        @empty
        <div class="text-gray-500">
            Belum ada tugas manual.
        </div>
        @endforelse

    </div>

</div>