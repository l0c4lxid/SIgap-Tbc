@extends('layouts.soft')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header d-flex flex-wrap gap-3 justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">Pasien di Kelurahan</h5>
                        <p class="text-sm text-muted mb-0">Daftar pasien pada puskesmas mitra kelurahan ini.</p>
                    </div>
                    <form method="GET" action="{{ route('kelurahan.patients') }}" class="d-flex gap-2">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white"><i class="fa fa-search text-muted"></i></span>
                            <input type="text" name="q" class="form-control" placeholder="Cari nama / nomor HP" value="{{ $search ?? '' }}">
                        </div>
                        <button type="submit" class="btn btn-sm btn-outline-primary">Cari</button>
                    </form>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="border rounded-2 p-3">
                                <p class="text-xs text-muted mb-1">Total Pasien</p>
                                <h5 class="mb-0">{{ $stats['total'] ?? 0 }}</h5>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="border rounded-2 p-3">
                                <p class="text-xs text-muted mb-1">Sudah Skrining</p>
                                <h5 class="mb-0 text-success">{{ $stats['screened'] ?? 0 }}</h5>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="border rounded-2 p-3">
                                <p a class="text-xs text-muted mb-1">Belum Skrining</p>
                                <h5 class="mb-0 text-warning">{{ $stats['unscreened'] ?? 0 }}</h5>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Pasien</th>
                                    <th>Kontak</th>
                                    <th>Puskesmas</th>
                                    <th>Kader</th>
                                    <th>Status Skrining</th>
                                    <th>Status Pengobatan</th>
                                    <th class="text-center">Detail</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $treatmentStatuses = [
                                        'contacted' => ['label' => 'Perlu Konfirmasi', 'badge' => 'bg-gradient-warning text-dark'],
                                        'scheduled' => ['label' => 'Terjadwal', 'badge' => 'bg-gradient-info'],
                                        'in_treatment' => ['label' => 'Sedang Berobat', 'badge' => 'bg-gradient-primary'],
                                        'recovered' => ['label' => 'Selesai', 'badge' => 'bg-gradient-success'],
                                    ];
                                @endphp
                                @forelse ($patients as $patient)
                                    @php
                                        $latestScreening = $patient->screenings->first();
                                        $positive = collect($latestScreening->answers ?? [])->filter(fn($ans) => $ans === 'ya')->count();
                                        $screeningLabel = $latestScreening
                                            ? ($positive >= 2 ? 'Suspek TBC' : ($positive === 1 ? 'Perlu Observasi' : 'Negatif'))
                                            : 'Belum Skrining';
                                        $screeningBadge = $latestScreening
                                            ? ($positive >= 2 ? 'bg-gradient-danger' : ($positive === 1 ? 'bg-gradient-warning text-dark' : 'bg-gradient-success'))
                                            : 'bg-gradient-secondary';
                                        $latestTreatment = $patient->treatments->first();
                                    @endphp
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <h6 class="mb-0 text-sm">{{ $patient->name }}</h6>
                                            <p class="text-xs text-muted mb-0">{{ $patient->detail->address ?? '-' }}</p>
                                        </td>
                                        <td>
                                            <p class="text-xs text-muted mb-0">HP: {{ $patient->phone }}</p>
                                        </td>
                                        <td>
                                            @php
                                                $kader = optional($patient->detail)->supervisor;
                                                $puskesmas = optional($kader?->detail)->supervisor;
                                            @endphp
                                            <span class="text-xs text-muted">{{ $puskesmas->name ?? '-' }}</span>
                                        </td>
                                        <td>
                                            <span class="text-xs text-muted">{{ $kader->name ?? '-' }}</span>
                                        </td>
                                        <td>
                                            <span class="badge {{ $screeningBadge }}">{{ $screeningLabel }}</span>
                                            @if ($latestScreening)
                                                <p class="text-xs text-muted mb-0">Terakhir: {{ $latestScreening->created_at->format('d M Y') }}</p>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($latestTreatment)
                                                @php
                                                    $treatmentInfo = $treatmentStatuses[$latestTreatment->status] ?? ['label' => ucfirst(str_replace('_', ' ', $latestTreatment->status)), 'badge' => 'bg-gradient-info'];
                                                @endphp
                                                <span class="badge {{ $treatmentInfo['badge'] }}">{{ $treatmentInfo['label'] }}</span>
                                                <p class="text-xs text-muted mb-0">
                                                    Jadwal: {{ optional($latestTreatment->next_follow_up_at)->format('d M Y') ?? 'Belum' }}
                                                </p>
                                            @else
                                                <span class="text-xs text-muted">Belum di daftar berobat.</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('kelurahan.patients.show', $patient) }}" class="btn btn-sm btn-outline-primary">
                                                Detail
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">Belum ada data pasien.</td>
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
