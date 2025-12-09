@extends('layouts.soft')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header d-flex flex-wrap gap-3 justify-content-between align-items-center">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <a href="{{ route('puskesmas.kelurahan') }}" class="text-sm text-muted"><i class="fa fa-arrow-left me-1"></i>Kembali</a>
                            <span class="text-muted">/</span>
                            <span class="text-sm text-muted">Kelurahan</span>
                        </div>
                        <h5 class="mb-0">{{ $kelurahan->name }}</h5>
                        <p class="text-sm text-muted mb-0">Data pasien yang alamatnya berada di wilayah kelurahan ini.</p>
                    </div>
                    <form method="GET" action="{{ route('puskesmas.kelurahan.show', $kelurahan) }}" class="d-flex flex-wrap gap-2 align-items-center" data-auto-submit>
                        <div class="input-group input-group-sm sigap-search" style="min-width: 230px;">
                            <span class="input-group-text"><i class="fa fa-search"></i></span>
                            <input type="text" name="q" class="form-control" placeholder="Cari nama / alamat pasien" value="{{ $search ?? '' }}">
                        </div>
                        <button type="submit" class="btn btn-sm btn-outline-primary">Cari</button>
                    </form>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-4">
                        <div class="col-md-4 col-sm-6">
                            <div class="border rounded p-3 h-100">
                                <p class="text-xs text-muted mb-1">Nama Kelurahan</p>
                                <h6 class="mb-0">{{ $kelurahan->name }}</h6>
                                <p class="text-xs text-muted mb-0">{{ optional($kelurahan->detail)->organization ?? '-' }}</p>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-6">
                            <div class="border rounded p-3 h-100">
                                <p class="text-xs text-muted mb-1">Alamat Kelurahan</p>
                                <p class="mb-0 text-sm">{{ optional($kelurahan->detail)->address ?? '-' }}</p>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-6">
                            <div class="border rounded p-3 h-100 bg-light">
                                <p class="text-xs text-muted mb-1">Total Pasien Sesuai Alamat</p>
                                <h5 class="mb-0">{{ method_exists($patients, 'total') ? number_format($patients->total()) : $patients->count() }}</h5>
                                <p class="text-xs text-muted mb-0">Alamat mengandung: {{ $kelurahan->name }}</p>
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
                                    <th>Kader</th>
                                    <th>Alamat</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $firstNumber = method_exists($patients, 'firstItem') ? $patients->firstItem() : 1;
                                @endphp
                                @forelse ($patients as $patient)
                                    @php
                                        $kader = optional($patient->detail)->supervisor;
                                    @endphp
                                    <tr>
                                        <td>{{ $firstNumber ? $firstNumber + $loop->index : $loop->iteration }}</td>
                                        <td>
                                            <h6 class="mb-0 text-sm">{{ $patient->name }}</h6>
                                            <p class="text-xs text-muted mb-0">NIK: {{ $patient->detail->nik ?? '-' }}</p>
                                        </td>
                                        <td>
                                            <p class="text-xs text-muted mb-0">HP: {{ $patient->phone }}</p>
                                        </td>
                                        <td>
                                            <p class="text-xs text-muted mb-0">{{ $kader->name ?? '-' }}</p>
                                            @if ($kader)
                                                <p class="text-xxs text-muted mb-0">Kontak: {{ $kader->phone }}</p>
                                            @endif
                                        </td>
                                        <td>
                                            <p class="text-xs mb-0">{{ $patient->detail->address ?? '-' }}</p>
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('puskesmas.patient.family', $patient) }}" class="btn btn-sm btn-outline-primary">Detail</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">Belum ada pasien dengan alamat yang sesuai.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @php
                        $hasPagination = method_exists($patients, 'firstItem');
                        $from = $hasPagination ? $patients->firstItem() : ($patients->count() ? 1 : 0);
                        $to = $hasPagination ? $patients->lastItem() : $patients->count();
                        $total = $hasPagination ? $patients->total() : $patients->count();
                    @endphp
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3">
                        <p class="text-sm text-muted mb-0">
                            Menampilkan <span class="fw-semibold">{{ $from }}</span> - <span class="fw-semibold">{{ $to }}</span> dari <span class="fw-semibold">{{ $total }}</span> pasien
                        </p>
                        @if ($hasPagination)
                            <div class="mb-0">
                                {{ $patients->withQueryString()->onEachSide(1)->links('pagination::bootstrap-5') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
