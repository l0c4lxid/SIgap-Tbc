@extends('layouts.soft')

@section('subjudul', 'Materi edukasi kader')

@section('content')
     {{-- Flipbook Helper --}}
     <div class="glass-card p-4 mb-6 text-center lg:text-left flex flex-col lg:flex-row justify-between items-center gap-4">
        <div>
             <h5 class="font-bold text-gray-800 mb-1">Buku Panduan Kader</h5>
             <p class="text-sm text-gray-500 mb-0">Klik tombol arrow atau swipe untuk membalik halaman.</p>
        </div>
        <button class="glass-button px-4 py-2 rounded-lg text-sm font-semibold" onclick="document.getElementById('flipFullscreen').click()">
            <i class="ri-fullscreen-line me-1"></i> Layar Penuh
        </button>
    </div>

    <div class="glass-card !bg-[#0f172a] p-1 md:p-4 mb-8 overflow-hidden relative">
         <div class="flip-embed mx-auto">
            <div id="flipbook" class="flipbook-stage" data-pdf-url="{{ asset('pdf/' . rawurlencode('Lembar balik.pdf')) }}">
                <div class="flipbook-loading text-white">Memuat flipbook...</div>
                <div class="flipbook-canvas">
                    <canvas id="flipbookCanvasLeft"></canvas>
                    <canvas id="flipbookCanvasRight"></canvas>
                </div>
            </div>
            <div class="flipbook-page-indicator">
                <span class="flipbook-page" id="flipPageLabel">1</span>
            </div>
            <div class="flipbook-controls">
                <button class="btn btn-outline-light btn-sm rounded-full w-10 h-10 flex items-center justify-center p-0" type="button" id="flipPrev" aria-label="Sebelumnya">
                    <i class="ri-arrow-left-s-line text-xl"></i>
                </button>
                <button class="btn btn-outline-light btn-sm rounded-full w-10 h-10 flex items-center justify-center p-0" type="button" id="flipNext" aria-label="Berikutnya">
                    <i class="ri-arrow-right-s-line text-xl"></i>
                </button>
            </div>
            <button class="btn btn-outline-light btn-sm flipbook-fullscreen-toggle" type="button" id="flipFullscreen" aria-label="Layar penuh">
                <i class="ri-fullscreen-line" id="flipFullscreenIcon"></i>
            </button>
        </div>
    </div>
    
    @if ($downloads->count())
        <div class="glass-card p-6">
            <div class="border-b border-gray-200/50 pb-4 mb-6">
                <h6 class="font-bold text-lg text-gray-800 mb-1">Unduhan PDF</h6>
                <p class="text-sm text-gray-500 mb-0">Salin versi PDF untuk dibaca offline atau dibagikan ke kader lain.</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($downloads as $item)
                    <div class="bg-white/50 border border-gray-200 rounded-xl p-4 flex flex-col justify-between gap-4 hover:shadow-md transition-all">
                        <div>
                             <h6 class="font-bold text-gray-800 text-sm mb-1 line-clamp-2" title="{{ $item['name'] }}">{{ $item['name'] }}</h6>
                             <div class="flex items-center gap-2 text-xs text-gray-500">
                                <span>{{ $item['updated_at']->translatedFormat('d M Y') }}</span>
                                <span>&bull;</span>
                                <span>{{ $item['size'] }} KB</span>
                            </div>
                        </div>
                        <a href="{{ $item['url'] }}" class="glass-button px-4 py-2 rounded-lg text-sm font-semibold flex items-center justify-center gap-2 no-underline" download>
                            <i class="ri-download-cloud-2-line"></i> Unduh PDF
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
@endsection

@push('styles')
    <style>
        /* Preserving Flipbook CSS logic but cleaning up for Tailwind context if needed */
        .flip-embed {
            position: relative;
            width: min(100%, 1100px);
            min-height: 75vh;
            border-radius: 16px; /* Matches rounded-xl slightly */
            overflow: hidden;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
            background: #0f172a;
            box-sizing: border-box;
        }

        .flipbook-stage {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            display: grid;
            place-items: center;
            background: #0f172a;
        }

        .flipbook-canvas {
            width: 100%;
            height: 100%;
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
            align-items: center;
            justify-items: center;
            padding: 1.5rem;
            perspective: 1200px;
            padding-bottom: 4rem;
        }

        #flipbookCanvasLeft,
        #flipbookCanvasRight {
            width: 100%;
            height: auto;
            max-height: 100%;
            box-shadow: 0 15px 35px rgba(0,0,0,0.5);
            border-radius: 8px;
            transform-style: preserve-3d;
        }
        
        /* ... existing flipbook animations and media queries ... */
        /* I will copy the critical parts from the original file to ensure functionality */
        
        .flipbook-loading {
            color: #f8fafc;
            font-weight: 600;
        }

        .flipbook-page-indicator {
            position: absolute;
            inset: 1rem 0 auto;
            display: flex;
            justify-content: center;
            z-index: 2;
        }

        .flipbook-page-indicator .flipbook-page {
            background: rgba(15, 23, 42, 0.7);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 999px;
            font-size: 0.875rem;
        }

        .flipbook-controls {
            position: absolute;
            inset: auto 0 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            z-index: 2;
            background: rgba(15, 23, 42, 0.7);
            padding: 0.4rem 0.75rem;
            border-radius: 999px;
        }

        .flipbook-fullscreen-toggle {
            position: absolute;
            top: 1rem;
            right: 1rem;
            z-index: 6;
            width: 40px;
            height: 40px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(15, 23, 42, 0.7);
            border-radius: 999px;
            color: white;
            border: 1px solid rgba(255,255,255,0.2);
        }
        .flipbook-fullscreen-toggle:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .flipbook-animate-next { animation: flipNext 0.45s ease; transform-origin: right center; }
        .flipbook-animate-prev { animation: flipPrev 0.45s ease; transform-origin: left center; }
        .flipbook-animate-load { animation: flipLoad 0.55s ease; }

        @keyframes flipNext {
            0% { transform: perspective(1400px) rotateY(0deg); opacity: 1; }
            50% { transform: perspective(1400px) rotateY(-28deg); opacity: 0.85; }
            100% { transform: perspective(1400px) rotateY(0deg); opacity: 1; }
        }

        @keyframes flipPrev {
            0% { transform: perspective(1400px) rotateY(0deg); opacity: 1; }
            50% { transform: perspective(1400px) rotateY(28deg); opacity: 0.85; }
            100% { transform: perspective(1400px) rotateY(0deg); opacity: 1; }
        }

        @keyframes flipLoad {
            0% { transform: perspective(1400px) rotateY(-10deg); opacity: 0; }
            100% { transform: perspective(1400px) rotateY(0deg); opacity: 1; }
        }

        @media (max-width: 768px) {
            .flip-embed {
                min-height: calc(100vh - 8rem);
                border-radius: 0;
                margin-left: -1.5rem; /* Offset parent padding if necessary, depends on layout */
                margin-right: -1.5rem;
                width: calc(100% + 3rem);
            }
            .flipbook-canvas {
                padding: 0.75rem;
            }
        }
        
        /* Fullscreen overrides */
        .flipbook-fullscreen {
            position: fixed;
            inset: 0;
            z-index: 9999;
            border-radius: 0;
            margin: 0;
            max-width: 100%;
            width: 100%;
            min-height: 100vh;
            background: #0f172a;
        }
        body.flipbook-mode {
            overflow: hidden;
        }
        body.flipbook-mode header, 
        body.flipbook-mode aside, 
        body.flipbook-mode footer,
        body.flipbook-mode .navbar {
             display: none !important;
        }
    </style>
@endpush
