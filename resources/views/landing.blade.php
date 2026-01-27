<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SItuba | Beranda</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Work+Sans:wght@300;400;500;600;700&display=swap"
        rel="stylesheet" media="print" onload="this.media='all'">

    <link rel="preload" href="{{ asset('css/landing.css') }}" as="style">
    <link rel="stylesheet" href="{{ asset('css/landing.css') }}">

    <link rel="preload" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}" as="style">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('favicon.ico') }}">
</head>

<body>
    <div class="scroll-progress" aria-hidden="true">
        <span></span>
    </div>

    <div class="page">
        <header class="site-header">
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

            <nav class="nav-links">
                <a href="#alur">Alur</a>
                <a href="#dampak">Dampak</a>
                <a href="#peran">Peran</a>
                <a href="{{ route('blog.index') }}" class="nav-ghost">
                    <i class="fa-solid fa-newspaper"></i>
                    <span>Berita &amp; Edukasi</span>
                </a>
            </nav>

            <div class="header-actions">
                <a href="{{ route('login') }}" class="btn btn-primary">
                    <i class="fa-solid fa-arrow-right-to-bracket"></i>
                    <span>Masuk Dashboard</span>
                </a>
                <button class="burger" id="burgerToggle" aria-label="Buka menu">
                    <i class="fa-solid fa-bars"></i>
                    <span>Menu</span>
                </button>
            </div>
        </header>

        <div class="mobile-menu" id="mobileMenu">
            <a href="#alur"><i class="fa-solid fa-route"></i> Alur SITUBA</a>
            <a href="#dampak"><i class="fa-solid fa-chart-line"></i> Dampak</a>
            <a href="#peran"><i class="fa-solid fa-people-group"></i> Peran</a>
            <a href="{{ route('blog.index') }}"><i class="fa-solid fa-newspaper"></i> Berita &amp; Edukasi</a>
            <a href="{{ route('login') }}"><i class="fa-solid fa-arrow-right-to-bracket"></i> Masuk Dashboard</a>
        </div>

        <main class="hero" id="top">
            <section class="hero-copy reveal">
                <div class="eyebrow-badge">
                    <i class="fa-solid fa-bolt"></i>
                    <span>Transformasi layanan TBC Kota Surakarta</span>
                </div>

                <h1 class="hero-title">
                    Jejaring lintas peran untuk <span class="highlight">deteksi dan pendampingan TBC</span> yang lebih
                    cepat.
                </h1>

                <p class="hero-subtitle">
                    SITUBA menyatukan data dari kader, kelurahan, puskesmas, hingga Dinas Kesehatan dalam satu pusat
                    kendali. Skrining cepat, tindak lanjut terukur, dan monitoring real-time membuat intervensi lebih
                    tepat sasaran.
                </p>

                <div class="hero-actions">
                    <a href="{{ route('login') }}" class="btn btn-cta">
                        <i class="fa-solid fa-stethoscope"></i>
                        <span>Mulai Pantau Kasus</span>
                    </a>
                    <a href="{{ route('blog.index') }}" class="btn btn-ghost">
                        <i class="fa-solid fa-book-open"></i>
                        <span>Lihat Edukasi</span>
                    </a>
                </div>

                <div class="trust-row">
                    <div class="trust-item">
                        <i class="fa-solid fa-shield-heart"></i>
                        <span>Keamanan data &amp; audit aktivitas</span>
                    </div>
                    <div class="trust-item">
                        <i class="fa-solid fa-satellite-dish"></i>
                        <span>Monitoring kota real-time</span>
                    </div>
                    <div class="trust-item">
                        <i class="fa-solid fa-mobile-screen-button"></i>
                        <span>Skrining cepat dari ponsel</span>
                    </div>
                </div>
            </section>

            <section class="hero-visual reveal">
                <div class="hero-card magic-frame floaty">
                    <div class="hero-card-header">
                        <div class="chip">
                            <i class="fa-solid fa-wave-square"></i>
                            <span>Command Center</span>
                        </div>
                        <div class="status-pill">
                            <span class="dot"></span>
                            Live sekarang
                        </div>
                    </div>

                    <div class="hero-card-grid">
                        <div class="metric-card hover-card">
                            <p>Skrining masuk</p>
                            <strong>+{{ number_format($screeningsLast30DaysCount ?? 0) }}</strong>
                            <span>30 hari terakhir</span>
                        </div>
                        <div class="metric-card hover-card">
                            <p>Tindak lanjut</p>
                            <strong>{{ $followUpRate ?? 0 }}%</strong>
                            <span>30 hari terakhir</span>
                        </div>
                        <div class="metric-card hover-card">
                            <p>Alert kritis</p>
                            <strong>{{ number_format($criticalAlertsCount ?? 0) }}</strong>
                            <span>30 hari terakhir</span>
                        </div>
                    </div>

                    <div class="insight-panel">
                        <div>
                            <h3><i class="fa-solid fa-location-crosshairs"></i> Peta risiko kota</h3>
                            <p>Zona prioritas teridentifikasi otomatis berdasarkan hasil skrining &amp; kepatuhan terapi.</p>
                        </div>
                        <div class="insight-pill">
                            <span>Prioritas tinggi</span>
                            <strong>{{ max(($priorityKelurahan ?? collect())->count(), 0) }} wilayah</strong>
                            @if (!empty($priorityKelurahan) && $priorityKelurahan->count())
                                <em>{{ $priorityKelurahan->implode(', ') }}</em>
                            @else
                                <em>Belum ada data</em>
                            @endif
                        </div>
                    </div>

                    <div class="hero-card-footer">
                        <div class="timeline-pill hover-card">
                            <i class="fa-solid fa-user-check"></i>
                            <div>
                                <strong>Skrining kader</strong>
                                <span>Input cepat + validasi</span>
                            </div>
                        </div>
                        <div class="timeline-pill hover-card">
                            <i class="fa-solid fa-heart-pulse"></i>
                            <div>
                                <strong>Follow-up puskesmas</strong>
                                <span>Jadwal &amp; terapi</span>
                            </div>
                        </div>
                        <div class="timeline-pill hover-card">
                            <i class="fa-solid fa-city"></i>
                            <div>
                                <strong>Monitoring pemda</strong>
                                <span>Data &amp; kebijakan</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <section class="impact" id="dampak">
            <div class="section-header reveal">
                <p class="section-kicker">Dampak nyata</p>
                <h2>Data terhubung, keputusan lebih cepat.</h2>
                <p>
                    SITUBA memastikan setiap data dari lapangan langsung mengalir ke dashboard kota sehingga respon
                    skrining, pengobatan, dan pendampingan berjalan serempak.
                </p>
            </div>

            <div class="impact-grid reveal">
                <div class="stat-card hover-card" style="--reveal-delay: 80ms;">
                    <span>Puskesmas terhubung</span>
                    <strong class="stat-value" data-count="{{ $puskesmasCount ?? 0 }}">{{ number_format($puskesmasCount ?? 0) }}</strong>
                    <p>Pemantauan terpadu untuk tindak lanjut kasus.</p>
                </div>
                <div class="stat-card hover-card" style="--reveal-delay: 140ms;">
                    <span>Kelurahan terlibat</span>
                    <strong class="stat-value" data-count="{{ $kelurahanCount ?? 0 }}">{{ number_format($kelurahanCount ?? 0) }}</strong>
                    <p>Kolaborasi lintas wilayah untuk penemuan kasus.</p>
                </div>
                <div class="stat-card accent hover-card" style="--reveal-delay: 200ms;">
                    <span>Rantai pelaporan</span>
                    <strong class="stat-value" data-count="100">100%</strong>
                    <p>Alur data real-time dari kader hingga pemda.</p>
                </div>
            </div>

            <div class="impact-list reveal">
                <div class="reveal reveal-left" style="--reveal-delay: 80ms;">
                    <h3><i class="fa-solid fa-bell"></i> Alert tepat waktu</h3>
                    <p>Notifikasi otomatis saat skrining positif, jadwal kontrol terlewat, atau terapi terhenti.</p>
                </div>
                <div class="reveal reveal-left" style="--reveal-delay: 140ms;">
                    <h3><i class="fa-solid fa-clipboard-check"></i> Kepatuhan terukur</h3>
                    <p>Ringkasan kepatuhan terapi dengan indikator tingkat wilayah dan fasilitas.</p>
                </div>
                <div class="reveal reveal-left" style="--reveal-delay: 200ms;">
                    <h3><i class="fa-solid fa-chart-pie"></i> Insight kebijakan</h3>
                    <p>Data agregat siap diolah untuk perencanaan program dan alokasi sumber daya.</p>
                </div>
            </div>

            <div class="logo-cloud reveal reveal-zoom">
                <span>Mitra SITUBA</span>
                <div class="logo-row">
                    <div class="logo-pill reveal reveal-right" style="--reveal-delay: 80ms;">Pemda Surakarta</div>
                    <div class="logo-pill reveal reveal-right" style="--reveal-delay: 120ms;">Puskesmas</div>
                    <div class="logo-pill reveal reveal-right" style="--reveal-delay: 160ms;">Kelurahan</div>
                    <div class="logo-pill reveal reveal-right" style="--reveal-delay: 200ms;">Kader</div>
                    <div class="logo-pill reveal reveal-right" style="--reveal-delay: 240ms;">Dinas Kesehatan</div>
                </div>
            </div>
        </section>

        <section class="story" id="alur">
            <div class="section-header reveal">
                <p class="section-kicker">Alur SITUBA</p>
                <h2>Perjalanan kasus TBC yang terstruktur.</h2>
                <p>Setiap langkah dipantau dan terekam agar tidak ada pasien yang tertinggal.</p>
            </div>

            <div class="story-grid">
                <div class="story-card reveal hover-card" style="--reveal-delay: 80ms;">
                    <div class="story-number">01</div>
                    <h3>Deteksi lapangan oleh kader</h3>
                    <p>Kader memetakan gejala, riwayat kontak, dan langsung mengirim skrining cepat dari ponsel.</p>
                </div>
                <div class="story-card reveal hover-card" style="--reveal-delay: 140ms;">
                    <div class="story-number">02</div>
                    <h3>Tindak lanjut puskesmas</h3>
                    <p>Petugas puskesmas memvalidasi hasil skrining, menjadwalkan pemeriksaan, dan memulai terapi.</p>
                </div>
                <div class="story-card reveal hover-card" style="--reveal-delay: 200ms;">
                    <div class="story-number">03</div>
                    <h3>Monitoring pemda</h3>
                    <p>Dinas Kesehatan memantau progres kota, validasi fasilitas, dan memastikan kepatuhan terapi.</p>
                </div>
            </div>
        </section>

        <section class="testimonials">
            <div class="section-header reveal">
                <p class="section-kicker">Social proof</p>
                <h2>Dipakai bersama oleh lintas peran.</h2>
                <p>Kolaborasi yang sama memastikan penanganan kasus lebih cepat dan data lebih akurat.</p>
            </div>

            <div class="testimonial-grid">
                <div class="testimonial-card reveal hover-card" style="--reveal-delay: 80ms;">
                    <div class="testimonial-stars">
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                    </div>
                    <p>"Dashboard SITUBA memudahkan kami memantau kepatuhan terapi secara harian dan mengambil langkah
                        cepat."</p>
                    <strong>Puskesmas Penanggung Jawab</strong>
                    <span>Surakarta</span>
                </div>
                <div class="testimonial-card reveal hover-card" style="--reveal-delay: 140ms;">
                    <div class="testimonial-stars">
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                    </div>
                    <p>"Alur skrining di ponsel sangat jelas. Notifikasi membantu kami fokus pada keluarga berisiko."</p>
                    <strong>Kader Wilayah</strong>
                    <span>Kelurahan di Surakarta</span>
                </div>
                <div class="testimonial-card reveal hover-card" style="--reveal-delay: 200ms;">
                    <div class="testimonial-stars">
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                    </div>
                    <p>"Data agregat SITUBA mempercepat penyusunan kebijakan dan memudahkan validasi fasilitas."</p>
                    <strong>Tim Pemda</strong>
                    <span>Bidang Pencegahan TBC</span>
                </div>
            </div>
        </section>

        <section class="roles-section" id="peran">
            <div class="section-header reveal">
                <p class="section-kicker">Peran terhubung</p>
                <h2>Semua aktor terintegrasi di satu layar.</h2>
                <p>Hak akses disesuaikan agar setiap peran fokus pada tugas inti masing-masing.</p>
            </div>

            <div class="roles-grid">
                <div class="role-card reveal hover-card" style="--reveal-delay: 60ms;">
                    <div class="role-icon"><i class="fa-solid fa-city"></i></div>
                    <h3>Pemerintah Daerah</h3>
                    <p>Melihat peta kasus kota, memantau capaian, dan menetapkan kebijakan berbasis data.</p>
                </div>
                <div class="role-card reveal hover-card" style="--reveal-delay: 120ms;">
                    <div class="role-icon"><i class="fa-solid fa-hospital"></i></div>
                    <h3>Puskesmas</h3>
                    <p>Mengelola kasus, jadwal kontrol, dan hasil pemeriksaan laboratorium secara terstruktur.</p>
                </div>
                <div class="role-card reveal hover-card" style="--reveal-delay: 180ms;">
                    <div class="role-icon"><i class="fa-solid fa-people-roof"></i></div>
                    <h3>Kelurahan &amp; Kader</h3>
                    <p>Skrining aktif, pemetaan kontak erat, dan pendampingan minum obat di lapangan.</p>
                </div>
                <div class="role-card reveal hover-card" style="--reveal-delay: 240ms;">
                    <div class="role-icon"><i class="fa-solid fa-user"></i></div>
                    <h3>Pasien &amp; Keluarga</h3>
                    <p>Menerima pengingat obat, jadwal kontrol, dan edukasi TBC yang terkurasi.</p>
                </div>
            </div>
        </section>

        <section class="cta-section" id="cta">
            <div class="cta-card magic-frame reveal">
                <div>
                    <h2>Siap memperkuat respon TBC Surakarta?</h2>
                    <p>Masuk ke dashboard SITUBA dan mulai pantau kasus secara menyeluruh hari ini.</p>
                </div>
                <a href="{{ route('login') }}" class="btn btn-cta">
                    <i class="fa-solid fa-arrow-right-to-bracket"></i>
                    <span>Masuk Dashboard</span>
                </a>
            </div>
        </section>

        <footer>
            <div class="footer-meta">
                <span>© <span id="year"></span> SITUBA.</span>
                <span class="dot"></span>
                <span>Surakarta, Jawa Tengah</span>
            </div>
            <a href="#top">Kembali ke atas</a>
        </footer>
    </div>

    <script>
        document.getElementById('year').textContent = new Date().getFullYear();

        const burger = document.getElementById('burgerToggle');
        const mobileMenu = document.getElementById('mobileMenu');
        const headerEl = document.querySelector('header');
        const progressBar = document.querySelector('.scroll-progress span');
        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        const syncMenuTop = () => {
            if (headerEl) {
                const rect = headerEl.getBoundingClientRect();
                const offset = rect.top + rect.height + 8;
                document.documentElement.style.setProperty('--mobile-menu-top', `${offset}px`);
            }
        };

        const updateProgress = () => {
            if (!progressBar) return;
            const scrollTop = window.scrollY;
            const docHeight = document.documentElement.scrollHeight - window.innerHeight;
            const progress = docHeight > 0 ? (scrollTop / docHeight) * 100 : 0;
            progressBar.style.width = `${Math.min(100, Math.max(0, progress))}%`;
        };

        syncMenuTop();
        updateProgress();
        window.addEventListener('resize', syncMenuTop);
        window.addEventListener('scroll', syncMenuTop, { passive: true });
        window.addEventListener('scroll', updateProgress, { passive: true });


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

        if (!prefersReducedMotion) {
            const counters = document.querySelectorAll('.stat-value[data-count]');
            counters.forEach((counter) => {
                const target = Number(counter.dataset.count || 0);
                if (!Number.isFinite(target) || target <= 0) return;
                let current = 0;
                const duration = 1200;
                const start = performance.now();

                const tick = (now) => {
                    const progress = Math.min((now - start) / duration, 1);
                    current = Math.floor(target * progress);
                    counter.textContent = new Intl.NumberFormat('id-ID').format(current);
                    if (progress < 1) {
                        requestAnimationFrame(tick);
                    } else {
                        counter.textContent = new Intl.NumberFormat('id-ID').format(target);
                    }
                };

                requestAnimationFrame(tick);
            });

            const revealItems = document.querySelectorAll('.reveal');
            const observer = new IntersectionObserver(
                (entries) => {
                    entries.forEach((entry) => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('is-visible');
                            observer.unobserve(entry.target);
                        }
                    });
                },
                { threshold: 0.18, rootMargin: '0px 0px -80px 0px' }
            );

            revealItems.forEach((item) => observer.observe(item));
        } else {
            document.querySelectorAll('.reveal').forEach((item) => item.classList.add('is-visible'));
        }
    </script>
</body>

</html>
