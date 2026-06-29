<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }} - Login</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">

    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-[#032A36] via-[#034454] to-[#045E72] px-4">
        <div class="w-full max-w-md">

            {{-- Card --}}
            <div class="bg-white rounded-2xl shadow-2xl p-8">

                {{-- Logo & Title --}}
                <div class="text-center mb-8">
                    <img src="/img/image.png" alt="Logo" class="h-14 mx-auto mb-4 object-contain">
                    <h1 class="text-2xl font-bold text-gray-800">Selamat Datang</h1>
                    <p class="text-gray-500 text-sm mt-1">Silakan masuk ke akun Anda</p>
                </div>

                {{-- Session Status --}}
                @if (session('status'))
                    <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">
                        {{ session('status') }}
                    </div>
                @endif

                {{-- Error --}}
                @if (session('error'))
                    <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">
                        {{ session('error') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Form --}}
                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    {{-- Email --}}
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                            class="block w-full px-4 py-3 rounded-xl border border-gray-300 text-gray-900 placeholder-gray-400 focus:border-[#032A36] focus:ring-2 focus:ring-[#032A36]/20 transition duration-200"
                            placeholder="email@example.com">
                    </div>

                    {{-- Password --}}
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <input id="password" type="password" name="password" required
                            class="block w-full px-4 py-3 rounded-xl border border-gray-300 text-gray-900 placeholder-gray-400 focus:border-[#032A36] focus:ring-2 focus:ring-[#032A36]/20 transition duration-200"
                            placeholder="••••••••">
                    </div>

                    {{-- Remember + Forgot --}}
                    <div class="flex items-center justify-between text-sm">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="remember"
                                class="rounded border-gray-300 text-[#032A36] focus:ring-[#032A36]">
                            <span class="text-gray-600">Ingat saya</span>
                        </label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}"
                                class="text-[#032A36] hover:underline font-medium">
                                Lupa password?
                            </a>
                        @endif
                    </div>

                    {{-- Button --}}
                    <button type="submit"
                        class="w-full py-3 px-4 rounded-xl bg-[#032A36] text-white font-semibold hover:bg-[#034454] focus:ring-2 focus:ring-[#032A36]/30 transition duration-200 shadow-lg cursor-pointer">
                        Masuk
                    </button>
                </form>

                {{-- Register --}}
                <p class="text-center text-sm text-gray-500 mt-6">
                    Belum punya akun?
                    <a href="{{ route('register') }}" class="text-[#032A36] font-medium hover:underline">
                        Daftar
                    </a>
                </p>

            </div>

            {{-- Footer --}}
            <p class="text-center text-xs text-white/60 mt-6">
                &copy; {{ date('Y') }} Environmental Defender. All rights reserved.
            </p>
        </div>
    </div>

</body>
</html>
