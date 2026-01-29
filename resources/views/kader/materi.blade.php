@php
    $breadcrumbs = [
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Materi Edukasi', 'url' => '#'],
    ];
    
    // Server-side Image Scanning (Option A Implementation)
    $materiDir = storage_path('app/public/materi/kader/pages');
    $pages = [];
    if (file_exists($materiDir)) {
        $files = scandir($materiDir);
        foreach ($files as $file) {
            if (in_array(pathinfo($file, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png', 'webp'])) {
                $pages[] = asset('storage/materi/kader/pages/' . $file);
            }
        }
        sort($pages); // Ensure 001, 002 order
    }
@endphp

@extends('layouts.soft')

@section('content')

    {{-- Header / Helper --}}
    <div class="glass-card px-6 py-6 mb-8 flex flex-col md:flex-row justify-between items-center gap-4">
        <div>
            <h5 class="font-bold text-gray-800 mb-1">
                <i class="fa-solid fa-book-open text-emerald-500 mr-2"></i> Buku Panduan Kader
            </h5>
            <p class="text-sm text-gray-500 mb-0 max-w-2xl">
                Gunakan panah atau geser (swipe) untuk membalik halaman. Gunakan fitur zoom untuk memperjelas teks.
            </p>
        </div>
        <div>
            <button onclick="document.getElementById('flipFullscreen').click()" 
                class="bg-gray-900 text-white px-5 py-2.5 rounded-xl text-sm font-bold shadow-lg shadow-gray-900/20 hover:bg-emerald-600 transition-all flex items-center gap-2">
                <i class="fa-solid fa-expand"></i> Layar Penuh
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
                 <div class="animate-spin text-4xl mb-4"><i class="fa-solid fa-circle-notch"></i></div>
                 <p>Memuat Flipbook...</p>
                 @if(empty($pages))
                    <!-- Pages will be generated client-side -->
                 @endif
             </div>
        </div>

        {{-- Hidden Fullscreen Toggle (Preserved for React Hook) --}}
        <button id="flipFullscreen" class="hidden"></button>
    </div>

    {{-- Downloads Section --}}
    <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="glass-card p-6 flex items-start gap-4">
            <div class="w-12 h-12 rounded-xl bg-red-50 text-red-500 flex items-center justify-center text-xl shrink-0">
                <i class="fa-regular fa-file-pdf"></i>
            </div>
            <div>
                <h6 class="font-bold text-gray-800">Unduh PDF Lengkap</h6>
                <p class="text-sm text-gray-500 mb-3">Versi dokumen asli untuk dicetak atau dibaca offline.</p>
                <a href="{{ asset('pdf/' . rawurlencode('Lembar balik.pdf')) }}" download class="text-sm font-bold text-red-500 hover:text-red-600 hover:underline">
                    Download PDF <i class="fa-solid fa-download ml-1"></i>
                </a>
            </div>
        </div>
        
        <div class="glass-card p-6 flex items-start gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-500 flex items-center justify-center text-xl shrink-0">
                <i class="fa-solid fa-share-nodes"></i>
            </div>
            <div>
                 <h6 class="font-bold text-gray-800">Bagikan Materi</h6>
                 <p class="text-sm text-gray-500 mb-3">Salin tautan untuk membagikan modul ini ke kader lain.</p>
                 <button onclick="navigator.clipboard.writeText(window.location.href); Swal.fire('Tersalin!', 'Tautan berhasil disalin.', 'success')" 
                    class="text-sm font-bold text-blue-500 hover:text-blue-600 hover:underline">
                    Salin Tautan <i class="fa-regular fa-copy ml-1"></i>
                 </button>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    @vite('resources/js/materi/flipbook-entry.tsx')
@endpush
