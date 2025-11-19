@php
    $brandName = config('app.name', 'SIGAP TBC');
@endphp
<aside class="sidenav navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-3" id="sidenav-main">
    <div class="sidenav-header">
        <i class="fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0 d-none" id="iconSidenav"></i>
        <a class="navbar-brand m-0" href="{{ route('dashboard') }}">
            <img src="{{ asset('assets/img/logo-ct-dark.png') }}" class="navbar-brand-img h-100" alt="logo">
            <span class="ms-1 font-weight-bold">{{ $brandName }}</span>
        </a>
    </div>
    <hr class="horizontal dark mt-0">
    <div class="collapse navbar-collapse w-auto" id="sidenav-collapse-main">
        <ul class="navbar-nav">
            @foreach ($navItems as $item)
                @php
                    $isActive = $item['url'] !== '#' && url()->current() === $item['url'];
                @endphp
                <li class="nav-item">
                    <a class="nav-link {{ $isActive ? 'active' : '' }}" href="{{ $item['url'] }}">
                        <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                            @include('layouts.partials.soft-icon', ['icon' => $item['icon'] ?? 'default', 'active' => $isActive])
                        </div>
                        <span class="nav-link-text ms-1">{{ $item['label'] }}</span>
                    </a>
                </li>
            @endforeach
            @if (! empty($profileNav))
                <li class="nav-item d-md-none px-3 mt-3">
                    <a class="btn btn-sm btn-outline-primary w-100" href="{{ $profileNav['url'] }}">
                        <i class="fa-solid fa-id-badge me-2"></i>{{ $profileNav['label'] }}
                    </a>
                </li>
            @endif
            <li class="nav-item d-md-none mt-3 px-3">
                <form method="POST" action="{{ route('logout') }}" data-confirm="Keluar dari aplikasi?" data-confirm-text="Ya, keluar">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-danger w-100">
                        <i class="fa-solid fa-power-off me-2"></i> Keluar
                    </button>
                </form>
            </li>
        </ul>
    </div>
</aside>

<style>
    #sidenav-main {
        height: 100vh;
        overflow: hidden;
        transition: transform 0.3s ease;
        touch-action: none;
        overscroll-behavior: contain;
    }

    #sidenav-collapse-main {
        overflow: hidden !important;
        height: 100%;
    }

    #sidenav-main .navbar-nav {
        overflow: hidden;
    }

    html.sidebar-open,
    body.sidebar-open {
        overflow: hidden;
        position: fixed;
        width: 100%;
        height: 100vh;
    }

    @media (max-width: 991.98px) {
        #sidenav-main {
            position: fixed;
            top: 0;
            left: 0;
            width: 260px;
            transform: translateX(-100%);
            background-color: #fff;
            z-index: 1100;
        }

        #sidenav-main.open {
            transform: translateX(0);
        }
    }
</style>

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
