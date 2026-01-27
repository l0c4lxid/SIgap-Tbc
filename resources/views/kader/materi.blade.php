@extends('layouts.soft')

@section('subjudul', 'Materi edukasi kader')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body d-flex flex-column gap-4">
                    <div class="flip-embed mx-auto">
                        <div id="flipbook" class="flipbook-stage" data-pdf-url="{{ asset('pdf/' . rawurlencode('Lembar balik.pdf')) }}">
                            <div class="flipbook-loading">Memuat flipbook...</div>
                            <div class="flipbook-canvas">
                                <canvas id="flipbookCanvasLeft"></canvas>
                                <canvas id="flipbookCanvasRight"></canvas>
                            </div>
                        </div>
                        <div class="flipbook-page-indicator">
                            <span class="flipbook-page" id="flipPageLabel">1</span>
                        </div>
                        <div class="flipbook-controls">
                            <button class="btn btn-outline-light btn-sm" type="button" id="flipPrev" aria-label="Sebelumnya">
                                <i class="ri-arrow-left-s-line"></i>
                            </button>
                            <button class="btn btn-outline-light btn-sm" type="button" id="flipNext" aria-label="Berikutnya">
                                <i class="ri-arrow-right-s-line"></i>
                            </button>
                        </div>
                        <button class="btn btn-outline-light btn-sm flipbook-fullscreen-toggle" type="button" id="flipFullscreen" aria-label="Layar penuh">
                            <i class="ri-fullscreen-line" id="flipFullscreenIcon"></i>
                        </button>
                    </div>
                    @if ($downloads->count())
                        <div class="downloads card shadow-sm border-0">
                            <div class="card-header">
                                <h6 class="mb-0">Unduhan PDF</h6>
                                <p class="text-sm text-muted mb-0">Salin versi PDF untuk dibaca offline atau dibagikan ke kader
                                    lain.</p>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    @foreach ($downloads as $item)
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <div class="download-item">
                                                <div>
                                                    <h6 class="mb-1 text-truncate">{{ $item['name'] }}</h6>
                                                    <p class="text-xs text-muted mb-1">
                                                        {{ $item['updated_at']->translatedFormat('d M Y') }} ·
                                                        {{ $item['size'] }} KB
                                                    </p>
                                                </div>
                                                <a href="{{ $item['url'] }}" class="btn btn-sm btn-outline-primary w-100" download>
                                                    <i class="ri-download-cloud-2-line me-1"></i> Unduh PDF
                                                </a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .flip-embed {
            position: relative;
            width: min(100%, 1100px);
            min-height: 75vh;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 30px 70px rgba(15, 23, 42, 0.18);
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
            box-shadow: 0 15px 35px rgba(15, 23, 42, 0.35);
            border-radius: 12px;
            transform-style: preserve-3d;
        }

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
            background: rgba(15, 23, 42, 0.55);
            padding: 0.25rem 0.75rem;
            border-radius: 999px;
        }

        .flipbook-controls {
            position: absolute;
            inset: auto 0 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            z-index: 2;
            background: rgba(15, 23, 42, 0.55);
            padding: 0.4rem 0.75rem;
            border-radius: 999px;
        }

        .flipbook-fullscreen-toggle {
            position: absolute;
            top: 1rem;
            right: 1rem;
            transform: none;
            z-index: 6;
            width: 40px;
            height: 40px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(15, 23, 42, 0.55);
            border-radius: 999px;
            font-size: 0;
        }

        .flipbook-fullscreen-toggle i {
            font-size: 18px;
        }

        .flipbook-page {
            color: #e2e8f0;
            font-weight: 600;
        }

        .flipbook-controls .btn {
            width: 40px;
            height: 40px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .flipbook-animate-next {
            animation: flipNext 0.45s ease;
            transform-origin: right center;
        }

        .flipbook-animate-prev {
            animation: flipPrev 0.45s ease;
            transform-origin: left center;
        }

        .flipbook-animate-load {
            animation: flipLoad 0.55s ease;
        }

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
                margin: 0 -1.25rem;
                margin-bottom: 1rem;
            }

            .flipbook-canvas {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 0.5rem;
                padding: 0.75rem;
            }

            #flipbookCanvasLeft {
                transform: rotateY(12deg);
            }

            #flipbookCanvasRight {
                transform: rotateY(-12deg);
            }
        }

        @media (max-width: 768px) and (orientation: landscape) {
            .flip-embed {
                min-height: calc(100vh - 4rem);
            }

            .flipbook-canvas {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 0.5rem;
                padding: 0.5rem;
            }

            #flipbookCanvasLeft,
            #flipbookCanvasRight {
                transform: none;
            }

            .flipbook-controls {
                inset: auto 50% 0.5rem;
                transform: translateX(50%);
                gap: 0.5rem;
            }

            .flipbook-controls .btn {
                width: 36px;
                height: 36px;
            }

            .flipbook-fullscreen-toggle {
                top: 0.5rem;
                right: 0.5rem;
                width: 36px;
                height: 36px;
            }
        }

        @media (max-width: 768px) and (orientation: portrait) {
            .flip-embed {
                min-height: calc(100vh - 8rem);
                margin: 0 -1.25rem;
                border-radius: 0;
            }

            .flipbook-canvas {
                transform: none;
                width: 100%;
                height: 100%;
                padding: 0.5rem;
            }

            .flipbook-controls {
                inset: auto 0 0.75rem;
                transform: none;
            }

            .flipbook-page-indicator {
                inset: 0.5rem 0 auto;
                transform: none;
            }

            .flipbook-fullscreen-toggle {
                top: 0.5rem;
                right: 0.5rem;
            }
        }

        .flipbook-force-portrait .flipbook-canvas {
            transform: none;
            width: 100%;
            height: 100%;
            padding: 0.5rem;
        }

        .flipbook-force-portrait .flipbook-controls {
            transform: none;
        }

        .flipbook-force-portrait .flipbook-page-indicator {
            transform: none;
        }

        @media (max-width: 768px) {
            .soft-footer.footer {
                display: none;
            }
        }

        .flipbook-fullscreen {
            position: fixed;
            inset: 0;
            z-index: 1050;
            border-radius: 0;
            margin: 0;
            max-width: 100%;
            width: 100%;
            min-height: 100vh;
            background: #0f172a;
        }

        .flipbook-fullscreen .flipbook-controls {
            inset: auto 0 1rem;
        }

        .flipbook-fullscreen .flipbook-page-indicator {
            inset: 1rem 0 auto;
        }

        body.flipbook-mode {
            overflow: hidden;
        }

        body.flipbook-mode .sidenav,
        body.flipbook-mode .navbar,
        body.flipbook-mode .soft-topbar,
        body.flipbook-mode #sidenav-main,
        body.flipbook-mode .soft-sidebar,
        body.flipbook-mode #soft-sidebar-backdrop,
        body.flipbook-mode footer {
            display: none !important;
        }

        body.flipbook-mode .main-content,
        body.flipbook-mode .container-fluid {
            margin: 0 !important;
            padding: 0 !important;
            max-width: 100% !important;
        }

        .flipbook-fullscreen.flipbook-force-landscape .flipbook-canvas {
            transform: rotate(90deg) scale(0.95);
            transform-origin: center center;
            width: 100vh;
            height: 100vw;
            padding: 0.5rem;
        }

        .flipbook-fullscreen.flipbook-force-landscape .flipbook-controls {
            transform: rotate(90deg);
            transform-origin: center center;
        }

        .flipbook-fullscreen.flipbook-force-landscape .flipbook-page-indicator {
            transform: rotate(90deg);
            transform-origin: center center;
        }

        .flipbook-animate-next {
            animation: flipNext 0.5s ease;
            transform-origin: right center;
        }

        .flipbook-animate-prev {
            animation: flipPrev 0.5s ease;
            transform-origin: left center;
        }

        .downloads .download-item {
            border: 1px solid rgba(15, 23, 42, 0.08);
            border-radius: 16px;
            padding: 1rem;
            background: rgba(248, 250, 252, 0.7);
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            height: 100%;
        }
    </style>
@endpush

@push('scripts')
@endpush
