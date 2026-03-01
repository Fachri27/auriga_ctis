@extends('layouts.main')

@section('content')

<div class="max-w-6xl mx-auto md:mt-40 mt-30">
    <div class="font-sans md:text-5xl text-3xl font-semibold text-center">{{ __('messages.about') }}</div>
    <div class="md:text-center font-sans md:text-lg text-sm mx-5 text-justify font-light mt-5">
        Lorem ipsum dolor sit amet consectetur adipisicing elit. Reprehenderit tempora similique velit? Ut quidem
        blanditiis, enim maiores libero cupiditate voluptas quae deserunt itaque vitae aperiam, a ipsam ex
        exercitationem repudiandae!
    </div>
    <div class="text-center mt-10">
        <button
            class="bg-blue-500 hover:bg-blue-700 text-white font-bold md:py-5 py-3 md:px-8 px-5 font-sans md:text-lg text-sm rounded">
            Hubungi Kami
        </button>
    </div>

    {{-- card 2 kolom --}}
    <div class="md:grid grid-cols-2 gap-8 mt-10 mx-4 space-y-5 md:space-y-0 mb-5">
        <div class="p-6 rounded-lg shadow-xl">
            <h2 class="text-2xl font-semibold mb-4 font-sans">Visi Kami</h2>
            <p class="text-sm md:text-lg font-light">
                Lorem ipsum dolor sit amet consectetur adipisicing elit. Reprehenderit tempora similique velit? Ut
                quidem blanditiis, enim maiores libero cupiditate voluptas quae deserunt itaque vitae aperiam, a ipsam
                ex exercitationem repudiandae! Lorem ipsum dolor sit amet, consectetur adipisicing elit. Quasi, alias?
                Nesciunt aliquam quibusdam quis laborum mollitia repudiandae, necessitatibus recusandae animi
                perspiciatis, commodi officia iure repellat similique aliquid corporis consequatur atque.
            </p>
        </div>
        <div class="p-6 rounded-lg shadow-xl">
            <h2 class="text-2xl font-semibold mb-4 font-sans">Visi Kami</h2>
            <p class="text-sm md:text-lg font-light">
                Lorem ipsum dolor sit amet consectetur adipisicing elit. Reprehenderit tempora similique velit? Ut
                quidem blanditiis, enim maiores libero cupiditate voluptas quae deserunt itaque vitae aperiam, a ipsam
                ex exercitationem repudiandae! Lorem ipsum dolor sit amet, consectetur adipisicing elit. Quasi, alias?
                Nesciunt aliquam quibusdam quis laborum mollitia repudiandae, necessitatibus recusandae animi
                perspiciatis, commodi officia iure repellat similique aliquid corporis consequatur atque.
            </p>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-6 py-16">

        {{-- HEADER --}}
        <div class="text-center mb-14">
            <h1 class="text-4xl font-bold">Panduan Pengguna</h1>
            <p class="text-gray-500 mt-3">
                Ikuti langkah-langkah berikut untuk menggunakan sistem dengan mudah.
            </p>
        </div>

        <div class="flex flex-col md:flex-row gap-10">

            {{-- SIDEBAR --}}
            <aside class="md:w-1/4">
                <div class="sticky top-24 bg-white shadow rounded-xl p-6">
                    <ul class="space-y-3 text-sm">
                        <li><a href="#login" class="hover:text-green-600">Login</a></li>
                        <li><a href="#dashboard" class="hover:text-green-600">Dashboard</a></li>
                        <li><a href="#cases" class="hover:text-green-600">Kelola Kasus</a></li>
                        <li><a href="#report" class="hover:text-green-600">Laporan</a></li>
                        <li><a href="#faq" class="hover:text-green-600">FAQ</a></li>
                    </ul>
                </div>
            </aside>


            {{-- CONTENT --}}
            <main class="md:w-3/4 space-y-16">

                {{-- LOGIN --}}
                <section id="login" class="space-y-4">
                    <h2 class="text-2xl font-semibold">Login</h2>
                    <ol class="list-decimal list-inside text-gray-600 space-y-2">
                        <li>Buka halaman login</li>
                        <li>Masukkan email dan password</li>
                        <li>Klik tombol Login</li>
                    </ol>
                </section>


                {{-- DASHBOARD --}}
                <section id="dashboard" class="space-y-4">
                    <h2 class="text-2xl font-semibold">Dashboard</h2>
                    <p class="text-gray-600">
                        Dashboard menampilkan ringkasan statistik kasus, laporan, dan aktivitas terbaru.
                    </p>
                </section>


                {{-- CASES --}}
                <section id="cases" class="space-y-4">
                    <h2 class="text-2xl font-semibold">Kelola Kasus</h2>
                    <ol class="list-decimal list-inside text-gray-600 space-y-2">
                        <li>Klik menu Cases</li>
                        <li>Pilih Tambah Kasus</li>
                        <li>Isi data lengkap</li>
                        <li>Simpan</li>
                    </ol>
                </section>


                {{-- REPORT --}}
                <section id="report" class="space-y-4">
                    <h2 class="text-2xl font-semibold">Laporan</h2>
                    <p class="text-gray-600">
                        Anda dapat mengekspor laporan dalam format Excel atau PDF.
                    </p>
                </section>


                {{-- FAQ --}}
                <section id="faq" class="space-y-4">
                    <h2 class="text-2xl font-semibold">FAQ</h2>

                    <div class="bg-white shadow rounded-xl p-6 space-y-4">
                        <p><strong>Lupa password?</strong><br> Gunakan fitur reset password.</p>
                        <p><strong>Data tidak tersimpan?</strong><br> Periksa koneksi internet Anda.</p>
                    </div>
                </section>

            </main>

        </div>

    </div>
</div>

@endsection