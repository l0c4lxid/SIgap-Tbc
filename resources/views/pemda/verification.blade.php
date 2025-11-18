@extends('layouts.soft')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex flex-wrap gap-3 justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0">Verifikasi Pengguna SIGAP TBC</h5>
                            <p class="text-sm text-muted mb-0">Kelola status aktif pengguna sesuai kebutuhan wilayah.</p>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <form method="GET" action="{{ route('pemda.verification') }}" class="d-flex gap-2">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-white"><i class="fa fa-search text-muted"></i></span>
                                    <input type="text" name="q" class="form-control" placeholder="Cari nama / nomor HP / instansi" value="{{ $search ?? '' }}">
                                </div>
                                <button type="submit" class="btn btn-sm btn-outline-primary">Cari</button>
                            </form>
                            <form method="POST" action="{{ route('pemda.verification.bulk-status') }}" class="d-flex align-items-center gap-2" data-confirm="Terapkan perubahan status massal?" data-confirm-text="Ya, terapkan">
                                @csrf
                                <select name="status" class="form-select form-select-sm">
                                    <option value="active">Aktifkan Semua</option>
                                    <option value="inactive">Nonaktifkan Semua</option>
                                </select>
                                <button type="submit" class="btn btn-sm btn-dark">Terapkan</button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Nama</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Peran</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Kontak</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Dibuat</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($records as $user)
                                    <tr>
                                        <td>
                                            <div class="d-flex px-2 py-1">
                                                <div><i class="fa-solid fa-user text-primary me-3"></i></div>
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h6 class="mb-0 text-sm">{{ $user->name }}</h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <p class="text-xs font-weight-bold mb-0">{{ $user->role->label() }}</p>
                                            <p class="text-xs text-secondary mb-0">{{ $user->detail->organization ?? '-' }}</p>
                                        </td>
                                        <td>
                                            <p class="text-xs mb-0">HP login: {{ $user->phone }}</p>
                                            <p class="text-xs text-secondary mb-0">{{ $user->detail->address ?? 'Alamat belum diisi' }}</p>
                                            @if ($user->detail?->nik)
                                                <p class="text-xs text-secondary mb-0">NIK: {{ $user->detail->nik }}</p>
                                            @endif
                                        </td>
                                        <td class="align-middle text-center">
                                            <span class="text-secondary text-xs font-weight-bold">
                                                {{ $user->created_at->format('d M Y H:i') }}
                                            </span>
                                        </td>
                                        <td class="align-middle text-center">
                                            <span class="badge badge-sm {{ $user->is_active ? 'bg-gradient-success' : 'bg-gradient-warning' }}">
                                                {{ $user->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                            </span>
                                        </td>
                                        <td class="align-middle text-center">
                                            <form method="POST" action="{{ route('pemda.verification.status', $user) }}" class="d-inline-block" data-confirm="Ubah status {{ $user->name }}?" data-confirm-text="Ya, ubah">
                                                @csrf
                                                <input type="hidden" name="status" value="{{ $user->is_active ? 'inactive' : 'active' }}">
                                                <div class="d-inline-flex align-items-center bg-light rounded-pill px-3 py-1 gap-2">
                                                    <span class="text-xs text-muted {{ $user->is_active ? '' : 'fw-bold text-danger' }}">Nonaktif</span>
                                                    <div class="form-check form-switch m-0">
                                                        <input class="form-check-input" type="checkbox" onchange="this.form.submit()" {{ $user->is_active ? 'checked' : '' }}>
                                                    </div>
                                                    <span class="text-xs text-muted {{ $user->is_active ? 'fw-bold text-success' : '' }}">Aktif</span>
                                                </div>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">Belum ada data pengguna.</td>
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

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            @if (session('status'))
                Swal.fire({
                    icon: 'success',
                    title: 'Sukses',
                    text: @json(session('status')),
                });
            @endif
        });
    </script>
@endpush
