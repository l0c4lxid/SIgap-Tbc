<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SITUBA Surakarta | Tuberculosis Assistant</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&display=swap"
        rel="stylesheet" media="print" onload="this.media='all'">

    <link rel="preload" href="{{ asset('css/landing.css') }}" as="style">
    <link rel="stylesheet" href="{{ asset('css/landing.css') }}">

    <link rel="preload" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}" as="style">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('favicon.ico') }}">

</head>

<body>
    <div class="page">
        <header>
            <div class="brand">
                <div class="brand-logo">
                    <picture>
                        <source srcset="{{ asset('situba-logo.webp') }}" type="image/webp">
                        <img src="{{ asset('assets/img/situba-logo.png') }}" alt="SITUBA Logo" width="42" height="42"
                            decoding="async" loading="lazy">
                    </picture>
                </div>
                <div class="brand-text">
                    <strong>SITUBA Surakarta</strong>
                    <span>Tuberculosis Assistant</span>
                </div>
            </div>
            <div class="header-actions">
                <a href="{{ route('blog.index') }}" class="btn btn-ghost header-link">
                    <i class="fa-solid fa-newspaper"></i>
                    <span>Berita &amp; Edukasi</span>
                </a>
                <a href="{{ route('login') }}" class="btn btn-primary header-link">
                    <i class="fa-solid fa-arrow-right-to-bracket"></i>
                    <span>Masuk Dashboard</span>
                </a>
                <button class="burger" id="burgerToggle">
                    <i class="fa-solid fa-bars"></i>
                    <span>Menu</span>
                </button>
            </div>
        </header>

        <div class="mobile-menu" id="mobileMenu">
            <a href="{{ route('blog.index') }}"><i class="fa-solid fa-newspaper"></i> Berita &amp; Edukasi</a>
            <a href="{{ route('login') }}"><i class="fa-solid fa-arrow-right-to-bracket"></i> Masuk Dashboard</a>
        </div>

        <main>
            <section>
                <div class="eyebrow-badge">
                    <i class="fa-solid fa-bolt"></i>
                    <span>Transformasi layanan TBC Kota Surakarta</span>
                </div>

                <h1 class="hero-title">
                    Jejaring <span class="highlight">Pemda, Puskesmas, Kelurahan, Kader, hingga Pasien</span> dalam satu
                    layar.
                </h1>

                <p class="hero-subtitle">
                    SITUBA mempercepat deteksi, pemantauan, dan pendampingan TBC di Surakarta. Data dari lapangan
                    mengalir
                    ke pusat secara real-time: kader, kelurahan, puskesmas hingga Dinas Kesehatan terhubung tanpa sekat.
                </p>

                <div class="hero-actions">
                    <a href="{{ route('login') }}" class="btn btn-primary btn-large">
                        <i class="fa-solid fa-stethoscope"></i>
                        <span>Mulai Pantau Kasus</span>
                    </a>

                </div>

                <div class="hero-meta">
                    <div class="hero-meta-item">
                        <i class="fa-solid fa-shield-heart"></i>
                        <span>End-to-end</span>
                    </div>
                    <div class="hero-meta-item">
                        <i class="fa-solid fa-mobile-screen-button"></i>
                        <span>Skrining dari ponsel kader</span>
                    </div>
                </div>

                <div class="hero-stats-row">
                    <div class="pill-stat">
                        <div>
                            <span>Puskesmas</span><br>
                            <strong>{{ number_format($puskesmasCount ?? 0) }}</strong>
                        </div>
                        <i class="fa-solid fa-hospital"></i>
                    </div>
                    <div class="pill-stat">
                        <div>
                            <span>Kelurahan</span><br>
                            <strong>{{ number_format($kelurahanCount ?? 0) }}</strong>
                        </div>
                        <i class="fa-solid fa-location-dot"></i>
                    </div>
                    <div class="pill-stat">
                        <div>
                            <span>Rantai pelaporan</span><br>
                            <strong>Realtime</strong>
                        </div>
                        <i class="fa-solid fa-link"></i>
                    </div>
                </div>
            </section>

            <section class="hero-card">
                <div class="hero-card-inner">
                    <div class="hero-card-header">
                        <div class="chip">
                            <i class="fa-solid fa-route"></i>
                            <span>Alur singkat SITUBA</span>
                        </div>
                        <div class="text-glow">SITUBA</div>
                    </div>
                    <h2 class="sr-only">Alur singkat SITUBA</h2>

                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-step">01</div>
                            <div class="timeline-content">
                                <h3>
                                    <i class="fa-solid fa-user-check"></i>
                                    Deteksi lapangan oleh kader
                                </h3>
                                <p>
                                    Kader memetakan gejala dan riwayat kontak lalu mengisi skrining cepat melalui
                                    ponsel.
                                    Hasil langsung terekam di sistem.
                                </p>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-step">02</div>
                            <div class="timeline-content">
                                <h3>
                                    <i class="fa-solid fa-heart-pulse"></i>
                                    Tindak lanjut puskesmas
                                </h3>
                                <p>
                                    Petugas puskesmas memantau hasil skrining, menjadwalkan pemeriksaan dan memulai
                                    pengobatan serta pendampingan rutin.
                                </p>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-step">03</div>
                            <div class="timeline-content">
                                <h3>
                                    <i class="fa-solid fa-city"></i>
                                    Monitoring
                                </h3>
                                <p>
                                    Dinas Kesehatan memonitor progres kota, memvalidasi akun fasilitas, dan memastikan
                                    kepatuhan terapi di seluruh rantai layanan.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="hero-card-footer">
                        <div class="mini-card">
                            <h4><i class="fa-solid fa-bell"></i> Notifikasi cepat</h4>
                            <p>Alert otomatis saat skrining positif, jadwal kontrol terlewat, atau obat tidak diambil.
                            </p>
                        </div>
                        <div class="mini-card">
                            <h4><i class="fa-solid fa-gauge-high"></i> Dashboard multi-peran</h4>
                            <p>Tampilan disesuaikan untuk pemda, puskesmas, kelurahan, dan kader.</p>
                        </div>
                        <div class="mini-card">
                            <h4><i class="fa-solid fa-shield-heart"></i> Data aman</h4>
                            <p>Hak akses berbasis peran, audit aktivitas, dan pengelolaan akun terpusat.</p>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <section class="roles-section">
            <div class="roles-title">Peran yang terhubung di SITUBA</div>
            <div class="roles-strip">
                <div class="roles-track">
                    <div class="role-pill">
                        <div class="role-pill-header">
                            <div class="role-pill-icon">
                                <i class="fa-solid fa-city"></i>
                            </div>
                            <strong>Pemerintah Daerah</strong>
                        </div>
                        <span>Melihat peta kasus kota, memantau capaian,<br> dan menetapkan kebijakan berbasis
                            data.</span>
                    </div>
                    <div class="role-pill">
                        <div class="role-pill-header">
                            <div class="role-pill-icon">
                                <i class="fa-solid fa-hospital"></i>
                            </div>
                            <strong>Puskesmas</strong>
                        </div>
                        <span>Mengelola kasus, jadwal kontrol,<br> dan hasil pemeriksaan laboratorium.</span>
                    </div>
                    <div class="role-pill role-pill--wide">
                        <div class="role-pill-header">
                            <div class="role-pill-icon">
                                <i class="fa-solid fa-people-roof"></i>
                            </div>
                            <strong>Kelurahan &amp; Kader</strong>
                        </div>
                        <span>Skrining aktif, pemetaan kontak erat,<br> dan pendampingan minum obat di lapangan.</span>
                    </div>
                    <div class="role-pill role-pill--wide">
                        <div class="role-pill-header">
                            <div class="role-pill-icon">
                                <i class="fa-solid fa-user"></i>
                            </div>
                            <strong>Pasien &amp; Keluarga</strong>
                        </div>
                        <span>Menerima pengingat obat, jadwal kontrol,<br> dan edukasi TBC yang terkurasi.</span>
                    </div>
                </div>
                <div class="roles-track">
                    <div class="role-pill">
                        <div class="role-pill-header">
                            <div class="role-pill-icon">
                                <i class="fa-solid fa-city"></i>
                            </div>
                            <strong>Pemerintah Daerah</strong>
                        </div>
                        <span>Melihat peta kasus kota, memantau capaian,<br> dan menetapkan kebijakan berbasis
                            data.</span>
                    </div>
                    <div class="role-pill">
                        <div class="role-pill-header">
                            <div class="role-pill-icon">
                                <i class="fa-solid fa-hospital"></i>
                            </div>
                            <strong>Puskesmas</strong>
                        </div>
                        <span>Mengelola kasus, jadwal kontrol,<br> dan hasil pemeriksaan laboratorium.</span>
                    </div>
                    <div class="role-pill role-pill--wide">
                        <div class="role-pill-header">
                            <div class="role-pill-icon">
                                <i class="fa-solid fa-people-roof"></i>
                            </div>
                            <strong>Kelurahan &amp; Kader</strong>
                        </div>
                        <span>Skrining aktif, pemetaan kontak erat, dan pendampingan minum obat di lapangan.</span>
                    </div>
                    <div class="role-pill role-pill--wide">
                        <div class="role-pill-header">
                            <div class="role-pill-icon">
                                <i class="fa-solid fa-user"></i>
                            </div>
                            <strong>Pasien &amp; Keluarga</strong>
                        </div>
                        <span>Menerima pengingat obat, jadwal kontrol,<br> dan edukasi TBC yang terkurasi.</span>
                    </div>
                </div>
            </div>
        </section>

        <footer>
            <div class="footer-meta">
                <span>© <span id="year"></span> SITUBA.</span>
                <span class="dot"></span>
            </div>

        </footer>
    </div>

    <script>
        document.getElementById('year').textContent = new Date().getFullYear();

        const burger = document.getElementById('burgerToggle');
        const mobileMenu = document.getElementById('mobileMenu');
        const headerEl = document.querySelector('header');

        const syncMenuTop = () => {
            if (headerEl) {
                const rect = headerEl.getBoundingClientRect();
                const offset = rect.top + rect.height + 8; // keep menu right under header
                document.documentElement.style.setProperty('--mobile-menu-top', `${offset}px`);
            }
        };

        syncMenuTop();
        window.addEventListener('resize', syncMenuTop);
        window.addEventListener('scroll', syncMenuTop, { passive: true });

        if (burger && mobileMenu) {
            burger.addEventListener('click', () => {
                syncMenuTop();
                mobileMenu.classList.toggle('show');
            });
            document.addEventListener('click', (e) => {
                if (!mobileMenu.contains(e.target) && !burger.contains(e.target)) {
                    mobileMenu.classList.remove('show');
                }
            });
        }
    </script>
</body>

</html>
