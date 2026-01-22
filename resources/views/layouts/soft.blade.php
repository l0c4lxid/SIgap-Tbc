@php
    use App\Enums\UserRole;
    use Illuminate\Support\Str;

    $user = auth()->user();
    $role = $user?->role;
    $navPresets = [
        UserRole::Puskesmas->value => [
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'dashboard'],
            ['label' => 'Kelurahan Binaan', 'url' => route('puskesmas.kelurahan'), 'icon' => 'folder'],
            ['label' => 'Data Kader', 'url' => route('puskesmas.kaders'), 'icon' => 'users'],
            ['label' => 'Skrining', 'url' => route('puskesmas.screenings'), 'icon' => 'screening'],
            ['label' => 'Berita', 'url' => route('news.index'), 'icon' => 'news'],
            // ['label' => 'Sembuh', 'url' => '#', 'icon' => 'sembuh'],
        ],
        UserRole::Kelurahan->value => [
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'dashboard'],
            ['label' => 'Puskesmas Mitra', 'url' => route('kelurahan.puskesmas'), 'icon' => 'folder'],
            ['label' => 'Data Kader', 'url' => route('kelurahan.kaders'), 'icon' => 'users'],
            ['label' => 'Berita', 'url' => route('news.index'), 'icon' => 'news'],
            // ['label' => 'Sembuh', 'url' => '#', 'icon' => 'sembuh'],
        ],
        UserRole::Pemda->value => [
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'dashboard'],
            ['label' => 'Verifikasi Pengguna', 'url' => route('pemda.verification'), 'icon' => 'verify'],
            [
                'label' => 'Skrining',
                'url' => route('pemda.screenings'),
                'icon' => 'screening',
                'active_routes' => ['pemda.screenings', 'pemda.screenings.show'],
            ],
            ['label' => 'Semua Berita', 'url' => route('news.index'), 'icon' => 'news'],
        ],
        UserRole::Kader->value => [
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'dashboard'],
            ['label' => 'Puskesmas Induk', 'url' => route('kader.puskesmas'), 'icon' => 'folder'],
            ['label' => 'Skrining', 'url' => route('kader.screening.index'), 'icon' => 'screening'],
            ['label' => 'Materi', 'url' => route('kader.materi'), 'icon' => 'materi'],
            ['label' => 'Berita', 'url' => route('news.index'), 'icon' => 'news'],
        ],
    ];

    $navItems = $navPresets[$role?->value ?? UserRole::Kader->value] ?? reset($navPresets);
    $currentUrl = url()->current();
    $activeNavItem = collect($navItems)
        ->first(function ($item) use ($currentUrl) {
            $base = rtrim($item['url'] ?? '#', '/');
            $routes = $item['active_routes'] ?? [];
            if (!empty($routes) && request()->route()) {
                return request()->routeIs($routes);
            }
            if ($base === '#') {
                return false;
            }
            return $currentUrl === ($item['url'] ?? '') || str_starts_with($currentUrl, $base . '/');
        });
    $navTitle = $activeNavItem['label'] ?? ($navItems[0]['label'] ?? 'Dashboard');

    $profileNav = [
        'label' => $role === UserRole::Pemda ? 'Profil Pemda' : 'Profil Saya',
        'url' => $role === UserRole::Pemda ? route('pemda.profile.edit') : route('profile.edit'),
        'icon' => 'profile',
    ];

    $now = now()->locale('id');
    $hour = (int) $now->format('H');
    $greeting = match (true) {
        $hour < 11 => 'Selamat pagi',
        $hour < 15 => 'Selamat siang',
        $hour < 19 => 'Selamat sore',
        default => 'Selamat malam',
    };
    $userInitials = collect(explode(' ', trim($user?->name ?? 'SITUBA')))
        ->filter()
        ->map(fn ($segment) => Str::upper(Str::substr($segment, 0, 1)))
        ->take(2)
        ->implode('') ?: 'ST';
    $roleHeadline = $role ? Str::headline($role->name) : 'Pengguna';
    $shortName = Str::words($user?->name ?? 'Pengguna', 2, '');
@endphp
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('favicon.ico') }}">
    <title>{{ config('app.name', 'SITUBA') }}</title>
    <link href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,800" rel="stylesheet" />
    <link href="{{ asset('assets/css/nucleo-icons.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/nucleo-svg.css') }}" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
    <link id="pagestyle" href="{{ asset('assets/css/soft-ui-dashboard.css?v=1.1.0') }}" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>

<body class="g-sidenav-show">
    @include('layouts.partials.soft-sidebar', [
        'navItems' => $navItems,
        'profileNav' => $profileNav,
    ])

           
    <main class="main-content sigap-main position-relative">
        <nav class="navbar navbar-main navbar-expand-lg px-0 mx-0 mx-lg-4 shadow-none border-radius-xl" id="navbarBlur" navbar-scroll="true">
            <div class="container-fluid py-2 py-lg-3 px-2 px-lg-3">
                <div class="soft-topbar">
                    <div class="soft-topbar__primary">
                        <div class="soft-topbar__summary">
                            <span class="soft-chip">
                                <i class="ri-user-smile-line me-1"></i>{{ $greeting }}, {{ $shortName ?: 'Pengguna' }}
                            </span>
                            <span class="soft-chip">
                                <i class="ri-shield-check-line me-1"></i>{{ $roleHeadline }}
                            </span>
                            <span class="soft-chip d-none d-md-inline-flex">
                                <i class="ri-calendar-line me-1"></i>{{ $now->translatedFormat('l, d M Y') }}
                            </span>
                        </div>
                            <div class="soft-topbar__heading">
                                <div>
                                    <h1 class="soft-page-title mb-0">{{ $navTitle }}</h1>
                    <p class="soft-topbar__subtitle mb-0">Data skrining dan tindak lanjut TBC yang terintegrasi di tiap level layanan.</p>
                                </div>
                            </div>
                    </div>
                    <div class="soft-topbar__actions">
                        <button id="iconNavbarSidenav" class="btn btn-outline-primary btn-icon d-xl-none" type="button" aria-label="Navigasi">
                            <i class="ri-menu-3-line"></i>
                        </button>
                        <div class="dropdown soft-profile-dropdown ms-auto">
                            <button class="btn border-0 bg-transparent p-0" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="soft-user-pill">
                                    <span class="soft-user-avatar">{{ $userInitials }}</span>
                                    <span class="d-none d-sm-flex flex-column text-start">
                                        <span class="fw-semibold">{{ Str::limit($user?->name ?? 'Pengguna', 22) }}</span>
                                        <small class="text-muted">{{ $roleHeadline }}</small>
                                    </span>
                                    <i class="ri-arrow-down-s-line text-sm"></i>
                                </span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end px-2 py-2" aria-labelledby="profileDropdown">
                                <a class="dropdown-item d-flex align-items-center gap-2" href="{{ $profileNav['url'] }}">
                                    <i class="fa-solid fa-id-badge text-primary"></i> {{ $profileNav['label'] }}
                                </a>
                                <form method="POST" action="{{ route('logout') }}" data-confirm="Keluar dari aplikasi?" data-confirm-text="Ya, keluar">
                                    @csrf
                                    <button type="submit" class="dropdown-item d-flex align-items-center gap-2 text-danger">
                                        <i class="fa-solid fa-arrow-right-from-bracket"></i> Keluar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <div class="container-fluid py-2 py-lg-4 px-2 px-lg-3 d-flex flex-column min-vh-100">
            <div class="soft-content-shell d-flex flex-column flex-grow-1">
                @yield('content')
                @include('layouts.partials.soft-footer')
            </div>
        </div>
    </main>

    <script src="{{ asset('assets/js/core/popper.min.js') }}"></script>
    <script src="{{ asset('assets/js/core/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/perfect-scrollbar.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/smooth-scrollbar.min.js') }}"></script>
    <script src="{{ asset('assets/js/soft-ui-dashboard.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('form[data-confirm]').forEach(form => {
                form.addEventListener('submit', function (event) {
                    if (form.dataset.confirmed === 'true') {
                        return;
                    }

                    event.preventDefault();

                    const message = form.dataset.confirm ?? 'Lanjutkan aksi ini?';
                    const confirmText = form.dataset.confirmText ?? 'Ya';
                    const cancelText = form.dataset.cancelText ?? 'Batal';

                    // Tutup sidebar lebih dulu agar dialog tidak tertutup di mode mobile
                    window.softSidebarClose?.();

                    Swal.fire({
                        icon: 'warning',
                        title: 'Konfirmasi',
                        text: message,
                        showCancelButton: true,
                        confirmButtonText: confirmText,
                        cancelButtonText: cancelText,
                        reverseButtons: true,
                    }).then(result => {
                        if (result.isConfirmed) {
                            form.dataset.confirmed = 'true';
                            form.submit();
                        }
                    });
                });
        });
        @if (session('status'))
            Swal.fire({
                icon: 'success',
                    title: 'Berhasil',
                    text: @json(session('status')),
                    });
        @endif
        @if ($errors->any())
            const errorMessages = @json($errors->all());
            if (errorMessages.length) {
                Swal.fire({
                    icon: 'error',
                    title: 'Terjadi kesalahan',
                        html: '<ul class="text-start mb-0">' + errorMessages.map(msg => `<li>${msg}</li>`).join('') + '</ul>',
                    });
                }
        @endif

        // Auto-submit search forms with debounce
        const autoForms = document.querySelectorAll('form[data-auto-submit]');
        autoForms.forEach(form => {
            const inputs = form.querySelectorAll('input[name="q"], select[name], textarea[name]');
            let debounceTimer = null;
            inputs.forEach(input => {
                input.addEventListener('input', () => {
                    if (debounceTimer) {
                        clearTimeout(debounceTimer);
                    }
                    debounceTimer = setTimeout(() => {
                        form.requestSubmit();
                    }, 450);
                });
            });
        });
    });
    </script>
    @stack('scripts')
</body>

</html>
