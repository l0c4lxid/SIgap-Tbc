@php
    use Illuminate\Support\Str;
    $isLogged = auth()->check();
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    @include('partials.seo-meta', [
        'title' => 'SITUBA | Blog & Berita Eliminasi TBC Surakarta',
        'description' => 'Kabar terbaru, artikel edukasi kesehatan, dan cerita sukses gerakan eliminasi TBC di Kota Surakarta.'
    ])
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Work+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .font-heading { font-family: 'Outfit', sans-serif; }
        .font-body { font-family: 'Work Sans', sans-serif; }
    </style>
</head>
<body class="font-body bg-gray-50 text-gray-900 antialiased relative overflow-x-hidden">

    <!-- Navbar -->
    <nav class="fixed w-full z-50 transition-all duration-300 bg-white/80 backdrop-blur-md border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <!-- Logo -->
                <div class="flex-shrink-0 flex items-center gap-3 cursor-pointer" onclick="window.location='{{ url('/') }}'">
                    <img class="h-10 w-auto" src="{{ asset('assets/img/situba-logo.png') }}" alt="SITUBA">
                    <span class="font-heading font-bold text-2xl text-gray-900 tracking-tight">SITUBA</span>
                </div>
                
                <!-- CTA Button -->
                <div class="flex items-center gap-4">
                     @if ($isLogged)
                        <a href="{{ route('dashboard') }}" class="hidden sm:inline-flex items-center justify-center px-6 py-2.5 border border-transparent text-sm font-semibold rounded-xl text-white bg-emerald-600 hover:bg-emerald-700 transition-all shadow-lg shadow-emerald-500/30">
                            Dashboard <i class="fa-solid fa-gauge ml-2"></i>
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="hidden sm:inline-flex items-center justify-center px-6 py-2.5 border border-transparent text-sm font-semibold rounded-xl text-white bg-gray-900 hover:bg-emerald-600 transition-all shadow-lg shadow-gray-900/20 hover:shadow-emerald-500/30">
                            Masuk <i class="fa-solid fa-arrow-right ml-2"></i>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="pt-28 pb-20 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto min-h-screen">
        
        <!-- Hero Section -->
        <div class="text-center max-w-3xl mx-auto mb-16 relative">
             <!-- Blobs -->
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-emerald-400/20 rounded-full blur-3xl -z-10 animate-pulse"></div>

            <span class="inline-block py-1 px-3 rounded-full bg-emerald-100 text-emerald-700 text-xs font-bold tracking-wide uppercase mb-4">
                Blog & Informasi
            </span>
            <h1 class="font-heading text-4xl md:text-5xl font-bold text-gray-900 leading-tight mb-6">
                Kabar Terbaru dari <br> <span class="text-emerald-600">Ekosistem Sehat</span>
            </h1>
            <p class="text-lg text-gray-600 mb-8 leading-relaxed">
                Temukan cerita inspiratif, update program, dan informasi kesehatan terkini seputar eliminasi TBC di Surakarta.
            </p>

            <!-- Search Bar -->
            <form action="{{ route('blog.index') }}" method="GET" class="max-w-xl mx-auto relative group">
                <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none text-gray-400 group-focus-within:text-emerald-500 transition-colors">
                    <i class="fa-solid fa-search text-lg"></i>
                </div>
                <input type="text" name="q" value="{{ $search ?? '' }}" 
                    class="block w-full pl-12 pr-4 py-4 bg-white border border-gray-200 rounded-2xl leading-5 placeholder-gray-400 focus:outline-none focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all shadow-xl shadow-gray-200/40 text-gray-900" 
                    placeholder="Cari artikel atau topik...">
            </form>
        </div>

        <!-- Blog Grid -->
        @if ($posts->count() || $pinnedMaterial)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                {{-- Pinned Material --}}
                @if($pinnedMaterial && !$search && $posts->onFirstPage())
                    <article class="bg-emerald-50 rounded-2xl overflow-hidden border border-emerald-100 shadow-lg relative transform hover:-translate-y-1 transition-all duration-300 flex flex-col h-full group ring-2 ring-emerald-500/20">
                         <!-- Pin Badge -->
                        <div class="absolute top-4 right-4 z-20">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-emerald-600 text-white text-xs font-bold shadow-md">
                                <i class="fa-solid fa-thumbtack rotate-45"></i> Disematkan
                            </span>
                        </div>

                        <!-- Image -->
                        <div class="relative h-56 overflow-hidden bg-gray-100 flex items-center justify-center p-4">
                            <img src="{{ $pinnedMaterial }}" alt="Materi Edukasi Utama" class="w-full h-full object-contain transition-transform duration-500 group-hover:scale-105">
                            <div class="absolute bottom-4 left-4 bg-white/90 backdrop-blur-sm px-3 py-1 rounded-lg text-xs font-bold text-emerald-700 shadow-sm border border-emerald-100">
                                <i class="fa-solid fa-book-open mr-1"></i> Materi Edukasi
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="p-6 flex flex-col flex-1">
                            <div class="flex items-center gap-2 text-xs text-emerald-600 mb-3 font-medium">
                                <span class="flex items-center"><i class="fa-regular fa-star mr-1.5"></i> Rekomendasi Utama</span>
                            </div>

                            <h3 class="font-heading text-xl font-bold text-gray-900 mb-3 leading-snug group-hover:text-emerald-600 transition-colors">
                                <a href="{{ route('public.materi') }}">
                                    Panduan Edukasi & Penanganan TBC Terkini
                                </a>
                            </h3>
                            
                            <p class="text-gray-600 text-sm leading-relaxed mb-6 line-clamp-3">
                                Akses materi visual lengkap mengenai pencegahan, penanganan, dan eliminasi TBC untuk kader dan masyarakat umum.
                            </p>

                            <div class="mt-auto pt-4 border-t border-emerald-100 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600 text-xs font-bold">
                                        M
                                    </div>
                                    <span class="text-xs font-medium text-gray-600">Admin SITUBA</span>
                                </div>
                                <a href="{{ route('public.materi') }}" class="text-sm font-bold text-emerald-600 hover:text-emerald-700 flex items-center gap-1 transition-colors">
                                    Lihat Materi <i class="fa-solid fa-arrow-right text-xs mt-0.5"></i>
                                </a>
                            </div>
                        </div>
                    </article>
                @endif

                @foreach ($posts as $post)
                    <article class="bg-white rounded-2xl overflow-hidden border border-gray-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col h-full group">
                        <!-- Image -->
                        <div class="relative h-56 overflow-hidden bg-gray-100">
                             @if ($post->image)
                                <img src="{{ asset('storage/' . $post->image->path) }}" alt="{{ $post->title }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-300 bg-gray-50">
                                    <i class="fa-regular fa-image text-4xl"></i>
                                </div>
                            @endif
                            <div class="absolute top-4 left-4 bg-white/90 backdrop-blur-sm px-3 py-1 rounded-lg text-xs font-bold text-emerald-700 shadow-sm">
                                <i class="fa-solid fa-bullhorn mr-1"></i> Publikasi Pemda
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="p-6 flex flex-col flex-1">
                            <div class="flex items-center gap-2 text-xs text-gray-500 mb-3 font-medium">
                                <span class="flex items-center"><i class="fa-regular fa-calendar mr-1.5"></i> {{ optional($post->published_at)?->format('d M Y') }}</span>
                            </div>

                            <h3 class="font-heading text-xl font-bold text-gray-900 mb-3 line-clamp-2 leading-snug group-hover:text-emerald-600 transition-colors">
                                <a href="{{ route('blog.show', $post) }}">
                                    {{ $post->title }}
                                </a>
                            </h3>
                            
                            <p class="text-gray-600 text-sm leading-relaxed mb-6 line-clamp-3">
                                {{ Str::limit(strip_tags($post->content), 120) }}
                            </p>

                            <div class="mt-auto pt-4 border-t border-gray-50 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600 text-xs font-bold">
                                        {{ substr($post->author->name ?? 'A', 0, 1) }}
                                    </div>
                                    <span class="text-xs font-medium text-gray-600 truncate max-w-[100px]">{{ $post->author->name ?? 'Admin' }}</span>
                                </div>
                                <a href="{{ route('blog.show', $post) }}" class="text-sm font-bold text-emerald-600 hover:text-emerald-700 flex items-center gap-1 transition-colors">
                                    Baca <i class="fa-solid fa-arrow-right text-xs mt-0.5"></i>
                                </a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <!-- Pagination -->
            @if ($posts->hasPages())
                <div class="mt-16 flex justify-center">
                    {{ $posts->links('pagination::tailwind') }} 
                    <!-- Fallback if vendor publish not done, simplistic custom pagination -->
                     <!-- Since we don't know if tailwind pagination view exists, let's assume default works or simple buttons -->
                </div>
                <!-- Custom Simple Pagination (in case standard one looks off) -->
                <div class="mt-12 flex justify-center gap-2">
                    @if (!$posts->onFirstPage())
                         <a href="{{ $posts->previousPageUrl() }}" class="px-4 py-2 bg-white border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-emerald-600 transition-colors">
                            <i class="fa-solid fa-chevron-left mr-1"></i> Prev
                         </a>
                    @endif
                    
                    @if ($posts->hasMorePages())
                        <a href="{{ $posts->nextPageUrl() }}" class="px-4 py-2 bg-white border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-emerald-600 transition-colors">
                            Next <i class="fa-solid fa-chevron-right ml-1"></i>
                         </a>
                    @endif
                </div>
            @endif

        @else
            <!-- Empty State -->
            <div class="text-center py-20 bg-white rounded-3xl border border-gray-100 shadow-sm max-w-2xl mx-auto">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-gray-50 rounded-full mb-6 text-gray-300">
                    <i class="fa-regular fa-newspaper text-4xl"></i>
                </div>
                <h3 class="font-heading text-xl font-bold text-gray-900 mb-2">Belum Ada Berita</h3>
                <p class="text-gray-500 max-w-sm mx-auto">
                    Saat ini belum ada artikel yang dipublikasikan. Silakan kembali lagi nanti untuk update terbaru.
                </p>
                @if($search)
                    <div class="mt-6">
                        <a href="{{ route('blog.index') }}" class="font-semibold text-emerald-600 hover:text-emerald-700">
                            <i class="fa-solid fa-times mr-1"></i> Hapus Pencarian
                        </a>
                    </div>
                @endif
            </div>
        @endif

    </div>

    <!-- Footer Simple -->
    <footer class="bg-white border-t border-gray-200 py-12 mt-auto">
        <div class="max-w-7xl mx-auto px-6 text-center">
            <p class="text-gray-500 text-sm">
                &copy; {{ date('Y') }} SITUBA - Sistem Informasi TBC Kota Surakarta.
            </p>
        </div>
    </footer>

</body>
</html>
