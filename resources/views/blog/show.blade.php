@php
    $isLogged = auth()->check();
@endphp

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $post->title }} — Blog SIGAP TBC</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="{{ asset('assets/img/favicon.png') }}">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
    <style>
        :root {
            --bg: #f7f9fd;
            --panel: #ffffff;
            --text: #0f172a;
            --muted: #475569;
            --primary: #0ea5e9;
            --border: rgba(15, 23, 42, 0.1);
            --shadow: 0 20px 50px rgba(15, 23, 42, 0.08);
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            background:
                radial-gradient(circle at 18% 20%, rgba(14, 165, 233, 0.18), transparent 32%),
                radial-gradient(circle at 82% 5%, rgba(34, 197, 94, 0.16), transparent 30%),
                var(--bg);
            color: var(--text);
            font-family: 'Space Grotesk', 'Inter', system-ui, -apple-system, sans-serif;
            min-height: 100vh;
        }

        a { color: inherit; text-decoration: none; }

        .shell {
            max-width: 960px;
            margin: 0 auto;
            padding: 32px 20px 60px;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 999px;
            background: rgba(14, 165, 233, 0.12);
            border: 1px solid rgba(14, 165, 233, 0.35);
            color: #0ea5e9;
            font-size: 12px;
            margin-top: 10px;
        }

        .card {
            background: var(--panel);
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: 28px;
            box-shadow: var(--shadow);
        }

        h1 {
            margin: 12px 0 8px;
            font-size: 32px;
            letter-spacing: -.01em;
        }

        .hero-img {
            width: 100%;
            max-height: 360px;
            object-fit: cover;
            border-radius: 18px;
            margin-bottom: 16px;
            border: 1px solid var(--border);
        }

        .meta {
            display: flex;
            gap: 14px;
            align-items: center;
            color: var(--muted);
            font-size: 14px;
            margin-bottom: 18px;
        }

        .meta i { color: var(--primary); }

        .content {
            color: var(--text);
            line-height: 1.7;
            font-size: 16px;
        }

        .content p { margin-bottom: 1em; }
        .content strong { color: #fff; }
        .content a { color: var(--primary); }

        .toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
        }

        .btn {
            border: 1px solid var(--border);
            background: #fff;
            color: var(--text);
            padding: 10px 16px;
            border-radius: 12px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08);
        }

        @media (max-width: 768px) {
            .toolbar { flex-direction: column; gap: 12px; align-items: flex-start; }
        }
    </style>
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
                <span><i class="fa fa-calendar"></i> {{ optional($post->published_at)->translatedFormat('d M Y H:i') }}</span>
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
