@php
    use App\Enums\UserRole;
    use Illuminate\Support\Str;

    $user = auth()->user();
    $role = $user?->role;
    $navPresets = [
        UserRole::Puskesmas->value => [
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'ri-dashboard-line'],
            ['label' => 'Kelurahan Binaan', 'url' => route('puskesmas.kelurahan'), 'icon' => 'ri-community-line'],
            ['label' => 'Data Kader', 'url' => route('puskesmas.kaders'), 'icon' => 'ri-group-line'],
            ['label' => 'Skrining', 'url' => route('puskesmas.screenings'), 'icon' => 'ri-stethoscope-line'],
            ['label' => 'Materi', 'url' => route('puskesmas.materi'), 'icon' => 'ri-book-open-line'],
            ['label' => 'Berita', 'url' => route('news.index'), 'icon' => 'ri-newspaper-line'],
        ],
        UserRole::Kelurahan->value => [
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'ri-dashboard-line'],
            ['label' => 'Puskesmas Mitra', 'url' => route('kelurahan.puskesmas'), 'icon' => 'ri-hospital-line'],
            ['label' => 'Data Kader', 'url' => route('kelurahan.kaders'), 'icon' => 'ri-group-line'],
            ['label' => 'Materi', 'url' => route('kelurahan.materi'), 'icon' => 'ri-book-open-line'],
            ['label' => 'Berita', 'url' => route('news.index'), 'icon' => 'ri-newspaper-line'],
        ],
        UserRole::Pemda->value => [
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'ri-dashboard-line'],
            ['label' => 'Verifikasi Pengguna', 'url' => route('pemda.verification'), 'icon' => 'ri-shield-user-line'],
            [
                'label' => 'Skrining',
                'url' => route('pemda.screenings'),
                'icon' => 'ri-stethoscope-line',
                'active_routes' => ['pemda.screenings', 'pemda.screenings.show'],
            ],
            ['label' => 'Materi', 'url' => route('pemda.materi'), 'icon' => 'ri-book-open-line'],
            ['label' => 'Semua Berita', 'url' => route('news.index'), 'icon' => 'ri-newspaper-line'],
        ],
        UserRole::Kader->value => [
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'ri-dashboard-line'],
            ['label' => 'Mitra', 'url' => route('kader.mitra'), 'icon' => 'ri-hand-heart-line'],
            ['label' => 'Skrining', 'url' => route('kader.screening.index'), 'icon' => 'ri-stethoscope-line'],
            ['label' => 'Materi', 'url' => route('kader.materi'), 'icon' => 'ri-book-open-line'],
            ['label' => 'Berita', 'url' => route('news.index'), 'icon' => 'ri-newspaper-line'],
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
    $navSubtitle = $subjudul ?? '';

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
    <title>SItuba | {{ $subjudul ?? 'Dashboard' }}</title>
    
    <!-- Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
    
    <!-- Icons: RemixIcon -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    
    <!-- Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>

<body class="antialiased">
    
    <!-- Sidebar -->
    @include('layouts.sidenav', [
        'navItems' => $navItems,
        'profileNav' => $profileNav,
    ])

    <!-- Main Content Wrapper -->
    <main class="xl:ml-80 p-4 min-h-screen transition-all duration-300">
        
        <!-- Top Navbar -->
        <div class="glass-panel z-30 rounded-2xl px-6 py-4 mb-6 flex items-center justify-between gap-4">
            
            <!-- Left: Toggle & Title -->
            <div class="flex items-center gap-4">
                <button onclick="toggleSidebar()" class="xl:hidden w-10 h-10 flex items-center justify-center rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200 border-none cursor-pointer">
                    <i class="ri-menu-2-line text-xl"></i>
                </button>
                
                <div class="flex flex-col">

                    <h1 class="text-xl font-bold text-gray-800 m-0 leading-tight">{{ $navTitle }}</h1>
                </div>
            </div>

            <!-- Right: Actions & Profile -->
            <div class="flex items-center gap-3 sm:gap-4">
                
                <!-- Date Chip (Desktop) -->
                <div class="hidden md:flex items-center gap-2 px-3 py-1.5 bg-white/50 rounded-lg border border-gray-100 text-xs font-medium text-gray-500">
                    <i class="ri-calendar-event-line"></i>
                    {{ $now->translatedFormat('l, d M Y') }}
                </div>

                <!-- Profile Dropdown -->
                <div class="relative group" id="profile-dropdown-container">
                    <button class="flex items-center gap-3 bg-white/50 pl-2 pr-4 py-1.5 rounded-full border border-gray-200 shadow-sm hover:shadow-md transition-all cursor-pointer border-none" 
                            onclick="document.getElementById('profile-menu').classList.toggle('hidden')">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-[var(--color-glass-primary)] to-[var(--color-glass-secondary)] text-white flex items-center justify-center font-bold text-xs">
                            {{ $userInitials }}
                        </div>
                        <div class="hidden sm:flex flex-col items-start pr-2">
                            <span class="text-sm font-bold text-gray-700 leading-none">{{ $shortName }}</span>
                            <span class="text-[10px] text-gray-400 font-medium uppercase mt-0.5">{{ $roleHeadline }}</span>
                        </div>
                        <i class="ri-arrow-down-s-line text-gray-400"></i>
                    </button>

                    <!-- Dropdown Menu -->
                    <div id="profile-menu" class="hidden absolute right-0 mt-3 w-56 bg-white rounded-xl shadow-xl overflow-hidden animate-fade-in origin-top-right ring-1 ring-black ring-opacity-5 z-50">
                        <div class="p-3 border-b border-gray-100">
                            <p class="text-sm font-bold text-gray-900 m-0">{{ $user?->name }}</p>
                            <p class="text-xs text-gray-500 m-0">{{ $user?->email }}</p>
                        </div>
                        <div class="p-1">
                            <a href="{{ $profileNav['url'] }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium text-gray-900 hover:bg-gray-50 hover:text-[var(--color-glass-primary)] transition-colors no-underline">
                                <i class="ri-user-settings-line text-lg"></i> {{ $profileNav['label'] }}
                            </a>
                        </div>
                        <div class="p-1 border-t border-gray-100">
                             <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium text-red-700 hover:bg-red-50 transition-colors border-none bg-transparent cursor-pointer">
                                    <i class="ri-logout-box-r-line text-lg"></i> Keluar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content Area -->
        <div class="animate-fade-in">
            @yield('content')
        </div>

        <!-- Footer -->
        <footer class="mt-8 mb-4 text-center">
            <p class="text-xs text-gray-400">
                &copy; {{ date('Y') }} SItuba Dashboard. All rights reserved.
            </p>
        </footer>
    </main>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Profile Dropdown Toggle Listener (Click outside to close)
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('profile-menu');
            const button = document.getElementById('profile-dropdown-container');
            if (dropdown && !button.contains(event.target)) {
                dropdown.classList.add('hidden');
            }
        });

        // SweetAlert Integration
        @if (session('status'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: @json(session('status')),
                confirmButtonColor: '#2563EB'
            });
        @endif
        
        @if ($errors->any())
            const errorMessages = @json($errors->all());
            Swal.fire({
                icon: 'error',
                title: 'Perhatian',
                html: '<ul class="text-left text-sm mb-0 list-disc pl-4">' + errorMessages.map(msg => `<li>${msg}</li>`).join('') + '</ul>',
                confirmButtonColor: '#EF4444'
            });
        @endif

        // Global Delete/Confirm Interceptor
        document.addEventListener('submit', function(e) {
            const form = e.target;
            const confirmMsg = form.dataset.confirm;
            const confirmBtnText = form.dataset.confirmText || 'Ya, lanjutkan';

            if (confirmMsg) {
                e.preventDefault();
                Swal.fire({
                    title: 'Konfirmasi',
                    text: confirmMsg,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#10B981',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: confirmBtnText,
                    cancelButtonText: 'Batal',
                    reverseButtons: true,
                     customClass: {
                        confirmButton: 'bg-[var(--color-glass-primary)] text-white px-4 py-2 rounded-lg font-bold shadow-md hover:opacity-90',
                        cancelButton: 'bg-gray-100 text-gray-600 px-4 py-2 rounded-lg font-bold hover:bg-gray-200'
                    },
                    buttonsStyling: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.removeAttribute('data-confirm');
                        form.submit();
                    }
                });
            }
        });
    </script>
    @stack('scripts')
</body>

</html>
