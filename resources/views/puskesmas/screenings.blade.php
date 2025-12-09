@extends('layouts.soft')

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
                        <p class="text-sm text-muted mb-0">Pantau status skrining pasien binaan kader mitra puskesmas.</p>
                    </div>
                    <form method="GET" action="{{ route('puskesmas.screenings') }}" class="d-flex flex-wrap gap-3 align-items-end w-100 justify-content-between" data-auto-submit>
                        <div class="d-flex flex-wrap gap-3 align-items-end">
                            <div class="d-flex flex-column">
                                <label class="text-xxs text-muted mb-1">Tanggal mulai</label>
                                <div class="input-group input-group-sm" style="min-width: 170px;">
                                    <span class="input-group-text bg-white"><i class="fa fa-calendar text-muted"></i></span>
                                    <input type="date" name="from" class="form-control" value="{{ $filters['from'] ?? '' }}">
                                </div>
                            </div>
                            <div class="d-flex flex-column">
                                <label class="text-xxs text-muted mb-1">Tanggal akhir</label>
                                <div class="input-group input-group-sm" style="min-width: 170px;">
                                    <span class="input-group-text bg-white"><i class="fa fa-calendar text-muted"></i></span>
                                    <input type="date" name="to" class="form-control" value="{{ $filters['to'] ?? '' }}">
                                </div>
                            </div>
                            <div class="d-flex align-items-end">
                                <a href="{{ route('puskesmas.screenings.export.excel', request()->query()) }}" class="btn btn-sm btn-outline-primary btn-export">
                                    <i class="fa fa-file-excel me-1"></i> Export Excel
                                </a>
                            </div>
                        </div>
                        <div class="d-flex flex-wrap gap-2 align-items-end justify-content-end">
                            <div class="d-flex flex-column">
                                <label class="text-xxs text-muted mb-1">Cari nama / HP / alamat</label>
                                <div class="input-group input-group-sm" style="min-width: 250px;">
                                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                                    <input type="text" name="q" class="form-control" placeholder="Cari nama / HP / alamat" value="{{ $search ?? '' }}">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-sm btn-outline-success">Terapkan</button>
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
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Terakhir Skrining</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Kontak</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $firstNumber = method_exists($patients, 'firstItem') ? $patients->firstItem() : 1;
                                @endphp
                                @forelse ($patients as $patient)
                                    @php
                                        $latestScreening = $patient->screenings->first();
                                        $answers = collect($latestScreening->answers ?? []);
                                        $positiveCount = $answers->filter(fn ($answer) => $answer === 'ya')->count();

                                        if (! $latestScreening) {
                                            $statusBadge = ['label' => 'Belum skrining', 'class' => 'bg-gradient-secondary'];
                                        } elseif ($positiveCount >= 2) {
                                            $statusBadge = ['label' => 'Suspek TBC', 'class' => 'bg-gradient-danger'];
                                        } elseif ($positiveCount === 1) {
                                            $statusBadge = ['label' => 'Perlu observasi', 'class' => 'bg-gradient-warning text-dark'];
                                        } else {
                                            $statusBadge = ['label' => 'Aman', 'class' => 'bg-gradient-success'];
                                        }

                                        $waNumber = preg_replace('/[^0-9]/', '', $patient->phone ?? '');
                                        if (Str::startsWith($waNumber, '0')) {
                                            $waNumber = '62'.substr($waNumber, 1);
                                        }

                                        $waMessage = rawurlencode('Halo '.$patient->name.'. Kami dari puskesmas ingin menindaklanjuti skrining TBC Anda. Silakan datang untuk pemeriksaan lanjutan.');
                                        $waLink = $waNumber ? 'https://wa.me/'.$waNumber.'?text='.$waMessage : null;
                                    @endphp
                                    <tr>
                                        <td>{{ $firstNumber ? $firstNumber + $loop->index : $loop->iteration }}</td>
                                        <td>
                                            <h6 class="mb-0 text-sm">
                                                <a href="{{ route('puskesmas.screenings.show', $patient) }}" class="text-decoration-none">
                                                    {{ $patient->name }}
                                                </a>
                                            </h6>
                                            <p class="text-xs text-muted mb-0">NIK: {{ $patient->detail->nik ?? '-' }}</p>
                                        </td>
                                        <td>
                                            <p class="text-sm fw-semibold mb-0">{{ $patient->detail->supervisor->name ?? '-' }}</p>
                                            <p class="text-xs text-muted mb-0">{{ $patient->detail->supervisor->phone ?? '-' }}</p>
                                        </td>
                                        <td>
                                            <span class="badge {{ $statusBadge['class'] }}">{{ $statusBadge['label'] }}</span>
                                            @if ($latestScreening)
                                                <p class="text-xs text-muted mb-0">{{ $positiveCount }} indikasi positif</p>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($latestScreening)
                                                <span class="text-xs text-muted">{{ $latestScreening->created_at->format('d M Y H:i') }}</span>
                                            @else
                                                <span class="text-xs text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column gap-2">
                                                <span class="text-xs text-muted">{{ $patient->phone }}</span>
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
                                        <td colspan="5" class="text-center py-4 text-muted">Belum ada pasien binaan.</td>
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
