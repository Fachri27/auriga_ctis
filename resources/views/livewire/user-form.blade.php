<div class="max-w-5xl mx-auto px-6 py-6 space-y-4">

    {{-- HEADER --}}
    <div class="flex items-center justify-between border-b border-[color:var(--hairline)] pb-3 cms-rise" style="animation-delay:.04s">
        <div>
            <div class="cms-eyebrow">USERS</div>
            <h1 class="text-xl font-semibold text-[color:var(--ink)] tracking-tight mt-0.5">{{ $user ? 'Edit User' : 'Tambah User' }}</h1>
        </div>
        <p class="cms-panel-sub hidden sm:block">Isi data user dan pilih role</p>
    </div>

    {{-- FORM --}}
    <div class="cms-panel cms-rise" style="animation-delay:.10s">
        <div class="cms-panel-body space-y-5" style="padding:20px">

            {{-- NAME --}}
            <div>
                <label class="block text-xs font-medium text-[color:var(--muted)] mb-1.5">Nama</label>
                <input type="text" wire:model.defer="name"
                    class="cms-input w-full" placeholder="Nama user">
                @error('name') <p class="text-[color:var(--danger)] text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- EMAIL --}}
            <div>
                <label class="block text-xs font-medium text-[color:var(--muted)] mb-1.5">Email</label>
                <input type="email" wire:model.defer="email"
                    class="cms-input w-full"
                    placeholder="email@example.com">
                @error('email') <p class="text-[color:var(--danger)] text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- PASSWORD --}}
                <div>
                    <label class="block text-xs font-medium text-[color:var(--muted)] mb-1.5">
                        Password
                        @if($user)
                        <span class="text-[color:var(--muted-2)]">(kosongkan jika tidak diganti)</span>
                        @endif
                    </label>
                    <input type="password" wire:model.defer="password"
                        class="cms-input w-full">
                    @error('password') <p class="text-[color:var(--danger)] text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-[color:var(--muted)] mb-1.5">
                        Password Confirmation
                        @if($user)
                        <span class="text-[color:var(--muted-2)]">(kosongkan jika tidak diganti)</span>
                        @endif
                    </label>
                    <input type="password" wire:model.defer="password_confirmation"
                        class="cms-input w-full">
                    @error('password') <p class="text-[color:var(--danger)] text-sm mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- ROLE --}}
            <div>
                <label class="block text-xs font-medium text-[color:var(--muted)] mb-1.5">Role</label>
                <select wire:model.defer="roles"
                    class="cms-input w-full">
                    <option value="">-- Pilih Role --</option>
                    @foreach ($allRoles as $r)
                    <option value="{{ $r }}">{{ ucfirst($r)}}
                    </option>
                    @endforeach
                </select>
                @error('roles') <p class="text-[color:var(--danger)] text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- ACTION --}}
            <div class="flex justify-end gap-2 pt-2 border-t border-[color:var(--hairline)]">
                <a href="{{ route('user.index') }}" class="cms-btn cms-btn-ghost">
                    Batal
                </a>

                <button wire:click="save" class="cms-btn cms-btn-leaf">
                    Simpan
                </button>
            </div>

        </div>
    </div>

</div>