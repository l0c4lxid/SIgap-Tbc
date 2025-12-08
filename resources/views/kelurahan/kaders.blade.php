@extends('layouts.soft')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header d-flex flex-wrap gap-3 justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">Kader di Wilayah Kelurahan</h5>
                        <p class="text-sm text-muted mb-0">Daftar kader yang ditugaskan oleh puskesmas mitra kelurahan ini.</p>
                    </div>
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <a href="{{ route('kelurahan.kaders.export.excel', request()->only('q')) }}" class="btn btn-sm btn-success">
                            <i class="fa fa-file-excel me-1"></i> Export Excel
                        </a>
                        <form method="GET" action="{{ route('kelurahan.kaders') }}" class="d-flex gap-2">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white"><i class="fa fa-search text-muted"></i></span>
                                <input type="text" name="q" class="form-control" placeholder="Cari nama / nomor HP / area" value="{{ $search ?? '' }}">
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
                                    <th>#</th>
                                    <th>Kader</th>
                                    <th>Puskesmas Induk</th>
                                    <th>Area</th>
                                    <th>Status</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $firstNumber = $kaders->firstItem();
                                @endphp
                                @forelse ($kaders as $kader)
                                    <tr>
                                        <td>{{ $firstNumber ? $firstNumber + $loop->index : $loop->iteration }}</td>
                                        <td>
                                            <h6 class="mb-0 text-sm">
                                                <a href="{{ route('kelurahan.kaders.show', $kader) }}" class="text-decoration-none">
                                                    {{ $kader->name }}
                                                </a>
                                            </h6>
                                            <p class="text-xs text-muted mb-0">HP: {{ $kader->phone }}</p>
                                        </td>
                                        <td>
                                            <span class="text-xs text-muted">{{ $kader->detail->supervisor->name ?? '-' }}</span>
                                        </td>
                                        <td>
                                            <span class="text-xs text-muted">{{ $kader->detail->area ?? '-' }}</span>
                                        </td>
                                        <td>
                                            <span class="badge {{ $kader->is_active ? 'bg-gradient-success' : 'bg-gradient-warning text-dark' }}">
                                                {{ $kader->is_active ? 'Aktif' : 'Nonaktif' }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('kelurahan.kaders.show', $kader) }}" class="btn btn-sm btn-outline-secondary me-1">
                                                Detail
                                            </a>
                                            <form method="POST" action="{{ route('kelurahan.kaders.status', $kader) }}" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="status" value="{{ $kader->is_active ? 'inactive' : 'active' }}">
                                                <button type="submit" class="btn btn-sm {{ $kader->is_active ? 'btn-outline-danger' : 'btn-outline-success' }}">
                                                    {{ $kader->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">Belum ada kader terdata untuk kelurahan ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @php
                        $hasPagination = method_exists($kaders, 'firstItem');
                        $from = $hasPagination ? $kaders->firstItem() : ($kaders->count() ? 1 : 0);
                        $to = $hasPagination ? $kaders->lastItem() : $kaders->count();
                        $total = $hasPagination ? $kaders->total() : $kaders->count();
                    @endphp
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3">
                        <p class="text-sm text-muted mb-0">
                            Menampilkan <span class="fw-semibold">{{ $from }}</span> - <span class="fw-semibold">{{ $to }}</span> dari <span class="fw-semibold">{{ $total }}</span> kader
                        </p>
                        @if ($hasPagination)
                            <div class="mb-0">
                                {{ $kaders->withQueryString()->onEachSide(1)->links('pagination::bootstrap-5') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
