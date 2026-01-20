@extends('layouts.soft')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center pb-1">
                    <div>
                        <h5 class="mb-0">Detail Skrining Pasien</h5>
                        <p class="text-sm text-muted mb-0">Lihat detail skrining yang tercatat oleh kader.</p>
                    </div>
                    <a href="{{ route('pemda.screenings') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fa fa-arrow-left me-1"></i>Kembali
                    </a>
                </div>
                <div class="card-body">
                    @php
                        $suspectCount = collect($screening->answers ?? [])
                            ->filter(fn ($ans, $key) => str_starts_with((string) $key, 'gejala_') && $ans === 'ya')
                            ->count();
                    @endphp
                    @php
                        $formatNumber = function ($value) {
                            if ($value === null || $value === '') {
                                return '-';
                            }
                            $number = is_numeric($value) ? (float) $value : $value;
                            $formatted = is_numeric($number) ? number_format($number, 2, '.', '') : $number;
                            return rtrim(rtrim($formatted, '0'), '.');
                        };
                    @endphp
                    <div class="d-flex flex-wrap gap-3 mb-4">
                        <span class="badge bg-gradient-{{ $suspectCount ? 'danger' : 'success' }}">
                            {{ $suspectCount ? 'Suspek TBC' : 'Non Suspek' }}
                        </span>
                        <span class="text-sm text-muted">Diinput {{ $screening->created_at->format('d M Y H:i') }}</span>
                        <span class="text-sm text-muted">Kader PJ: {{ $screening->kader?->name ?? '-' }}</span>
                    </div>

                    <div class="row g-4">
                        <div class="col-12">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <span class="badge bg-gradient-primary">A</span>
                                <span class="text-sm fw-semibold">Identitas & Alamat</span>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm align-middle detail-table mb-0">
                                    <tbody>
                                        <tr>
                                            <td class="detail-table__label">WNI</td>
                                            <td class="detail-table__colon">:</td>
                                            <td class="detail-table__value">{{ $screening->patient_is_wni ? 'Ya' : 'Tidak' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="detail-table__label">Nama Peserta</td>
                                            <td class="detail-table__colon">:</td>
                                            <td class="detail-table__value">{{ $screening->patient_name ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="detail-table__label">NIK</td>
                                            <td class="detail-table__colon">:</td>
                                            <td class="detail-table__value">{{ $screening->patient_nik ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="detail-table__label">Jenis Kelamin</td>
                                            <td class="detail-table__colon">:</td>
                                            <td class="detail-table__value">{{ $screening->patient_gender ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="detail-table__label">Tempat Lahir</td>
                                            <td class="detail-table__colon">:</td>
                                            <td class="detail-table__value">{{ $screening->patient_birth_place ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="detail-table__label">Tanggal Lahir</td>
                                            <td class="detail-table__colon">:</td>
                                            <td class="detail-table__value">{{ optional($screening->patient_birth_date)->locale('id')->translatedFormat('d F Y') }}</td>
                                        </tr>
                                        <tr>
                                            <td class="detail-table__label">Umur</td>
                                            <td class="detail-table__colon">:</td>
                                            <td class="detail-table__value">{{ $screening->patient_age ?? '-' }} tahun</td>
                                        </tr>
                                        <tr>
                                            <td class="detail-table__label">Berat Badan</td>
                                            <td class="detail-table__colon">:</td>
                                            <td class="detail-table__value">{{ $formatNumber($screening->patient_weight) }} kg</td>
                                        </tr>
                                        <tr>
                                            <td class="detail-table__label">Tinggi Badan</td>
                                            <td class="detail-table__colon">:</td>
                                            <td class="detail-table__value">{{ $formatNumber($screening->patient_height) }} cm</td>
                                        </tr>
                                        <tr>
                                            <td class="detail-table__label">Nomor HP</td>
                                            <td class="detail-table__colon">:</td>
                                            <td class="detail-table__value">{{ $screening->patient_phone ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="detail-table__label">RT</td>
                                            <td class="detail-table__colon">:</td>
                                            <td class="detail-table__value">{{ $screening->patient_address_rt ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="detail-table__label">RW</td>
                                            <td class="detail-table__colon">:</td>
                                            <td class="detail-table__value">{{ $screening->patient_address_rw ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="detail-table__label">Kelurahan</td>
                                            <td class="detail-table__colon">:</td>
                                            <td class="detail-table__value">{{ $screening->patient_address_kelurahan ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="detail-table__label">Alamat KTP</td>
                                            <td class="detail-table__colon">:</td>
                                            <td class="detail-table__value">{{ $screening->patient_address_ktp ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="detail-table__label">Alamat Domisili</td>
                                            <td class="detail-table__colon">:</td>
                                            <td class="detail-table__value">{{ $screening->patient_address_domisili ?? '-' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="badge bg-gradient-info">Bagian 1</span>
                                <span class="text-sm fw-semibold">Faktor Risiko & Riwayat</span>
                            </div>
                            <div class="row g-2">
                                <div class="col-12 col-lg-6">
                                    @foreach ($riskQuestions as $key => $question)
                                        @if ($loop->iteration <= 5)
                                            <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                                                <span class="text-sm">{{ $loop->iteration }}. {{ $question }}</span>
                                                <span class="badge bg-gradient-{{ ($screening->answers[$key] ?? 'tidak') === 'ya' ? 'danger' : 'success' }}">
                                                    {{ ($screening->answers[$key] ?? 'tidak') === 'ya' ? 'Ya' : 'Tidak' }}
                                                </span>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                                <div class="col-12 col-lg-6">
                                    @foreach ($riskQuestions as $key => $question)
                                        @if ($loop->iteration > 5)
                                            <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                                                <span class="text-sm">{{ $loop->iteration }}. {{ $question }}</span>
                                                <span class="badge bg-gradient-{{ ($screening->answers[$key] ?? 'tidak') === 'ya' ? 'danger' : 'success' }}">
                                                    {{ ($screening->answers[$key] ?? 'tidak') === 'ya' ? 'Ya' : 'Tidak' }}
                                                </span>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="badge bg-gradient-warning">Bagian 2</span>
                                <span class="text-sm fw-semibold">Gejala TBC (Penentu Suspek)</span>
                            </div>
                            <div class="row g-2">
                                <div class="col-12 col-lg-6">
                                    @foreach ($symptomQuestions as $key => $question)
                                        @if ($loop->iteration <= 3)
                                            <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                                                <span class="text-sm">{{ $loop->iteration + count($riskQuestions) }}. {{ $question }}</span>
                                                <span class="badge bg-gradient-{{ ($screening->answers[$key] ?? 'tidak') === 'ya' ? 'danger' : 'success' }}">
                                                    {{ ($screening->answers[$key] ?? 'tidak') === 'ya' ? 'Ya' : 'Tidak' }}
                                                </span>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                                <div class="col-12 col-lg-6">
                                    @foreach ($symptomQuestions as $key => $question)
                                        @if ($loop->iteration > 3)
                                            <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                                                <span class="text-sm">{{ $loop->iteration + count($riskQuestions) }}. {{ $question }}</span>
                                                <span class="badge bg-gradient-{{ ($screening->answers[$key] ?? 'tidak') === 'ya' ? 'danger' : 'success' }}">
                                                    {{ ($screening->answers[$key] ?? 'tidak') === 'ya' ? 'Ya' : 'Tidak' }}
                                                </span>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
