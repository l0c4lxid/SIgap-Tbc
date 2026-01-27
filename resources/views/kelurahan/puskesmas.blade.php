@extends('layouts.soft')

@section('subjudul', 'Daftar puskesmas mitra')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header d-flex flex-wrap gap-3 justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">Puskesmas Mitra</h5>
                        <p class="text-sm text-muted mb-0">Pilih puskesmas induk jika belum terhubung. Hubungan aktif ditandai pada kartu.</p>
                    </div>
                    <form method="GET" action="{{ route('kelurahan.puskesmas') }}" class="d-flex gap-2 kelurahan-puskesmas__search" data-auto-submit>
                        <div class="input-group input-group-sm kelurahan-puskesmas__search-input">
                            <span class="input-group-text bg-white"><i class="fa fa-search text-muted"></i></span>
                            <input type="text" name="q" class="form-control" placeholder="Cari nama / alamat" value="{{ $search ?? '' }}">
                        </div>
                        <button type="submit" class="btn btn-sm btn-outline-primary kelurahan-puskesmas__search-btn">Cari</button>
                    </form>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">#</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Nama Puskesmas</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Kontak</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Alamat</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Status</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $firstNumber = $puskesmasList->firstItem();
                                @endphp
                                @forelse ($puskesmasList as $puskesmas)
                                    <tr>
                                        <td>{{ $firstNumber ? $firstNumber + $loop->index : $loop->iteration }}</td>
                                        <td>
                                            <h6 class="mb-0 text-sm">{{ $puskesmas->name }}</h6>
                                            <p class="text-xs text-muted mb-0">{{ $puskesmas->detail->organization ?? 'Puskesmas' }}</p>
                                        </td>
                                        <td>
                                            <p class="text-xs mb-0">HP Admin: {{ $puskesmas->phone }}</p>
                                        </td>
                                        <td>
                                            <p class="text-xs mb-0">{{ $puskesmas->detail->address ?? '-' }}</p>
                                        </td>
                                        <td>
                                            @php
                                                $isActive = $currentPuskesmasId === $puskesmas->id;
                                                $isPending = optional($kelurahan->detail)->pending_supervisor_id === $puskesmas->id;
                                            @endphp
                                            @if ($isActive)
                                                <span class="badge bg-gradient-success text-white">Mitra aktif</span>
                                            @elseif($isPending)
                                                <span class="badge bg-gradient-warning text-dark">Menunggu persetujuan</span>
                                            @else
                                                <span class="badge bg-gradient-secondary">Belum terhubung</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if ($isActive)
                                                <form method="POST" action="{{ route('kelurahan.puskesmas.detach', $puskesmas) }}"
                                                    data-confirm="Lepas kemitraan dengan {{ $puskesmas->name }}?"
                                                    data-confirm-text="Lepas"
                                                    data-cancel-text="Batal">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        Lepas mitra
                                                    </button>
                                                </form>
                                            @elseif($isPending)
                                                <span class="text-xs text-muted">Menunggu persetujuan puskesmas</span>
                                            @else
                                                <form method="POST" action="{{ route('kelurahan.puskesmas.request', $puskesmas) }}">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-primary">Ajukan sebagai induk</button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">Belum ada puskesmas mitra.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @php
                        $hasPagination = method_exists($puskesmasList, 'firstItem');
                        $from = $hasPagination ? $puskesmasList->firstItem() : ($puskesmasList->count() ? 1 : 0);
                        $to = $hasPagination ? $puskesmasList->lastItem() : $puskesmasList->count();
                        $total = $hasPagination ? $puskesmasList->total() : $puskesmasList->count();
                    @endphp
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3">
                        <p class="text-sm text-muted mb-0">
                            Menampilkan <span class="fw-semibold">{{ $from }}</span> - <span class="fw-semibold">{{ $to }}</span> dari <span class="fw-semibold">{{ $total }}</span> puskesmas
                        </p>
                        @if ($hasPagination)
                            <div class="mb-0">
                                {{ $puskesmasList->withQueryString()->onEachSide(1)->links('pagination.compact-arrows') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
