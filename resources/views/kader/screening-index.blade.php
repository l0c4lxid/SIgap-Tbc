@extends('layouts.soft')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">Daftar Skrining Pasien</h5>
                        <p class="text-sm text-muted mb-0">Pilih pasien untuk melakukan skrining lanjutan.</p>
                    </div>
                    <form method="GET" action="{{ route('kader.screening.index') }}" class="d-flex flex-wrap gap-2 align-items-center">
                        <div class="input-group input-group-sm" style="min-width: 240px;">
                            <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" name="q" class="form-control" placeholder="Cari nama / nomor HP" value="{{ $search ?? '' }}">
                        </div>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">Semua status</option>
                            <option value="belum" @selected($status === 'belum')>Belum skrining</option>
                            <option value="sudah" @selected($status === 'sudah')>Sudah skrining</option>
                        </select>
                        <button type="submit" class="btn btn-sm btn-outline-primary">Terapkan</button>
                    </form>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th>Pasien</th>
                                    <th>Status Skrining</th>
                                    <th>Terakhir Skrining</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($patients as $patient)
                                    @php
                                        $latest = $patient->screenings->first();
                                        $positive = collect($latest->answers ?? [])->filter(fn ($ans) => $ans === 'ya')->count();
                                        $statusBadge = 'bg-gradient-secondary';
                                        $label = 'Belum skrining';
                                        if ($latest) {
                                            $label = $positive >= 2 ? 'Suspek TBC' : ($positive === 1 ? 'Perlu Observasi' : 'Tidak Ada Gejala');
                                            $statusBadge = $positive >= 2 ? 'bg-gradient-danger' : ($positive === 1 ? 'bg-gradient-warning text-dark' : 'bg-gradient-success');
                                        }
                                    @endphp
                                    <tr>
                                        <td>
                                            <h6 class="mb-0 text-sm">{{ $patient->name }}</h6>
                                            <p class="text-xs text-muted mb-0">{{ $patient->phone }}</p>
                                        </td>
                                        <td>
                                            <span class="badge {{ $statusBadge }}">{{ $label }}</span>
                                        </td>
                                        <td>
                                            @if ($latest)
                                                <span class="text-xs text-muted">{{ $latest->created_at->format('d M Y H:i') }}</span>
                                            @else
                                                <span class="text-xs text-muted">Belum pernah</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($latest)
                                                <button class="btn btn-sm btn-secondary" disabled>Sudah Skrining</button>
                                            @else
                                                <a href="{{ route('kader.patients.screening', $patient) }}" class="btn btn-sm btn-success">Mulai Skrining</a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">Belum ada pasien binaan.</td>
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
