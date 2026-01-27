@extends('layouts.soft')

@section('subjudul', 'Detail kader puskesmas')

@section('content')
    <div class="row">
        <div class="col-12 mb-3">
            <a href="{{ route('puskesmas.kaders') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fa fa-arrow-left"></i> Kembali ke daftar kader
            </a>
        </div>
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">{{ $kader->name }}</h5>
                        <p class="text-sm text-muted mb-0">Detail kader mitra puskesmas Anda.</p>
                    </div>
                    <span class="badge {{ $kader->is_active ? 'bg-gradient-success text-white' : 'bg-gradient-warning text-dark' }}">
                        {{ $kader->is_active ? 'Aktif' : 'Tidak Aktif' }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row gy-3">
                        <div class="col-md-6">
                            <p class="text-xs text-muted mb-1">Nomor HP</p>
                            <p class="mb-0 fw-semibold">{{ $kader->phone }}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="text-xs text-muted mb-1">Organisasi/Instansi</p>
                            <p class="mb-0 fw-semibold">{{ $kader->detail->organization ?? 'Kader' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="text-xs text-muted mb-1">Catatan</p>
                            <p class="mb-0">{{ $kader->detail->notes ?? '-' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="text-xs text-muted mb-1">Terdaftar sejak</p>
                            <p class="mb-0">{{ $kader->created_at->format('d M Y H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header">
                    <h6 class="mb-0">Kelola Status</h6>
                </div>
                <div class="card-body">
                    <p class="text-sm text-muted">Aktif/nonaktifkan akses kader ini ke aplikasi.</p>
                    <form method="POST" action="{{ route('puskesmas.kaders.status', $kader) }}">
                        @csrf
                        <input type="hidden" name="status" value="{{ $kader->is_active ? 'inactive' : 'active' }}">
                        <button type="submit" class="btn w-100 {{ $kader->is_active ? 'btn-outline-danger' : 'btn-outline-success' }}">
                            {{ $kader->is_active ? 'Nonaktifkan Kader' : 'Aktifkan Kader' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-12 mt-3">
            <div class="card shadow-sm border-0">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Skrining yang Dicatat</h6>
                        <p class="text-sm text-muted mb-0">Daftar skrining yang diinput oleh kader ini.</p>
                    </div>
                    <span class="badge bg-light text-dark">
                        {{ number_format($totalScreenings) }} total
                    </span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">No</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Pasien</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">NIK</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Alamat</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Tanggal</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $firstNumber = method_exists($screenings, 'firstItem') ? $screenings->firstItem() : 1;
                                @endphp
                                @forelse ($screenings as $screening)
                                    @php
                                        $positiveCount = collect($screening->answers ?? [])
                                            ->filter(fn ($answer, $key) => str_starts_with((string) $key, 'gejala_') && $answer === 'ya')
                                            ->count();
                                    @endphp
                                    <tr>
                                        <td class="text-sm">{{ $firstNumber ? $firstNumber + $loop->index : $loop->iteration }}</td>
                                        <td>
                                            <a href="{{ route('puskesmas.screenings.show', $screening) }}" class="text-sm fw-semibold text-decoration-none">
                                                {{ $screening->patient_name ?? '-' }}
                                            </a>
                                        </td>
                                        <td class="text-sm text-muted">{{ $screening->patient_nik ?? '-' }}</td>
                                        <td class="text-sm text-muted">
                                            {{ $screening->patient_address_domisili ?? $screening->patient_address ?? '-' }}
                                        </td>
                                        <td class="text-sm text-muted">{{ $screening->created_at->format('d M Y H:i') }}</td>
                                        <td>
                                            <span class="badge bg-gradient-{{ $positiveCount ? 'danger' : 'success' }}">
                                                {{ $positiveCount ? 'Suspek' : 'Aman' }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">Belum ada skrining tercatat.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if (method_exists($screenings, 'firstItem'))
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3">
                            <p class="text-sm text-muted mb-0">
                                Menampilkan <span class="fw-semibold">{{ $screenings->firstItem() ?? 0 }}</span> - <span class="fw-semibold">{{ $screenings->lastItem() ?? 0 }}</span> dari <span class="fw-semibold">{{ $screenings->total() }}</span> skrining
                            </p>
                            <div class="mb-0">
                                {{ $screenings->withQueryString()->onEachSide(1)->links('pagination::bootstrap-5') }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @if (session('status'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: @json(session('status')),
                });
            });
        </script>
    @endif
@endpush
