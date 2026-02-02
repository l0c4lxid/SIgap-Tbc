<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <title>SItuba | Materi Edukasi</title>
    
    <!-- Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
    
    <!-- Icons: RemixIcon -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    
    <!-- Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>

<body class="antialiased bg-gray-50">
    
    <!-- Simple Navbar -->
    <div class="glass-panel sticky top-0 z-30 px-6 py-4 flex items-center justify-between gap-4 border-b border-gray-200/50 backdrop-blur-md bg-white/80">
        <a href="{{ url('/blog') }}" class="flex items-center gap-3 hover:opacity-80 transition-opacity no-underline">
             <img src="{{ asset('assets/img/situba-logo.png') }}" alt="SITUBA" class="h-8 w-8 object-contain">
             <div class="flex flex-col">
                <span class="font-bold text-gray-800 leading-none">SITUBA</span>
                <span class="text-[10px] text-gray-500 font-medium">Materi Edukasi Publik</span>
             </div>
        </a>
        
        <div>
            @auth
                <a href="{{ route('dashboard') }}" class="text-sm font-bold text-emerald-600 hover:text-emerald-700 no-underline">
                    Ke Dashboard <i class="ri-arrow-right-line ml-1"></i>
                </a>
            @else
                <a href="{{ route('login') }}" class="bg-gray-900 text-white px-4 py-2 rounded-lg text-sm font-bold hover:bg-emerald-600 transition-colors no-underline">
                    Masuk
                </a>
            @endauth
        </div>
    </div>

    <!-- Main Content Wrapper -->
    <main class="max-w-7xl mx-auto p-4 min-h-screen transition-all duration-300">
        <div class="animate-fade-in mt-6">
            @yield('content')
        </div>

        <footer class="mt-12 mb-8 text-center">
            <p class="text-xs text-gray-400">
                &copy; {{ date('Y') }} SITUBA Surakarta. All rights reserved.
            </p>
        </footer>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @stack('scripts')
</body>
</html>
