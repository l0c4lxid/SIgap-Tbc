@php
    $brandName = config('app.name', 'SITUBA');
    $navDescriptions = [
        'dashboard' => 'Ringkasan progres dan insight cepat',
        'screening' => 'Kelola skrining dan suspek',
        'anggota' => 'Pantau keluarga dan kontak erat',
        'users' => 'Pembinaan kader dan petugas',
        'folder' => 'Data skrining dan fasilitas',
        'verify' => 'Validasi dan kontrol akses',
        'profile' => 'Perbarui data pribadi',
        'materi' => 'Materi edukasi kader',
        'news' => 'Kirim dan pantau publikasi blog',
    ];
@endphp
<aside id="sidenav-main" class="soft-sidebar d-none d-xl-flex" aria-labelledby="soft-sidebar-label">
    <div class="soft-sidebar__inner">
        <div class="soft-sidebar__header">
            <div class="soft-sidebar__brand">
                <a class="soft-sidebar__brand-link" href="{{ route('dashboard') }}" id="soft-sidebar-label">
                    <div class="soft-sidebar__brand-logo">
                        <i class="ri-shield-check-line"></i>
                    </div>
                    <div>
                        <h4 class="soft-sidebar__brand-title mb-0">{{ $brandName }}</h4>
                        <p class="soft-sidebar__brand-desc mb-0">Dashboard terpadu pemantauan eliminasi TBC.</p>
                    </div>
                </a>
                <button id="soft-sidebar-close" class="btn btn-light btn-sm soft-sidebar__close soft-sidebar__close--inside d-xl-none" type="button"
                    aria-label="Tutup navigasi">
                    <i class="ri-close-line"></i>
                </button>
            </div>
        </div>

        <div class="soft-sidebar__body">
            <div class="soft-sidebar__section">
                <p class="soft-sidebar__title">Navigasi utama</p>
                <nav class="nav flex-column soft-sidebar__nav" aria-label="Navigasi utama">
                    @foreach ($navItems as $item)
                        @php
                            $currentUrl = url()->current();
                            $base = rtrim($item['url'] ?? '#', '/');
                            $activeRoutes = $item['active_routes'] ?? [];
                            $isActive = false;
                            if (!empty($activeRoutes) && request()->route()) {
                                $isActive = request()->routeIs($activeRoutes);
                            } elseif ($base !== '#') {
                                $isActive = $currentUrl === ($item['url'] ?? '') || str_starts_with($currentUrl, $base . '/');
                            }
                            $description = $navDescriptions[$item['icon'] ?? ''] ?? 'Lihat detail dan tindak lanjut';
                        @endphp
                        <a class="nav-link soft-sidebar__link {{ $isActive ? 'active' : '' }}" href="{{ $item['url'] }}"
                            data-bs-toggle="tooltip" data-bs-placement="right" title="{{ $description }}"
                            @if ($isActive) aria-current="page" @endif>
                            <span class="soft-sidebar__icon">
                                @include('layouts.partials.soft-icon', ['icon' => $item['icon'] ?? 'default', 'active' => $isActive])
                            </span>
                            <span class="soft-sidebar__texts">
                                <span class="soft-sidebar__label">{{ $item['label'] }}</span>
                            </span>
                            <span class="soft-sidebar__pill {{ $isActive ? 'active' : '' }}"></span>
                        </a>
                    @endforeach
                </nav>
            </div>

            
        </div>
    </div>
</aside>
<div id="soft-sidebar-backdrop" class="soft-sidebar-backdrop" aria-hidden="true"></div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const sidebar = document.getElementById('sidenav-main');
        const toggleButton = document.getElementById('soft-sidebar-toggle');
        const closeButton = document.getElementById('soft-sidebar-close');
        const backdrop = document.getElementById('soft-sidebar-backdrop');
        const navLinks = sidebar?.querySelectorAll('.soft-sidebar__link') ?? [];
        let closeTimer = null;

        const isMobile = () => window.matchMedia('(max-width: 1199.98px)').matches;

        const setSidebarOpen = (open) => {
            if (!sidebar) {
                return;
            }
            const mobile = isMobile();

            if (mobile) {
                if (open) {
                    if (closeTimer) {
                        clearTimeout(closeTimer);
                        closeTimer = null;
                    }
                    sidebar.classList.remove('d-none');
                    requestAnimationFrame(() => {
                        sidebar.classList.add('is-open');
                    });
                } else {
                    sidebar.classList.remove('is-open');
                    closeTimer = window.setTimeout(() => {
                        sidebar.classList.add('d-none');
                        closeTimer = null;
                    }, 260);
                }
                sidebar.setAttribute('aria-hidden', open ? 'false' : 'true');
            } else {
                sidebar.classList.remove('d-none');
                sidebar.classList.remove('is-open');
                sidebar.removeAttribute('aria-hidden');
            }

            document.body.classList.toggle('soft-sidebar-open', open && mobile);
            toggleButton?.setAttribute('aria-expanded', open ? 'true' : 'false');
        };

        const openSidebar = () => {
            if (!isMobile()) {
                return;
            }
            setSidebarOpen(true);
        };

        const closeSidebar = () => {
            setSidebarOpen(false);
        };

        // Expose a close helper for global scripts (e.g., logout confirmations)
        window.softSidebarClose = closeSidebar;

        toggleButton?.addEventListener('click', (event) => {
            event.preventDefault();
            if (sidebar?.classList.contains('is-open')) {
                closeSidebar();
            } else {
                openSidebar();
            }
        });

        closeButton?.addEventListener('click', closeSidebar);
        backdrop?.addEventListener('click', closeSidebar);
        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                if (isMobile()) {
                    closeSidebar();
                }
            });
        });

        window.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeSidebar();
            }
        });

        window.addEventListener('resize', () => {
            setSidebarOpen(false);
        });

        setSidebarOpen(false);

        const tooltipNodes = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipNodes.forEach(node => {
            if (window.bootstrap?.Tooltip) {
                new bootstrap.Tooltip(node);
            }
        });


    });
</script>
