@extends('layouts.soft')

@section('subjudul', 'Daftar skrining puskesmas')

@php
    use Illuminate\Support\Str;
@endphp

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header d-flex flex-wrap gap-3 justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">Monitoring Skrining Pasien</h5>
                        <p class="text-sm text-muted mb-0">Pantau laporan skrining yang dicatat kader mitra puskesmas.</p>
                    </div>
                    <form method="GET" action="{{ route('puskesmas.screenings') }}" class="d-flex flex-wrap gap-3 align-items-end w-100 justify-content-between screenings-filter" data-auto-submit>
                        <div class="d-flex flex-wrap gap-3 align-items-end screenings-filter__row">
                            <div class="d-flex flex-column screenings-filter__field">
                                <label class="text-xxs text-muted mb-1">Tanggal mulai (DD-MM-YYYY)</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-white"><i class="fa fa-calendar text-muted"></i></span>
                                    <input type="date" name="from" class="form-control" placeholder="DD-MM-YYYY" value="{{ $filters['from'] ?? now()->subMonth()->toDateString() }}">
                                </div>
                            </div>
                            <div class="d-flex flex-column screenings-filter__field">
                                <label class="text-xxs text-muted mb-1">Tanggal akhir (DD-MM-YYYY)</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-white"><i class="fa fa-calendar text-muted"></i></span>
                                    <input type="date" name="to" class="form-control" placeholder="DD-MM-YYYY" value="{{ $filters['to'] ?? now()->toDateString() }}">
                                </div>
                            </div>
                            <div class="d-flex align-items-end screenings-filter__field">
                                <a href="{{ route('puskesmas.screenings.export.excel', request()->query()) }}" class="btn btn-sm btn-outline-primary btn-export w-100">
                                    <i class="fa fa-file-excel me-1"></i> Export Excel
                                </a>
                            </div>
                        </div>
                        <div class="d-flex flex-wrap gap-2 align-items-end justify-content-end screenings-filter__row screenings-filter__row--inline">
                            <div class="d-flex flex-column screenings-filter__field">
                                <label class="text-xxs text-muted mb-1">Cari nama / NIK / HP / alamat / RT/RW / kelurahan</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                                    <input type="text" name="q" class="form-control" placeholder="Cari nama / NIK / HP / alamat / RT/RW / kelurahan" value="{{ $search ?? '' }}">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-sm btn-outline-success screenings-filter__apply">Terapkan</button>
                        </div>
                    </form>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">#</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Pasien</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Kader</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Status Skrining</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Tanggal</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Kontak</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $firstNumber = method_exists($screenings, 'firstItem') ? $screenings->firstItem() : 1;
                                @endphp
                                @forelse ($screenings as $screening)
                                    @php
                                        $answers = collect($screening->answers ?? []);
                                        $positiveCount = $answers
                                            ->filter(fn ($answer, $key) => str_starts_with((string) $key, 'gejala_') && $answer === 'ya')
                                            ->count();

                                        if ($positiveCount >= 1) {
                                            $statusBadge = ['label' => 'Suspek TBC', 'class' => 'bg-gradient-danger'];
                                        } else {
                                            $statusBadge = ['label' => 'Aman', 'class' => 'bg-gradient-success'];
                                        }

                                        $waNumber = preg_replace('/[^0-9]/', '', $screening->patient_phone ?? '');
                                        if (Str::startsWith($waNumber, '0')) {
                                            $waNumber = '62'.substr($waNumber, 1);
                                        }

                                        $waMessage = rawurlencode('Halo '.$screening->patient_name.'. Kami dari puskesmas ingin menindaklanjuti skrining TBC Anda. Silakan datang untuk pemeriksaan lanjutan.');
                                        $waLink = $waNumber ? 'https://wa.me/'.$waNumber.'?text='.$waMessage : null;
                                    @endphp
                                    <tr>
                                        <td>{{ $firstNumber ? $firstNumber + $loop->index : $loop->iteration }}</td>
                                        <td>
                                            <h6 class="mb-0 text-sm">
                                                <a href="{{ route('puskesmas.screenings.show', $screening) }}" class="text-decoration-none">
                                                    {{ $screening->patient_name ?? '-' }}
                                                </a>
                                            </h6>
                                            <p class="text-xs text-muted mb-0">NIK: {{ $screening->patient_nik ?? '-' }}</p>
                                            <p class="text-xxs text-muted mb-0">RT/RW {{ $screening->patient_address_rt ?? '-' }}/{{ $screening->patient_address_rw ?? '-' }} • {{ $screening->patient_address_kelurahan ?? '-' }}</p>
                                        </td>
                                        <td>
                                            <p class="text-sm fw-semibold mb-0">{{ $screening->kader?->name ?? '-' }}</p>
                                            <p class="text-xs text-muted mb-0">{{ $screening->kader?->phone ?? '-' }}</p>
                                        </td>
                                        <td>
                                            <span class="badge {{ $statusBadge['class'] }}">{{ $statusBadge['label'] }}</span>
                                            <p class="text-xs text-muted mb-0">{{ $positiveCount }} indikasi positif</p>
                                        </td>
                                        <td>
                                            <span class="text-xs text-muted">{{ $screening->created_at->format('d M Y H:i') }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column gap-2">
                                                <span class="text-xs text-muted">{{ $screening->patient_phone ?? '-' }}</span>
                                                @if ($waLink)
                                                    <a href="{{ $waLink }}" target="_blank" class="btn btn-sm btn-success">
                                                        <i class="fa-brands fa-whatsapp me-1"></i> Chat Puskesmas
                                                    </a>
                                                @else
                                                    <span class="badge bg-light text-muted">Nomor tidak valid</span>
                                                @endif
                                            </div>
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
                                {{ $screenings->withQueryString()->onEachSide(1)->links('pagination.compact-arrows') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
