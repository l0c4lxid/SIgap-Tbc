@php
    $appName = config('app.name', 'SITUBA');
    $siteName = 'SITUBA Surakarta';
    $pageTitle = $title ?? (isset($subjudul) ? $subjudul . ' - ' . $appName : 'SITUBA Surakarta | Tuberculosis Assistant');
    $pageDescription = $description ?? 'SITUBA (Sistem Informasi Tuberkulosis Surakarta) - Platform digital kolaboratif Pemda, Puskesmas, Kelurahan, dan Kader Kesehatan untuk skrining dini, edukasi, dan monitoring eliminasi TBC di Kota Surakarta.';
    
    // Resolve absolute URL & force HTTPS for social scrapers (WhatsApp, FB, Telegram)
    $baseUrl = url('/');
    if (str_starts_with($baseUrl, 'http://') && !str_contains($baseUrl, 'localhost')) {
        $baseUrl = str_replace('http://', 'https://', $baseUrl);
    }

    $rawImage = $image ?? asset('android-chrome-512x512.png');
    if (!str_starts_with($rawImage, 'http')) {
        $pageImage = rtrim($baseUrl, '/') . '/' . ltrim($rawImage, '/');
    } else {
        $pageImage = str_replace('http://situba.my.id', 'https://situba.my.id', $rawImage);
    }

    $rawUrl = $url ?? url()->current();
    $pageUrl = str_replace('http://situba.my.id', 'https://situba.my.id', $rawUrl);
    $pageType = $type ?? 'website';
@endphp

<!-- Primary Meta Tags -->
<title>{{ $pageTitle }}</title>
<meta name="title" content="{{ $pageTitle }}">
<meta name="description" content="{{ $pageDescription }}">
<meta name="keywords" content="SITUBA, SITUBA Surakarta, TBC Surakarta, Tuberkulosis Solo, Skrining TBC, Puskesmas Surakarta, Kader Kesehatan Solo, Eliminasi TBC Solo, Dinas Kesehatan Surakarta">
<meta name="author" content="Pemerintah Kota Surakarta & SITUBA">
<meta name="robots" content="index, follow">

<!-- Schema.org / Google / WhatsApp itemprop -->
<meta itemprop="name" content="{{ $pageTitle }}">
<meta itemprop="description" content="{{ $pageDescription }}">
<meta itemprop="image" content="{{ $pageImage }}">

<!-- Favicon & PWA Icons -->
<link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
<link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
<link rel="manifest" href="{{ asset('site.webmanifest') }}">
<meta name="theme-color" content="#10b981">
<meta name="msapplication-TileColor" content="#10b981">

<!-- Open Graph / Facebook / WhatsApp -->
<meta property="og:type" content="{{ $pageType }}">
<meta property="og:url" content="{{ $pageUrl }}">
<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:title" content="{{ $pageTitle }}">
<meta property="og:description" content="{{ $pageDescription }}">
<meta property="og:image" content="{{ $pageImage }}">
<meta property="og:image:secure_url" content="{{ $pageImage }}">
<meta property="og:image:type" content="image/png">
<meta property="og:image:width" content="512">
<meta property="og:image:height" content="512">
<meta property="og:image:alt" content="{{ $siteName }}">

<!-- Twitter / X -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:url" content="{{ $pageUrl }}">
<meta name="twitter:title" content="{{ $pageTitle }}">
<meta name="twitter:description" content="{{ $pageDescription }}">
<meta name="twitter:image" content="{{ $pageImage }}">
