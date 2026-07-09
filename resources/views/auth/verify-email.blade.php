<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }} - Verifikasi Email</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">

    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-[#264c16] via-[#1d3d11] to-[#132b0b] px-4 py-12">
        <div class="w-full max-w-md">

            {{-- Card --}}
            <div class="bg-white rounded-2xl shadow-2xl p-8">

                {{-- Logo & Title --}}
                <div class="text-center mb-8">
                    <img src="/img/image.png" alt="Logo" class="h-14 mx-auto mb-4 object-contain">
                    <h1 class="text-2xl font-bold text-gray-800">Verifikasi Email</h1>
                    <p class="text-gray-500 text-sm mt-1">Langkah terakhir untuk menyelesaikan pendaftaran</p>
                </div>

                {{-- Info --}}
                <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-xl text-sm text-blue-800 flex items-start gap-3">
                    <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    <span>Terima kasih telah mendaftar! Kami telah mengirimkan link verifikasi ke email Anda. Silakan periksa inbox atau folder spam Anda.</span>
                </div>

                {{-- Status Messages --}}
                @if (session('status') == 'verification-link-sent')
                <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl flex items-start gap-3">
                    <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="text-sm text-green-800">Link verifikasi baru telah dikirim ke email Anda.</span>
                </div>
                @endif

                @if (session('success'))
                <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl flex items-start gap-3">
                    <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="text-sm text-green-800">{{ session('success') }}</span>
                </div>
                @endif

                {{-- Steps --}}
                <div class="space-y-4 mb-6">
                    <h3 class="text-sm font-semibold text-gray-900">Langkah-langkah verifikasi:</h3>
                    <ol class="space-y-2">
                        <li class="flex items-start">
                            <span class="flex-shrink-0 h-6 w-6 flex items-center justify-center rounded-full bg-green-100 text-sm font-medium text-[#264c16]">1</span>
                            <span class="ml-3 text-sm text-gray-600">Buka email yang dikirim ke <strong class="text-gray-900">{{ Auth::user()->email ?? session('emailForVerification') }}</strong></span>
                        </li>
                        <li class="flex items-start">
                            <span class="flex-shrink-0 h-6 w-6 flex items-center justify-center rounded-full bg-green-100 text-sm font-medium text-[#264c16]">2</span>
                            <span class="ml-3 text-sm text-gray-600">Klik tombol <strong class="text-gray-900">"Verifikasi Email"</strong> di email</span>
                        </li>
                        <li class="flex items-start">
                            <span class="flex-shrink-0 h-6 w-6 flex items-center justify-center rounded-full bg-green-100 text-sm font-medium text-[#264c16]">3</span>
                            <span class="ml-3 text-sm text-gray-600">Email Anda akan terverifikasi dan Anda siap menggunakan aplikasi</span>
                        </li>
                    </ol>
                </div>

                {{-- Resend --}}
                <form method="POST" action="{{ route('verification.send') }}" class="mb-4">
                    @csrf
                    <button type="submit"
                        class="w-full py-3 px-4 rounded-xl bg-[#264c16] text-white font-semibold hover:bg-[#034454] focus:ring-2 focus:ring-[#264c16]/30 transition duration-200 shadow-lg cursor-pointer flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Kirim Ulang Email Verifikasi
                    </button>
                </form>

                {{-- Logout --}}
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="w-full py-3 px-4 rounded-xl border border-gray-300 text-gray-700 font-semibold hover:bg-gray-50 focus:ring-2 focus:ring-gray-300 transition duration-200 cursor-pointer">
                        Keluar
                    </button>
                </form>

            </div>

            {{-- Copyright --}}
            <p class="text-center text-xs text-white/60 mt-6">
                &copy; {{ date('Y') }} Environmental Defender. All rights reserved.
            </p>
        </div>
    </div>

</body>
</html>