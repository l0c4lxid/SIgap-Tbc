@extends('layouts.soft')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                    <div class="card-header d-flex flex-wrap justify-content-between alignments-center gap-3">
                        <div>
                            <h5 class="mb-0">Pengelolaan Pengobatan Pasien</h5>
                            <p class="text-sm text-muted mb-0">Pantau progres pasien yang sedang ditindaklanjuti pengobatan TBC.</p>
                        </div>
                        <div class="d-flex flex-column flex-lg-row align-items-lg-center gap-3 ms-lg-auto w-100 w-lg-auto">
                            <ul class="nav nav-pills flex-wrap">
                                @foreach ($statuses as $value => $label)
                                    <li class="nav-item">
                                        <a class="nav-link {{ $activeStatus === $value ? 'active' : '' }}" href="{{ route('puskesmas.treatment', ['status' => $value, 'q' => $search]) }}">
                                            {{ $label }}
                                            <span class="badge bg-white text-dark ms-1">{{ $counts[$value] ?? 0 }}</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                            <div class="d-flex flex-wrap gap-2 align-items-center">
                                <form method="GET" action="{{ route('puskesmas.treatment') }}" class="d-flex gap-2" data-auto-submit>
                                    @if ($activeStatus)
                                        <input type="hidden" name="status" value="{{ $activeStatus }}">
                                    @endif
                                    <div class="input-group input-group-sm">
                                        <input type="text" class="form-control" name="q" placeholder="Cari nama/telepon" value="{{ $search }}">
                                        <button class="btn btn-outline-secondary" type="submit"><i class="fa fa-search"></i></button>
                                    </div>
                                    @if ($search !== '')
                                        <a href="{{ route('puskesmas.treatment', array_filter(['status' => $activeStatus])) }}" class="btn btn-sm btn-light">Reset</a>
                                    @endif
                                </form>
                                <a href="{{ route('puskesmas.treatment.export.excel', request()->query()) }}" class="btn btn-sm btn-outline-success btn-export">
                                    <i class="fa fa-file-excel me-1"></i> Export Excel
                                </a>
                            </div>
                        </div>
                    </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 60px;">No.</th>
                                    <th>Pasien</th>
                                    <th>Kader Penghubung</th>
                                    <th>Hasil Skrining Terakhir</th>
                                    <th>Status Pengobatan</th>
                                    <th>Jadwal Kontrol</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $firstNumber = $treatments->firstItem();
                                @endphp
                                @forelse ($treatments as $treatment)
                                    @php
                                        $patient = $treatment->patient;
                                        $latestScreening = $patient->screenings->first();
                                        $positiveCount = collect($latestScreening->answers ?? [])->filter(fn ($answer) => $answer === 'ya')->count();
                                    @endphp
                                    <tr class="align-middle">
                                        <td class="text-center fw-semibold">{{ $firstNumber ? $firstNumber + $loop->index : $loop->iteration }}</td>
                                        <td>
                                            <h6 class="mb-0 text-sm">{{ $patient->name }}</h6>
                                            <p class="text-xs text-muted mb-0">{{ $patient->phone }}</p>
                                        </td>
                                        <td>
                                            <p class="text-sm fw-semibold mb-0">{{ $patient->detail->supervisor->name ?? '-' }}</p>
                                            <p class="text-xs text-muted mb-0">{{ $patient->detail->supervisor->phone ?? '-' }}</p>
                                        </td>
                                        <td>
                                            @if ($latestScreening)
                                                <span class="badge bg-gradient-{{ $positiveCount >= 2 ? 'danger' : ($positiveCount === 1 ? 'warning text-dark' : 'success') }}">
                                                    {{ $positiveCount }} indikasi "Ya"
                                                </span>
                                                <p class="text-xs text-muted mb-0">{{ $latestScreening->created_at->format('d M Y H:i') }}</p>
                                            @else
                                                <span class="badge bg-gradient-secondary">Belum ada</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-gradient-info">{{ ucfirst(str_replace('_', ' ', $treatment->status)) }}</span>
                                            @if ($treatment->notes)
                                                <p class="text-xs text-muted mb-0">{{ $treatment->notes }}</p>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($treatment->next_follow_up_at)
                                                <span class="text-xs text-muted">{{ $treatment->next_follow_up_at->format('d M Y') }}</span>
                                            @else
                                                <span class="text-xs text-muted">Belum dijadwalkan</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a class="btn btn-outline-success" href="{{ route('puskesmas.treatment.show', $patient) }}">
                                                    Kelola
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">Belum ada pasien dalam pengobatan.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @php
                        $hasPagination = method_exists($treatments, 'firstItem');
                        $from = $hasPagination ? $treatments->firstItem() : ($treatments->count() ? 1 : 0);
                        $to = $hasPagination ? $treatments->lastItem() : $treatments->count();
                        $total = $hasPagination ? $treatments->total() : $treatments->count();
                    @endphp
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3">
                        <p class="text-sm text-muted mb-0">
                            Menampilkan <span class="fw-semibold">{{ $from }}</span> - <span class="fw-semibold">{{ $to }}</span> dari <span class="fw-semibold">{{ $total }}</span> pasien
                        </p>
                        @if ($hasPagination)
                            <div class="mb-0">
                                {{ $treatments->withQueryString()->onEachSide(1)->links('pagination::bootstrap-5') }}
                            </div>
                        @endif
                    </div>
            </div>
        </div>
    </div>
</div>

@endsection
