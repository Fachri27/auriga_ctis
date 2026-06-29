<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-[#032A36] via-[#034454] to-[#045E72] px-4 py-12">
    <div class="w-full max-w-md">

        {{-- Card --}}
        <div class="bg-white rounded-2xl shadow-2xl p-8">

            {{-- Logo & Title --}}
            <div class="text-center mb-8">
                <img src="/img/image.png" alt="Logo" class="h-14 mx-auto mb-4 object-contain">
                <h1 class="text-2xl font-bold text-gray-800">Daftar Akun Baru</h1>
                <p class="text-gray-500 text-sm mt-1">Buat akun untuk memulai</p>
            </div>

            {{-- Form --}}
            <form wire:submit.prevent="register" class="space-y-5">

                {{-- Name --}}
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                        Nama Lengkap
                    </label>
                    <input type="text" wire:model="name" id="name"
                        class="block w-full px-4 py-3 rounded-xl border border-gray-300 text-gray-900 placeholder-gray-400 focus:border-[#032A36] focus:ring-2 focus:ring-[#032A36]/20 transition duration-200 @error('name') border-red-400 @enderror"
                        placeholder="Masukkan nama lengkap" autocomplete="name">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Email --}}
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                        Email
                    </label>
                    <div class="relative">
                        <input type="email" wire:model="email" id="email"
                            class="block w-full px-4 py-3 rounded-xl border border-gray-300 text-gray-900 placeholder-gray-400 focus:border-[#032A36] focus:ring-2 focus:ring-[#032A36]/20 transition duration-200 @error('email') border-red-400 @enderror"
                            placeholder="nama@example.com" autocomplete="email">
                        @if($email && !$errors->has('email'))
                            <div class="absolute right-4 top-1/2 -translate-y-1/2 text-green-500">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        @endif
                    </div>
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password --}}
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                        Kata Sandi
                    </label>
                    <div class="relative">
                        <input type="{{ $showPassword ? 'text' : 'password' }}" wire:model="password" id="password"
                            class="block w-full px-4 py-3 rounded-xl border border-gray-300 text-gray-900 placeholder-gray-400 focus:border-[#032A36] focus:ring-2 focus:ring-[#032A36]/20 transition duration-200 @error('password') border-red-400 @enderror"
                            placeholder="Minimal 8 karakter" autocomplete="new-password">
                        <button type="button" wire:click="togglePasswordVisibility"
                            class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 cursor-pointer">
                            @if($showPassword)
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-4.803m5.596-3.856a3.375 3.375 0 11-4.753 4.753m4.753-4.753L3.73 3.73m4.753 4.753l4.753 4.753"/>
                                </svg>
                            @else
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            @endif
                        </button>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Minimal 8 karakter, kombinasi huruf besar, huruf kecil, angka, dan simbol</p>
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Confirm Password --}}
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
                        Konfirmasi Kata Sandi
                    </label>
                    <input type="{{ $showPassword ? 'text' : 'password' }}" wire:model="password_confirmation" id="password_confirmation"
                        class="block w-full px-4 py-3 rounded-xl border border-gray-300 text-gray-900 placeholder-gray-400 focus:border-[#032A36] focus:ring-2 focus:ring-[#032A36]/20 transition duration-200 @error('password_confirmation') border-red-400 @enderror"
                        placeholder="Ulangi kata sandi" autocomplete="new-password">
                    @error('password_confirmation')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Submit --}}
                <button type="submit" wire:loading.attr="disabled"
                    class="w-full py-3 px-4 rounded-xl bg-[#032A36] text-white font-semibold hover:bg-[#034454] focus:ring-2 focus:ring-[#032A36]/30 transition duration-200 shadow-lg disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer">
                    @if($isSubmitting)
                        <span class="inline-flex items-center gap-2">
                            <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                            </svg>
                            Mendaftar...
                        </span>
                    @else
                        Daftar
                    @endif
                </button>

                {{-- Reset --}}
                <button type="button" wire:click="resetForm"
                    class="w-full py-3 px-4 rounded-xl border border-gray-300 text-gray-700 font-semibold hover:bg-gray-50 focus:ring-2 focus:ring-gray-300 transition duration-200 cursor-pointer">
                    Reset Form
                </button>
            </form>

            {{-- Login link --}}
            <p class="text-center text-sm text-gray-500 mt-6">
                Sudah punya akun?
                <a href="{{ route('login') }}" class="text-[#032A36] font-medium hover:underline">
                    Masuk
                </a>
            </p>

            {{-- Terms --}}
            <p class="text-center text-xs text-gray-400 mt-4 leading-relaxed">
                Dengan mendaftar, Anda menyetujui
                <a href="#" class="text-[#032A36] hover:underline">Ketentuan Layanan</a>
                dan
                <a href="#" class="text-[#032A36] hover:underline">Kebijakan Privasi</a>
            </p>

        </div>

        {{-- Footer --}}
        <p class="text-center text-xs text-white/60 mt-6">
            &copy; {{ date('Y') }} Environmental Defender. All rights reserved.
        </p>
    </div>
</div>
