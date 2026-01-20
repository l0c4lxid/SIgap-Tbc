@extends('layouts.soft')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header d-flex flex-wrap gap-3 justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">Data Kader Mitra</h5>
                        <p class="text-sm text-muted mb-0">Daftar kader yang bekerja sama dengan puskesmas ini.</p>
                    </div>
                    <div class="d-flex flex-column align-items-end gap-2 ms-auto" style="min-width: 300px; max-width: 420px;">
                        <form method="GET" action="{{ route('puskesmas.kaders') }}" class="d-flex flex-wrap align-items-center gap-2 w-100 justify-content-end" data-auto-submit>
                            <div class="input-group input-group-sm flex-grow-1" style="min-width: 240px;">
                                <span class="input-group-text bg-white"><i class="fa fa-search text-muted"></i></span>
                                <input type="text" name="q" class="form-control" placeholder="Cari nama / nomor HP / catatan" value="{{ $search ?? '' }}">
                            </div>
                            @if ($search)
                                <a href="{{ route('puskesmas.kaders') }}" class="btn btn-sm btn-light px-3">Reset</a>
                            @endif
                        </form>
                        <a href="{{ route('puskesmas.kaders.export.excel', request()->query()) }}" class="btn btn-sm btn-outline-success btn-export align-self-end">
                            <i class="fa fa-file-excel me-1"></i> Export Excel
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">#</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Nama Kader</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Kontak</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Catatan</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Didaftarkan</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $firstNumber = method_exists($kaders, 'firstItem') ? $kaders->firstItem() : null;
                                @endphp
                                @forelse ($kaders as $kader)
                                    <tr>
                                        <td>{{ $firstNumber ? $firstNumber + $loop->index : $loop->iteration }}</td>
                                        <td>
                                            <h6 class="mb-0 text-sm">
                                                <a href="{{ route('puskesmas.kaders.show', $kader) }}" class="text-decoration-none">
                                                    {{ $kader->name }}
                                                </a>
                                            </h6>
                                            <p class="text-xs text-muted mb-0">{{ $kader->detail->organization ?? 'Kader' }}</p>
                                        </td>
                                        <td>
                                            <p class="text-xs mb-0">Nomor HP: {{ $kader->phone }}</p>
                                        </td>
                                        <td>
                                            <p class="text-xs mb-0">{{ $kader->detail->notes ?? '-' }}</p>
                                        </td>
                                        <td class="text-center">
                                            @if ($kader->is_active)
                                                <span class="badge bg-gradient-success text-white">Aktif</span>
                                            @else
                                                <span class="badge bg-gradient-warning text-dark">Tidak Aktif</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="text-xs text-muted">{{ $kader->created_at->format('d M Y') }}</span>
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('puskesmas.kaders.show', $kader) }}" class="btn btn-sm btn-outline-primary px-3">
                                                Detail & Kelola
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">Belum ada kader mitra.</td>
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
