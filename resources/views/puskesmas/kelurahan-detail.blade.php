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
                        <p class="text-sm text-muted mb-0">Data skrining dengan alamat pasien di wilayah kelurahan ini.</p>
                    </div>
                    <form method="GET" action="{{ route('puskesmas.kelurahan.show', $kelurahan) }}" class="d-flex flex-wrap gap-2 align-items-center" data-auto-submit>
                        <div class="input-group input-group-sm sigap-search" style="min-width: 230px;">
                            <span class="input-group-text"><i class="fa fa-search"></i></span>
                            <input type="text" name="q" class="form-control" placeholder="Cari nama / alamat / RT/RW / kelurahan" value="{{ $search ?? '' }}">
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
                                <p class="text-xs text-muted mb-1">Total Skrining Sesuai Alamat</p>
                                <h5 class="mb-0">{{ method_exists($screenings, 'total') ? number_format($screenings->total()) : $screenings->count() }}</h5>
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
                                    <th>Tanggal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $firstNumber = method_exists($screenings, 'firstItem') ? $screenings->firstItem() : 1;
                                @endphp
                                @forelse ($screenings as $screening)
                                    <tr>
                                        <td>{{ $firstNumber ? $firstNumber + $loop->index : $loop->iteration }}</td>
                                        <td>
                                            <h6 class="mb-0 text-sm">{{ $screening->patient_name ?? '-' }}</h6>
                                            <p class="text-xs text-muted mb-0">NIK: {{ $screening->patient_nik ?? '-' }}</p>
                                        </td>
                                        <td>
                                            <p class="text-xs text-muted mb-0">HP: {{ $screening->patient_phone ?? '-' }}</p>
                                        </td>
                                        <td>
                                            <p class="text-xs text-muted mb-0">{{ $screening->kader?->name ?? '-' }}</p>
                                            @if ($screening->kader)
                                                <p class="text-xxs text-muted mb-0">Kontak: {{ $screening->kader->phone ?? '-' }}</p>
                                            @endif
                                        </td>
                                        <td>
                                            <p class="text-xs mb-0">{{ $screening->patient_address_domisili ?? $screening->patient_address ?? '-' }}</p>
                                            <p class="text-xxs text-muted mb-0">RT/RW {{ $screening->patient_address_rt ?? '-' }}/{{ $screening->patient_address_rw ?? '-' }} • {{ $screening->patient_address_kelurahan ?? '-' }}</p>
                                        </td>
                                        <td>
                                            <p class="text-xs text-muted mb-0">{{ $screening->created_at->format('d M Y H:i') }}</p>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">Belum ada skrining dengan alamat yang sesuai.</td>
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
