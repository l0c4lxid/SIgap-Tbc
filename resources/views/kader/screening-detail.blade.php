@extends('layouts.soft')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center pb-1">
                    <div>
                        <h5 class="mb-0">Detail Skrining Pasien</h5>
                        <p class="text-sm text-muted mb-0">Lihat detail skrining yang tercatat.</p>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        @if (!$isEdit)
                            <a href="{{ route('kader.screening.show', ['screening' => $screening, 'edit' => 1]) }}" class="btn btn-sm btn-primary">
                                <i class="fa-solid fa-pen me-1"></i>Edit
                            </a>
                        @endif
                        <form method="POST" action="{{ route('kader.screening.destroy', $screening) }}" data-confirm="Hapus data skrining ini?" data-confirm-text="Hapus">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                        </form>
                    </div>
                </div>
                <div class="card-body">
                    @if (!$isEdit)
                        @php
                            $suspectCount = collect($screening->answers ?? [])
                                ->filter(fn ($ans, $key) => str_starts_with((string) $key, 'gejala_') && $ans === 'ya')
                                ->count();
                        @endphp
                        <div class="d-flex flex-wrap gap-3 mb-4">
                            <span class="badge bg-gradient-{{ $suspectCount ? 'danger' : 'success' }}">
                                {{ $suspectCount ? 'Suspek TBC' : 'Non Suspek' }}
                            </span>
                            <span class="text-sm text-muted">Diinput {{ $screening->created_at->format('d M Y H:i') }}</span>
                        </div>

                        <div class="row g-4">
                            <div class="col-12">
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <span class="badge bg-gradient-primary">A</span>
                                    <span class="text-sm fw-semibold">Identitas & Alamat</span>
                                </div>
                                <div class="detail-grid">
                                    <div class="detail-item">
                                        <span class="detail-label">WNI</span>
                                        <span class="detail-value">{{ $screening->patient_is_wni ? 'Ya' : 'Tidak' }}</span>
                                    </div>
                                    <div class="detail-item detail-item--wide">
                                        <span class="detail-label">Nama Peserta</span>
                                        <span class="detail-value">{{ $screening->patient_name ?? '-' }}</span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">NIK</span>
                                        <span class="detail-value">{{ $screening->patient_nik ?? '-' }}</span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Jenis Kelamin</span>
                                        <span class="detail-value">{{ $screening->patient_gender ?? '-' }}</span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Tempat Lahir</span>
                                        <span class="detail-value">{{ $screening->patient_birth_place ?? '-' }}</span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Tanggal Lahir</span>
                                        <span class="detail-value">{{ optional($screening->patient_birth_date)->format('d M Y') }}</span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Umur</span>
                                        <span class="detail-value">{{ $screening->patient_age ?? '-' }} tahun</span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Berat Badan</span>
                                        <span class="detail-value">{{ $screening->patient_weight ?? '-' }} kg</span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Tinggi Badan</span>
                                        <span class="detail-value">{{ $screening->patient_height ?? '-' }} cm</span>
                                    </div>
                                    <div class="detail-item detail-item--wide">
                                        <span class="detail-label">Nomor HP</span>
                                        <span class="detail-value">{{ $screening->patient_phone ?? '-' }}</span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">RT</span>
                                        <span class="detail-value">{{ $screening->patient_address_rt ?? '-' }}</span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">RW</span>
                                        <span class="detail-value">{{ $screening->patient_address_rw ?? '-' }}</span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Kelurahan</span>
                                        <span class="detail-value">{{ $screening->patient_address_kelurahan ?? '-' }}</span>
                                    </div>
                                    <div class="detail-item detail-item--wide">
                                        <span class="detail-label">Alamat KTP</span>
                                        <span class="detail-value">{{ $screening->patient_address_ktp ?? '-' }}</span>
                                    </div>
                                    <div class="detail-item detail-item--wide">
                                        <span class="detail-label">Alamat Domisili</span>
                                        <span class="detail-value">{{ $screening->patient_address_domisili ?? '-' }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="badge bg-gradient-info">Bagian 1</span>
                                    <span class="text-sm fw-semibold">Faktor Risiko & Riwayat</span>
                                </div>
                                <div class="row g-2">
                                    @foreach ($riskQuestions as $key => $question)
                                        <div class="col-12 col-lg-6">
                                            <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                                                <span class="text-sm">{{ $loop->iteration }}. {{ $question }}</span>
                                                <span class="badge bg-gradient-{{ ($screening->answers[$key] ?? 'tidak') === 'ya' ? 'danger' : 'success' }}">
                                                    {{ ($screening->answers[$key] ?? 'tidak') === 'ya' ? 'Ya' : 'Tidak' }}
                                                </span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="badge bg-gradient-warning">Bagian 2</span>
                                    <span class="text-sm fw-semibold">Gejala TBC (Penentu Suspek)</span>
                                </div>
                                <div class="row g-2">
                                    @foreach ($symptomQuestions as $key => $question)
                                        <div class="col-12 col-lg-6">
                                            <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                                                <span class="text-sm">{{ $loop->iteration + count($riskQuestions) }}. {{ $question }}</span>
                                                <span class="badge bg-gradient-{{ ($screening->answers[$key] ?? 'tidak') === 'ya' ? 'danger' : 'success' }}">
                                                    {{ ($screening->answers[$key] ?? 'tidak') === 'ya' ? 'Ya' : 'Tidak' }}
                                                </span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <p class="text-xs text-muted mt-3">Hasil otomatis: minimal 1 jawaban "Ya" pada pertanyaan 11-15 langsung tercatat sebagai suspek.</p>
                            </div>
                        </div>
                        <div class="mt-4">
                            <a href="{{ route('kader.screening.index') }}" class="btn btn-outline-secondary">Kembali</a>
                        </div>
                    @else
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="badge bg-gradient-primary">A</span>
                            <span class="text-sm fw-semibold">Identitas & Alamat</span>
                        </div>
                        <form method="POST" action="{{ route('kader.screening.update', $screening) }}" id="screeningForm">
                            @csrf
                            @method('PUT')
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">WNI</label>
                                    <select name="patient_is_wni" class="form-select" required>
                                        <option value="">Pilih</option>
                                        <option value="1" @selected(old('patient_is_wni', (string) $screening->patient_is_wni) === '1')>Ya</option>
                                        <option value="0" @selected(old('patient_is_wni', (string) $screening->patient_is_wni) === '0')>Tidak</option>
                                    </select>
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label">Nama Peserta</label>
                                    <input type="text" name="patient_name" class="form-control" value="{{ old('patient_name', $screening->patient_name) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">NIK</label>
                                    <input type="text" name="patient_nik" class="form-control" value="{{ old('patient_nik', $screening->patient_nik) }}" id="patientNik">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Jenis Kelamin</label>
                                    <select name="patient_gender" class="form-select" required>
                                        <option value="">Pilih</option>
                                        <option value="L" @selected(old('patient_gender', $screening->patient_gender) === 'L')>Laki-laki</option>
                                        <option value="P" @selected(old('patient_gender', $screening->patient_gender) === 'P')>Perempuan</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Tempat Lahir</label>
                                    <input type="text" name="patient_birth_place" class="form-control" value="{{ old('patient_birth_place', $screening->patient_birth_place) }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Tanggal Lahir</label>
                                    <input type="date" name="patient_birth_date" class="form-control" value="{{ old('patient_birth_date', optional($screening->patient_birth_date)->format('Y-m-d')) }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Umur (tahun)</label>
                                    <input type="number" name="patient_age" class="form-control" min="0" max="150" value="{{ old('patient_age', $screening->patient_age) }}" readonly required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Berat Badan (kg)</label>
                                    <input type="number" step="0.1" name="patient_weight" class="form-control" min="0" value="{{ old('patient_weight', $screening->patient_weight) }}" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Tinggi Badan (cm)</label>
                                    <input type="number" step="0.1" name="patient_height" class="form-control" min="0" value="{{ old('patient_height', $screening->patient_height) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Nomor HP (opsional)</label>
                                    <input type="text" name="patient_phone" class="form-control" value="{{ old('patient_phone', $screening->patient_phone) }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">RT</label>
                                    <input type="text" name="patient_address_rt" class="form-control" value="{{ old('patient_address_rt', $screening->patient_address_rt) }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">RW</label>
                                    <input type="text" name="patient_address_rw" class="form-control" value="{{ old('patient_address_rw', $screening->patient_address_rw) }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Kelurahan</label>
                                    <input type="text" name="patient_address_kelurahan" class="form-control" value="{{ old('patient_address_kelurahan', $screening->patient_address_kelurahan) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Alamat KTP</label>
                                    <input type="text" name="patient_address_ktp" class="form-control" value="{{ old('patient_address_ktp', $screening->patient_address_ktp) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Alamat Domisili</label>
                                    <input type="text" name="patient_address_domisili" class="form-control" value="{{ old('patient_address_domisili', $screening->patient_address_domisili) }}" required>
                                    @php
                                        $domisiliSame = $screening->patient_address_domisili && $screening->patient_address_ktp
                                            ? $screening->patient_address_domisili === $screening->patient_address_ktp
                                            : false;
                                    @endphp
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" id="domisiliSame" name="domisili_same" @checked(old('domisili_same', $domisiliSame))>
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
                                                        <input class="form-check-input" type="radio" name="{{ $key }}" value="ya" id="{{ $key }}_ya" @checked(old($key, $screening->answers[$key] ?? '') === 'ya') required>
                                                        <label class="form-check-label" for="{{ $key }}_ya">Ya</label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="{{ $key }}" value="tidak" id="{{ $key }}_tidak" @checked(old($key, $screening->answers[$key] ?? '') === 'tidak')>
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
                                                        <input class="form-check-input" type="radio" name="{{ $key }}" value="ya" id="{{ $key }}_ya" @checked(old($key, $screening->answers[$key] ?? '') === 'ya') required>
                                                        <label class="form-check-label" for="{{ $key }}_ya">Ya</label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="{{ $key }}" value="tidak" id="{{ $key }}_tidak" @checked(old($key, $screening->answers[$key] ?? '') === 'tidak')>
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
                                                <input class="form-check-input" type="radio" name="{{ $key }}" value="ya" id="{{ $key }}_ya" @checked(old($key, $screening->answers[$key] ?? '') === 'ya') required>
                                                <label class="form-check-label" for="{{ $key }}_ya">Ya</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="{{ $key }}" value="tidak" id="{{ $key }}_tidak" @checked(old($key, $screening->answers[$key] ?? '') === 'tidak')>
                                                <label class="form-check-label" for="{{ $key }}_tidak">Tidak</label>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <p class="text-xs text-muted mt-3">Hasil otomatis: minimal 1 jawaban "Ya" pada pertanyaan 11-15 langsung tercatat sebagai suspek.</p>

                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-4">
                                <a href="{{ route('kader.screening.show', $screening) }}" class="btn btn-outline-secondary">Batal</a>
                                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const wniField = document.querySelector('select[name="patient_is_wni"]');
            const nikField = document.getElementById('patientNik');
            const birthDateField = document.querySelector('input[name="patient_birth_date"]');
            const ageField = document.querySelector('input[name="patient_age"]');
            const addressKtpField = document.querySelector('input[name="patient_address_ktp"]');
            const addressDomField = document.querySelector('input[name="patient_address_domisili"]');
            const domisiliSame = document.getElementById('domisiliSame');

            const toggleNikRequired = () => {
                if (!wniField || !nikField) {
                    return;
                }
                const isWni = wniField.value === '1';
                nikField.required = isWni;
                nikField.closest('.col-md-6')?.classList.toggle('opacity-50', !isWni);
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

            wniField?.addEventListener('change', toggleNikRequired);
            birthDateField?.addEventListener('change', syncAge);
            domisiliSame?.addEventListener('change', syncDomisili);
            addressKtpField?.addEventListener('input', () => {
                if (domisiliSame?.checked) {
                    addressDomField.value = addressKtpField.value;
                }
            });

            toggleNikRequired();
            syncAge();
            syncDomisili();
        });
    </script>
@endpush
