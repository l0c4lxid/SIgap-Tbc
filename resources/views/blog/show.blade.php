@php
    $isLogged = auth()->check();
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    @include('partials.seo-meta', [
        'title' => $post->title . ' | SITUBA Artikel',
        'description' => Str::limit(strip_tags($post->summary ?? $post->content), 160),
        'image' => (!empty($post->image) ? (str_starts_with($post->image, 'http') ? $post->image : asset('storage/' . $post->image)) : asset('android-chrome-512x512.png')),
        'type' => 'article'
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
        
        /* Typography for Content */
        .prose h1, .prose h2, .prose h3 { font-family: 'Outfit', sans-serif; color: #111827; margin-top: 1.5em; margin-bottom: 0.5em; font-weight: 700; }
        .prose p { margin-bottom: 1.25em; line-height: 1.8; color: #374151; }
        .prose ul { list-style-type: disc; padding-left: 1.5em; margin-bottom: 1.25em; }
        .prose blockquote { border-left-width: 4px; border-color: #10b981; padding-left: 1em; font-style: italic; color: #4b5563; }
        .prose img { border-radius: 1rem; margin: 2rem 0; width: 100%; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); }
    </style>
</head>
<body class="font-body bg-gray-50 text-gray-900 antialiased min-h-screen flex flex-col">

    <!-- Navbar Sticky -->
    <nav class="sticky top-0 z-50 bg-white/90 backdrop-blur-md border-b border-gray-100 shadow-sm transition-all">
        <div class="max-w-4xl mx-auto px-4 sm:px-6">
            <div class="flex items-center justify-between h-16">
                
                <a href="{{ route('blog.index') }}" class="group inline-flex items-center gap-2 text-sm font-semibold text-gray-600 hover:text-emerald-600 transition-colors">
                    <div class="w-8 h-8 rounded-full bg-gray-100 group-hover:bg-emerald-100 flex items-center justify-center transition-colors">
                        <i class="fa-solid fa-arrow-left text-xs"></i>
                    </div>
                    <span>Kembali</span>
                </a>

                <div class="flex items-center gap-3">
                     <!-- Share Button (Dummy) -->
                     <button class="w-9 h-9 flex items-center justify-center rounded-full text-gray-500 hover:bg-gray-100 transition-colors" title="Bagikan">
                        <i class="fa-solid fa-share-nodes"></i>
                     </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Content -->
    <main class="flex-1 w-full max-w-4xl mx-auto px-4 sm:px-6 py-10">
        
        <article class="bg-white rounded-3xl p-6 sm:p-10 shadow-sm border border-gray-100">
            
            <!-- Header -->
            <header class="mb-10 text-center sm:text-left">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 text-xs font-bold uppercase mb-6">
                    <i class="fa-solid fa-check-circle"></i> Publikasi Resmi
                </div>
                
                <h1 class="font-heading text-3xl sm:text-4xl md:text-5xl font-bold text-gray-900 leading-tight mb-6">
                    {{ $post->title }}
                </h1>

                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-6 text-sm text-gray-500 border-t border-b border-gray-50 py-4">
                    <div class="flex items-center gap-2">
                         <div class="w-10 h-10 rounded-full bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white font-bold text-sm shadow-md">
                            {{ substr($post->author->name ?? 'A', 0, 1) }}
                        </div>
                        <div class="flex flex-col text-left">
                            <span class="text-xs font-medium text-gray-400 uppercase">Penulis</span>
                            <span class="font-bold text-gray-900">{{ $post->author->name ?? 'Administrator' }}</span>
                        </div>
                    </div>
                    
                    <div class="hidden sm:block w-px h-8 bg-gray-200"></div>

                    <div class="flex items-center gap-2">
                        <div class="w-10 h-10 rounded-full bg-indigo-50 text-indigo-500 flex items-center justify-center">
                            <i class="fa-regular fa-calendar"></i>
                        </div>
                         <div class="flex flex-col text-left">
                            <span class="text-xs font-medium text-gray-400 uppercase">Terbit</span>
                            <span class="font-bold text-gray-900">{{ optional($post->published_at)?->format('d F Y') }}</span>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Featured Image -->
            @if ($post->image)
                <div class="relative w-full aspect-video rounded-2xl overflow-hidden mb-10 shadow-lg bg-gray-100">
                    <img src="{{ asset('storage/' . $post->image->path) }}" alt="{{ $post->title }}" class="absolute inset-0 w-full h-full object-cover">
                </div>
            @endif

            <!-- Body -->
            <div class="prose prose-lg prose-emerald max-w-none font-body text-gray-600">
                {!! nl2br(e($post->content)) !!}
            </div>

            <!-- Footer Meta -->
            <div class="mt-12 pt-8 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                <p class="text-sm text-gray-400 italic">
                    Artikel ini telah ditinjau dan disetujui oleh <strong class="text-gray-600">Dinas Kesehatan Kota Surakarta</strong>.
                </p>
                
                <div class="flex gap-2">
                    <!-- Example Tags -->
                    <span class="px-3 py-1 bg-gray-100 text-gray-600 rounded-lg text-xs font-medium">#TBC</span>
                    <span class="px-3 py-1 bg-gray-100 text-gray-600 rounded-lg text-xs font-medium">#Kesehatan</span>
                </div>
            </div>

        </article>

    </main>

    <!-- Footer Simple -->
    <footer class="bg-white border-t border-gray-100 py-8">
        <div class="max-w-7xl mx-auto px-6 text-center">
            <a href="/" class="inline-flex items-center gap-2 mb-4 opacity-50 hover:opacity-100 transition-opacity">
                 <img src="{{ asset('assets/img/situba-logo.png') }}" class="h-6 w-auto grayscale" alt="Logo">
                 <span class="font-heading font-bold text-gray-900">SITUBA</span>
            </a>
            <p class="text-gray-400 text-sm">
                &copy; {{ date('Y') }} Sistem Informasi TBC Kota Surakarta.
            </p>
        </div>
    </footer>

</body>
</html>
