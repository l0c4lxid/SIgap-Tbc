<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Daftar di SITUBA - Sistem Informasi Tuberkulosis terintegrasi untuk pemantauan TBC oleh kader, puskesmas, kelurahan, dan pemda.">
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('favicon.ico') }}">
    <title>SItuba | Registrasi</title>
    <link href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,800" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
        <link rel="stylesheet" href="{{ asset('css/register.css') }}">

</head>

@php($roleOptions = $roleOptions ?? \App\Enums\UserRole::options([\App\Enums\UserRole::Pemda]))
@php($roleEmojis = [
    \App\Enums\UserRole::Kelurahan->value => '🏘️ ',
    \App\Enums\UserRole::Puskesmas->value => '🏥 ',
    \App\Enums\UserRole::Kader->value => '🧑‍⚕️ ',
])

<body>
    <div class="nav">
        <a href="{{ url('/') }}" class="brand">
            <span>{{ config('app.name', 'SITUBA') }}</span>
            <strong>Registrasi</strong>
        </a>
        <a href="{{ route('login') }}" class="pill-link"><i class="fa fa-arrow-left"></i>Punya akun?</a>
    </div>

    <div class="shell">
        <div class="card">
            <div class="role-chip"><i class="fa fa-user-plus"></i> Daftar Pengguna Baru</div>
            <h1>Bergabung ke SITUBA</h1>
            <p class="lead">Pilih peran yang sesuai dan lengkapi data. Akun akan aktif setelah diverifikasi Pemda.</p>

            <form method="POST" action="{{ route('register') }}" autocomplete="off">
                @csrf
                <div class="form-grid">
                    <div class="field-block">
                        <div class="label-row">
                            <label for="role">Daftar sebagai</label>
                            <span class="helper">Pilih peran agar relasi data benar</span>
                        </div>
                        <div class="input-wrap with-select">
                            <i class="fa fa-users input-icon"></i>
                            <select id="role" name="role" class="select" required>
                                <option value="">Pilih peran</option>
                                @foreach ($roleOptions as $role)
                                    <option value="{{ $role['value'] }}" @selected(old('role') === $role['value'])>
                                        {{ ($roleEmojis[$role['value']] ?? '🔖 ') . $role['label'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="field-block">
                        <div class="label-row">
                            <label for="name" id="name-label" data-default="Nama Penanggung Jawab">Nama</label>
                            <span class="helper">Isi sesuai identitas</span>
                        </div>
                        <div class="input-wrap">
                            <i class="fa fa-id-badge input-icon"></i>
                            <input type="text" class="input" id="name" name="name" value="{{ old('name') }}" required>
                        </div>
                    </div>
                </div>

                <div class="section role-section d-none" data-role-section="kelurahan">
                    <h4 class="role-chip" style="background: rgba(14,165,233,0.12);">Kelurahan</h4>
                    <div class="form-grid">
                        <div class="field-block">
                            <div class="label-row">
                                <label for="kelurahan_name">Nama Kelurahan</label>
                                <span class="helper">Contoh: Kel. Manahan</span>
                            </div>
                            <div class="input-wrap">
                                <i class="fa fa-building input-icon"></i>
                                <input type="text" class="input" id="kelurahan_name" name="kelurahan_name"
                                    value="{{ old('kelurahan_name') }}">
                            </div>
                        </div>
                        <div class="field-block">
                            <div class="label-row">
                                <label for="kelurahan_puskesmas_id">Puskesmas Pembina</label>
                                <span class="helper">Pilih puskesmas induk</span>
                            </div>
                            @if (($puskesmasOptions ?? collect())->isEmpty())
                                <div class="note" style="text-align:left;">Belum ada Puskesmas aktif. Hubungi admin.</div>
                            @else
                                <div class="input-wrap with-select">
                                    <i class="fa fa-hospital input-icon"></i>
                                    <select id="kelurahan_puskesmas_id" name="kelurahan_puskesmas_id" class="select">
                                        <option value="">Pilih puskesmas</option>
                                        @foreach ($puskesmasOptions as $puskesmas)
                                            <option value="{{ $puskesmas->id }}"
                                                @selected(old('kelurahan_puskesmas_id') == $puskesmas->id)>🏥 {{ optional($puskesmas->detail)->organization ?? $puskesmas->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                        </div>
                        <div class="field-block full">
                            <div class="label-row">
                                <label for="kelurahan_address">Alamat Kelurahan</label>
                                <span class="helper">Isi alamat kantor kelurahan</span>
                            </div>
                            <div class="input-wrap">
                                <i class="fa fa-location-dot input-icon"></i>
                                <input type="text" class="input" id="kelurahan_address" name="kelurahan_address"
                                    value="{{ old('kelurahan_address') }}">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="section role-section d-none" data-role-section="puskesmas">
                    <h4 class="role-chip" style="background: rgba(99,102,241,0.12);">Puskesmas</h4>
                    <div class="form-grid">
                        <div class="field-block">
                            <div class="label-row">
                                <label for="puskesmas_name">Nama Puskesmas</label>
                                <span class="helper">Contoh: Puskesmas Gajahan</span>
                            </div>
                            <div class="input-wrap">
                                <i class="fa fa-clinic-medical input-icon"></i>
                                <input type="text" class="input" id="puskesmas_name" name="puskesmas_name"
                                    value="{{ old('puskesmas_name') }}">
                            </div>
                        </div>
                        <div class="field-block full">
                            <div class="label-row">
                                <label for="puskesmas_address">Alamat Puskesmas</label>
                                <span class="helper">Alamat lengkap fasilitas</span>
                            </div>
                            <div class="input-wrap">
                                <i class="fa fa-location-dot input-icon"></i>
                                <input type="text" class="input" id="puskesmas_address" name="puskesmas_address"
                                    value="{{ old('puskesmas_address') }}">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="section role-section d-none" data-role-section="kader">
                    <h4 class="role-chip" style="background: rgba(34,197,94,0.12);">Kader</h4>
                    <div class="form-grid">
                        <div class="field-block full">
                            <div class="label-row">
                                <label for="kader_puskesmas_id">Puskesmas Induk</label>
                                <span class="helper">Hubungkan ke puskesmas pembina</span>
                            </div>
                            @if (($puskesmasOptions ?? collect())->isEmpty())
                                <div class="note" style="text-align:left;">Belum ada Puskesmas aktif. Hubungi admin.</div>
                            @else
                                <div class="input-wrap with-select">
                                    <i class="fa fa-hospital input-icon"></i>
                                    <select id="kader_puskesmas_id" name="kader_puskesmas_id" class="select">
                                        <option value="">Pilih puskesmas</option>
                                        @foreach ($puskesmasOptions as $puskesmas)
                                            <option value="{{ $puskesmas->id }}"
                                                @selected(old('kader_puskesmas_id') == $puskesmas->id)>🏥 {{ optional($puskesmas->detail)->organization ?? $puskesmas->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>


                <div class="form-grid" style="margin-top: 16px;">
                    <div class="field-block">
                        <div class="label-row">
                            <label for="phone">Nomor HP (Username)</label>
                            <span class="helper">Gunakan format 08xxxxxxxxxx</span>
                        </div>
                        <div class="input-wrap">
                            <i class="fa fa-phone input-icon"></i>
                            <input type="text" class="input" id="phone" name="phone" value="{{ old('phone') }}"
                                required>
                        </div>
                    </div>
                    <div class="field-block">
                        <div class="label-row">
                            <label for="password">Masukan Password</label>
                            <span class="helper d-lg-none">Min sesuai kebijakan keamanan</span>
                        </div>
                        <div class="input-wrap">
                            <i class="fa fa-lock input-icon"></i>
                            <input type="password" class="input" id="password" name="password" required
                                autocomplete="new-password">
                        </div>
                    </div>
                    <div class="field-block">
                        <div class="label-row">
                            <label for="password_confirmation">Konfirmasi Password</label>
                            <span class="helper d-lg-none">Ulangi password yang sama</span>
                        </div>
                        <div class="input-wrap">
                            <i class="fa fa-lock input-icon"></i>
                            <input type="password" class="input" id="password_confirmation" name="password_confirmation"
                                required autocomplete="new-password">
                        </div>
                    </div>
                </div>

                <div class="note" style="margin: 12px 0 0; text-align:left;">
                    <i class="fa fa-shield-halved"></i> Dengan mendaftar saya menyetujui kebijakan privasi SITUBA. Akun
                    dinonaktifkan hingga diverifikasi yang berwewenang.
                </div>

                <button type="submit" class="btn-primary" style="margin-top: 16px;">Daftar Sekarang</button>
                <p class="note" style="margin-top: 10px;">Sudah punya akun? <a href="{{ route('login') }}">Masuk di
                        sini</a></p>
            </form>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const roleSelect = document.getElementById('role');
            const sections = document.querySelectorAll('[data-role-section]');
            const nameLabel = document.getElementById('name-label');
            const defaultLabel = nameLabel?.dataset.default ?? nameLabel?.textContent;
            const labelMap = {
                kelurahan: 'Nama PJ Kelurahan',
                puskesmas: 'Nama PJ Puskesmas',
                kader: 'Nama Kader',
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
</body>

</html>
