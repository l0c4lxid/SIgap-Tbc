@extends('layouts.soft')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header d-flex flex-wrap gap-3 justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">Data Skrining Kader</h5>
                        <p class="text-sm text-muted mb-0">Rekap seluruh skrining yang diinput oleh kader.</p>
                    </div>
                    <form method="GET" action="{{ route('pemda.screenings') }}" class="d-flex flex-wrap gap-3 align-items-end w-100 justify-content-between" data-auto-submit>
                        <div class="d-flex flex-wrap gap-3 align-items-end">
                            <div class="d-flex flex-column">
                                <label class="text-xxs text-muted mb-1">Tanggal mulai</label>
                                <div class="input-group input-group-sm" style="min-width: 170px;">
                                    <span class="input-group-text bg-white"><i class="fa fa-calendar text-muted"></i></span>
                                    <input type="date" name="from" class="form-control" value="{{ $filters['from'] ?? '' }}">
                                </div>
                            </div>
                            <div class="d-flex flex-column">
                                <label class="text-xxs text-muted mb-1">Tanggal akhir</label>
                                <div class="input-group input-group-sm" style="min-width: 170px;">
                                    <span class="input-group-text bg-white"><i class="fa fa-calendar text-muted"></i></span>
                                    <input type="date" name="to" class="form-control" value="{{ $filters['to'] ?? '' }}">
                                </div>
                            </div>
                            <div class="d-flex flex-column">
                                <span class="text-xxs text-muted mb-1 invisible">Spasi</span>
                                <a href="{{ route('pemda.screenings.export.excel', request()->query()) }}" class="btn btn-sm btn-outline-primary btn-export">
                                    <i class="fa fa-file-excel me-1"></i> Export Excel
                                </a>
                            </div>
                        </div>
                        <div class="d-flex flex-wrap gap-2 align-items-end justify-content-end">
                            <div class="d-flex flex-column">
                                <label class="text-xxs text-muted mb-1">Cari nama / NIK / kader</label>
                                <div class="input-group input-group-sm" style="min-width: 250px;">
                                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                                    <input type="text" name="q" class="form-control" placeholder="Cari nama / NIK / kader" value="{{ $search ?? '' }}">
                                </div>
                            </div>
                            <div class="d-flex flex-column">
                                <span class="text-xxs text-muted mb-1 invisible">Spasi</span>
                                <button type="submit" class="btn btn-sm btn-outline-success">Terapkan</button>
                            </div>
                        </div>
                    </form>
                    <div class="row g-3 mt-3 w-100">
                        <div class="col-6 col-lg-3">
                            <div class="border border-primary rounded-3 p-3 h-100">
                                <p class="text-xs text-primary mb-1">Jumlah Skrining</p>
                                <h5 class="mb-0 text-primary">{{ number_format($summary['screenings'] ?? 0) }}</h5>
                            </div>
                        </div>
                        <div class="col-6 col-lg-3">
                            <div class="border border-success rounded-3 p-3 h-100">
                                <p class="text-xs text-success mb-1">Jumlah RT</p>
                                <h5 class="mb-0 text-success">{{ number_format($summary['rt'] ?? 0) }}</h5>
                            </div>
                        </div>
                        <div class="col-6 col-lg-3">
                            <div class="border border-warning rounded-3 p-3 h-100">
                                <p class="text-xs text-warning mb-1">Jumlah RW</p>
                                <h5 class="mb-0 text-warning">{{ number_format($summary['rw'] ?? 0) }}</h5>
                            </div>
                        </div>
                        <div class="col-6 col-lg-3">
                            <div class="border border-info rounded-3 p-3 h-100">
                                <p class="text-xs text-info mb-1">Jumlah Kelurahan</p>
                                <h5 class="mb-0 text-info">{{ number_format($summary['kelurahan'] ?? 0) }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">No</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Nama</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">NIK</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Kader PJ</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Tanggal</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $firstNumber = method_exists($screenings, 'firstItem') ? $screenings->firstItem() : 1;
                                @endphp
                                @forelse ($screenings as $screening)
                                    <tr>
                                        <td class="text-sm">{{ $firstNumber ? $firstNumber + $loop->index : $loop->iteration }}</td>
                                        <td>
                                            <h6 class="mb-0 text-sm">{{ $screening->patient_name ?? '-' }}</h6>
                                        </td>
                                        <td class="text-sm text-muted">{{ $screening->patient_nik ?? '-' }}</td>
                                        <td>
                                            <span class="text-sm fw-semibold">{{ $screening->kader?->name ?? '-' }}</span>
                                        </td>
                                        <td class="text-sm text-muted">{{ $screening->created_at->format('d M Y H:i') }}</td>
                                        <td>
                                            <a href="{{ route('pemda.screenings.show', $screening) }}" class="btn btn-sm btn-outline-primary">Detail</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">Belum ada skrining tercatat.</td>
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
