@extends('layouts.soft')

@php
    $statusBadges = [
        'pending' => 'bg-gradient-secondary',
        'in_progress' => 'bg-gradient-warning text-dark',
        'suspect' => 'bg-gradient-danger',
        'clear' => 'bg-gradient-success',
    ];
@endphp

@section('content')
    <div class="row">
        <div class="col-12 mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Detail Pasien - {{ $patient->name }}</h5>
                <a href="{{ route('kelurahan.patients') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fa fa-arrow-left me-1"></i> Kembali
                </a>
            </div>
        </div>
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header">
                    <h6 class="mb-0">Profil Pasien</h6>
                    <p class="text-sm text-muted mb-0">Informasi dasar pasien.</p>
                </div>
                <div class="card-body">
                    <p class="mb-1"><strong>HP:</strong> {{ $patient->phone }}</p>
                    <p class="mb-1"><strong>NIK:</strong> {{ $patient->detail->nik ?? '-' }}</p>
                    <p class="mb-1"><strong>Alamat:</strong> {{ $patient->detail->address ?? '-' }}</p>
                    <p class="mb-1"><strong>Status Akun:</strong>
                        <span class="badge {{ $patient->is_active ? 'bg-gradient-success' : 'bg-gradient-warning text-dark' }}">
                            {{ $patient->is_active ? 'Aktif' : 'Tidak Aktif' }}
                        </span>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header">
                    <h6 class="mb-0">Relasi Puskesmas</h6>
                    <p class="text-sm text-muted mb-0">Puskesmas dan kader yang mendampingi.</p>
                </div>
                <div class="card-body">
                    <p class="mb-1"><strong>Puskesmas:</strong> {{ $puskesmas->name ?? '-' }}</p>
                    <p class="mb-1"><strong>Kader:</strong> {{ $patient->detail->supervisor->name ?? '-' }}</p>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header">
                    <h6 class="mb-0">Riwayat Skrining</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th>Waktu</th>
                                    <th>Positif</th>
                                    <th>Petugas</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($patient->screenings as $screening)
                                    @php
                                        $positive = collect($screening->answers ?? [])->filter(fn($ans) => $ans === 'ya')->count();
                                    @endphp
                                    <tr>
                                        <td class="text-xs text-muted">{{ $screening->created_at->format('d M Y H:i') }}</td>
                                        <td>
                                            <span class="badge bg-gradient-{{ $positive >= 2 ? 'danger' : ($positive === 1 ? 'warning text-dark' : 'success') }}">
                                                {{ $positive }}
                                            </span>
                                        </td>
                                        <td class="text-xs text-muted">{{ $screening->kader->name ?? 'Mandiri' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">Belum ada data.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header">
                    <h6 class="mb-0">Riwayat Pengobatan</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th>Status</th>
                                    <th>Jadwal / Catatan</th>
                                    <th>Diperbarui</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($patient->treatments as $treatment)
                                    <tr>
                                        <td>
                                            <span class="badge bg-gradient-info">{{ ucfirst(str_replace('_', ' ', $treatment->status)) }}</span>
                                        </td>
                                        <td class="text-xs">
                                            <div>Jadwal: {{ optional($treatment->next_follow_up_at)->format('d M Y') ?? 'Belum' }}</div>
                                            @if ($treatment->notes)
                                                <div>Catatan: {{ $treatment->notes }}</div>
                                            @endif
                                        </td>
                                        <td class="text-xs text-muted">{{ $treatment->updated_at->format('d M Y H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">Belum ada data.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header">
                    <h6 class="mb-0">Anggota Keluarga</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Kontak</th>
                                    <th>Status Skrining</th>
                                    <th>Catatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($patient->familyMembers as $member)
                                    <tr>
                                        <td>
                                            <strong>{{ $member->name }}</strong>
                                            <p class="text-xs text-muted mb-0">Relasi: {{ $member->relation ?? '-' }}</p>
                                        </td>
                                        <td>
                                            <p class="text-xs text-muted mb-0">{{ $member->phone ?? '-' }}</p>
                                            @if ($member->nik)
                                                <p class="text-xs text-muted mb-0">NIK: {{ $member->nik }}</p>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge {{ $statusBadges[$member->screening_status] ?? 'bg-secondary' }}">
                                                {{ ucfirst(str_replace('_', ' ', $member->screening_status ?? 'pending')) }}
                                            </span>
                                            @if ($member->last_screened_at)
                                                <p class="text-xs text-muted mb-0">Terakhir: {{ $member->last_screened_at->format('d M Y H:i') }}</p>
                                            @endif
                                        </td>
                                        <td>{{ $member->notes ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">Belum ada anggota keluarga.</td>
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
