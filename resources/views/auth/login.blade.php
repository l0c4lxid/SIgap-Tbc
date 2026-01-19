<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description"
        content="SITUBA - Sistem Informasi Tuberkulosis terintegrasi. Masuk untuk mengelola pemantauan TBC: kader, puskesmas, kelurahan, hingga pemda.">
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('favicon.ico') }}">
    <title>{{ config('app.name', 'SITUBA') }} &mdash; Masuk</title>
    <link href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,800" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
        <link rel="stylesheet" href="{{ asset('css/login.css') }}">

</head>

<body>
    <div class="nav">
        <a href="{{ url('/') }}" class="brand">
            <span>{{ config('app.name', 'SITUBA') }}</span>
            <strong>Masuk</strong>
        </a>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('blog.index') }}" class="ghost-btn ghost-btn--blog">
                <i class="fa fa-newspaper"></i> Blog
            </a>
        </div>
    </div>

    <div class="shell">
        <div class="card">
            <h1>Silahkan Masuk</h1>
            <p>Masuk dengan nomor HP dan password untuk mengelola layanan SITUBA.</p>

            <form method="POST" action="{{ route('login') }}" autocomplete="off">
                @csrf
                <div class="field">
                    <label for="phone">Nomor HP</label>
                    <i class="fa fa-phone field-icon"></i>
                    <input type="text" id="phone" name="phone" class="input" placeholder="08xxxxxxxxxx"
                        value="{{ old('phone') }}" required autofocus>
                </div>
                <div class="field">
                    <label for="password">Password</label>
                    <i class="fa fa-lock field-icon"></i>
                    <input type="password" id="password" name="password" class="input" placeholder="••••••••" required
                        autocomplete="current-password">
                    <button type="button" class="toggle-pass" aria-label="Tampilkan password">
                        <i class="fa fa-eye"></i>
                    </button>
                </div>
                <label class="remember">
                    <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                    Ingat saya
                </label>
                <button type="submit" class="btn-primary">Masuk</button>
            </form>

            <p class="muted" style="margin-top: 14px;">
                Lupa password? Hubungi pihak terkait untuk reset akun.<br>
                Belum punya akun? <a href="{{ route('register') }}" class="link">Daftar sekarang</a>
            </p>
        </div>

        <div class="hero">
            <div class="scene">
                <div class="lungs-3d">
                    <div class="halo"></div>
                    <div class="halo secondary"></div>
                    <div class="spark"></div>
                    <div class="spark"></div>
                    <div class="spark"></div>
                    <div class="spark"></div>
                    <div class="grid"></div>
                    <div class="lungs" id="lungs">
                        <div class="trachea">
                            <div class="carina"></div>
                        </div>

                        <div class="lung left">
                            <div class="bronchus"></div>
                            <div class="branch"></div>
                            <div class="branch"></div>
                            <div class="branch"></div>
                            <div class="alveoli"></div>
                        </div>

                        <div class="lung right">
                            <div class="bronchus"></div>
                            <div class="branch"></div>
                            <div class="branch"></div>
                            <div class="branch"></div>
                            <div class="alveoli"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="hero-copy">
                Pemantauan kesehatan TBC dengan aplikasi SITUBA.
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/js/core/popper.min.js') }}"></script>
    <script src="{{ asset('assets/js/core/bootstrap.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const togglePass = document.querySelector('.toggle-pass');
            const passInput = document.querySelector('#password');
            if (togglePass && passInput) {
                togglePass.addEventListener('click', () => {
                    const isText = passInput.type === 'text';
                    passInput.type = isText ? 'password' : 'text';
                    togglePass.innerHTML = isText ? '<i class="fa fa-eye"></i>' : '<i class="fa fa-eye-slash"></i>';
                });
            }

            @if (session('status'))
                Swal.fire({
                    icon: 'success',
                    title: 'Informasi',
                    text: @json(session('status')),
                });
            @endif

                @if ($errors->any())
                    const errors = @json($errors->all());
                    const lastPhone = @json(old('phone'));
                    if (errors.length) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Login gagal',
                            html: `
                                                        <div class="text-start">
                                                            <p class="mb-2">Login gagal, coba masukkan username atau password yang benar.</p>
                                                            ${errors.length ? `<p class="mb-2 text-sm text-danger">${errors.join('<br>')}</p>` : ''}
                                                            ${lastPhone ? `<p class="mb-0"><strong>Nomor terakhir:</strong> ${lastPhone}</p>` : ''}
                                                        </div>
                                                    `,
                            confirmButtonText: 'Coba lagi',
                            showCloseButton: true,
                            confirmButtonColor: '#0ea5e9',
                        });
                    }
                @endif
        });
    </script>
</body>

</html>
