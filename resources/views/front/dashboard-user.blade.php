@extends('layouts.app')

@section('content')
{{-- <div class="w-full bg-[#032A36] py-10 px-5 text-white" x-data="{}">

    <!-- MAP (Mobile & Desktop Responsive) -->
    <div class="flex justify-center mb-10">
        <img src="/img/map-indonesia.png"
             alt="Map"
             class="w-full max-w-4xl opacity-90">
    </div>

    <!-- FILTER SECTION -->
    <div class="max-w-4xl mx-auto">

        <!-- MOBILE TITLE (optional) -->
        <h2 class="text-center text-lg font-semibold mb-6 md:hidden tracking-wide">
            Cari Data Indonesia
        </h2>

        <!-- INPUTS GRID -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">

            <!-- Keyword -->
            <div>
                <label class="text-xs uppercase tracking-widest opacity-70 mb-1 block">Keyword</label>
                <input type="text"
                       class="w-full bg-white/10 border border-white/30 px-3 py-3 rounded-md focus:border-white/60 outline-none text-sm placeholder-white/50"
                       placeholder="Cari kata kunci...">
            </div>

            <!-- Sector -->
            <div>
                <label class="text-xs uppercase tracking-widest opacity-70 mb-1 block">Sector</label>
                <input type="text"
                       class="w-full bg-white/10 border border-white/30 px-3 py-3 rounded-md focus:border-white/60 outline-none text-sm placeholder-white/50"
                       placeholder="Contoh: Energi">
            </div>

            <!-- Status -->
            <div>
                <label class="text-xs uppercase tracking-widest opacity-70 mb-1 block">Status</label>
                <input type="text"
                       class="w-full bg-white/10 border border-white/30 px-3 py-3 rounded-md focus:border-white/60 outline-none text-sm placeholder-white/50"
                       placeholder="Aktif / Tidak aktif">
            </div>

            <!-- Location -->
            <div>
                <label class="text-xs uppercase tracking-widest opacity-70 mb-1 block">Location</label>
                <input type="text"
                       class="w-full bg-white/10 border border-white/30 px-3 py-3 rounded-md focus:border-white/60 outline-none text-sm placeholder-white/50"
                       placeholder="Nama kota / provinsi">
            </div>

            <!-- SEARCH BUTTON -->
            <div class="flex items-end">
                <button class="w-full py-3 rounded-md border border-white/40 bg-white/10 hover:bg-white hover:text-[#00323C] transition font-semibold tracking-wide text-sm flex items-center justify-center gap-2">
                    <span>Cari</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                         fill="currentColor" viewBox="0 0 16 16">
                        <path
                            d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001l3.85 
                            3.85a1 1 0 0 0 
                            1.415-1.414l-3.85-3.85zm-5.242 
                            1.656a5 5 0 1 1 
                            0-10 5 5 0 0 1 0 10z" />
                    </svg>
                </button>
            </div>

        </div>
    </div>

</div> --}}
@include('front.components.peta')
@include('front.components.card')
@include('front.components.case_statistik')
@include('front.components.dokumentasi')
{{-- @include('front.components.footer') --}}

@endsection