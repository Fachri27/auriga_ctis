<div class="max-w-xl mx-auto py-10">

    {{-- HEADER --}}
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">
            {{ $user ? 'Edit User' : 'Tambah User' }}
        </h1>
        <p class="text-sm text-gray-600">
            Isi data user dan pilih role
        </p>
    </div>

    {{-- FORM --}}
    <div class="bg-white rounded-xl shadow p-6 space-y-5">

        {{-- NAME --}}
        <div>
            <label class="block text-sm font-medium mb-1">Nama</label>
            <input type="text" wire:model.defer="name"
                class="w-full rounded-lg border-gray-300 focus:ring-black focus:border-black" placeholder="Nama user">
            @error('name') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- EMAIL --}}
        <div>
            <label class="block text-sm font-medium mb-1">Email</label>
            <input type="email" wire:model.defer="email"
                class="w-full rounded-lg border-gray-300 focus:ring-black focus:border-black"
                placeholder="email@example.com">
            @error('email') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- PASSWORD --}}
        <div>
            <label class="block text-sm font-medium mb-1">
                Password
                @if($user)
                <span class="text-xs text-gray-500">(kosongkan jika tidak diganti)</span>
                @endif
            </label>
            <input type="password" wire:model.defer="password"
                class="w-full rounded-lg border-gray-300 focus:ring-black focus:border-black">
            @error('password') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">
                Password Confirmation
                @if($user)
                <span class="text-xs text-gray-500">(kosongkan jika tidak diganti)</span>
                @endif
            </label>
            <input type="password" wire:model.defer="password_confirmation"
                class="w-full rounded-lg border-gray-300 focus:ring-black focus:border-black">
            @error('password') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- ROLE --}}
        <div>
            <label class="block text-sm font-medium mb-1">Role</label>
            <select wire:model.defer="roles"
                class="w-full rounded-lg border-gray-300 focus:ring-black focus:border-black">
                <option value="">-- Pilih Role --</option>
                @foreach ($allRoles as $r)
                <option value="{{ $r }}">{{ ucfirst($r)}}
                </option>
                @endforeach
            </select>
            @error('roles') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- ACTION --}}
        <div class="flex justify-end gap-3 pt-4">
            <a href="{{ route('user.index') }}" class="px-4 py-2 rounded-lg border text-gray-700 hover:bg-gray-50">
                Batal
            </a>

            <button wire:click="save" class="px-5 py-2 rounded-lg bg-black text-white hover:bg-gray-800">
                Simpan
            </button>
        </div>

    </div>

</div>