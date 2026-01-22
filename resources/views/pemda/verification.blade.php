@extends('layouts.soft')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card pemda-verification">
<div class="card-header pemda-verification__header">
  <div class="d-flex flex-wrap gap-3 justify-content-between align-items-start align-items-lg-center">
    <div class="pemda-verification__title">
      <h5 class="mb-1">Verifikasi Pengguna SITUBA</h5>
      <p class="text-sm text-muted mb-0">Kelola status aktif pengguna sesuai kebutuhan wilayah.</p>
    </div>

    <div class="w-100">
      <div class="row g-2 align-items-center">
        {{-- FORM FILTER + SEARCH --}}
        <div class="col-12 col-lg">
          <form method="GET"
                action="{{ route('pemda.verification') }}"
                class="row g-2 align-items-center"
                data-auto-submit>

            <div class="col-12 col-md-4 col-lg-3">
              <select name="role" class="form-select form-select-sm form-control">
                <option value="">Semua Peran</option>
                @foreach ($roleOptions as $option)
                  <option value="{{ $option['value'] }}" {{ $selectedRole === $option['value'] ? 'selected' : '' }}>
                    {{ $option['label'] }}
                  </option>
                @endforeach
              </select>
            </div>

            <div class="col-12 col-md-8 col-lg">
              <div class="input-group input-group-sm">
                <span class="input-group-text bg-white"><i class="fa fa-search text-muted"></i></span>
                <input type="text"
                       name="q"
                       class="form-control form-control-sm"
                       placeholder="Cari nama / nomor HP / instansi"
                       value="{{ $search ?? '' }}">
              </div>
            </div>

            <div class="col-12 col-lg-auto">
              <button type="submit" class="btn btn-sm btn-primary px-4 w-100 w-lg-auto">
                Cari
              </button>
            </div>
          </form>
        </div>

        {{-- FORM BULK --}}
        <div class="col-12 col-lg-auto">
          <form method="POST"
                action="{{ route('pemda.verification.bulk-status') }}"
                class="row g-2 align-items-center"
                data-confirm="Terapkan perubahan status massal?"
                data-confirm-text="Ya, terapkan">
            @csrf
            <input type="hidden" name="role" value="{{ $selectedRole }}">

            <div class="col-12 col-md">
              <select name="status" class="form-select form-select-sm form-control">
                <option value="active">Aktifkan Semua</option>
                <option value="inactive">Nonaktifkan Semua</option>
              </select>
            </div>

            <div class="col-12 col-md-auto">
              <button type="submit" class="btn btn-sm btn-dark px-4 w-100">
                <i class="fa fa-bolt me-1"></i> Terapkan
              </button>
            </div>
          </form>
        </div>

      </div>
    </div>
  </div>
</div>

                <div class="card-body">
                    <div class="table-responsive pemda-verification__table-wrap">
                        <table class="table table-hover align-items-center mb-0 pemda-verification__table">
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
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3 pemda-verification__pager">
                        <p class="text-sm text-muted mb-0">
                            Menampilkan <span class="fw-semibold">{{ $from }}</span> - <span class="fw-semibold">{{ $to }}</span> dari <span class="fw-semibold">{{ $total }}</span> pengguna
                        </p>
                        @if ($hasPagination && $records->lastPage() > 1)
                            <div class="mb-0 pemda-verification__pagination">
                                @php
                                    $currentPage = $records->currentPage();
                                    $lastPage = $records->lastPage();
                                    $window = 2;
                                    $startPage = max(1, $currentPage - $window);
                                    $endPage = min($lastPage, $currentPage + $window);
                                @endphp
                                <nav aria-label="Pagination">
                                    <ul class="pagination pemda-verification__pagination-list">
                                        <li class="page-item {{ $records->onFirstPage() ? 'disabled' : '' }}">
                                            <a class="page-link" href="{{ $records->previousPageUrl() ?? '#' }}" rel="prev" aria-label="Previous">&laquo;</a>
                                        </li>
                                        @if ($startPage > 1)
                                            <li class="page-item">
                                                <a class="page-link" href="{{ $records->url(1) }}">1</a>
                                            </li>
                                            @if ($startPage > 2)
                                                <li class="page-item disabled">
                                                    <span class="page-link">&hellip;</span>
                                                </li>
                                            @endif
                                        @endif
                                        @for ($page = $startPage; $page <= $endPage; $page++)
                                            <li class="page-item {{ $page === $currentPage ? 'active' : '' }}">
                                                <a class="page-link" href="{{ $records->url($page) }}">{{ $page }}</a>
                                            </li>
                                        @endfor
                                        @if ($endPage < $lastPage)
                                            @if ($endPage < $lastPage - 1)
                                                <li class="page-item disabled">
                                                    <span class="page-link">&hellip;</span>
                                                </li>
                                            @endif
                                            <li class="page-item">
                                                <a class="page-link" href="{{ $records->url($lastPage) }}">{{ $lastPage }}</a>
                                            </li>
                                        @endif
                                        <li class="page-item {{ $records->hasMorePages() ? '' : 'disabled' }}">
                                            <a class="page-link" href="{{ $records->nextPageUrl() ?? '#' }}" rel="next" aria-label="Next">&raquo;</a>
                                        </li>
                                    </ul>
                                </nav>
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
