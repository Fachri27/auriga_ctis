<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-600 px-4">
        <div class="w-full max-w-md bg-white rounded-2xl shadow-2xl p-8">

            {{-- Logo / Title --}}
            <div class="text-center mb-6">
                <div class="mx-auto w-14 h-14 flex items-center justify-center rounded-full bg-indigo-100 text-indigo-600 text-2xl font-bold">
                    🔐
                </div>
                <h1 class="text-2xl font-bold mt-4 text-gray-800">
                    Selamat Datang
                </h1>
                <p class="text-gray-500 text-sm mt-1">
                    Silakan login untuk melanjutkan
                </p>
            </div>

            <!-- Session Status -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf

                <!-- Email -->
                <div>
                    <x-input-label for="email" value="Email" />
                    <x-text-input
                        id="email"
                        type="email"
                        name="email"
                        :value="old('email')"
                        required
                        autofocus
                        class="mt-1 block w-full rounded-xl"
                        placeholder="email@example.com"
                    />
                    <x-input-error :messages="$errors->get('email')" class="mt-1" />
                </div>

                <!-- Password -->
                <div>
                    <x-input-label for="password" value="Password" />
                    <x-text-input
                        id="password"
                        type="password"
                        name="password"
                        required
                        class="mt-1 block w-full rounded-xl"
                        placeholder="••••••••"
                    />
                    <x-input-error :messages="$errors->get('password')" class="mt-1" />
                </div>

                <!-- Remember + Forgot -->
                <div class="flex items-center justify-between text-sm">
                    <label class="flex items-center gap-2">
                        <input
                            type="checkbox"
                            name="remember"
                            class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                        >
                        <span class="text-gray-600">Remember me</span>
                    </label>

                    @if (Route::has('password.request'))
                        <a
                            href="{{ route('password.request') }}"
                            class="text-indigo-600 hover:underline"
                        >
                            Lupa password?
                        </a>
                    @endif
                </div>

                <!-- Button -->
                <button
                    type="submit"
                    class="w-full py-3 rounded-xl bg-indigo-600 text-white font-semibold hover:bg-indigo-700 transition duration-200 shadow-lg"
                >
                    Masuk
                </button>
            </form>

            <!-- Register -->
            <p class="text-center text-sm text-gray-500 mt-6">
                Belum punya akun?
                <a href="{{ route('register') }}" class="text-indigo-600 font-medium hover:underline">
                    Daftar sekarang
                </a>
            </p>

        </div>
    </div>
</x-guest-layout>