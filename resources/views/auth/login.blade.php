<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('assets/img/apple-icon.png') }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/img/favicon.png') }}">
    <title>{{ config('app.name', 'SIGAP TBC') }} &mdash; Masuk</title>
    <link href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,800" rel="stylesheet" />
    <link href="{{ asset('assets/css/nucleo-icons.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/nucleo-svg.css') }}" rel="stylesheet" />
    <link id="pagestyle" href="{{ asset('assets/css/soft-ui-dashboard.css') }}" rel="stylesheet" />
</head>

<body class="">
    <div class="container position-sticky z-index-sticky top-0">
        <div class="row">
            <div class="col-12">
                <nav
                    class="navbar navbar-expand-lg blur blur-rounded top-0 z-index-3 shadow position-absolute my-3 py-2 start-0 end-0 mx-4">
                    <div class="container-fluid pe-0">
                        <a class="navbar-brand font-weight-bolder ms-lg-0 ms-3" href="{{ url('/') }}">
                            {{ config('app.name', 'SIGAP TBC') }}
                        </a>
                    </div>
                </nav>
            </div>
        </div>
    </div>
    <main class="main-content mt-0">
        <section>
            <div class="page-header min-vh-75">
                <div class="container">
                    <div class="row">
                        <div class="col-xl-4 col-lg-5 col-md-6 d-flex flex-column mx-auto">
                            <div class="card card-plain mt-8">
                                <div class="card-header pb-0 text-left bg-transparent">
                                    <h3 class="font-weight-bolder text-info text-gradient">Selamat datang!</h3>
                                    <p class="mb-0">Masukkan nomor HP dan password untuk masuk ke SITUBA.</p>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="{{ route('login') }}" role="form">
                                        @csrf
                                        <label for="phone">Nomor HP</label>
                                        <div class="mb-3">
                                            <input type="text" id="phone" name="phone" class="form-control"
                                                placeholder="08xxxxxxxxxx" value="{{ old('phone') }}" required
                                                autofocus>
                                        </div>
                                        <label for="password">Password</label>
                                        <div class="mb-3">
                                            <input type="password" id="password" name="password" class="form-control"
                                                placeholder="••••••••" required autocomplete="current-password">
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="rememberMe"
                                                name="remember" {{ old('remember') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="rememberMe">Ingat saya</label>
                                        </div>
                                        <div class="text-center">
                                            <button type="submit"
                                                class="btn bg-gradient-info w-100 mt-4 mb-0">Masuk</button>
                                        </div>
                                    </form>
                                </div>
                                <div class="card-footer text-center pt-0 px-lg-2 px-1">
                                    <p class="mb-2 text-sm mx-auto">
                                        Lupa password? Hubungi admin SIGAP TBC untuk reset akun.
                                    </p>
                                    <p class="mb-4 text-sm mx-auto">
                                        Belum punya akun?
                                        <a href="{{ route('register') }}"
                                            class="text-info text-gradient font-weight-bold">Daftar sekarang</a>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="oblique position-absolute top-0 h-100 d-md-block d-none me-n8">
                                <div class="oblique-image bg-cover position-absolute fixed-top ms-auto h-100 z-index-0 ms-n6"
                                    style="background-image: url('{{ asset('assets/img/curved-images/curved6.jpg') }}')">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
    <footer class="footer py-5">
        <div class="container">
            <div class="row">
                <div class="col-8 mx-auto text-center mt-1">
                    <p class="mb-0 text-secondary">
                        &copy; {{ now()->year }} {{ config('app.name', 'SIGAP TBC') }}.
                    </p>
                </div>
            </div>
        </div>
    </footer>
    <script src="{{ asset('assets/js/core/popper.min.js') }}"></script>
    <script src="{{ asset('assets/js/core/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/perfect-scrollbar.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/smooth-scrollbar.min.js') }}"></script>
    <script src="{{ asset('assets/js/soft-ui-dashboard.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            @if (session('status'))
                Swal.fire({
                    icon: 'success',
                    title: 'Informasi',
                    text: @json(session('status')),
                });
            @endif

                @if ($errors->any())
                    const errors = @json($errors->all());
                    if (errors.length) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Login gagal',
                            html: '<ul class="text-start mb-0">' + errors.map(msg => `<li>${msg}</li>`).join('') + '</ul>',
                        });
                    }
                @endif
        });
    </script>
</body>

</html>