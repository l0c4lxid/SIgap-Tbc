@extends('layouts.soft')

@section('subjudul', 'Kelurahan binaan puskesmas')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header d-flex flex-wrap gap-3 justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">Kelurahan Binaan</h5>
                        <p class="text-sm text-muted mb-0">Daftar kelurahan yang terhubung ke puskesmas ini.</p>
                    </div>
                    <form method="GET" action="{{ route('puskesmas.kelurahan') }}" class="d-flex flex-wrap gap-2 align-items-center" data-auto-submit>
                        <div class="input-group input-group-sm" style="min-width: 220px;">
                            <span class="input-group-text bg-white"><i class="fa fa-search text-muted"></i></span>
                            <input type="text" name="q" class="form-control" placeholder="Cari nama / alamat" value="{{ $search ?? '' }}">
                        </div>
                        <button type="submit" class="btn btn-sm btn-outline-primary">Terapkan</button>
                    </form>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <div class="border rounded p-3 h-100 bg-light">
                                <p class="text-xs text-muted mb-1">Total kelurahan</p>
                                <h5 class="mb-0">{{ number_format($stats['total']) }}</h5>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded p-3 h-100">
                                <p class="text-xs text-muted mb-1">Aktif</p>
                                <h5 class="mb-0 text-success">{{ number_format($stats['active']) }}</h5>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded p-3 h-100">
                                <p class="text-xs text-muted mb-1">Menunggu persetujuan</p>
                                <h5 class="mb-0 text-warning">{{ number_format($stats['pending']) }}</h5>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">#</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Nama Kelurahan</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Alamat</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Status</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Ditambahkan</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2 text-center">Kelola</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $firstNumber = method_exists($kelurahan, 'firstItem') ? $kelurahan->firstItem() : null;
                                @endphp
                                @forelse ($kelurahan as $row)
                                    <tr>
                                        <td>{{ $firstNumber ? $firstNumber + $loop->index : $loop->iteration }}</td>
                                        <td>
                                            <h6 class="mb-0 text-sm">
                                                <a href="{{ route('puskesmas.kelurahan.show', $row) }}" class="text-decoration-none">
                                                    {{ $row->name }}
                                                </a>
                                            </h6>
                                            <p class="text-xs text-muted mb-0">{{ optional($row->detail)->organization ?? '-' }}</p>
                                        </td>
                                        <td>
                                            <p class="text-xs mb-0">{{ optional($row->detail)->address ?? '-' }}</p>
                                        </td>
                                        <td>
                                            @php
                                                $isActive = optional($row->detail)->supervisor_id === auth()->id();
                                                $isPending = optional($row->detail)->pending_supervisor_id === auth()->id();
                                            @endphp
                                            @if ($isActive)
                                                <span class="badge bg-gradient-success">Aktif</span>
                                            @elseif($isPending)
                                                <span class="badge bg-gradient-warning text-dark">Menunggu persetujuan</span>
                                            @else
                                                <span class="badge bg-gradient-secondary">Tidak aktif</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="text-xs text-muted">{{ $row->created_at?->format('d M Y') }}</span>
                                        </td>
                                        <td class="text-center">
                                            @if ($isActive)
                                                <form method="POST" action="{{ route('puskesmas.kelurahan.destroy', $row) }}" class="d-inline" data-confirm="Lepas kemitraan kelurahan ini?" data-confirm-text="Ya, lepas">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-warning">Lepas</button>
                                                </form>
                                            @elseif($isPending)
                                                <form method="POST" action="{{ route('puskesmas.kelurahan.approve', $row) }}" class="d-inline ms-1">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-success">Setujui</button>
                                                </form>
                                            @else
                                                <span class="text-xs text-muted">Tidak terhubung</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">Belum ada kelurahan terhubung.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @php
                        $hasPagination = method_exists($kelurahan, 'firstItem');
                        $from = $hasPagination ? $kelurahan->firstItem() : ($kelurahan->count() ? 1 : 0);
                        $to = $hasPagination ? $kelurahan->lastItem() : $kelurahan->count();
                        $total = $hasPagination ? $kelurahan->total() : $kelurahan->count();
                    @endphp
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3">
                        <p class="text-sm text-muted mb-0">
                            Menampilkan <span class="fw-semibold">{{ $from }}</span> - <span class="fw-semibold">{{ $to }}</span> dari <span class="fw-semibold">{{ $total }}</span> kelurahan
                        </p>
                        @if ($hasPagination)
                            <div class="mb-0">
                                {{ $kelurahan->withQueryString()->onEachSide(1)->links('pagination::bootstrap-5') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
