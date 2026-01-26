@extends('layouts.soft')

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <style>
        .select2-container {
            width: 100% !important;
        }
        .select2-container .select2-selection--single {
            height: 38px;
            padding: 0.375rem 0.75rem;
            border: 1px solid #d2d6da;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
        }
        .select2-container .select2-selection__rendered {
            padding-left: 0;
            line-height: 1.5;
        }
        .select2-container .select2-selection__arrow {
            height: 36px;
        }
    </style>
@endpush

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header pb-1">
                    <h5 class="mb-0">Tambah Skrining Pasien</h5>
                    <p class="text-sm text-muted mb-2">Lengkapi identitas pasien dan isi pertanyaan skrining.</p>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-gradient-primary">A</span>
                        <span class="text-sm fw-semibold">Identitas & Alamat</span>
                    </div>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('kader.screening.store') }}" id="screeningForm">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">WNI</label>
                                <select name="patient_is_wni" class="form-select" required>
                                    <option value="">Pilih</option>
                                    <option value="1" @selected(old('patient_is_wni', '1') === '1')>Ya</option>
                                    <option value="0" @selected(old('patient_is_wni') === '0')>Tidak</option>
                                </select>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">Nama Peserta</label>
                                <input type="text" name="patient_name" class="form-control" value="{{ old('patient_name') }}" placeholder="Nama sesuai KTP" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">NIK</label>
                                <input type="text" name="patient_nik" class="form-control" value="{{ old('patient_nik') }}" id="patientNik" inputmode="numeric" placeholder="NIK (angka saja)" oninput="this.value = this.value.replace(/\\D/g, '')" onkeypress="if (event.key && !/[0-9]/.test(event.key)) event.preventDefault()">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Jenis Kelamin</label>
                                <select name="patient_gender" class="form-select" required>
                                    <option value="">Pilih</option>
                                    <option value="L" @selected(old('patient_gender') === 'L')>Laki-laki</option>
                                    <option value="P" @selected(old('patient_gender') === 'P')>Perempuan</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Tempat Lahir</label>
                                <input type="text" name="patient_birth_place" class="form-control" value="{{ old('patient_birth_place') }}" placeholder="Contoh: Surakarta" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Tanggal Lahir</label>
                                <input type="date" name="patient_birth_date" class="form-control" value="{{ old('patient_birth_date') }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Umur (tahun)</label>
                                <input type="number" name="patient_age" class="form-control" min="0" max="150" value="{{ old('patient_age') }}" readonly required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Berat Badan (kg)</label>
                                <input type="number" step="0.1" name="patient_weight" class="form-control" min="0" value="{{ old('patient_weight') }}" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Tinggi Badan (cm)</label>
                                <input type="number" step="0.1" name="patient_height" class="form-control" min="0" value="{{ old('patient_height') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nomor HP (opsional)</label>
                                <input type="text" name="patient_phone" class="form-control" value="{{ old('patient_phone') }}" placeholder="Nomor HP (angka saja)" inputmode="numeric" oninput="this.value = this.value.replace(/\\D/g, '')" onkeypress="if (event.key && !/[0-9]/.test(event.key)) event.preventDefault()">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">RT</label>
                                <input type="text" name="patient_address_rt" class="form-control" value="{{ old('patient_address_rt') }}" placeholder="00" inputmode="numeric" maxlength="3" oninput="this.value = this.value.replace(/\\D/g, '').slice(0, 3)" onkeypress="if (event.key && !/[0-9]/.test(event.key)) event.preventDefault()" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">RW</label>
                                <input type="text" name="patient_address_rw" class="form-control" value="{{ old('patient_address_rw') }}" placeholder="00" inputmode="numeric" maxlength="3" oninput="this.value = this.value.replace(/\\D/g, '').slice(0, 3)" onkeypress="if (event.key && !/[0-9]/.test(event.key)) event.preventDefault()" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Kelurahan</label>
                                <select name="patient_address_kelurahan" class="form-select select2-kelurahan" required>
                                    <option value="">Pilih kelurahan</option>
                                    @if (!empty($kelurahanName)
                                        && !collect($kelurahanOptions ?? [])->contains($kelurahanName)
                                        && (empty($kelurahanOptions) || \Illuminate\Support\Str::contains(\Illuminate\Support\Str::lower($kelurahanName), 'kelurahan')))
                                        <option value="{{ $kelurahanName }}" selected>{{ $kelurahanName }}</option>
                                    @endif
                                    @foreach ($kelurahanOptions ?? [] as $kelurahanOption)
                                        <option value="{{ $kelurahanOption }}" @selected(old('patient_address_kelurahan', $kelurahanName ?? '') === $kelurahanOption)>
                                            {{ $kelurahanOption }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Alamat KTP</label>
                                <input type="text" name="patient_address_ktp" class="form-control" value="{{ old('patient_address_ktp') }}" placeholder="Alamat sesuai KTP" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Alamat Domisili</label>
                                <input type="text" name="patient_address_domisili" class="form-control" value="{{ old('patient_address_domisili') }}" placeholder="Alamat tempat tinggal sekarang" required>
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" id="domisiliSame" name="domisili_same">
                                    <label class="form-check-label" for="domisiliSame">Sama dengan alamat KTP</label>
                                </div>
                            </div>
                        </div>

                        <hr class="horizontal dark opacity-10 my-4">

                        <h6 class="mb-3">Pertanyaan Skrining</h6>
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-gradient-info">Bagian 1</span>
                                    <span class="text-sm fw-semibold">Faktor Risiko & Riwayat</span>
                                </div>
                            </div>
                            <div class="col-12 col-lg-6">
                                @foreach ($riskQuestions as $key => $question)
                                    @if ($loop->iteration <= 5)
                                        <div class="mb-4">
                                            <label class="form-label">{{ $loop->iteration }}. {{ $question }}</label>
                                            <div class="d-flex flex-wrap gap-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="{{ $key }}" value="ya" id="{{ $key }}_ya" @checked(old($key) === 'ya') required>
                                                    <label class="form-check-label" for="{{ $key }}_ya">Ya</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="{{ $key }}" value="tidak" id="{{ $key }}_tidak" @checked(old($key) === 'tidak')>
                                                    <label class="form-check-label" for="{{ $key }}_tidak">Tidak</label>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                            <div class="col-12 col-lg-6">
                                @foreach ($riskQuestions as $key => $question)
                                    @if ($loop->iteration > 5)
                                        <div class="mb-4">
                                            <label class="form-label">{{ $loop->iteration }}. {{ $question }}</label>
                                            <div class="d-flex flex-wrap gap-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="{{ $key }}" value="ya" id="{{ $key }}_ya" @checked(old($key) === 'ya') required>
                                                    <label class="form-check-label" for="{{ $key }}_ya">Ya</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="{{ $key }}" value="tidak" id="{{ $key }}_tidak" @checked(old($key) === 'tidak')>
                                                    <label class="form-check-label" for="{{ $key }}_tidak">Tidak</label>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                            <div class="col-12 mt-2">
                                <hr class="horizontal dark opacity-10 my-3">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-gradient-warning">Bagian 2</span>
                                    <span class="text-sm fw-semibold">Gejala TBC (Penentu Suspek)</span>
                                </div>
                            </div>
                            @foreach ($symptomQuestions as $key => $question)
                                <div class="col-12">
                                    <label class="form-label">{{ $loop->iteration + count($riskQuestions) }}. {{ $question }}</label>
                                    <div class="d-flex flex-wrap gap-3">
                                        <div class="form-check">
                                        <input class="form-check-input" type="radio" name="{{ $key }}" value="ya" id="{{ $key }}_ya" @checked(old($key) === 'ya') required>
                                            <label class="form-check-label" for="{{ $key }}_ya">Ya</label>
                                        </div>
                                        <div class="form-check">
                                        <input class="form-check-input" type="radio" name="{{ $key }}" value="tidak" id="{{ $key }}_tidak" @checked(old($key) === 'tidak')>
                                            <label class="form-check-label" for="{{ $key }}_tidak">Tidak</label>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <p class="text-xs text-muted mt-3">Hasil otomatis: minimal 1 jawaban "Ya" pada pertanyaan 11-15 langsung tercatat sebagai suspek.</p>

                        <div class="text-end mt-4">
                            <a href="{{ route('kader.screening.index') }}" class="btn btn-outline-secondary me-2">Batal</a>
                            <button type="submit" class="btn btn-primary">Simpan Skrining</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const wniField = document.querySelector('select[name="patient_is_wni"]');
            const nikField = document.getElementById('patientNik');
            const birthDateField = document.querySelector('input[name="patient_birth_date"]');
            const ageField = document.querySelector('input[name="patient_age"]');
            const addressKtpField = document.querySelector('input[name="patient_address_ktp"]');
            const addressDomField = document.querySelector('input[name="patient_address_domisili"]');
            const domisiliSame = document.getElementById('domisiliSame');
            const rtField = document.querySelector('input[name="patient_address_rt"]');
            const rwField = document.querySelector('input[name="patient_address_rw"]');

            const toggleNikRequired = () => {
                if (!wniField || !nikField) {
                    return;
                }
                const isWni = wniField.value === '1';
                nikField.required = isWni;
                nikField.closest('.col-md-6')?.classList.toggle('opacity-50', !isWni);
            };

            wniField?.addEventListener('change', toggleNikRequired);
            toggleNikRequired();

            const syncAge = () => {
                if (!birthDateField || !ageField) {
                    return;
                }
                if (!birthDateField.value) {
                    ageField.value = '';
                    return;
                }
                const today = new Date();
                const birth = new Date(birthDateField.value);
                let age = today.getFullYear() - birth.getFullYear();
                const monthDiff = today.getMonth() - birth.getMonth();
                if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
                    age -= 1;
                }
                ageField.value = age >= 0 ? age : '';
                syncLansia();
            };

            const syncLansia = () => {
                const ageValue = parseInt(ageField?.value ?? '', 10);
                const lansiaYes = document.getElementById('lansia_ya');
                const lansiaNo = document.getElementById('lansia_tidak');
                if (!Number.isFinite(ageValue) || !lansiaYes || !lansiaNo) {
                    return;
                }
                if (ageValue >= 66) {
                    lansiaYes.checked = true;
                } else {
                    lansiaNo.checked = true;
                }
            };

            birthDateField?.addEventListener('change', syncAge);
            syncAge();

            const syncDomisili = () => {
                if (!domisiliSame || !addressKtpField || !addressDomField) {
                    return;
                }
                if (domisiliSame.checked) {
                    addressDomField.value = addressKtpField.value;
                    addressDomField.setAttribute('readonly', 'readonly');
                } else {
                    addressDomField.removeAttribute('readonly');
                }
            };

            domisiliSame?.addEventListener('change', syncDomisili);
            addressKtpField?.addEventListener('input', () => {
                if (domisiliSame?.checked) {
                    addressDomField.value = addressKtpField.value;
                }
            });
            syncDomisili();

            const enforceDigits = (field) => {
                if (!field) {
                    return;
                }
                field.addEventListener('input', () => {
                    field.value = field.value.replace(/\D/g, '').slice(0, 3);
                });
            };
            enforceDigits(rtField);
            enforceDigits(rwField);

            const padTwoDigits = (field) => {
                if (!field) {
                    return;
                }
                field.addEventListener('blur', () => {
                    const value = field.value.trim();
                    if (value.length === 1) {
                        field.value = `0${value}`;
                    }
                });
            };
            padTwoDigits(rtField);
            padTwoDigits(rwField);

            const applyIndonesianValidation = (field) => {
                if (!field) {
                    return;
                }
                field.addEventListener('invalid', () => {
                    if (field.validity.valueMissing) {
                        field.setCustomValidity('Harap isi kolom ini.');
                        return;
                    }
                    if (field.validity.patternMismatch) {
                        field.setCustomValidity('Format tidak sesuai.');
                        return;
                    }
                    if (field.validity.typeMismatch) {
                        field.setCustomValidity('Format tidak valid.');
                        return;
                    }
                    field.setCustomValidity('Data tidak valid.');
                });
                field.addEventListener('input', () => {
                    field.setCustomValidity('');
                });
                field.addEventListener('change', () => {
                    field.setCustomValidity('');
                });
            };

            document.querySelectorAll('#screeningForm input, #screeningForm select, #screeningForm textarea')
                .forEach((field) => applyIndonesianValidation(field));

            if (window.jQuery && window.jQuery.fn.select2) {
                window.jQuery('.select2-kelurahan').select2({
                    width: '100%',
                    placeholder: 'Pilih kelurahan',
                });
            }
        });
    </script>
@endpush
