@php
    use Illuminate\Support\Str;
    $isLogged = auth()->check();
@endphp

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog SIGAP TBC</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="{{ asset('assets/img/favicon.png') }}">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
    <style>
        :root {
            --bg: #f5f7fb;
            --panel: #ffffff;
            --card: #ffffff;
            --text: #0f172a;
            --muted: #475569;
            --primary: #0ea5e9;
            --accent: #a855f7;
            --border: rgba(15, 23, 42, 0.08);
            --shadow: 0 20px 50px rgba(15, 23, 42, 0.08);
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            background:
                radial-gradient(circle at 20% 20%, rgba(56, 189, 248, 0.18), transparent 32%),
                radial-gradient(circle at 80% 0%, rgba(168, 85, 247, 0.14), transparent 28%),
                var(--bg);
            color: var(--text);
            font-family: 'Space Grotesk', 'Inter', system-ui, -apple-system, sans-serif;
            min-height: 100vh;
        }

        a { color: inherit; text-decoration: none; }

        .shell {
            max-width: 1200px;
            margin: 0 auto;
            padding: 32px 20px 60px;
        }

        header {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 24px;
            align-items: center;
            margin-bottom: 28px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .brand .logo {
            width: 48px;
            height: 48px;
            display: grid;
            place-items: center;
            border-radius: 16px;
            background: linear-gradient(135deg, #0ea5e9, #22c55e);
            color: #fff;
            font-weight: 700;
        }

        .brand h1 {
            margin: 0;
            font-size: 24px;
            letter-spacing: -.01em;
        }

        .brand p {
            margin: 4px 0 0;
            color: var(--muted);
            font-size: 14px;
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
            transition: transform 120ms ease, box-shadow 120ms ease;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.05);
        }

        .btn:hover { transform: translateY(-2px); box-shadow: 0 16px 32px rgba(15, 23, 42, 0.12); }
        .btn.primary { background: linear-gradient(135deg, #0ea5e9, #22c55e); color: #fff; border: none; }

        .hero {
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.12), rgba(34, 197, 94, 0.12));
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: 28px;
            margin-bottom: 26px;
            box-shadow: var(--shadow);
        }

        .hero h2 {
            margin: 0 0 6px;
            font-size: 28px;
            letter-spacing: -.01em;
        }

        .hero p {
            margin: 0 0 14px;
            color: var(--muted);
            font-size: 15px;
        }

        .search-row {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 12px;
            align-items: center;
        }

        .input-wrap {
            position: relative;
        }

        .input-wrap i {
            position: absolute;
            top: 50%;
            left: 12px;
            transform: translateY(-50%);
            color: var(--muted);
        }

        .input-wrap input {
            width: 100%;
            padding: 12px 14px 12px 38px;
            border-radius: 12px;
            border: 1px solid var(--border);
            background: #fff;
            color: var(--text);
            font-size: 15px;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 18px;
            margin-top: 20px;
        }

        .card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 18px;
            padding: 18px;
            display: grid;
            gap: 10px;
            height: 100%;
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow);
        }

        .card-thumb {
            border-radius: 14px;
            overflow: hidden;
            height: 180px;
            background: #e2e8f0;
        }

        .card-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 12px;
            background: rgba(14, 165, 233, 0.12);
            color: #0ea5e9;
            border: 1px solid rgba(14, 165, 233, 0.35);
            margin-top: 6px;
        }

        .card h3 {
            margin: 4px 0 0;
            font-size: 18px;
        }

        .card p {
            margin: 0;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.5;
        }

        .meta {
            display: flex;
            gap: 12px;
            align-items: center;
            color: var(--muted);
            font-size: 12px;
        }

        .meta i { color: var(--primary); }

        .card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: auto;
            position: relative;
            z-index: 1;
        }

        .cta {
            display: flex;
            gap: 10px;
        }

        .read-more {
            color: var(--primary);
            font-weight: 600;
            font-size: 14px;
        }

        .empty {
            text-align: center;
            color: var(--muted);
            padding: 48px 12px;
        }

        .pagination {
            display: flex;
            gap: 8px;
            justify-content: center;
            margin-top: 28px;
        }

        .pagination a, .pagination span {
            padding: 8px 12px;
            border-radius: 10px;
            border: 1px solid var(--border);
            color: var(--text);
            font-size: 14px;
            background: #fff;
            box-shadow: 0 12px 24px rgba(15, 23, 42, 0.05);
        }

        .pagination .active {
            background: linear-gradient(135deg, #0ea5e9, #22c55e);
            border: none;
            color: #fff;
            font-weight: 700;
            box-shadow: var(--shadow);
        }

        @media (max-width: 768px) {
            header { grid-template-columns: 1fr; }
            .cta { justify-content: flex-start; }
            .hero { padding: 18px; }
            .search-row { grid-template-columns: 1fr; }
        }
    </style>
</head>

<body>
    <div class="shell">
        <header>
            <div class="brand">
                <div class="logo">SG</div>
                <div>
                    <h1>Blog SIGAP TBC</h1>
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
            <p>Temukan berita yang sudah dikurasi oleh Pemda sebelum tampil ke publik. Semuanya berasal dari kontribusi kader, puskesmas, dan pasien.</p>
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
                            <span><i class="fa fa-calendar"></i> {{ optional($post->published_at)->translatedFormat('d M Y') }}</span>
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
