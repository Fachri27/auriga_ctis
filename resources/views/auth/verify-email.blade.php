<x-guest-layout>
    <div
        class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="w-full max-w-md space-y-8">
            <!-- Header -->
            <div class="text-center">
                <div class="flex justify-center mb-4">
                    <div class="bg-blue-100 rounded-full p-3">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                            </path>
                        </svg>
                    </div>
                </div>
                <h2 class="text-3xl font-bold tracking-tight text-gray-900">
                    Verifikasi Email Anda
                </h2>
                <p class="mt-2 text-sm text-gray-600">
                    Langkah terakhir untuk menyelesaikan pendaftaran
                </p>
            </div>

            <!-- Content Card -->
            <div class="bg-white py-8 px-6 shadow rounded-lg sm:px-10">
                <!-- Message -->
                <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-md">
                    <p class="text-sm text-blue-800">
                        Terima kasih telah mendaftar! Kami telah mengirimkan link verifikasi ke email Anda.
                        Silakan periksa inbox atau folder spam Anda.
                    </p>
                </div>

                <!-- Status Message -->
                @if (session('status') == 'verification-link-sent')
                <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-md flex items-start">
                    <svg class="w-5 h-5 text-green-600 mr-3 flex-shrink-0 mt-0.5" fill="currentColor"
                        viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd"></path>
                    </svg>
                    <p class="text-sm text-green-800">
                        Link verifikasi baru telah dikirim ke email Anda.
                    </p>
                </div>
                @endif

                @if (session('success'))
                <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-md flex items-start">
                    <svg class="w-5 h-5 text-green-600 mr-3 flex-shrink-0 mt-0.5" fill="currentColor"
                        viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd"></path>
                    </svg>
                    <p class="text-sm text-green-800">
                        {{ session('success') }}
                    </p>
                </div>
                @endif

                <!-- Instructions -->
                <div class="space-y-4 mb-6">
                    <h3 class="text-sm font-semibold text-gray-900">Langkah-langkah verifikasi:</h3>
                    <ol class="space-y-2">
                        <li class="flex items-start">
                            <span
                                class="flex-shrink-0 h-6 w-6 flex items-center justify-center rounded-full bg-indigo-100 text-sm font-medium text-indigo-600">1</span>
                            <span class="ml-3 text-sm text-gray-600">Buka email yang dikirim ke {{
                                Auth::user()->email ?? session('emailForVerification') }}</span>
                        </li>
                        <li class="flex items-start">
                            <span
                                class="flex-shrink-0 h-6 w-6 flex items-center justify-center rounded-full bg-indigo-100 text-sm font-medium text-indigo-600">2</span>
                            <span class="ml-3 text-sm text-gray-600">Klik tombol "Verifikasi Email" di email</span>
                        </li>
                        <li class="flex items-start">
                            <span
                                class="flex-shrink-0 h-6 w-6 flex items-center justify-center rounded-full bg-indigo-100 text-sm font-medium text-indigo-600">3</span>
                            <span class="ml-3 text-sm text-gray-600">Email Anda akan terverifikasi dan Anda siap
                                menggunakan aplikasi</span>
                        </li>
                    </ol>
                </div>

                <!-- Resend Button -->
                <form method="POST" action="{{ route('verification.send') }}" class="mb-4">
                    @csrf
                    <button type="submit"
                        class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                            </path>
                        </svg>
                        Kirim Ulang Email Verifikasi
                    </button>
                </form>

                <!-- Logout Button -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="w-full flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                        Keluar
                    </button>
                </form>
            </div>

            <!-- Help Section -->
            <div class="bg-white py-6 px-6 shadow rounded-lg sm:px-10">
                <h3 class="text-sm font-semibold text-gray-900 mb-4">Butuh Bantuan?</h3>
                <p class="text-sm text-gray-600 mb-3">
                    Email tidak diterima? Pastikan untuk:
                </p>
                <ul class="space-y-2 text-sm text-gray-600">
                    <li class="flex items-start">
                        <svg class="w-4 h-4 mr-2 mt-0.5 text-gray-400 flex-shrink-0" fill="currentColor"
                            viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 10 10.293 5.707a1 1 0 010-1.414z"
                                clip-rule="evenodd"></path>
                        </svg>
                        Periksa folder "Spam" atau "Promotions"
                    </li>
                    <li class="flex items-start">
                        <svg class="w-4 h-4 mr-2 mt-0.5 text-gray-400 flex-shrink-0" fill="currentColor"
                            viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 10 10.293 5.707a1 1 0 010-1.414z"
                                clip-rule="evenodd"></path>
                        </svg>
                        Pastikan email yang Anda daftar sudah benar
                    </li>
                    <li class="flex items-start">
                        <svg class="w-4 h-4 mr-2 mt-0.5 text-gray-400 flex-shrink-0" fill="currentColor"
                            viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 10 10.293 5.707a1 1 0 010-1.414z"
                                clip-rule="evenodd"></path>
                        </svg>
                        Gunakan tombol "Kirim Ulang" jika belum menerima
                    </li>
                </ul>
            </div>
        </div>
    </div>
</x-guest-layout>