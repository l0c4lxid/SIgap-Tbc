@php
    use Illuminate\Support\Str;
    $isLogged = auth()->check();
@endphp

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SItuba | Blog</title>
    <meta name="description"
        content="Blog SITUBA berisi artikel, info kesehatan, dan testimoni seputar pemantauan Tuberkulosis oleh kader, puskesmas, kelurahan, dan pemda.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('favicon.ico') }}">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
        <link rel="stylesheet" href="{{ asset('css/blog-index.css') }}">

</head>

<body>
    <div class="shell">
        <header>
            <div class="brand">
                <div class="logo">
                    {{-- <i class="fa-solid fa-lungs"></i> --}}
                    <img src="{{ asset('assets/img/situba-logo.png') }}" alt="SITUBA Logo" style="width:42px; height:auto;">
                </div>
                <div>
                    <h1>Blog SITUBA</h1>
                    <p>Rangkuman cerita lapangan, edukasi, dan testimoni.</p>
                </div>
            </div>
            <div class="cta">
                @if ($isLogged)
                    <a class="btn primary" href="{{ route('dashboard') }}"><i class="fa fa-gauge"></i>Dashboard</a>
                @else
                    <a class="btn primary" href="{{ route('login') }}"><i class="fa fa-arrow-left"></i>Login</a>
                @endif
            </div>
        </header>

        <div class="hero">
            <h2>Kasus inspiratif dan kabar terbaru eliminasi TBC.</h2>
            <p>Temukan berita yang sudah dikurasi oleh Pemda sebelum tampil ke publik. Semuanya berasal dari kontribusi
                kader dan puskesmas.</p>
            <form class="search-row" method="GET" action="{{ route('blog.index') }}">
                <div class="input-wrap">
                    <i class="fa fa-search"></i>
                    <input type="text" name="q" value="{{ $search }}" placeholder="Cari judul atau topik...">
                </div>
                <button class="btn primary" type="submit"><i class="fa fa-filter"></i>Filter</button>
            </form>
        </div>

        @if ($posts->count())
            <div class="grid">
                @foreach ($posts as $post)
                    <article class="card">
                        @if ($post->image)
                            <div class="card-thumb">
                                <img src="{{ asset('storage/' . $post->image->path) }}" alt="Gambar {{ $post->title }}">
                            </div>
                        @endif
                        <div class="pill"><i class="fa fa-bullhorn"></i>Publikasi Pemda</div>
                        <h3>{{ $post->title }}</h3>
                        <p>{{ Str::limit(strip_tags($post->content), 160) }}</p>
                        <div class="meta">
                            <span><i class="fa fa-user"></i> {{ $post->author->name ?? 'Kontributor' }}</span>
                            <span><i class="fa fa-calendar"></i> {{ optional($post->published_at)?->format('d M Y') }}</span>
                        </div>
                        <div class="card-footer">
                            <a class="read-more" href="{{ route('blog.show', $post) }}">Baca lebih lanjut →</a>
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <div class="empty">
                <p class="mb-1">Belum ada berita terpublikasi.</p>
                <p class="text-sm">Kunjungi lagi setelah Pemda menerbitkan kiriman terbaru.</p>
            </div>
        @endif

        @if ($posts->hasPages())
            <div class="pagination">
                @if ($posts->onFirstPage())
                    <span>←</span>
                @else
                    <a href="{{ $posts->previousPageUrl() }}">←</a>
                @endif

                @foreach (range(1, $posts->lastPage()) as $page)
                    @if ($page == $posts->currentPage())
                        <span class="active">{{ $page }}</span>
                    @elseif ($page === 1 || $page === $posts->lastPage() || abs($page - $posts->currentPage()) <= 1)
                        <a href="{{ $posts->url($page) }}">{{ $page }}</a>
                    @endif
                @endforeach

                @if ($posts->hasMorePages())
                    <a href="{{ $posts->nextPageUrl() }}">→</a>
                @else
                    <span>→</span>
                @endif
            </div>
        @endif
    </div>
</body>

</html>
