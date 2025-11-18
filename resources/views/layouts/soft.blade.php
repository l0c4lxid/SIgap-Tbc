@php
    use App\Enums\UserRole;

    $user = auth()->user();
    $role = $user?->role;
    $navPresets = [
        UserRole::Pasien->value => [
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'dashboard'],
            ['label' => 'Skrining', 'url' => route('patient.screening'), 'icon' => 'screening'],
            ['label' => 'Anggota Keluarga', 'url' => '#', 'icon' => 'anggota'],
        ],
        UserRole::Puskesmas->value => [
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'dashboard'],
            ['label' => 'Data Kader', 'url' => route('puskesmas.kaders'), 'icon' => 'users'],
            ['label' => 'Data Pasien', 'url' => route('puskesmas.patients'), 'icon' => 'folder'],
            ['label' => 'Skrining', 'url' => '#', 'icon' => 'screening'],
            ['label' => 'Berobat', 'url' => '#', 'icon' => 'berobat'],
            ['label' => 'Sembuh', 'url' => '#', 'icon' => 'sembuh'],
        ],
        UserRole::Kelurahan->value => [
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'dashboard'],
            ['label' => 'Puskesmas Mitra', 'url' => route('kelurahan.puskesmas'), 'icon' => 'folder'],
            ['label' => 'Skrining', 'url' => '#', 'icon' => 'screening'],
            ['label' => 'Berobat', 'url' => '#', 'icon' => 'berobat'],
            ['label' => 'Sembuh', 'url' => '#', 'icon' => 'sembuh'],
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
            ['label' => 'Skrining', 'url' => '#', 'icon' => 'screening'],
            ['label' => 'Berobat', 'url' => '#', 'icon' => 'berobat'],
        ],
    ];

    $navItems = $navPresets[$role?->value ?? UserRole::Pasien->value] ?? reset($navPresets);
    $activeNavItem = collect($navItems)
        ->first(fn ($item) => ($item['url'] ?? '#') !== '#' && url()->current() === $item['url']);
    $navTitle = $activeNavItem['label'] ?? ($navItems[0]['label'] ?? 'Dashboard');

    $profileNav = [
        'label' => $role === UserRole::Pemda ? 'Profil Pemda' : 'Profil Saya',
        'url' => $role === UserRole::Pemda ? route('pemda.profile.edit') : route('profile.edit'),
        'icon' => 'profile',
    ];
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
    <link id="pagestyle" href="{{ asset('assets/css/soft-ui-dashboard.css?v=1.1.0') }}" rel="stylesheet" />
    <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
    @stack('styles')
</head>

<body class="g-sidenav-show bg-gray-100">
    <div class="min-height-300 bg-primary position-absolute w-100"></div>
    @include('layouts.partials.soft-sidebar', ['navItems' => $navItems, 'profileNav' => $profileNav])

    <main class="main-content position-relative bg-gray-100 max-height-vh-100 h-100 border-radius-lg">
        <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl" id="navbarBlur" navbar-scroll="true">
            <div class="container-fluid py-1 px-3">
                <nav aria-label="breadcrumb">
                    <h6 class="font-weight-bolder mb-0">{{ $navTitle }}</h6>
                </nav>
                <div class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-4" id="navbar">
                    <div class="ms-md-auto pe-md-3 d-flex align-items-center"></div>
                    <ul class="navbar-nav  justify-content-end">
                        <li class="nav-item d-xl-none ps-3 d-flex align-items-center">
                            <a href="javascript:;" class="nav-link text-body p-0" id="iconNavbarSidenav">
                                <div class="sidenav-toggler-inner">
                                    <i class="sidenav-toggler-line"></i>
                                    <i class="sidenav-toggler-line"></i>
                                    <i class="sidenav-toggler-line"></i>
                                </div>
                            </a>
                        </li>
                        <li class="nav-item dropdown d-none d-md-flex align-items-center">
                            <a href="#" class="nav-link text-body font-weight-bold px-0" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="d-sm-inline d-none me-2">{{ $user?->name }}</span>
                                <i class="fa fa-chevron-down text-sm"></i>
                            </a>
                            @php
                                $profileLink = $role === \App\Enums\UserRole::Pemda ? route('pemda.profile.edit') : route('profile.edit');
                                $profileText = $role === \App\Enums\UserRole::Pemda ? 'Profil Pemda' : 'Profil Saya';
                            @endphp
                            <div class="dropdown-menu dropdown-menu-end px-2 py-2 me-sm-n4" aria-labelledby="profileDropdown">
                                <a class="dropdown-item d-flex align-items-center gap-2" href="{{ $profileLink }}">
                                    <i class="fa-solid fa-id-badge text-primary"></i> {{ $profileText }}
                                </a>
                                <form method="POST" action="{{ route('logout') }}" data-confirm="Keluar dari aplikasi?" data-confirm-text="Ya, keluar">
                                    @csrf
                                    <button type="submit" class="dropdown-item border-0 d-flex align-items-center gap-2">
                                        <i class="ni ni-button-power text-danger"></i> Keluar
                                    </button>
                                </form>
                            </div>
                        </li>
                    </ul>
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
