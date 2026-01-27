@extends('layouts.soft')

@section('subjudul', 'Detail kader kelurahan')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <a href="{{ route('kelurahan.kaders') }}" class="text-sm text-muted"><i class="fa fa-arrow-left me-1"></i>Kembali ke Data Kader</a>
                    <h4 class="mb-0 mt-1">{{ $kader->name }}</h4>
                    <p class="text-sm text-muted mb-0">HP: {{ $kader->phone }} | Puskesmas: {{ $kader->detail->supervisor->name ?? '-' }}</p>
                </div>
                <span class="badge {{ $kader->is_active ? 'bg-gradient-success' : 'bg-gradient-warning text-dark' }}">
                    {{ $kader->is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <p class="text-sm text-muted mb-1">Pasien Tercatat</p>
                    <h4 class="mb-0">{{ number_format($screeningSummary['total_patients']) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <p class="text-sm text-muted mb-1">Total Skrining</p>
                    <h4 class="mb-0">{{ number_format($screeningSummary['total_screenings']) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <p class="text-sm text-muted mb-1">Suspek TBC</p>
                    <h4 class="mb-0">{{ number_format($screeningSummary['suspect']) }}</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">Skrining Terbaru</h5>
                        <p class="text-sm text-muted mb-0">Ringkasan skrining yang dicatat kader ini.</p>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Pasien</th>
                                    <th>Nomor HP</th>
                                    <th>Alamat</th>
                                    <th>Tanggal Skrining</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($screenings as $screening)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td class="fw-semibold text-sm">{{ $screening->patient_name ?? '-' }}</td>
                                        <td class="text-sm text-muted">{{ $screening->patient_phone ?? '-' }}</td>
                                        <td class="text-sm text-muted">
                                            {{ $screening->patient_address_domisili ?? $screening->patient_address ?? '-' }}
                                            <div class="text-xxs text-muted">RT/RW {{ $screening->patient_address_rt ?? '-' }}/{{ $screening->patient_address_rw ?? '-' }} • {{ $screening->patient_address_kelurahan ?? '-' }}</div>
                                        </td>
                                        <td class="text-sm text-muted">{{ $screening->created_at?->format('d M Y') ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">Belum ada skrining tercatat untuk kader ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
