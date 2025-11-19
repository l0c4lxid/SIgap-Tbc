@extends('layouts.soft')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header d-flex flex-wrap gap-3 justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">Data Pasien SIGAP TBC</h5>
                        <p class="text-sm text-muted mb-0">Pemda dapat memonitor seluruh pasien beserta relasi kader dan puskesmas.</p>
                    </div>
                    <form method="GET" action="{{ route('pemda.patients') }}" class="d-flex flex-wrap gap-2">
                        <div class="input-group input-group-sm" style="min-width:220px;">
                            <span class="input-group-text bg-white"><i class="fa fa-search text-muted"></i></span>
                            <input type="text" name="q" class="form-control" placeholder="Cari nama / nomor HP / alamat" value="{{ $search ?? '' }}">
                        </div>
                        <select name="puskesmas_id" class="form-select form-select-sm">
                            <option value="">Semua Puskesmas</option>
                            @foreach ($puskesmasOptions as $option)
                                <option value="{{ $option->id }}" @selected(($filters['puskesmas_id'] ?? '') == $option->id)>{{ $option->name }}</option>
                            @endforeach
                        </select>
                        <select name="kelurahan_id" class="form-select form-select-sm">
                            <option value="">Semua Kelurahan</option>
                            @foreach ($kelurahanOptions as $option)
                                <option value="{{ $option->id }}" @selected(($filters['kelurahan_id'] ?? '') == $option->id)>{{ $option->name }}</option>
                            @endforeach
                        </select>
                        <select name="month" class="form-select form-select-sm">
                            <option value="">Bulan</option>
                            @foreach ($months as $value => $label)
                                <option value="{{ $value }}" @selected(($filters['month'] ?? '') == $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <select name="year" class="form-select form-select-sm">
                            <option value="">Tahun</option>
                            @foreach ($years as $year)
                                <option value="{{ $year }}" @selected(($filters['year'] ?? '') == $year)>{{ $year }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-sm btn-outline-primary">Cari</button>
                        @if ($search || ($filters['puskesmas_id'] ?? '') || ($filters['kelurahan_id'] ?? '') || ($filters['month'] ?? '') || ($filters['year'] ?? ''))
                            <a href="{{ route('pemda.patients') }}" class="btn btn-sm btn-light">Reset</a>
                        @endif
                    </form>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-sm-6 col-md-3 mb-3">
                            <div class="border rounded-2 p-3">
                                <p class="text-xs text-muted mb-1">Total Pasien</p>
                                <h5 class="mb-0">{{ $stats['total'] ?? 0 }}</h5>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3 mb-3">
                            <div class="border rounded-2 p-3">
                                <p class="text-xs text-muted mb-1">Belum Skrining</p>
                                <h5 class="mb-0 text-warning">{{ $stats['belum_skrining'] ?? 0 }}</h5>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3 mb-3">
                            <div class="border rounded-2 p-3">
                                <p class="text-xs text-muted mb-1">Sudah Skrining</p>
                                <h5 class="mb-0 text-success">{{ $stats['sudah_skrining'] ?? 0 }}</h5>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3 mb-3">
                            <div class="border rounded-2 p-3">
                                <p class="text-xs text-muted mb-1">Suspek (&ge; 2 Ya)</p>
                                <h5 class="mb-0 text-danger">{{ $stats['suspect'] ?? 0 }}</h5>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">#</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Nama</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Status Skrining</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Status Pengobatan</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Detail</th>
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
                                        $kader = optional($patient->detail)->supervisor;
                                        $puskesmas = optional($kader?->detail)->supervisor;
                                        $latestScreening = $patient->screenings->first();
                                        $positiveAnswers = collect($latestScreening->answers ?? [])->filter(fn($ans) => $ans === 'ya')->count();
                                        $screeningLabel = $latestScreening
                                            ? ($positiveAnswers >= 2 ? 'Suspek TBC' : ($positiveAnswers === 1 ? 'Perlu Observasi' : 'Negatif'))
                                            : 'Belum Skrining';
                                        $screeningBadge = $latestScreening
                                            ? ($positiveAnswers >= 2 ? 'bg-gradient-danger' : ($positiveAnswers === 1 ? 'bg-gradient-warning text-dark' : 'bg-gradient-success'))
                                            : 'bg-gradient-secondary';
                                        $latestTreatment = $patient->treatments->first();
                                        $treatmentStatus = $latestTreatment
                                            ? ($treatmentStatuses[$latestTreatment->status] ?? ['label' => ucfirst(str_replace('_', ' ', $latestTreatment->status)), 'badge' => 'bg-gradient-info'])
                                            : null;
                                    @endphp
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <h6 class="mb-0 text-sm">{{ $patient->name }}</h6>
                                            <p class="text-xs text-muted mb-0">HP: {{ $patient->phone }} • NIK: {{ $patient->detail->nik ?? '-' }}</p>
                                            @if ($puskesmas)
                                                <p class="text-xs text-muted mb-0">Puskesmas: {{ $puskesmas->name }}</p>
                                            @endif
                                            @if ($kader)
                                                <p class="text-xs text-muted mb-0">Kader: {{ $kader->name }}</p>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge {{ $screeningBadge }}">{{ $screeningLabel }}</span>
                                            @if ($latestScreening)
                                                <p class="text-xs text-muted mb-0">Terakhir: {{ $latestScreening->created_at->format('d M Y') }}</p>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($treatmentStatus)
                                                <span class="badge {{ $treatmentStatus['badge'] }}">{{ $treatmentStatus['label'] }}</span>
                                                <p class="text-xs text-muted mb-0">
                                                    Jadwal: {{ optional($latestTreatment->next_follow_up_at)->format('d M Y') ?? 'Belum' }}
                                                </p>
                                            @else
                                                <span class="text-xs text-muted">Belum masuk daftar berobat</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('pemda.patients.show', $patient) }}" class="btn btn-sm btn-outline-primary">
                                                Detail
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">Belum ada data pasien.</td>
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
