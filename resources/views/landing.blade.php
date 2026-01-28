<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SITUBA Surakarta | Tuberculosis Assistant</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Work+Sans:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- CSS & JS -->
    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
        'resources/css/landing-animations.css',
        'resources/js/landing-animations.js'
    ])

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* Custom Tailwind-like Overrides if needed inline */
        .glass-nav {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .hero-gradient {
            background: linear-gradient(135deg, #f0fdf4 0%, #ffffff 100%);
        }

        .text-situba-green {
            color: #10b981;
        }

        .bg-situba-green {
            background-color: #10b981;
        }

        .border-situba-green {
            border-color: #10b981;
        }
    </style>
</head>

<body class="font-sans antialiased text-gray-800 bg-white">

    <!-- Scroll Progress -->
    <div id="scroll-progress" class="scroll-progress-bar"></div>

    <!-- Navbar -->
    <header class="fixed top-0 w-full z-50 glass-nav transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <!-- Data Reveal: Fade Down -->
                <div class="flex items-center gap-3 reveal-base start-visible" style="transition-delay: 100ms;">

                    <img src="{{ asset('assets/img/situba-logo.png') }}" alt="SITUBA Logo"
                        class="h-10 w-10 object-contain">
                    <div class="flex flex-col leading-tight">
                        <span class="font-bold text-gray-900 text-lg tracking-tight">SITUBA</span>
                        <span class="text-xs text-gray-500 font-medium">Surakarta City</span>
                    </div>
                </div>

                <!-- Desktop Menu -->
                <nav class="hidden md:flex items-center gap-8 reveal-base start-visible"
                    style="transition-delay: 200ms;">
                    <a href="#alur"
                        class="text-sm font-medium text-gray-600 hover:text-emerald-600 transition-colors flex items-center gap-2">
                        <i class="fa-solid fa-route text-emerald-500/80"></i> Alur
                    </a>
                    <a href="#dampak"
                        class="text-sm font-medium text-gray-600 hover:text-emerald-600 transition-colors flex items-center gap-2">
                        <i class="fa-solid fa-chart-line text-emerald-500/80"></i> Dampak
                    </a>

                    <a href="{{ route('blog.index') }}"
                        class="text-sm font-medium text-gray-600 hover:text-emerald-600 transition-colors flex items-center gap-2">
                        <i class="fa-solid fa-newspaper text-emerald-500/80"></i> Berita
                    </a>

                    <a href="{{ route('login') }}"
                        class="px-5 py-2.5 rounded-full bg-gray-900 text-white text-sm font-semibold hover:bg-emerald-600 transition-all shadow-lg hover:shadow-emerald-500/20 transform hover:-translate-y-0.5">
                        <i class="fa-solid fa-arrow-right-to-bracket mr-2"></i> Dashboard
                    </a>
                </nav>

                <!-- Mobile Menu Button -->
                <button id="mobile-menu-btn" class="md:hidden p-2 text-gray-600 focus:outline-none">
                    <i class="fa-solid fa-bars text-xl"></i>
                </button>
            </div>
        </div>

        <!-- Mobile Drawer -->
        <div id="mobile-menu"
            class="hidden absolute top-20 left-0 w-full bg-white border-b border-gray-100 shadow-xl flex-col p-6 space-y-4 md:hidden">
            <a href="#alur" class="block text-base font-medium text-gray-700 py-2 border-b border-gray-50">Alur
                SITUBA</a>
            <a href="#dampak" class="block text-base font-medium text-gray-700 py-2 border-b border-gray-50">Dampak</a>

            <a href="{{ route('blog.index') }}"
                class="block text-base font-medium text-gray-700 py-2 border-b border-gray-50">Berita & Edukasi</a>
            <a href="{{ route('login') }}"
                class="block w-full text-center py-3 mt-4 rounded-lg bg-emerald-600 text-white font-bold shadow-md">
                Masuk Dashboard
            </a>
        </div>
    </header>

    <main class="pt-20">
        <!-- Hero Section -->
        <section class="relative min-h-[90vh] flex items-center hero-gradient overflow-hidden" id="top">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full py-12 lg:py-0">
                <div class="grid lg:grid-cols-2 gap-12 items-center">

                    <!-- Copy -->
                    <!-- Copy -->
                    <div class="flex flex-col gap-8 order-2 lg:order-1">
                        <!-- 1. Badge (Mobile: Order 1, Desktop: DOM Order) -->
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-50 border border-emerald-100 text-emerald-700 text-xs font-bold uppercase tracking-wide hero-reveal delay-100 self-start order-1 lg:order-none">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            GovTech Kesehatan
                        </div>

                        <!-- 2. Mobile Visual Card (Mobile: Order 2, Desktop: Hidden) -->
                        <div class="block lg:hidden order-2 w-full max-w-md self-center hero-reveal delay-300">
                             <div class="relative w-full bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden hover-lift">
                                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                                    <div class="flex items-center gap-2">
                                        <div class="w-3 h-3 rounded-full bg-red-400"></div>
                                        <div class="w-3 h-3 rounded-full bg-amber-400"></div>
                                        <div class="w-3 h-3 rounded-full bg-green-400"></div>
                                    </div>
                                    <div class="text-xs font-mono text-gray-400">Command Center Live</div>
                                </div>
                                <div class="p-6 space-y-6">
                                    <div class="p-4 bg-emerald-50 rounded-xl border border-emerald-100 flex items-start gap-4">
                                        <div class="p-2 bg-white rounded-lg shadow-sm text-emerald-600">
                                            <i class="fa-solid fa-circle-exclamation"></i>
                                        </div>
                                        <div>
                                            <h4 class="text-sm font-bold text-gray-900">Prioritas Wilayah</h4>
                                            <p class="text-xs text-gray-600 mt-1">3 kelurahan memerlukan atensi tinggi minggu ini.</p>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div class="p-4 bg-white rounded-xl border border-gray-100 shadow-sm">
                                            <div class="text-xs text-gray-500 mb-1">Skrining Baru</div>
                                            <div class="text-2xl font-bold text-gray-900">150</div>
                                            <div class="text--[10px] text-emerald-600 font-medium">+12% vs bulan lalu</div>
                                        </div>
                                        <div class="p-4 bg-white rounded-xl border border-gray-100 shadow-sm">
                                            <div class="text-xs text-gray-500 mb-1">Tindak Lanjut</div>
                                            <div class="text-2xl font-bold text-gray-900">85%</div>
                                            <div class="text-[10px] text-gray-400 font-medium">Target: 90%</div>
                                        </div>
                                    </div>
                                    <div class="space-y-3">
                                        <div class="flex items-center justify-between text-sm py-2 border-b border-gray-50">
                                            <span class="flex items-center gap-2 text-gray-600"><i class="fa-solid fa-users text-gray-400"></i> Kader Input Data</span>
                                            <span class="text-xs font-mono bg-gray-100 px-2 py-0.5 rounded">Just now</span>
                                        </div>
                                        <div class="flex items-center justify-between text-sm py-2 border-b border-gray-50">
                                            <span class="flex items-center gap-2 text-gray-600"><i class="fa-solid fa-user-doctor text-gray-400"></i> Validasi PKM</span>
                                            <span class="text-xs font-mono bg-gray-100 px-2 py-0.5 rounded">2m ago</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 3. Title (Mobile: Order 4, Desktop: DOM Order) -->
                        <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold leading-tight text-gray-900 hero-reveal delay-200 order-4 lg:order-none">
                            Deteksi TBC Lebih Cepat, <span class="text-emerald-600">Terintegrasi</span>
                        </h1>

                        <!-- 4. Description (Mobile: Order 5, Desktop: DOM Order) -->
                        <p class="text-lg text-gray-600 max-w-xl hero-reveal delay-300 leading-relaxed order-5 lg:order-none">
                            SITUBA menghubungkan kader, puskesmas, dan Pemda Surakarta dalam satu komando.
                            Respon real-time untuk eliminasi TBC yang terukur.
                        </p>

                        <!-- Wrapper: contents on Mobile (children ordered individually), Flex on Desktop (children ordered by source) -->
                        <div class="contents lg:flex lg:flex-row lg:gap-4 lg:items-center lg:w-fit lg:order-none">
                            
                            <!-- Mulai Pantau: Order 3 on Mobile (Above Title) -->
                            <a href="{{ route('login') }}" class="flex items-center justify-center px-8 py-3.5 rounded-lg bg-gray-900 text-white font-semibold shadow-xl hover:bg-emerald-600 transition-all hover:-translate-y-1 w-full sm:w-auto order-3 lg:order-none hero-reveal delay-400">
                                Mulai Pantau Kasus
                            </a>

                            <!-- Pelajari Alur: Order 6 on Mobile (Below Desc) -->
                            <a href="#alur" class="flex items-center justify-center px-8 py-3.5 rounded-lg bg-white border border-gray-200 text-gray-700 font-semibold hover:border-emerald-200 hover:bg-emerald-50 transition-all w-full sm:w-auto order-6 lg:order-none hero-reveal delay-400">
                                Pelajari Alur <i class="fa-solid fa-arrow-down ml-2 text-xs"></i>
                            </a>
                        </div>

                        <!-- 6. Trust Bullets (Mobile: Order 6, Desktop: Order 5) -->
                        <div class="flex flex-wrap gap-6 pt-4 border-t border-gray-100 hero-reveal delay-500 order-6 lg:order-5">
                            <div class="flex items-center gap-2 text-sm text-gray-500">
                                <i class="fa-solid fa-check-circle text-emerald-500"></i>
                                <span>Data Terenkripsi</span>
                            </div>
                            <div class="flex items-center gap-2 text-sm text-gray-500">
                                <i class="fa-solid fa-bolt text-emerald-500"></i>
                                <span>Real-time Sync</span>
                            </div>
                            <div class="flex items-center gap-2 text-sm text-gray-500">
                                <i class="fa-solid fa-mobile-screen text-emerald-500"></i>
                                <span>Mobile Ready</span>
                            </div>
                        </div>
                    </div>

                    <!-- Visual (Desktop Only) -->
                    <div class="hidden lg:flex order-1 lg:order-2 hero-reveal delay-300 justify-center lg:justify-end relative">
                        <!-- Glow Effect -->
                        <div
                            class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[120%] h-[120%] bg-emerald-500/10 blur-3xl rounded-full pointer-events-none">
                        </div>

                        <!-- Card -->
                        <div
                            class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden hover-lift">
                            <!-- Header -->
                            <div
                                class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                                <div class="flex items-center gap-2">
                                    <div class="w-3 h-3 rounded-full bg-red-400"></div>
                                    <div class="w-3 h-3 rounded-full bg-amber-400"></div>
                                    <div class="w-3 h-3 rounded-full bg-green-400"></div>
                                </div>
                                <div class="text-xs font-mono text-gray-400">Command Center Live</div>
                            </div>

                            <!-- Body -->
                            <div class="p-6 space-y-6">
                                <!-- Insight Panel -->
                                <div
                                    class="p-4 bg-emerald-50 rounded-xl border border-emerald-100 flex items-start gap-4">
                                    <div class="p-2 bg-white rounded-lg shadow-sm text-emerald-600">
                                        <i class="fa-solid fa-circle-exclamation"></i>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-bold text-gray-900">Prioritas Wilayah</h4>
                                        <p class="text-xs text-gray-600 mt-1">
                                            {{ max(($priorityKelurahan ?? collect())->count(), 0) }} kelurahan
                                            memerlukan atensi tinggi minggu ini.
                                        </p>
                                    </div>
                                </div>

                                <!-- Metric Grid -->
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="p-4 bg-white rounded-xl border border-gray-100 shadow-sm">
                                        <div class="text-xs text-gray-500 mb-1">Skrining Baru</div>
                                        <div class="text-2xl font-bold text-gray-900"
                                            data-target="{{ $screeningsLast30DaysCount ?? 150 }}">0</div>
                                        <div class="text-[10px] text-emerald-600 font-medium">+12% vs bulan lalu</div>
                                    </div>
                                    <div class="p-4 bg-white rounded-xl border border-gray-100 shadow-sm">
                                        <div class="text-xs text-gray-500 mb-1">Tindak Lanjut</div>
                                        <div class="text-2xl font-bold text-gray-900"
                                            data-target="{{ $followUpRate ?? 85 }}" data-suffix="%">0%</div>
                                        <div class="text-[10px] text-gray-400 font-medium">Target: 90%</div>
                                    </div>
                                </div>

                                <!-- Action List -->
                                <div class="space-y-3">
                                    <div class="flex items-center justify-between text-sm py-2 border-b border-gray-50">
                                        <span class="flex items-center gap-2 text-gray-600"><i
                                                class="fa-solid fa-users text-gray-400"></i> Kader Input Data</span>
                                        <span class="text-xs font-mono bg-gray-100 px-2 py-0.5 rounded">Just now</span>
                                    </div>
                                    <div class="flex items-center justify-between text-sm py-2 border-b border-gray-50">
                                        <span class="flex items-center gap-2 text-gray-600"><i
                                                class="fa-solid fa-user-doctor text-gray-400"></i> Validasi PKM</span>
                                        <span class="text-xs font-mono bg-gray-100 px-2 py-0.5 rounded">2m ago</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Scroll Hint -->
                <div
                    class="absolute bottom-4 left-1/2 -translate-x-1/2 hidden lg:flex flex-col items-center gap-2 animate-bounce opacity-50">
                    <span class="text-xs font-medium uppercase tracking-widest text-gray-400">Scroll</span>
                    <i class="fa-solid fa-chevron-down text-gray-400"></i>
                </div>
            </div>
        </section>

        <!-- Dampak Section -->
        <section id="dampak" class="py-20 lg:py-28 bg-white overflow-hidden">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                <!-- Pipeline Title -->
                <div class="mb-16 pipeline-title flex items-end gap-4">
                    <div class="pipeline-left text-4xl font-bold text-gray-900 leading-none">
                        Dampak Nyata
                    </div>
                    <div class="pipeline-line h-[2px] bg-emerald-500 flex-1 mb-2 shadow-sm rounded-full opacity-50">
                    </div>
                    <div class="pipeline-right text-gray-500 font-medium text-lg leading-none pb-1 hidden sm:block">
                        Data Terhubung
                    </div>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <!-- Metric 1 -->
                    <div
                        class="p-8 rounded-2xl bg-gray-50 hover:bg-white border border-transparent hover:border-gray-100 shadow-sm hover:shadow-xl hover-lift transition-all reveal-base delay-100 group">
                        <div
                            class="w-12 h-12 bg-white rounded-xl shadow-sm flex items-center justify-center text-emerald-600 text-xl mb-6 group-hover:scale-110 transition-transform">
                            <i class="fa-solid fa-hospital-user"></i>
                        </div>
                        <h3 class="text-4xl font-bold text-gray-900 mb-2" data-countup
                            data-target="{{ $puskesmasCount ?? 15 }}">0</h3>
                        <p class="text-gray-500 font-medium mb-4">Fasilitas Kesehatan</p>
                        <p class="text-sm text-gray-400 leading-relaxed">Terhubung langsung untuk validasi diagnosa dan
                            pemantauan pengobatan.</p>
                    </div>

                    <!-- Metric 2 -->
                    <div
                        class="p-8 rounded-2xl bg-gray-50 hover:bg-white border border-transparent hover:border-gray-100 shadow-sm hover:shadow-xl hover-lift transition-all reveal-base delay-200 group">
                        <div
                            class="w-12 h-12 bg-white rounded-xl shadow-sm flex items-center justify-center text-blue-600 text-xl mb-6 group-hover:scale-110 transition-transform">
                            <i class="fa-solid fa-map-location-dot"></i>
                        </div>
                        <h3 class="text-4xl font-bold text-gray-900 mb-2" data-countup
                            data-target="{{ $kelurahanCount ?? 54 }}">0</h3>
                        <p class="text-gray-500 font-medium mb-4">Kelurahan Aktif</p>
                        <p class="text-sm text-gray-400 leading-relaxed">Jaringan kewilayahan untuk pelacakan kontak
                            erat dan pendampingan.</p>
                    </div>

                    <!-- Metric 3 -->
                    <div
                        class="p-8 rounded-2xl bg-gray-900 text-white shadow-xl hover-lift transition-all reveal-base delay-300 shimmer relative group overflow-hidden">
                        <div class="relative z-10">
                            <div
                                class="w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center text-emerald-400 text-xl mb-6 backdrop-blur-sm group-hover:scale-110 transition-transform">
                                <i class="fa-solid fa-link"></i>
                            </div>
                            <h3 class="text-4xl font-bold mb-2">100%</h3>
                            <p class="text-gray-300 font-medium mb-4">Rantai Digital</p>
                            <p class="text-sm text-gray-400 leading-relaxed">Eliminasi kertas. Dari kader input hingga
                                Dinkes memantau dalam satu alur.</p>
                        </div>
                    </div>
                </div>

                <!-- Sponsors Marquee -->
                <div class="mt-20 pt-10 border-t border-gray-100 reveal-base delay-400 overflow-hidden relative">
                    <p class="text-center text-xs font-bold text-gray-400 uppercase tracking-widest mb-8">Didukung Oleh
                        Ekosistem Kota</p>

                    <div class="marquee-container mask-linear-fade">
                        <div class="marquee-content items-center">
                            <!-- Set 1 -->
                            <div class="flex items-center gap-8">
                                <div
                                    class="px-6 py-2 rounded-full bg-gray-50 border border-gray-100 text-gray-500 font-semibold whitespace-nowrap flex items-center gap-2">
                                    <i class="fa-solid fa-building-columns text-emerald-500"></i> Pemda Surakarta
                                </div>
                                <div
                                    class="px-6 py-2 rounded-full bg-gray-50 border border-gray-100 text-gray-500 font-semibold whitespace-nowrap flex items-center gap-2">
                                    <i class="fa-solid fa-notes-medical text-emerald-500"></i> Dinas Kesehatan
                                </div>
                                <div
                                    class="px-6 py-2 rounded-full bg-gray-50 border border-gray-100 text-gray-500 font-semibold whitespace-nowrap flex items-center gap-2">
                                    <i class="fa-solid fa-handshake-simple text-emerald-500"></i> Forum Kota Sehat
                                </div>
                                <div
                                    class="px-6 py-2 rounded-full bg-gray-50 border border-gray-100 text-gray-500 font-semibold whitespace-nowrap flex items-center gap-2">
                                    <i class="fa-solid fa-house-medical text-emerald-500"></i> Puskesmas Wilayah
                                </div>
                                <div
                                    class="px-6 py-2 rounded-full bg-gray-50 border border-gray-100 text-gray-500 font-semibold whitespace-nowrap flex items-center gap-2">
                                    <i class="fa-solid fa-user-nurse text-emerald-500"></i> Kader Kesehatan
                                </div>
                            </div>

                            <!-- Set 2 (Duplicate - Hidden on Desktop) -->
                            <div class="flex items-center gap-8 md:hidden">
                                <div
                                    class="px-6 py-2 rounded-full bg-gray-50 border border-gray-100 text-gray-500 font-semibold whitespace-nowrap flex items-center gap-2">
                                    <i class="fa-solid fa-building-columns text-emerald-500"></i> Pemda Surakarta
                                </div>
                                <div
                                    class="px-6 py-2 rounded-full bg-gray-50 border border-gray-100 text-gray-500 font-semibold whitespace-nowrap flex items-center gap-2">
                                    <i class="fa-solid fa-notes-medical text-emerald-500"></i> Dinas Kesehatan
                                </div>
                                <div
                                    class="px-6 py-2 rounded-full bg-gray-50 border border-gray-100 text-gray-500 font-semibold whitespace-nowrap flex items-center gap-2">
                                    <i class="fa-solid fa-handshake-simple text-emerald-500"></i> Forum Kota Sehat
                                </div>
                                <div
                                    class="px-6 py-2 rounded-full bg-gray-50 border border-gray-100 text-gray-500 font-semibold whitespace-nowrap flex items-center gap-2">
                                    <i class="fa-solid fa-house-medical text-emerald-500"></i> Puskesmas Wilayah
                                </div>
                                <div
                                    class="px-6 py-2 rounded-full bg-gray-50 border border-gray-100 text-gray-500 font-semibold whitespace-nowrap flex items-center gap-2">
                                    <i class="fa-solid fa-user-nurse text-emerald-500"></i> Kader Kesehatan
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </section>

        <!-- Alur Section -->
        <section id="alur" class="py-24 bg-gray-50 overflow-hidden">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

                <!-- Fresh New Header (Split Layout) -->
                <div class="mb-24 reveal-base">
                    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-8 md:gap-12 border-b border-gray-200 pb-8">
                        <div class="md:w-1/2 relative">
                            <span class="absolute -top-6 left-0 text-6xl font-black text-gray-100/50 select-none z-0 pointer-events-none -translate-y-2">FLOW</span>
                            <div class="relative z-10">
                                <span class="text-emerald-600 font-bold tracking-widest text-xs uppercase mb-2 block">Sistem Terintegrasi v2.0</span>
                                <h2 class="text-3xl md:text-5xl font-black text-gray-900 leading-tight">
                                    Sinergi Digital <br>
                                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 to-teal-500">Penanganan TBC</span>
                                </h2>
                            </div>
                        </div>
                        <div class="md:w-1/2 md:pb-2">
                             <p class="text-gray-500 text-lg leading-relaxed border-l-4 border-emerald-500 pl-6">
                                Transformasi layanan kesehatan kota. Menghubungkan semua pemangku kepentingan dalam satu gerak langkah yang presisi dan transparan.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Fresh Process Grid (Horizontal Flow) -->
                <div class="relative items-start grid grid-cols-1 md:grid-cols-3 gap-8 md:gap-4 lg:gap-8">
                    
                    <!-- Connector Line (Desktop Only - Behind Cards) -->
                    <div class="hidden md:block absolute top-12 left-[16%] right-[16%] h-0.5 bg-gradient-to-r from-emerald-200 via-blue-200 to-orange-200 -z-10"></div>

                    <!-- Step 1: Pemda -->
                    <div class="group relative bg-white rounded-3xl p-8 border border-gray-100 shadow-xl shadow-gray-200/50 hover:shadow-2xl hover:shadow-emerald-100/50 transition-all duration-300 hover:-translate-y-2 reveal-base h-full flex flex-col">
                        <!-- Step Badge -->
                        <div class="absolute -top-4 left-8 bg-emerald-600 text-white text-xs font-bold px-3 py-1 rounded-full uppercase tracking-widest shadow-lg shadow-emerald-200">
                            Hulu
                        </div>
                        
                        <!-- Header -->
                        <div class="mb-6 flex items-center justify-between">
                            <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl group-hover:scale-110 group-hover:rotate-3 transition-transform duration-300">
                                <i class="fa-solid fa-building-columns"></i>
                            </div>
                            <span class="text-6xl font-black text-emerald-50 opacity-50 select-none">01</span>
                        </div>
                        
                        <!-- Title -->
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Pemda Surakarta</h3>
                        <p class="text-sm text-gray-500 mb-8 border-l-2 border-emerald-200 pl-3">
                            Regulasi & Kebijakan
                        </p>

                        <!-- Action Items -->
                        <div class="mt-auto space-y-3">
                            <div class="bg-gray-50 hover:bg-emerald-50 rounded-xl p-4 transition-colors border border-transparent hover:border-emerald-100 group/item">
                                <div class="flex items-center gap-3 mb-1">
                                    <div class="w-8 h-8 rounded-full bg-white text-emerald-500 flex items-center justify-center shadow-sm text-sm">
                                        <i class="fa-solid fa-chart-pie"></i>
                                    </div>
                                    <span class="font-bold text-gray-800 text-sm">Dashboard Kota</span>
                                </div>
                                <p class="text-xs text-slate-500 pl-11">Pantau <span class="text-emerald-600 font-semibold">Peta Sebaran</span> real-time.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Arrow 1->2 (Mobile: Down, Desktop: Right) -->
                    <div class="md:hidden flex justify-center -my-4 z-10">
                        <i class="fa-solid fa-arrow-down text-gray-300 animate-bounce"></i>
                    </div>

                    <!-- Step 2: Puskesmas -->
                    <div class="group relative bg-white rounded-3xl p-8 border border-gray-100 shadow-xl shadow-gray-200/50 hover:shadow-2xl hover:shadow-blue-100/50 transition-all duration-300 hover:-translate-y-2 reveal-base delay-100 h-full flex flex-col">
                         <!-- Step Badge -->
                         <div class="absolute -top-4 left-8 bg-blue-600 text-white text-xs font-bold px-3 py-1 rounded-full uppercase tracking-widest shadow-lg shadow-blue-200">
                            Proses
                        </div>

                        <!-- Header -->
                        <div class="mb-6 flex items-center justify-between">
                            <div class="w-14 h-14 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-2xl group-hover:scale-110 group-hover:rotate-3 transition-transform duration-300">
                                <i class="fa-solid fa-hospital-user"></i>
                            </div>
                            <span class="text-6xl font-black text-blue-50 opacity-50 select-none">02</span>
                        </div>

                        <!-- Title -->
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Puskesmas Wilayah</h3>
                        <p class="text-sm text-gray-500 mb-8 border-l-2 border-blue-200 pl-3">
                            Validasi & Pengobatan
                        </p>

                        <!-- Action Items -->
                        <div class="mt-auto space-y-3">
                            <div class="bg-gray-50 hover:bg-blue-50 rounded-xl p-4 transition-colors border border-transparent hover:border-blue-100 flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-white text-blue-500 flex items-center justify-center shadow-sm text-sm shrink-0">
                                    <i class="fa-solid fa-file-medical"></i>
                                </div>
                                <span class="font-bold text-gray-800 text-sm">Manajemen Kasus</span>
                            </div>
                            <div class="bg-gray-50 hover:bg-blue-50 rounded-xl p-4 transition-colors border border-transparent hover:border-blue-100 flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-white text-purple-500 flex items-center justify-center shadow-sm text-sm shrink-0">
                                    <i class="fa-solid fa-flask"></i>
                                </div>
                                <span class="font-bold text-gray-800 text-sm">Input Lab</span>
                            </div>
                        </div>
                    </div>

                    <!-- Arrow 2->3 (Mobile: Down) -->
                    <div class="md:hidden flex justify-center -my-4 z-10">
                        <i class="fa-solid fa-arrow-down text-gray-300 animate-bounce"></i>
                    </div>

                    <!-- Step 3: Kader -->
                    <div class="group relative bg-white rounded-3xl p-8 border border-gray-100 shadow-xl shadow-gray-200/50 hover:shadow-2xl hover:shadow-orange-100/50 transition-all duration-300 hover:-translate-y-2 reveal-base delay-200 h-full flex flex-col">
                         <!-- Step Badge -->
                         <div class="absolute -top-4 left-8 bg-orange-500 text-white text-xs font-bold px-3 py-1 rounded-full uppercase tracking-widest shadow-lg shadow-orange-200">
                            Hilir
                        </div>

                        <!-- Header -->
                        <div class="mb-6 flex items-center justify-between">
                            <div class="w-14 h-14 rounded-2xl bg-orange-50 text-orange-500 flex items-center justify-center text-2xl group-hover:scale-110 group-hover:rotate-3 transition-transform duration-300">
                                <i class="fa-solid fa-user-nurse"></i>
                            </div>
                            <span class="text-6xl font-black text-orange-50 opacity-50 select-none">03</span>
                        </div>

                        <!-- Title -->
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Kader Kesehatan</h3>
                        <p class="text-sm text-gray-500 mb-8 border-l-2 border-orange-200 pl-3">
                            Ujung Tombak Lapangan
                        </p>

                        <!-- Action Items -->
                        <div class="mt-auto space-y-3">
                            <div class="bg-gray-50 hover:bg-orange-50 rounded-xl p-4 transition-colors border border-transparent hover:border-orange-100 flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-white text-orange-500 flex items-center justify-center shadow-sm text-sm shrink-0">
                                    <i class="fa-solid fa-mobile-screen-button"></i>
                                </div>
                                <span class="font-bold text-gray-800 text-sm">Skrining Mobile</span>
                            </div>
                            <div class="bg-gray-50 hover:bg-orange-50 rounded-xl p-4 transition-colors border border-transparent hover:border-orange-100 flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-white text-red-500 flex items-center justify-center shadow-sm text-sm shrink-0">
                                    <i class="fa-solid fa-magnifying-glass-location"></i>
                                </div>
                                <span class="font-bold text-gray-800 text-sm">Pelacakan Kontak</span>
                            </div>
                        </div>
                    </div>
                </div>

                </div>
            </div>
        </section>



        <!-- CTA Section -->
        <section class="py-20 px-4">
            <div
                class="max-w-5xl mx-auto rounded-3xl bg-gray-900 text-white overflow-hidden relative shadow-2xl reveal-base">
                <!-- Background Decoration -->
                <div
                    class="absolute top-0 right-0 -mt-20 -mr-20 w-96 h-96 bg-emerald-500/20 blur-3xl rounded-full pointer-events-none">
                </div>
                <div
                    class="absolute bottom-0 left-0 -mb-20 -ml-20 w-80 h-80 bg-blue-500/20 blur-3xl rounded-full pointer-events-none">
                </div>

                <div class="relative z-10 px-8 py-16 md:p-16 text-center">
                    <h2 class="text-3xl md:text-4xl font-bold mb-6">Siap memperkuat respon TBC Surakarta?</h2>
                    <p class="text-gray-400 max-w-2xl mx-auto mb-10 text-lg">
                        Bergabung dengan ratusan kader dan nakes lainnya. Masuk ke dashboard SITUBA untuk mulai memantau
                        dan menindaklanjuti kasus hari ini.
                    </p>
                    <a href="{{ route('login') }}"
                        class="inline-flex items-center justify-center px-8 py-4 text-base font-bold text-gray-900 bg-white rounded-lg hover:bg-emerald-50 transition-colors shadow-lg shimmer">
                        Masuk Dashboard <i class="fa-solid fa-arrow-right ml-2"></i>
                    </a>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-100 py-12">
        <div
            class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center gap-6">
            <div class="flex items-center gap-3 opacity-80 decoration-clone">
                <img src="{{ asset('assets/img/situba-logo.png') }}" alt="Logo" class="h-8 w-8 grayscale opacity-50">
                <span class="text-sm text-gray-400">© {{ date('Y') }} SITUBA Surakarta.</span>
            </div>

            <div class="flex gap-6 text-sm text-gray-500">
                <a href="#" class="hover:text-emerald-600 transition-colors">Kebijakan Privasi</a>
                <a href="#" class="hover:text-emerald-600 transition-colors">Bantuan</a>
            </div>
        </div>
    </footer>


</body>

</html>
