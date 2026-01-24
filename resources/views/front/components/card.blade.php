@php
$limit = $limit ?? null;
$offset = $offset ?? 0;

$cases = $limit
? $cases->skip($offset)->take($limit)
: $cases->skip($offset);
@endphp
<style>
    .corner-triangle {
        width: 0;
        height: 0;
        border-top: 20px solid transparent;
        border-right: 20px solid #505153;
        /* warna abu kecil */
        position: absolute;
        bottom: 0;
        right: 0;
    }
</style>

<div class="max-w-7xl mx-auto mt-10 px-4 mb-20 poppins-regular">
    <h2 class="text-slate-500 text-sm font-semibold tracking-wider mb-3">CASE</h2>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">

        <!-- CARD 1 -->
        @foreach ($cases as $c)
        <div class="bg-[#f2f2f3] border border-gray-300 shadow-sm">
            <div class="w-full aspect-[16/9] overflow-hidden">
                <img src="/img/contoh1.jpg" class="w-full object-cover">
            </div>

            <article class="p-4 relative bg-[#a8a8a8]">

                <!-- Category -->
                <div class="min-h-[140px] flex flex-col">
                    <p class="text-xl tracking-wider uppercase font-semibold text-[#003974]">
                        {{ $c->slug }}
                    </p>

                    <!-- Title -->
                    <a href="{{ route('public.verify.case', $c->case_number) }}">
                        <p class="mt-1 text-lg leading-snug text-white font-normal">
                            {{ $c->title }}
                        </p>
                    </a>
                </div>

                <!-- corner triangle -->
                <div class="corner-triangle"></div>
            </article>
        </div>
        @endforeach
    </div>
</div>