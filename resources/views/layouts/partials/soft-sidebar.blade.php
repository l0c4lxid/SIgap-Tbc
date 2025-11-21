@php
    $brandName = config('app.name', 'SIGAP TBC');
    $navDescriptions = [
        'dashboard' => 'Ringkasan progres dan insight cepat',
        'screening' => 'Kelola skrining dan suspek',
        'anggota' => 'Pantau keluarga dan kontak erat',
        'users' => 'Pembinaan kader dan petugas',
        'folder' => 'Data pasien dan fasilitas',
        'berobat' => 'Pendampingan pengobatan',
        'verify' => 'Validasi dan kontrol akses',
        'profile' => 'Perbarui data pribadi',
        'materi' => 'Materi edukasi kader & pasien',
        'news' => 'Kirim dan pantau publikasi blog',
    ];
@endphp
<aside id="sidenav-main"
    class="soft-sidebar sidenav navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-3">
    <button class="btn btn-icon btn-outline-primary position-absolute top-0 end-0 mt-3 me-3 d-lg-none" id="iconSidenav"
        type="button" aria-label="Tutup navigasi">
        <i class="ri-close-line"></i>
    </button>
    <a class="text-decoration-none text-white" href="{{ route('dashboard') }}">
        <div class="soft-sidebar__brand">
            <div class="soft-sidebar__badge mb-3">
                <i class="ri-shield-check-line"></i>
                SIGAP Mode Aktif
            </div>
            <h4 class="mb-1">{{ $brandName }}</h4>
            <p class="text-sm text-muted mb-0">Dashboard terpadu untuk pemantauan eliminasi TBC kota/kabupaten.</p>
        </div>
    </a>
    <div class="soft-sidebar__nav mt-2">
        <p class="soft-sidebar__title">Navigasi utama</p>
        @foreach ($navItems as $item)
            @php
                $isActive = $item['url'] !== '#' && url()->current() === $item['url'];
                $description = $navDescriptions[$item['icon'] ?? ''] ?? 'Lihat detail dan tindak lanjut';
            @endphp
            <a class="soft-sidebar__link {{ $isActive ? 'is-active' : '' }}" href="{{ $item['url'] }}">
                <span class="soft-sidebar__icon">
                    @include('layouts.partials.soft-icon', ['icon' => $item['icon'] ?? 'default', 'active' => $isActive])
                </span>
                <span class="soft-sidebar__texts">
                    <span class="soft-sidebar__label">{{ $item['label'] }}</span>
                    <span class="soft-sidebar__description">{{ $description }}</span>
                </span>
            </a>
        @endforeach
    </div>

    <div class="soft-sidebar__cta">
        <p class="text-sm text-muted mb-2">Perbarui informasi agar koordinasi pemantauan tetap akurat.</p>
        @if (!empty($profileNav))
            <a class="btn btn-sm btn-primary w-100 mb-2" href="{{ $profileNav['url'] }}">
                <i class="ri-id-card-line me-1"></i>{{ $profileNav['label'] }}
            </a>
        @endif
        <form method="POST" action="{{ route('logout') }}" data-confirm="Keluar dari aplikasi?" data-confirm-text="Ya, keluar">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-primary w-100">
                <i class="ri-logout-circle-r-line me-1"></i> Keluar
            </button>
        </form>
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

        toggler?.addEventListener('click', toggleSidebar);
        closeBtn?.addEventListener('click', toggleSidebar);
    });
</script>
