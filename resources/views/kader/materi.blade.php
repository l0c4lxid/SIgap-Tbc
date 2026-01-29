@extends('layouts.soft')

@section('subjudul', 'Materi edukasi kader')

@section('content')
    {{-- Header / Helper --}}
    <div class="glass-card p-4 md:p-5 mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="min-w-0">
                <h5 class="font-bold text-gray-800 mb-1 leading-tight">Buku Panduan Kader</h5>
                <p class="text-sm text-gray-500 mb-0">
                    Klik tombol panah atau <span class="font-semibold">swipe</span> untuk membalik halaman.
                </p>
            </div>

            <div class="flex items-center gap-2 md:gap-3 shrink-0">
                <button
                    type="button"
                    class="glass-button px-4 py-2 rounded-lg text-sm font-semibold inline-flex items-center justify-center gap-2"
                    onclick="document.getElementById('flipFullscreen').click()"
                >
                    <i class="ri-fullscreen-line text-base"></i>
                    <span class="hidden sm:inline">Layar Penuh</span>
                    <span class="sm:hidden">Fullscreen</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Flipbook Container --}}
    <div class="glass-card p-2 sm:p-3 md:p-4 mb-8 overflow-hidden relative">
        <div class="flip-embed mx-auto">
            <div id="flipbook"
                 class="flipbook-stage"
                 data-pdf-url="{{ asset('pdf/' . rawurlencode('Lembar balik.pdf')) }}"
            >
                <div class="flipbook-loading text-white">Memuat flipbook...</div>

                <div class="flipbook-canvas">
                    <canvas id="flipbookCanvasLeft"></canvas>
                    <canvas id="flipbookCanvasRight"></canvas>
                </div>
            </div>

            {{-- top badge --}}
            <div class="flipbook-page-indicator" aria-hidden="true">
                <span class="flipbook-page">
                    Halaman <span id="flipPageLabel">1</span>
                </span>
            </div>

            {{-- bottom controls --}}
            <div class="flipbook-controls" role="group" aria-label="Kontrol flipbook">
                <button class="flipbook-btn" type="button" id="flipPrev" aria-label="Sebelumnya">
                    <i class="ri-arrow-left-s-line"></i>
                </button>

                <span class="flipbook-sep" aria-hidden="true"></span>

                <button class="flipbook-btn" type="button" id="flipNext" aria-label="Berikutnya">
                    <i class="ri-arrow-right-s-line"></i>
                </button>
            </div>

            {{-- floating fullscreen button (keep id) --}}
            <button class="flipbook-fullscreen-toggle" type="button" id="flipFullscreen" aria-label="Layar penuh">
                <i class="ri-fullscreen-line" id="flipFullscreenIcon"></i>
            </button>
        </div>
    </div>

    {{-- Downloads --}}
    @if ($downloads->count())
        <div class="glass-card p-5 md:p-6">
            <div class="border-b border-gray-200/50 pb-4 mb-6">
                <h6 class="font-bold text-lg text-gray-800 mb-1">Unduhan PDF</h6>
                <p class="text-sm text-gray-500 mb-0">Salin versi PDF untuk dibaca offline atau dibagikan ke kader lain.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4 md:gap-6">
                @foreach ($downloads as $item)
                    <div class="download-card">
                        <div class="min-w-0">
                            <h6 class="font-bold text-gray-800 text-sm mb-1 line-clamp-2" title="{{ $item['name'] }}">
                                {{ $item['name'] }}
                            </h6>
                            <div class="flex items-center gap-2 text-xs text-gray-500">
                                <span>{{ $item['updated_at']->translatedFormat('d M Y') }}</span>
                                <span class="opacity-60">&bull;</span>
                                <span>{{ $item['size'] }} KB</span>
                            </div>
                        </div>

                        <a href="{{ $item['url'] }}"
                           class="glass-button px-4 py-2 rounded-lg text-sm font-semibold inline-flex items-center justify-center gap-2 no-underline"
                           download
                        >
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
        /* ===== Flipbook container: cleaner + responsive + no overflow issues ===== */
        .flip-embed{
            position: relative;
            width: min(100%, 1100px);
            height: clamp(520px, 75vh, 860px);
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 18px 55px rgba(0,0,0,.32);
            background: #0f172a;
            box-sizing: border-box;
            isolation: isolate;
        }

        .flipbook-stage{
            position:absolute;
            inset:0;
            width:100%;
            height:100%;
            display:grid;
            place-items:center;
            background:#0f172a;
        }

        .flipbook-loading{
            position:absolute;
            inset:0;
            display:grid;
            place-items:center;
            font-weight:600;
            color:#f8fafc;
            background: radial-gradient(ellipse at center, rgba(255,255,255,0.06), rgba(15,23,42,0.9));
            z-index:3;
        }

        .flipbook-canvas{
            width:100%;
            height:100%;
            display:grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
            align-items:center;
            justify-items:center;
            padding: 18px 18px 76px; /* bottom space for controls */
            perspective: 1200px;
        }

        #flipbookCanvasLeft,
        #flipbookCanvasRight{
            width:100%;
            height:auto;
            max-height:100%;
            border-radius: 10px;
            box-shadow: 0 14px 32px rgba(0,0,0,.50);
            transform-style:preserve-3d;
            background: rgba(255,255,255,0.02);
        }

        /* Badge page indicator */
        .flipbook-page-indicator{
            position:absolute;
            top: 14px;
            left: 0;
            right: 0;
            display:flex;
            justify-content:center;
            z-index: 4;
            pointer-events:none;
        }
        .flipbook-page-indicator .flipbook-page{
            background: rgba(15,23,42,0.72);
            border: 1px solid rgba(255,255,255,0.14);
            color:#fff;
            padding:.35rem .8rem;
            border-radius: 999px;
            font-size:.85rem;
            backdrop-filter: blur(10px);
        }

        /* Bottom controls */
        .flipbook-controls{
            position:absolute;
            left: 12px;
            right: 12px;
            bottom: 12px;
            z-index: 5;
            display:flex;
            align-items:center;
            justify-content:center;
            gap: 12px;
            padding: 10px 12px;
            border-radius: 999px;
            background: rgba(15,23,42,0.72);
            border: 1px solid rgba(255,255,255,0.14);
            backdrop-filter: blur(10px);
        }

        .flipbook-btn{
            width: 44px;
            height: 44px;
            border-radius: 999px;
            display:inline-flex;
            align-items:center;
            justify-content:center;
            border: 1px solid rgba(255,255,255,0.18);
            background: rgba(255,255,255,0.06);
            color:#fff;
            transition: transform .12s ease, background .12s ease, border-color .12s ease;
        }
        .flipbook-btn:hover{
            background: rgba(255,255,255,0.10);
            border-color: rgba(255,255,255,0.28);
            transform: translateY(-1px);
            color:#fff;
        }
        .flipbook-btn i{ font-size: 22px; line-height: 1; }

        .flipbook-sep{
            width: 1px;
            height: 20px;
            background: rgba(255,255,255,0.18);
            border-radius: 999px;
        }

        /* Floating fullscreen button (top-right) */
        .flipbook-fullscreen-toggle{
            position:absolute;
            top: 12px;
            right: 12px;
            z-index: 6;
            width: 44px;
            height: 44px;
            padding: 0;
            display:inline-flex;
            align-items:center;
            justify-content:center;
            background: rgba(15,23,42,0.72);
            border-radius: 999px;
            color:#fff;
            border: 1px solid rgba(255,255,255,0.14);
            backdrop-filter: blur(10px);
            transition: transform .12s ease, background .12s ease, border-color .12s ease;
        }
        .flipbook-fullscreen-toggle:hover{
            background: rgba(255,255,255,0.10);
            border-color: rgba(255,255,255,0.28);
            transform: translateY(-1px);
            color:#fff;
        }

        /* Animations (keep your existing class names) */
        .flipbook-animate-next{ animation: flipNext .45s ease; transform-origin: right center; }
        .flipbook-animate-prev{ animation: flipPrev .45s ease; transform-origin: left center; }
        .flipbook-animate-load{ animation: flipLoad .55s ease; }

        @keyframes flipNext{
            0%{ transform: perspective(1400px) rotateY(0deg); opacity:1; }
            50%{ transform: perspective(1400px) rotateY(-28deg); opacity:.86; }
            100%{ transform: perspective(1400px) rotateY(0deg); opacity:1; }
        }
        @keyframes flipPrev{
            0%{ transform: perspective(1400px) rotateY(0deg); opacity:1; }
            50%{ transform: perspective(1400px) rotateY(28deg); opacity:.86; }
            100%{ transform: perspective(1400px) rotateY(0deg); opacity:1; }
        }
        @keyframes flipLoad{
            0%{ transform: perspective(1400px) rotateY(-10deg); opacity:0; }
            100%{ transform: perspective(1400px) rotateY(0deg); opacity:1; }
        }

        /* Downloads card */
        .download-card{
            background: rgba(255,255,255,0.55);
            border: 1px solid rgba(229,231,235,0.9);
            border-radius: 16px;
            padding: 16px;
            display:flex;
            flex-direction:column;
            justify-content:space-between;
            gap: 14px;
            transition: box-shadow .15s ease, transform .15s ease;
        }
        .download-card:hover{
            box-shadow: 0 14px 28px rgba(15,23,42,0.10);
            transform: translateY(-1px);
        }

        /* Mobile: show 1 canvas only (lebih enak), controls tetap nyaman */
        @media (max-width: 768px){
            .flip-embed{
                height: calc(100vh - 10rem);
                border-radius: 16px;
            }
            .flipbook-canvas{
                grid-template-columns: 1fr;
                gap: 10px;
                padding: 14px 14px 78px;
            }
            #flipbookCanvasRight{
                display:none;
            }
        }

        /* Fullscreen overrides (keep your existing behavior) */
        .flipbook-fullscreen{
            position: fixed;
            inset: 0;
            z-index: 9999;
            border-radius: 0;
            margin: 0;
            max-width: 100%;
            width: 100%;
            height: 100vh;
            background: #0f172a;
        }
        body.flipbook-mode{
            overflow:hidden;
        }
        body.flipbook-mode header,
        body.flipbook-mode aside,
        body.flipbook-mode footer,
        body.flipbook-mode .navbar{
            display:none !important;
        }
    </style>
@endpush
