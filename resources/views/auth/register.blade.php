<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('assets/img/apple-icon.png') }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/img/favicon.png') }}">
    <title>{{ config('app.name', 'SIGAP TBC') }} &mdash; Registrasi</title>
    <link href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,800" rel="stylesheet" />
    <link href="{{ asset('assets/css/nucleo-icons.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/nucleo-svg.css') }}" rel="stylesheet" />
    <link id="pagestyle" href="{{ asset('assets/css/soft-ui-dashboard.css') }}" rel="stylesheet" />
</head>

<body class="">
    <nav class="navbar navbar-expand-lg position-absolute top-0 z-index-3 w-100 shadow-none my-3 navbar-transparent mt-4">
        <div class="container">
            <a class="navbar-brand font-weight-bolder ms-lg-0 ms-3 text-white" href="{{ url('/') }}">
                {{ config('app.name', 'SIGAP TBC') }}
            </a>
        </div>
    </nav>
    <main class="main-content mt-0">
        <section class="min-vh-100 mb-8">
            <div class="page-header align-items-start min-vh-50 pt-5 pb-11 m-3 border-radius-lg" style="background-image: url('{{ asset('assets/img/curved-images/curved14.jpg') }}');">
                <span class="mask bg-gradient-dark opacity-6"></span>
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-lg-6 text-center mx-auto">
                            <h1 class="text-white mb-2 mt-5">Registrasi Pengguna</h1>
                            <p class="text-lead text-white">Pilih jenis pengguna terlebih dahulu lalu isi form sesuai kebutuhan peran.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="container">
                <div class="row mt-lg-n10 mt-md-n11 mt-n10 justify-content-center">
                    <div class="col-xl-9 col-lg-10 col-md-11">
                        <div class="card z-index-0">
                            <div class="card-header text-center pt-4">
                                <h5>Form Registrasi SIGAP TBC</h5>
                                <p class="text-sm mb-0">Akun Pemda dibuat oleh admin. Silakan pilih peran lain jika ingin mendaftar.</p>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="{{ route('register') }}">
                                    @csrf
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="role" class="form-label">Daftar sebagai</label>
                                            @php($roleOptions = $roleOptions ?? \App\Enums\UserRole::options([ \App\Enums\UserRole::Pemda ]))
                                            <select id="role" name="role" class="form-select" required>
                                                <option value="">Pilih peran</option>
                                                @foreach ($roleOptions as $role)
                                                    <option value="{{ $role['value'] }}" @selected(old('role') === $role['value'])>{{ $role['label'] }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="name" id="name-label" data-default="Nama Penanggung Jawab / Pasien" class="form-label">Nama Penanggung Jawab / Pasien</label>
                                            <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required autofocus>
                                        </div>
                                    </div>

            <div class="role-section mt-4 d-none" data-role-section="kelurahan">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="kelurahan_name" class="form-label">Kelurahan</label>
                        <input type="text" class="form-control" id="kelurahan_name" name="kelurahan_name" value="{{ old('kelurahan_name') }}">
                    </div>
                    <div class="col-12">
                        <label for="kelurahan_address" class="form-label">Alamat Kelurahan</label>
                        <input type="text" class="form-control" id="kelurahan_address" name="kelurahan_address" value="{{ old('kelurahan_address') }}">
                    </div>
                </div>
            </div>

            <div class="role-section mt-4 d-none" data-role-section="puskesmas">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="puskesmas_name" class="form-label">Puskesmas</label>
                        <input type="text" class="form-control" id="puskesmas_name" name="puskesmas_name" value="{{ old('puskesmas_name') }}">
                    </div>
                    <div class="col-12">
                        <label for="puskesmas_address" class="form-label">Alamat Puskesmas</label>
                        <input type="text" class="form-control" id="puskesmas_address" name="puskesmas_address" value="{{ old('puskesmas_address') }}">
                    </div>
                </div>
            </div>

            <div class="role-section mt-4 d-none" data-role-section="kader">
                <div class="row g-3">
                    <div class="col-md-12">
                        <label for="kader_puskesmas_id" class="form-label">Puskesmas Induk</label>
                        @if (($puskesmasOptions ?? collect())->isEmpty())
                            <div class="alert alert-warning mb-0">
                                Belum ada Puskesmas aktif. Hubungi admin untuk menambah data.
                            </div>
                                                @else
                                                    <select id="kader_puskesmas_id" name="kader_puskesmas_id" class="form-select">
                                                        <option value="">Pilih puskesmas</option>
                                                        @foreach ($puskesmasOptions as $puskesmas)
                                                            <option value="{{ $puskesmas->id }}" @selected(old('kader_puskesmas_id') == $puskesmas->id)>
                                                                {{ $puskesmas->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="role-section mt-4 d-none" data-role-section="pasien">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label for="pasien_kk" class="form-label">Nomor KK</label>
                                                <input type="text" class="form-control" id="pasien_kk" name="pasien_kk" value="{{ old('pasien_kk') }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label for="pasien_kader_id" class="form-label">Kader Penanggung Jawab</label>
                                                @if (($kaderOptions ?? collect())->isEmpty())
                                                    <div class="alert alert-warning mb-0">
                                                        Belum ada Kader aktif. Hubungi admin untuk menambah data.
                                                    </div>
                                                @else
                                                    <select id="pasien_kader_id" name="pasien_kader_id" class="form-select">
                                                        <option value="">Pilih kader</option>
                                                        @foreach ($kaderOptions as $kader)
                                                            <option value="{{ $kader->id }}" @selected(old('pasien_kader_id') == $kader->id)>
                                                                {{ $kader->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                @endif
                                            </div>
                                            <div class="col-12">
                                                <label for="pasien_address" class="form-label">Alamat Pasien</label>
                                                <input type="text" class="form-control" id="pasien_address" name="pasien_address" value="{{ old('pasien_address') }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row g-3 mt-4">
                                        <div class="col-md-6">
                                            <label for="phone" class="form-label">Nomor HP (untuk login)</label>
                                            <input type="text" class="form-control" id="phone" name="phone" value="{{ old('phone') }}" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="password" class="form-label">Password</label>
                                            <input type="password" class="form-control" id="password" name="password" required autocomplete="new-password">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
                                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required autocomplete="new-password">
                                        </div>
                                    </div>

                                    <div class="form-check form-switch mt-4">
                                        <input class="form-check-input" type="checkbox" id="terms" checked disabled>
                                        <label class="form-check-label" for="terms">Dengan mendaftar saya menyetujui kebijakan privasi SIGAP TBC.</label>
                                    </div>
                                    <div class="text-center">
                                        <button type="submit" class="btn bg-gradient-dark w-100 my-4 mb-2">Daftar Sekarang</button>
                                    </div>
                                    <p class="text-xs text-muted text-center">Akun akan dinonaktifkan hingga diverifikasi oleh Pemda.</p>
                                    <p class="text-sm mt-3 mb-0 text-center">Sudah punya akun? <a href="{{ route('login') }}" class="text-dark font-weight-bolder">Masuk di sini</a></p>
                                </form>
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
            @if ($errors->any())
                const errors = @json($errors->all());
                if (errors.length) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Registrasi gagal',
                        html: '<ul class="text-start mb-0">' + errors.map(msg => `<li>${msg}</li>`).join('') + '</ul>',
                    });
                }
            @endif
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const roleSelect = document.getElementById('role');
            const sections = document.querySelectorAll('[data-role-section]');
            const nameLabel = document.getElementById('name-label');
            const defaultLabel = nameLabel?.dataset.default ?? nameLabel?.textContent;
            const labelMap = {
                kelurahan: 'Nama Penanggung Jawab Kelurahan',
                puskesmas: 'Nama Penanggung Jawab Puskesmas',
                kader: 'Nama Penanggung Jawab Kader',
                pasien: 'Nama Pasien',
            };

            function toggleRoleSections() {
                const role = roleSelect.value;
                sections.forEach(section => {
                    section.classList.toggle('d-none', section.dataset.roleSection !== role);
                });

                if (nameLabel) {
                    nameLabel.textContent = labelMap[role] ?? defaultLabel;
                }
            }

            roleSelect.addEventListener('change', toggleRoleSections);
            toggleRoleSections();
        });
    </script>
</body>

</html>
