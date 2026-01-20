@extends('layouts.soft')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">Daftar Skrining Pasien</h5>
                        <p class="text-sm text-muted mb-0">Kelola laporan skrining yang Anda catat di lapangan.</p>
                    </div>
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <a href="{{ route('kader.screening.create') }}" class="btn btn-sm btn-primary">
                            <i class="fa-solid fa-plus me-1"></i>Tambah Skrining
                        </a>
                        <a href="{{ route('kader.screening.export.excel') }}" class="btn btn-sm btn-outline-success">
                            <i class="fa-solid fa-file-excel me-1"></i>Export Excel
                        </a>
                        <form method="GET" action="{{ route('kader.screening.index') }}" class="d-flex flex-wrap gap-2 align-items-center" data-auto-submit>
                            <div class="input-group input-group-sm" style="min-width: 240px;">
                                <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                                <input type="text" name="q" class="form-control" placeholder="Cari nama / NIK / HP / RT/RW / kelurahan" value="{{ $search ?? '' }}">
                            </div>
                            <button type="submit" class="btn btn-sm btn-outline-primary">Cari</button>
                        </form>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width:60px;">No.</th>
                                    <th>Pasien</th>
                                    <th>NIK</th>
                                    <th class="text-center">Jawaban Ya</th>
                                    <th class="text-center">Waktu</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $firstNumber = method_exists($screenings, 'firstItem') ? $screenings->firstItem() : null;
                                @endphp
                                @forelse ($screenings as $screening)
                                    @php
                                        $positive = collect($screening->answers ?? [])
                                            ->filter(fn ($ans, $key) => str_starts_with((string) $key, 'gejala_') && $ans === 'ya')
                                            ->count();
                                    @endphp
                                    <tr>
                                        <td class="text-center fw-semibold">{{ $firstNumber ? $firstNumber + $loop->index : $loop->iteration }}</td>
                                        <td>
                                            <h6 class="mb-0 text-sm">
                                                <a href="{{ route('kader.screening.show', $screening) }}" class="text-decoration-none">
                                                    {{ $screening->patient_name ?? '-' }}
                                                </a>
                                            </h6>
                                        </td>
                                        <td class="text-sm text-muted">{{ $screening->patient_nik ?? '-' }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-gradient-{{ $positive ? 'danger' : 'success' }}">{{ $positive }}</span>
                                        </td>
                                        <td class="text-center text-sm text-muted">
                                            {{ $screening->created_at->format('d M Y H:i') }}
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('kader.screening.show', $screening) }}" class="btn btn-sm btn-outline-primary">
                                                Detail
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted">Belum ada skrining tercatat.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @php
                        $hasPagination = method_exists($screenings, 'firstItem');
                        $from = $hasPagination ? $screenings->firstItem() : ($screenings->count() ? 1 : 0);
                        $to = $hasPagination ? $screenings->lastItem() : $screenings->count();
                        $total = $hasPagination ? $screenings->total() : $screenings->count();
                    @endphp
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3">
                        <p class="text-sm text-muted mb-0">
                            Menampilkan <span class="fw-semibold">{{ $from }}</span> - <span class="fw-semibold">{{ $to }}</span> dari <span class="fw-semibold">{{ $total }}</span> skrining
                        </p>
                        @if ($hasPagination)
                            <div class="mb-0">
                                {{ $screenings->withQueryString()->onEachSide(1)->links('pagination::bootstrap-5') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
