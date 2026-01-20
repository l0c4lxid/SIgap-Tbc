@extends('layouts.soft')

@section('content')
    @php
        $answers = collect($screening->answers ?? []);
        $positiveCount = $answers
            ->filter(fn ($answer, $key) => str_starts_with((string) $key, 'gejala_') && $answer === 'ya')
            ->count();

        if ($positiveCount >= 1) {
            $statusBadge = ['label' => 'Suspek TBC', 'class' => 'bg-gradient-danger'];
        } else {
            $statusBadge = ['label' => 'Aman', 'class' => 'bg-gradient-success'];
        }
    @endphp

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header d-flex flex-wrap gap-3 justify-content-between align-items-center">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <a href="{{ route('puskesmas.screenings') }}" class="text-sm text-muted"><i class="fa fa-arrow-left me-1"></i> Kembali</a>
                            <span class="text-muted">/</span>
                            <span class="text-sm text-muted">Detail Skrining</span>
                        </div>
                        <h5 class="mb-0">{{ $screening->patient_name ?? '-' }}</h5>
                        <p class="text-sm text-muted mb-0">Informasi lengkap hasil skrining pasien.</p>
                    </div>
                    <span class="badge {{ $statusBadge['class'] }}">{{ $statusBadge['label'] }}</span>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="border rounded p-3 h-100">
                                <p class="text-xs text-muted mb-1">Identitas Pasien</p>
                                <h6 class="mb-1">{{ $screening->patient_name ?? '-' }}</h6>
                                <p class="text-xs text-muted mb-0">WNI: {{ $screening->patient_is_wni ? 'Ya' : 'Tidak' }}</p>
                                <p class="text-xs text-muted mb-0">NIK: {{ $screening->patient_nik ?? '-' }}</p>
                                <p class="text-xs text-muted mb-0">HP: {{ $screening->patient_phone ?? '-' }}</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded p-3 h-100">
                                <p class="text-xs text-muted mb-1">Alamat</p>
                                <p class="text-sm mb-1">{{ $screening->patient_address_domisili ?? $screening->patient_address ?? '-' }}</p>
                                <p class="text-xs text-muted mb-0">Alamat KTP: {{ $screening->patient_address_ktp ?? '-' }}</p>
                                <p class="text-xs text-muted mb-0">RT/RW: {{ $screening->patient_address_rt ?? '-' }}/{{ $screening->patient_address_rw ?? '-' }}</p>
                                <p class="text-xs text-muted mb-0">Kelurahan: {{ $screening->patient_address_kelurahan ?? '-' }}</p>
                                <p class="text-xs text-muted mb-0">Tercatat: {{ $screening->created_at->format('d M Y') }}</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded p-3 h-100 bg-light">
                                <p class="text-xs text-muted mb-1">Kader Pembina</p>
                                <h6 class="mb-1">{{ $kader?->name ?? '-' }}</h6>
                                <p class="text-xs text-muted mb-0">HP: {{ $kader?->phone ?? '-' }}</p>
                                <p class="text-xs text-muted mb-0">Catatan: {{ $kader?->detail?->notes ?? '-' }}</p>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100">
                                <p class="text-xs text-muted mb-1">Data Demografi</p>
                                <p class="text-xs text-muted mb-0">Jenis Kelamin: {{ $screening->patient_gender ?? '-' }}</p>
                                <p class="text-xs text-muted mb-0">TTL: {{ $screening->patient_birth_place ?? '-' }}, {{ optional($screening->patient_birth_date)?->format('d M Y') ?? '-' }}</p>
                                <p class="text-xs text-muted mb-0">Umur: {{ $screening->patient_age ?? '-' }} tahun</p>
                                <p class="text-xs text-muted mb-0">BB/TB: {{ $screening->patient_weight ?? '-' }} kg / {{ $screening->patient_height ?? '-' }} cm</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100">
                                <p class="text-xs text-muted mb-1">Status Skrining</p>
                                <h6 class="mb-1">{{ $statusBadge['label'] }}</h6>
                                <p class="text-xs text-muted mb-0">Tanggal skrining: {{ $screening->created_at->format('d M Y H:i') }}</p>
                                <p class="text-xs text-muted mb-0">Jumlah indikasi positif: {{ $positiveCount }}</p>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="border rounded p-3 h-100">
                                <p class="text-xs text-muted mb-2">Detail Jawaban</p>
                                @if ($answers->isEmpty())
                                    <p class="text-sm text-muted mb-0">Belum ada detail jawaban.</p>
                                @else
                                    <ul class="mb-0">
                                        @foreach ($answers as $key => $value)
                                            @php
                                                $label = is_string($key) ? ucwords(str_replace('_', ' ', $key)) : 'Pertanyaan '.($loop->iteration);
                                                $display = $value === 'ya' ? 'Ya' : ($value === 'tidak' ? 'Tidak' : (string) $value);
                                            @endphp
                                            <li class="mb-1">
                                                <strong>{{ $label }}:</strong> {{ $display }}
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if ($screening->notes)
                        <div class="mt-4">
                            <p class="text-xs text-muted mb-1">Catatan Skrining</p>
                            <div class="border rounded p-3 bg-light">
                                <p class="mb-0 text-sm">{{ $screening->notes }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
