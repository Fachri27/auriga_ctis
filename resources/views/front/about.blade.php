@extends('layouts.main')

@section('content')

<style>
.about-content img,
.about-content video,
.about-content iframe {
    max-width: 100%;
    height: auto;
}
.about-content p,
.about-content div {
    word-break: break-word;
    overflow-wrap: break-word;
}
</style>


<div class="max-w-6xl mx-auto md:mt-40 mt-30">
    <div class="font-sans md:text-5xl text-3xl font-semibold text-center">{{ __('messages.about') }}</div>
    <div class="md:text-center font-sans md:text-lg text-sm mx-5 text-justify font-light mt-5 about-content">
        @php $content = $about?->translation(app()->getLocale())?->content; @endphp
        {!! $content ?: '<em class="text-gray-400">' . (app()->getLocale() === 'id' ? 'Belum ada teks about' : 'No about text yet') . '</em>' !!}
    </div>
    <div class="text-center mt-10">
        <button
            class="bg-[#032A36] hover:bg-[#044454] text-white font-bold md:py-5 py-3 md:px-8 px-5 font-sans md:text-lg text-sm rounded">
            Hubungi Kami
        </button>
    </div>

    <div class="md:grid grid-cols-2 gap-8 mt-10 mx-4 space-y-5 md:space-y-0 mb-5">
        <div class="p-6 rounded-lg shadow-xl about-content">
            <h2 class="text-2xl font-semibold mb-4 font-sans">{{ app()->getLocale() === 'id' ? 'Visi Kami' : 'Our Vision' }}</h2>
            <div class="text-sm md:text-lg font-light">
                @php $vision = $about?->translation(app()->getLocale())?->vision; @endphp
                {!! $vision ?: '<em class="text-gray-400">' . (app()->getLocale() === 'id' ? 'Belum ada teks visi' : 'No vision text yet') . '</em>' !!}
            </div>
        </div>
        <div class="p-6 rounded-lg shadow-xl about-content">
            <h2 class="text-2xl font-semibold mb-4 font-sans">{{ app()->getLocale() === 'id' ? 'Misi Kami' : 'Our Mission' }}</h2>
            <div class="text-sm md:text-lg font-light">
                @php $mission = $about?->translation(app()->getLocale())?->mission; @endphp
                {!! $mission ?: '<em class="text-gray-400">' . (app()->getLocale() === 'id' ? 'Belum ada teks misi' : 'No mission text yet') . '</em>' !!}
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-6 py-16">
        <div class="text-center mb-14">
            <h1 class="text-4xl font-bold">{{ app()->getLocale() === 'id' ? 'Panduan Pengguna' : 'User Guide' }}</h1>
            <p class="text-gray-500 mt-3">
                {{ app()->getLocale() === 'id' ? 'Ikuti langkah-langkah berikut untuk menggunakan sistem dengan mudah.' : 'Follow these steps to use the system easily.' }}
            </p>
        </div>

        <div class="flex flex-col md:flex-row gap-10">
            <aside class="md:w-1/4">
                <div class="sticky top-24 bg-white shadow rounded-xl p-6">
                    <ul class="space-y-3 text-sm">
                        <li><a href="#login" class="hover:text-green-600">{{ app()->getLocale() === 'id' ? 'Login' : 'Login' }}</a></li>
                        <li><a href="#dashboard" class="hover:text-green-600">{{ app()->getLocale() === 'id' ? 'Dashboard' : 'Dashboard' }}</a></li>
                        <li><a href="#cases" class="hover:text-green-600">{{ app()->getLocale() === 'id' ? 'Kelola Kasus' : 'Manage Cases' }}</a></li>
                        <li><a href="#report" class="hover:text-green-600">{{ app()->getLocale() === 'id' ? 'Laporan' : 'Reports' }}</a></li>
                        <li><a href="#faq" class="hover:text-green-600">FAQ</a></li>
                    </ul>
                </div>
            </aside>

            <main class="md:w-3/4 space-y-16">
                <section id="login" class="space-y-4">
                    <h2 class="text-2xl font-semibold">{{ app()->getLocale() === 'id' ? 'Login' : 'Login' }}</h2>
                    <ol class="list-decimal list-inside text-gray-600 space-y-2">
                        <li>{{ app()->getLocale() === 'id' ? 'Buka halaman login' : 'Open the login page' }}</li>
                        <li>{{ app()->getLocale() === 'id' ? 'Masukkan email dan password' : 'Enter your email and password' }}</li>
                        <li>{{ app()->getLocale() === 'id' ? 'Klik tombol Login' : 'Click the Login button' }}</li>
                    </ol>
                </section>

                <section id="dashboard" class="space-y-4">
                    <h2 class="text-2xl font-semibold">{{ app()->getLocale() === 'id' ? 'Dashboard' : 'Dashboard' }}</h2>
                    <p class="text-gray-600">
                        {{ app()->getLocale() === 'id' ? 'Dashboard menampilkan ringkasan statistik kasus, laporan, dan aktivitas terbaru.' : 'The dashboard displays a summary of case statistics, reports, and recent activities.' }}
                    </p>
                </section>

                <section id="cases" class="space-y-4">
                    <h2 class="text-2xl font-semibold">{{ app()->getLocale() === 'id' ? 'Kelola Kasus' : 'Manage Cases' }}</h2>
                    <ol class="list-decimal list-inside text-gray-600 space-y-2">
                        <li>{{ app()->getLocale() === 'id' ? 'Klik menu Cases' : 'Click the Cases menu' }}</li>
                        <li>{{ app()->getLocale() === 'id' ? 'Pilih Tambah Kasus' : 'Select Add Case' }}</li>
                        <li>{{ app()->getLocale() === 'id' ? 'Isi data lengkap' : 'Fill in the complete data' }}</li>
                        <li>{{ app()->getLocale() === 'id' ? 'Simpan' : 'Save' }}</li>
                    </ol>
                </section>

                <section id="report" class="space-y-4">
                    <h2 class="text-2xl font-semibold">{{ app()->getLocale() === 'id' ? 'Laporan' : 'Reports' }}</h2>
                    <p class="text-gray-600">
                        {{ app()->getLocale() === 'id' ? 'Anda dapat mengekspor laporan dalam format Excel atau PDF.' : 'You can export reports in Excel or PDF format.' }}
                    </p>
                </section>

                <section id="faq" class="space-y-4">
                    <h2 class="text-2xl font-semibold">FAQ</h2>
                    <div class="bg-white shadow rounded-xl p-6 space-y-4">
                        <p><strong>{{ app()->getLocale() === 'id' ? 'Lupa password?' : 'Forgot password?' }}</strong><br> {{ app()->getLocale() === 'id' ? 'Gunakan fitur reset password.' : 'Use the password reset feature.' }}</p>
                        <p><strong>{{ app()->getLocale() === 'id' ? 'Data tidak tersimpan?' : 'Data not saved?' }}</strong><br> {{ app()->getLocale() === 'id' ? 'Periksa koneksi internet Anda.' : 'Check your internet connection.' }}</p>
                    </div>
                </section>
            </main>
        </div>
    </div>
</div>

@endsection
