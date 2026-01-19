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
<aside id="sidenav-main" class="soft-sidebar">
    <button class="soft-sidebar__close d-lg-none" id="iconSidenav" type="button" aria-label="Tutup navigasi">
        <i class="ri-close-line"></i>
    </button>

    <a class="soft-sidebar__brand" href="{{ route('dashboard') }}">
        <div class="soft-sidebar__brand-logo">
            <i class="ri-shield-check-line"></i>
        </div>
        <div>
            <p class="soft-sidebar__brand-sub">SITUBA Mode Aktif</p>
            <h4 class="soft-sidebar__brand-title mb-0">{{ $brandName }}</h4>
            <p class="soft-sidebar__brand-desc mb-0">Dashboard terpadu pemantauan eliminasi TBC.</p>
        </div>
    </a>

    <nav class="soft-sidebar__nav" aria-label="Navigasi utama">
        <p class="soft-sidebar__title">Navigasi utama</p>
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
            <a class="soft-sidebar__link {{ $isActive ? 'is-active' : '' }}" href="{{ $item['url'] }}"
                data-bs-toggle="tooltip" data-bs-placement="right" title="{{ $description }}">
                <span class="soft-sidebar__icon">
                    @include('layouts.partials.soft-icon', ['icon' => $item['icon'] ?? 'default', 'active' => $isActive])
                </span>
                <span class="soft-sidebar__texts">
                    <span class="soft-sidebar__label">{{ $item['label'] }}</span>
                </span>
                <span class="soft-sidebar__pill {{ $isActive ? 'is-active' : '' }}"></span>
            </a>
        @endforeach
    </nav>

    <div class="soft-sidebar__cta" id="soft-sidebar-cta">
        <div class="d-flex align-items-start justify-content-between">
            <div class="me-2">
                <p class="soft-sidebar__cta-text mb-2">Perbarui informasi agar koordinasi pemantauan akurat.</p>
                @if (!empty($profileNav))
                    <a class="btn btn-sm btn-primary w-100" href="{{ $profileNav['url'] }}">
                        <i class="ri-id-card-line me-1"></i>{{ $profileNav['label'] }}
                    </a>
                @endif
            </div>
            <button type="button" class="soft-sidebar__cta-close" id="soft-sidebar-cta-close" aria-label="Tutup">
                <i class="ri-close-line"></i>
            </button>
        </div>
    </div>

</aside>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toggler = document.getElementById('iconNavbarSidenav');
        const sidebar = document.getElementById('sidenav-main');
        const closeBtn = document.getElementById('iconSidenav');
        const html = document.documentElement;
        const body = document.body;
        let scrollPosition = 0;
        const preventScroll = (event) => event.preventDefault();
        const passiveOptions = { passive: false };

        const toggleSidebar = () => {
            const willOpen = !sidebar.classList.contains('open');
            sidebar.classList.toggle('open', willOpen);
            closeBtn?.classList.toggle('d-none', !willOpen);
            html.classList.toggle('sidebar-open', willOpen);
            body.classList.toggle('sidebar-open', willOpen);

            if (willOpen) {
                scrollPosition = window.pageYOffset || html.scrollTop;
                body.style.top = `-${scrollPosition}px`;
                sidebar.addEventListener('wheel', preventScroll, passiveOptions);
                sidebar.addEventListener('touchmove', preventScroll, passiveOptions);
            } else {
                body.style.removeProperty('top');
                window.scrollTo(0, scrollPosition);
                sidebar.removeEventListener('wheel', preventScroll, passiveOptions);
                sidebar.removeEventListener('touchmove', preventScroll, passiveOptions);
            }
        };

        // Expose a close helper for global scripts (e.g., logout confirmations)
        window.softSidebarClose = () => {
            if (sidebar?.classList.contains('open')) {
                toggleSidebar();
            }
        };

        const tooltipNodes = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipNodes.forEach(node => {
            if (window.bootstrap?.Tooltip) {
                new bootstrap.Tooltip(node);
            }
        });

        toggler?.addEventListener('click', toggleSidebar);
        closeBtn?.addEventListener('click', toggleSidebar);

        const cta = document.getElementById('soft-sidebar-cta');
        const ctaClose = document.getElementById('soft-sidebar-cta-close');
        const ctaKey = 'softSidebarCtaHidden';
        if (localStorage.getItem(ctaKey) === '1') {
            cta?.classList.add('d-none');
        }
        ctaClose?.addEventListener('click', () => {
            cta?.classList.add('d-none');
            localStorage.setItem(ctaKey, '1');
        });
    });
</script>
