@php
    $breadcrumbs = [
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Materi Edukasi', 'url' => '#'],
    ]; // Note: Breadcrumbs might not act render in public layout, which is fine.

    // Public Route URL for sharing
    $publicLink = route('public.materi'); 
@endphp

@extends(auth()->check() ? 'layouts.soft' : 'layouts.public-viewer')

@section('content')

    {{-- Header / Helper --}}
    <div class="glass-card px-6 py-6 mb-8 flex flex-col md:flex-row justify-between items-center gap-4">
        <div>
            <h5 class="font-bold text-gray-800 mb-1 flex items-center">
                <i class="ri-book-open-line text-emerald-500 mr-2 text-xl"></i> Buku Panduan Kader
            </h5>
            <p class="text-sm text-gray-500 mb-0 max-w-2xl">
                Gunakan panah atau geser (swipe) untuk membalik halaman. Gunakan fitur zoom untuk memperjelas teks.
            </p>
        </div>
        <div>
            <button onclick="window.dispatchEvent(new CustomEvent('toggle-flipbook-fullscreen'))" 
                class="bg-gray-900 text-white px-5 py-2.5 rounded-xl text-sm font-bold shadow-lg shadow-gray-900/20 hover:bg-emerald-600 transition-all flex items-center gap-2">
                <i class="ri-fullscreen-line text-lg"></i> Layar Penuh
            </button>
        </div>
    </div>

    {{-- FLIPBOOK STAGE (React Mount Point) --}}
    <div class="flip-embed relative w-full bg-[#1e1e1e] rounded-2xl overflow-hidden shadow-2xl border border-gray-800" 
         style="min-height: 80vh;">
        
        {{-- React Root --}}
        <div id="materiFlipbook" 
             data-pages='@json($pages)' 
             data-pdf-url="{{ asset('pdf/' . rawurlencode('Lembar balik.pdf')) }}"
             class="w-full h-full">
             
             {{-- Loading State (SSR / No-JS fallback) --}}
             <div class="absolute inset-0 flex flex-col items-center justify-center text-white/50">
                 <div class="animate-spin text-4xl mb-4"><i class="ri-loader-4-line"></i></div>
                 <p>Memuat Flipbook...</p>
             </div>
        </div>
    </div>

    {{-- Downloads Section --}}
    <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="glass-card p-6 flex items-start gap-4">
            <div class="w-12 h-12 rounded-xl bg-red-50 text-red-500 flex items-center justify-center text-xl shrink-0">
                <i class="ri-file-pdf-2-line text-2xl"></i>
            </div>
            <div>
                <h6 class="font-bold text-gray-800">Unduh PDF Lengkap</h6>
                <p class="text-sm text-gray-500 mb-3">Versi dokumen asli untuk dicetak atau dibaca offline.</p>
                <a href="{{ asset('pdf/' . rawurlencode('Lembar balik.pdf')) }}" download class="text-sm font-bold text-red-500 hover:text-red-600 hover:underline flex items-center gap-1">
                    Download PDF <i class="ri-download-line"></i>
                </a>
            </div>
        </div>
        
        <div class="glass-card p-6 flex items-start gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-500 flex items-center justify-center text-xl shrink-0">
                <i class="ri-share-forward-line text-2xl"></i>
            </div>
            <div>
                 <h6 class="font-bold text-gray-800">Bagikan Materi</h6>
                 <p class="text-sm text-gray-500 mb-3">Salin tautan untuk membagikan modul ini ke kader atau masyarakat.</p>
                 <button onclick="navigator.clipboard.writeText('{{ $publicLink }}'); Swal.fire({icon: 'success', title: 'Tersalin!', text: 'Tautan publik berhasil disalin ke clipboard.', timer: 1500, showConfirmButton: false})" 
                    class="text-sm font-bold text-blue-500 hover:text-blue-600 hover:underline flex items-center gap-1">
                    Salin Tautan Publik <i class="ri-file-copy-line"></i>
                 </button>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    @vite('resources/js/materi/flipbook-entry.tsx')
@endpush
