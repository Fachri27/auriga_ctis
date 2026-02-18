@extends('layouts.main')

@section('content')



<div>
    {{-- ============================= --}}
    {{-- BAGIAN PARALLAX HEADER --}}
    {{-- ============================= --}}
    <section x-data="{ offset: 0 }" x-init="
                window.addEventListener('scroll', () => {
                    if (window.innerWidth >= 768) {
                        offset = window.scrollY * 0.3
                    }
                })
            " class="relative min-h-[30vh] md:min-h-[80vh] overflow-hidden md:mb-20 mb-10">
        <!-- Background Image -->
        <div class="absolute inset-0 w-full h-full bg-center bg-cover"
            :style="`transform: translateY(${offset}px); background-image: url('{{ asset('storage/' . $case->image) }}')`">
        </div>
    </section>

    <div class="max-w-4xl mx-auto text-center my-10 sm:my-16 md:my-20 px-5">
        {{-- {!! nl2br(e($translation->title)) !!} --}}
        <div class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-base mb-7 font-serif">
            {{ $case->title ?? $case->slug }}
        </div>
    </div>
</div>

<div class="
      prose
      max-w-2xl mx-auto
      px-5
      poppins-regular

      md:text-md sm:text-base text-sm
      text-left

      prose-p:leading-relaxed md:prose-p:leading-relaxed
      prose-p:tracking-[0.020em]
      prose-p:my-[1em]

      prose-h2:text-[24px]
      prose-h2:mt-8 prose-h2:mb-4 prose-h2:font-bold

      prose-h3:text-[21px]
      prose-h3:mt-6 prose-h3:mb-3 prose-h3:font-semibold
    ">
    {!! $case->content !!}
</div>


{{--  --}}

@endsection