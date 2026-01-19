@extends('layouts.soft')

@section('content')
    <div class="row">
        <div class="col-lg-10">
            <div class="card shadow-sm border-0">
                <div class="card-header pb-1">
                    <h5 class="mb-0">Tambah Skrining Pasien</h5>
                    <p class="text-sm text-muted mb-0">Lengkapi identitas pasien dan isi pertanyaan skrining.</p>
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
                                <input type="text" name="patient_name" class="form-control" value="{{ old('patient_name') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">NIK</label>
                                <input type="text" name="patient_nik" class="form-control" value="{{ old('patient_nik') }}" id="patientNik">
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
                                <input type="text" name="patient_birth_place" class="form-control" value="{{ old('patient_birth_place') }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Tanggal Lahir</label>
                                <input type="date" name="patient_birth_date" class="form-control" value="{{ old('patient_birth_date') }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Umur (tahun)</label>
                                <input type="number" name="patient_age" class="form-control" min="0" max="150" value="{{ old('patient_age') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Alamat KTP</label>
                                <input type="text" name="patient_address_ktp" class="form-control" value="{{ old('patient_address_ktp') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Alamat Domisili</label>
                                <input type="text" name="patient_address_domisili" class="form-control" value="{{ old('patient_address_domisili') }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">RT</label>
                                <input type="text" name="patient_address_rt" class="form-control" value="{{ old('patient_address_rt') }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">RW</label>
                                <input type="text" name="patient_address_rw" class="form-control" value="{{ old('patient_address_rw') }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Kelurahan</label>
                                <input type="text" name="patient_address_kelurahan" class="form-control" value="{{ old('patient_address_kelurahan') }}" required>
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
                                <input type="text" name="patient_phone" class="form-control" value="{{ old('patient_phone') }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Catatan</label>
                                <textarea name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
                            </div>
                        </div>

                        <hr class="horizontal dark opacity-10 my-4">

                        <h6 class="mb-3">Pertanyaan Skrining</h6>
                        @foreach ($questions as $key => $question)
                            <div class="mb-4">
                                <label class="form-label">{{ $loop->iteration }}. {{ $question }}</label>
                                <div class="d-flex gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="{{ $key }}" value="ya" id="{{ $key }}_ya" required>
                                        <label class="form-check-label" for="{{ $key }}_ya">Ya</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="{{ $key }}" value="tidak" id="{{ $key }}_tidak">
                                        <label class="form-check-label" for="{{ $key }}_tidak">Tidak</label>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        <p class="text-xs text-muted">Hasil otomatis: minimal 1 jawaban "Ya" langsung tercatat sebagai suspek.</p>

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
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const wniField = document.querySelector('select[name="patient_is_wni"]');
            const nikField = document.getElementById('patientNik');

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
        });
    </script>
@endpush
