@extends('layouts.soft')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex flex-wrap gap-3 justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0">Verifikasi Pengguna SITUBA</h5>
                            <p class="text-sm text-muted mb-0">Kelola status aktif pengguna sesuai kebutuhan wilayah.</p>
                        </div>
                        <div class="d-flex flex-column flex-lg-row gap-2 w-100 align-items-stretch justify-content-end">
                            <form method="GET" action="{{ route('pemda.verification') }}" class="d-flex flex-wrap align-items-center gap-2 flex-grow-1 justify-content-end" data-auto-submit>
                                <div style="min-width: 180px; max-width: 220px;">
                                    <select name="role" class="form-select form-select-sm w-100">
                                        <option value="">Semua Peran</option>
                                        @foreach ($roleOptions as $option)
                                            <option value="{{ $option['value'] }}" {{ $selectedRole === $option['value'] ? 'selected' : '' }}>
                                                {{ $option['label'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="input-group input-group-sm flex-grow-1" style="min-width: 260px; max-width: 460px;">
                                    <span class="input-group-text bg-white"><i class="fa fa-search text-muted"></i></span>
                                    <input type="text" name="q" class="form-control" placeholder="Cari nama / nomor HP / instansi" value="{{ $search ?? '' }}">
                                </div>
                                <button type="submit" class="btn btn-group-lg btn-primary d-flex align-items-center ms-2 mt-3" style="padding:.375rem .75rem;">
                                    {{-- <i class="fa fa-search"></i> --}}
                                    <span class="ms-1">Cari</span>
                                </button>
                            </form>
                            <form method="POST" action="{{ route('pemda.verification.bulk-status') }}" class="d-flex align-items-center gap-2" data-confirm="Terapkan perubahan status massal?" data-confirm-text="Ya, terapkan">
                                @csrf
                                <input type="hidden" name="role" value="{{ $selectedRole }}">
                                <select name="status" class="form-select form-select-sm" style="min-width: 160px;">
                                    <option value="active">Aktifkan Semua</option>
                                    <option value="inactive">Nonaktifkan Semua</option>
                                </select>
                                <button type="submit" class="btn btn-sm btn-dark mt-3 px-3 d-flex align-items-center gap-1"><i class="fa fa-bolt"></i><span>Terapkan</span></button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">No</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Nama</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Peran</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Dibuat</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($records as $user)
                                    <tr>
                                        <td class="align-middle">
                                            <span class="text-xs text-muted">
                                                {{ ($records->firstItem() ?? 0) + $loop->index }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('pemda.verification.show', $user) }}" class="d-flex px-2 py-1 text-body" title="Detail user">
                                                <div><i class="fa-solid fa-user text-primary me-3"></i></div>
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h6 class="mb-0 text-sm">{{ $user->name }}</h6>
                                                </div>
                                            </a>
                                        </td>
                                        <td>
                                            <p class="text-xs font-weight-bold mb-0">{{ $user->role->label() }}</p>
                                            <p class="text-xs text-secondary mb-0">{{ $user->detail->organization ?? '-' }}</p>
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
                    @php
                        $hasPagination = method_exists($records, 'firstItem');
                        $from = $hasPagination ? $records->firstItem() : ($records->count() ? 1 : 0);
                        $to = $hasPagination ? $records->lastItem() : $records->count();
                        $total = $hasPagination ? $records->total() : $records->count();
                    @endphp
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3">
                        <p class="text-sm text-muted mb-0">
                            Menampilkan <span class="fw-semibold">{{ $from }}</span> - <span class="fw-semibold">{{ $to }}</span> dari <span class="fw-semibold">{{ $total }}</span> pengguna
                        </p>
                        @if ($hasPagination)
                            <div class="mb-0">
                                {{ $records->withQueryString()->onEachSide(1)->links('pagination::bootstrap-5') }}
                            </div>
                        @endif
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
