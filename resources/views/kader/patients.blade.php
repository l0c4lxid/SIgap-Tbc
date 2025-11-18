@extends('layouts.soft')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header d-flex flex-wrap gap-3 justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">Data Pasien Binaan</h5>
                        <p class="text-sm text-muted mb-0">Menampilkan warga yang terdaftar dengan kader ini.</p>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('kader.patients.create') }}" class="btn btn-sm btn-primary">
                            <i class="fa fa-plus me-1"></i> Tambah Pasien
                        </a>
                        <form method="GET" action="{{ route('kader.patients') }}" class="d-flex gap-2">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white"><i class="fa fa-search text-muted"></i></span>
                                <input type="text" name="q" class="form-control" placeholder="Cari nama / nomor HP / alamat" value="{{ $search ?? '' }}">
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
                                @forelse ($patients as $patient)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
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
                                            <div class="d-flex flex-column gap-2 align-items-center">
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="{{ route('kader.patients.show', $patient) }}" class="btn btn-outline-secondary">Detail</a>
                                                    @if ($patient->screenings->isEmpty())
                                                        <a href="{{ route('kader.patients.screening', $patient) }}" class="btn btn-success text-white">Skrining</a>
                                                    @else
                                                        <span class="btn btn-success text-white" style="pointer-events: none;">Sudah skrining</span>
                                                    @endif
                                                </div>
                                                <form method="POST" action="{{ route('kader.patients.status', $patient) }}" data-confirm="Ubah status akun {{ $patient->name }}?" data-confirm-text="Ya, ubah" class="d-inline-block">
                                                    @csrf
                                                    <input type="hidden" name="status" value="{{ $patient->is_active ? 'inactive' : 'active' }}">
                                                    <div class="badge rounded-pill bg-light border d-inline-flex align-items-center gap-2 px-3 py-2">
                                                        <span class="text-xs text-muted {{ $patient->is_active ? '' : 'fw-bold text-danger' }}">Nonaktif</span>
                                                        <div class="form-check form-switch m-0">
                                                            <input class="form-check-input" type="checkbox" onchange="this.form.submit()" {{ $patient->is_active ? 'checked' : '' }}>
                                                        </div>
                                                        <span class="text-xs text-muted {{ $patient->is_active ? 'fw-bold text-success' : '' }}">Aktif</span>
                                                    </div>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">Belum ada pasien binaan.</td>
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
