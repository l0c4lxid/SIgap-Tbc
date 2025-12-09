@extends('layouts.soft')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header d-flex flex-wrap gap-3 justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">Data Pasien Skrining</h5>
                        <p class="text-sm text-muted mb-0">Pantau pasien binaan kader dan progres skrining mereka.</p>
                    </div>
                    <form method="GET" action="{{ route('puskesmas.patients') }}" class="d-flex flex-wrap gap-2 align-items-center" data-auto-submit>
                        <div class="input-group input-group-sm sigap-search" style="min-width: 220px;">
                            <span class="input-group-text"><i class="fa fa-search"></i></span>
                            <input type="text" name="q" class="form-control" placeholder="Cari nama, nomor HP, atau alamat" value="{{ $search ?? '' }}">
                        </div>
                        <button type="submit" class="btn btn-sm btn-outline-primary">Terapkan</button>
                    </form>
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
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Kader Pembina</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Hasil Skrining</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Didaftarkan</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Anggota</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $firstNumber = $patients->firstItem();
                                @endphp
                                @forelse ($patients as $patient)
                                    <tr>
                                        <td>{{ $firstNumber ? $firstNumber + $loop->index : $loop->iteration }}</td>
                                        <td>
                                            <h6 class="mb-0 text-sm">{{ $patient->name }}</h6>
                                            <p class="text-xs text-muted mb-1">NIK: {{ $patient->detail->nik ?? '-' }}</p>
                                        </td>
                                        <td>
                                            <p class="text-xs mb-0">Nomor HP: {{ $patient->phone }}</p>
                                        </td>
                                        <td>
                                            <p class="text-xs mb-0">{{ $patient->detail->address ?? '-' }}</p>
                                        </td>
                                        <td>
                                            @php
                                                $kader = optional($patient->detail)->supervisor;
                                            @endphp
                                            <div class="d-flex flex-column">
                                                <span class="text-sm fw-semibold">{{ $kader?->name ?? '-' }}</span>
                                                @if ($kader)
                                                    <span class="text-xs text-muted">Kontak: {{ $kader->phone }}</span>
                                                @else
                                                    <span class="text-xs text-muted">Belum ada kader terhubung</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            @php
                                                $latestScreening = $patient->screenings->first();
                                                $answers = collect($latestScreening->answers ?? []);
                                                $positiveCount = $answers->filter(fn ($answer) => $answer === 'ya')->count();

                                                if (! $latestScreening) {
                                                    $screeningLabel = 'Belum skrining';
                                                    $screeningClass = 'bg-gradient-secondary';
                                                } elseif ($positiveCount >= 2) {
                                                    $screeningLabel = 'Suspek TBC';
                                                    $screeningClass = 'bg-gradient-danger';
                                                } elseif ($positiveCount === 1) {
                                                    $screeningLabel = 'Perlu observasi';
                                                    $screeningClass = 'bg-gradient-warning text-dark';
                                                } else {
                                                    $screeningLabel = 'Aman';
                                                    $screeningClass = 'bg-gradient-success';
                                                }
                                            @endphp
                                            <span class="badge {{ $screeningClass }}">{{ $screeningLabel }}</span>
                                            @if ($latestScreening)
                                                <p class="text-xs text-muted mb-0">Terakhir: {{ $latestScreening->created_at->format('d M Y') }}</p>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if ($patient->is_active)
                                                <span class="badge bg-gradient-success text-white">Akun aktif</span>
                                            @else
                                                <span class="badge bg-gradient-warning text-dark">Belum aktif</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="text-xs text-muted">{{ $patient->created_at->format('d M Y') }}</span>
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('puskesmas.patient.family', $patient) }}" class="btn btn-sm btn-outline-success">Lihat Detail Anggota</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">Belum ada pasien dari kader Anda.</td>
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
