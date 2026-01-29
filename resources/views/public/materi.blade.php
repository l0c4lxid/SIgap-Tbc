@extends('layouts.public-viewer')

@section('content')
    
    {{-- Cinema Mode Wrappper --}}
    <div class="flex flex-col items-center justify-center min-h-[85vh] w-full max-w-[1600px] mx-auto">
        
        {{-- Header Section --}}
        <div class="text-center mb-8 animate-fade-in-up">
            <span class="inline-block px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs font-bold uppercase tracking-widest mb-3">
                Materi Edukasi Resmi
            </span>
            <h1 class="text-3xl md:text-5xl font-black text-gray-900 mb-2 tracking-tight">
                Lembar Balik TBC
            </h1>
            <p class="text-gray-500 max-w-2xl mx-auto text-lg">
                Panduan visual pencegahan dan penanganan TBC untuk kader dan masyarakat.
            </p>
        </div>

        <div class="relative w-full aspect-video md:aspect-[16/9] lg:aspect-[2/1] bg-gray-900 rounded-3xl shadow-2xl overflow-hidden border-4 border-gray-900 ring-1 ring-gray-200">
            
            {{-- React Mount Point --}}
            <div id="materiFlipbook" 
                 data-pages='@json($pages)' 
                 data-pdf-url="{{ asset('pdf/' . rawurlencode('Lembar balik.pdf')) }}"
                 class="w-full h-full relative">
                 
                 {{-- Loading Indicator (SSR) --}}
                 {{-- Placed INSIDE so React wipes it when mounting --}}
                 <div class="absolute inset-0 flex flex-col items-center justify-center text-white/30 pointer-events-none z-0">
                     <i class="ri-loader-4-line animate-spin text-4xl mb-3"></i>
                     <span class="text-xs font-mono uppercase tracking-widest">Memuat...</span>
                 </div>
            </div>
        </div>

        {{-- Footer Actions --}}
        <div class="mt-8 flex flex-wrap gap-4 justify-center animate-fade-in-up" style="animation-delay: 200ms;">
            <a href="{{ asset('pdf/' . rawurlencode('Lembar balik.pdf')) }}" download 
               class="flex items-center gap-2 px-6 py-3 rounded-xl bg-white border border-gray-200 text-gray-700 font-bold hover:bg-gray-50 hover:border-emerald-200 hover:text-emerald-600 transition-all shadow-sm hover:shadow-lg no-underline">
                <i class="ri-download-cloud-2-line text-xl"></i>
                <span>Unduh PDF</span>
            </a>
            
            <button onclick="navigator.clipboard.writeText(window.location.href); Swal.fire({icon: 'success', title: 'Tersalin!', text: 'Tautan berhasil disalin.', timer: 1500, showConfirmButton: false})"
               class="flex items-center gap-2 px-6 py-3 rounded-xl bg-emerald-600 text-white font-bold hover:bg-emerald-700 transition-all shadow-lg hover:shadow-emerald-500/30 hover:-translate-y-1">
                <i class="ri-share-forward-fill text-xl"></i>
                <span>Bagikan Materi</span>
            </button>
        </div>

    </div>

@endsection

@push('scripts')
    @vite('resources/js/materi/flipbook-entry.tsx')
@endpush
