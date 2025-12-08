@extends('layouts.soft')

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
                    <p class="text-sm text-muted mb-1">Total Pasien</p>
                    <h4 class="mb-0">{{ number_format($patientSummary['total']) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <p class="text-sm text-muted mb-1">Sudah Terskrining</p>
                    <h4 class="mb-0">{{ number_format($patientSummary['screened']) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <p class="text-sm text-muted mb-1">Belum Terskrining</p>
                    <h4 class="mb-0">{{ number_format($patientSummary['unscreened']) }}</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">Pasien yang Dibina</h5>
                        <p class="text-sm text-muted mb-0">Ringkasan pasien yang diinputkan kader ini.</p>
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
                                    <th>Skrining Terakhir</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($patients as $patient)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td class="fw-semibold text-sm">{{ $patient->name }}</td>
                                        <td class="text-sm text-muted">{{ $patient->phone ?? '-' }}</td>
                                        <td class="text-sm text-muted">{{ $patient->detail->address ?? '-' }}</td>
                                        <td class="text-sm text-muted">
                                            @php
                                                $lastScreening = $patient->screenings->first();
                                            @endphp
                                            {{ $lastScreening?->created_at?->format('d M Y') ?? 'Belum ada' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">Belum ada pasien tercatat untuk kader ini.</td>
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
