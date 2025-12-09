@extends('layouts.soft')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header d-flex flex-wrap gap-3 justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">Data Pasien Skrining</h5>
                        <p class="text-sm text-muted mb-0">Kelola pasien binaan dan awasi progres skrining mereka.</p>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('kader.patients.create') }}" class="btn btn-sm btn-primary">
                            <i class="fa fa-plus me-1"></i> Tambah Pasien
                        </a>
                        <form method="GET" action="{{ route('kader.patients') }}" class="d-flex gap-2" data-auto-submit>
                            <div class="input-group input-group-sm sigap-search">
                                <span class="input-group-text"><i class="fa fa-search"></i></span>
                                <input type="text" name="q" class="form-control" placeholder="Cari nama, nomor HP, atau alamat" value="{{ $search ?? '' }}">
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
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">#</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Nama</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Kontak</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Alamat</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">NIK</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Didaftarkan</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $firstNumber = method_exists($patients, 'firstItem') ? $patients->firstItem() : null;
                                @endphp
                                @forelse ($patients as $patient)
                                    <tr>
                                        <td>{{ $firstNumber ? $firstNumber + $loop->index : $loop->iteration }}</td>
                                        <td>
                                            <h6 class="mb-0 text-sm">{{ $patient->name }}</h6>
                                            <p class="text-xs text-muted mb-1">{{ $patient->detail->organization ?? 'Pasien' }}</p>
                                            @if ($patient->is_active)
                                                <span class="badge bg-gradient-success text-white">Akun aktif</span>
                                            @else
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="badge bg-gradient-warning text-dark">Menunggu verifikasi</span>
                                                    <div class="form-check form-switch m-0">
                                                        <input class="form-check-input" type="checkbox" disabled>
                                                    </div>
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <p class="text-xs mb-0">Nomor HP: {{ $patient->phone }}</p>
                                        </td>
                                        <td>
                                            <p class="text-xs mb-0">{{ $patient->detail->address ?? '-' }}</p>
                                        </td>
                                        <td class="text-center">
                                            <span class="text-xs font-weight-bold">{{ $patient->detail->nik ?? '-' }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="text-xs text-muted">{{ $patient->created_at->format('d M Y') }}</span>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex flex-nowrap align-items-center justify-content-center gap-1">
                                                <a href="{{ route('kader.patients.show', $patient) }}" class="btn btn-sm btn-outline-secondary px-2">Detail</a>
                                                @if ($patient->screenings->isEmpty())
                                                    <a href="{{ route('kader.patients.screening', $patient) }}" class="btn btn-sm btn-success text-white px-2">Skrining</a>
                                                @else
                                                    <span class="btn btn-sm btn-success text-white px-2" style="pointer-events: none;">Sudah</span>
                                                @endif
                                                <form method="POST" action="{{ route('kader.patients.status', $patient) }}" data-confirm="Ubah status akun {{ $patient->name }}?" data-confirm-text="Ya, ubah" class="d-inline m-0">
                                                    @csrf
                                                    <input type="hidden" name="status" value="{{ $patient->is_active ? 'inactive' : 'active' }}">
                                                    <button type="submit" class="btn btn-sm {{ $patient->is_active ? 'btn-outline-danger' : 'btn-outline-success' }} px-2">
                                                        {{ $patient->is_active ? 'Nonaktif' : 'Aktifkan' }}
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">Belum ada pasien binaan.</td>
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
