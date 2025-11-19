@php
    use App\Enums\UserRole;
    use Illuminate\Support\Str;

    $user = auth()->user();
    $role = $user?->role;
    $navPresets = [
        UserRole::Pasien->value => [
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'dashboard'],
            ['label' => 'Skrining', 'url' => route('patient.screening'), 'icon' => 'screening'],
            ['label' => 'Anggota Keluarga', 'url' => route('patient.family'), 'icon' => 'anggota'],
        ],
        UserRole::Puskesmas->value => [
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'dashboard'],
            ['label' => 'Data Kader', 'url' => route('puskesmas.kaders'), 'icon' => 'users'],
            ['label' => 'Data Pasien', 'url' => route('puskesmas.patients'), 'icon' => 'folder'],
            ['label' => 'Skrining', 'url' => route('puskesmas.screenings'), 'icon' => 'screening'],
            ['label' => 'Berobat', 'url' => route('puskesmas.treatment'), 'icon' => 'berobat'],
            // ['label' => 'Sembuh', 'url' => '#', 'icon' => 'sembuh'],
        ],
        UserRole::Kelurahan->value => [
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'dashboard'],
            ['label' => 'Puskesmas Mitra', 'url' => route('kelurahan.puskesmas'), 'icon' => 'folder'],
            ['label' => 'Data Kader', 'url' => route('kelurahan.kaders'), 'icon' => 'users'],
            ['label' => 'Data Pasien', 'url' => route('kelurahan.patients'), 'icon' => 'folder'],
            // ['label' => 'Sembuh', 'url' => '#', 'icon' => 'sembuh'],
        ],
        UserRole::Pemda->value => [
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'dashboard'],
            ['label' => 'Verifikasi Pengguna', 'url' => route('pemda.verification'), 'icon' => 'verify'],
            ['label' => 'Data Pasien', 'url' => route('pemda.patients'), 'icon' => 'folder'],
        ],
        UserRole::Kader->value => [
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'dashboard'],
            ['label' => 'Puskesmas Induk', 'url' => route('kader.puskesmas'), 'icon' => 'folder'],
            ['label' => 'Data Pasien', 'url' => route('kader.patients'), 'icon' => 'folder'],
            ['label' => 'Skrining', 'url' => route('kader.screening.index'), 'icon' => 'screening'],
        ],
    ];

    $navItems = $navPresets[$role?->value ?? UserRole::Pasien->value] ?? reset($navPresets);
    $activeNavItem = collect($navItems)
        ->first(fn($item) => ($item['url'] ?? '#') !== '#' && url()->current() === $item['url']);
    $navTitle = $activeNavItem['label'] ?? ($navItems[0]['label'] ?? 'Dashboard');

    $profileNav = [
        'label' => $role === UserRole::Pemda ? 'Profil Pemda' : 'Profil Saya',
        'url' => $role === UserRole::Pemda ? route('pemda.profile.edit') : route('profile.edit'),
        'icon' => 'profile',
    ];

    $now = now();
    $hour = (int) $now->format('H');
    $greeting = match (true) {
        $hour < 11 => 'Selamat pagi',
        $hour < 15 => 'Selamat siang',
        $hour < 19 => 'Selamat sore',
        default => 'Selamat malam',
    };
    $userInitials = collect(explode(' ', trim($user?->name ?? 'Sigap TBC')))
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
    <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('assets/img/apple-icon.png') }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/img/favicon.png') }}">
    <title>{{ config('app.name', 'SIGAP TBC') }}</title>
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

           
    <main class="main-content position-relative bg-gray-100 max-height-vh-100 h-100 border-radius-lg">
        <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl" id="navbarBlur" navbar-scroll="true">
            <div class="container-fluid py-3 px-3">
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
                                <p class="soft-topbar__subtitle mb-0">Data pelayanan TBC terintegrasi dan langkah tindak lanjut prioritas.</p>
                            </div>
                        </div>
                    </div>
                    <div class="soft-topbar__actions">
                        <button id="iconNavbarSidenav" class="btn btn-outline-primary btn-icon d-lg-none" type="button" aria-label="Navigasi">
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

        <div class="container-fluid py-4 d-flex flex-column min-vh-100">
            @yield('content')
            @include('layouts.partials.soft-footer')
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
        });
    </script>
    @stack('scripts')
</body>

</html>
