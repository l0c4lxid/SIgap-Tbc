@php
    $isLogged = auth()->check();
@endphp

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SItuba | Blog</title>
    <meta name="description" content="{{ Str::limit(strip_tags($post->summary ?? $post->content), 150) }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('favicon.ico') }}">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
        <link rel="stylesheet" href="{{ asset('css/blog-show.css') }}">

</head>

<body>
    <div class="shell">
        <header>
            <a class="btn" href="{{ route('blog.index') }}"><i class="fa fa-arrow-left"></i> Kembali ke Blog</a>
            @if ($isLogged)
                <a class="btn" href="{{ route('dashboard') }}"><i class="fa fa-gauge"></i> Dashboard</a>
            @else
                <a class="btn" href="{{ route('login') }}"><i class="fa fa-sign-in-alt"></i> Login</a>
            @endif
        </header>

        <article class="card">
            @if ($post->image)
                <img src="{{ asset('storage/' . $post->image->path) }}" alt="Gambar {{ $post->title }}" class="hero-img">
            @endif
            <span class="pill"><i class="fa fa-bullhorn"></i>Publikasi Pemda</span>
            <h1>{{ $post->title }}</h1>
            <div class="meta">
                <span><i class="fa fa-user"></i> {{ $post->author->name ?? 'Kontributor' }}</span>
                <span><i class="fa fa-calendar"></i> {{ optional($post->published_at)?->format('d M Y H:i') }}</span>
            </div>

            <div class="content">
                {!! nl2br(e($post->content)) !!}
            </div>

            <div class="toolbar">
                <div class="pill"><i class="fa fa-shield-halved"></i> Tinjau & disetujui Pemda</div>
                <div class="btn"><i class="fa fa-share-alt"></i> Bagikan</div>
            </div>
        </article>
    </div>
</body>

</html>
