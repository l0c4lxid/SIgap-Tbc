@extends('layouts.soft')

@section('content')
    <div class="row">
        @foreach ($cards as $card)
            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">{{ $card['label'] }}</p>
                                    <h5 class="font-weight-bolder">{{ $card['value'] }}</h5>
                                    <p class="mb-0 text-xs text-muted">
                                        {{ $card['subtitle'] ?? '' }}
                                    </p>
                                    @if (! empty($card['trend']))
                                        <span class="text-xs text-{{ ($card['color'] ?? 'primary') === 'danger' ? 'danger' : 'success' }} font-weight-bolder">
                                            {{ $card['trend'] }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-{{ $card['color'] ?? 'primary' }} shadow text-center border-radius-md">
                                    <i class="{{ $card['icon'] ?? 'fa-solid fa-circle-info' }} text-white text-lg"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @if ($user->role === \App\Enums\UserRole::Pasien && in_array($user->detail->treatment_status ?? 'none', ['contacted', 'scheduled']))
        <div class="alert alert-warning mt-4" role="alert">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <strong>Perhatian!</strong> Anda terindikasi Suspek TBC. Segera hubungi kader dan tambahkan anggota keluarga yang mungkin terpapar.
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('patient.family') }}" class="btn btn-sm btn-outline-danger">
                        <i class="fa-solid fa-users me-1"></i> Kelola Anggota Risiko
                    </a>
                    <a href="{{ route('patient.screening') }}" class="btn btn-sm btn-danger">
                        <i class="fa-solid fa-notes-medical me-1"></i> Lakukan Skrining Mandiri
                    </a>
                </div>
            </div>
        </div>
    @endif

    @if ($recentScreenings && $recentScreenings->count())
        <div class="row mt-4">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header d-flex flex-wrap justify-content-between align-items-center pb-0">
                        <div>
                            <h6 class="mb-0">Aktivitas Skrining Terbaru</h6>
                            <p class="text-sm text-muted mb-0">Pantau laporan skrining dari pasien dan kader.</p>
                        </div>
                        <span class="badge bg-gradient-primary text-white">{{ $recentScreenings->total() }} total</span>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Pasien</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Kader</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Jawaban Ya</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Waktu</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($recentScreenings as $screening)
                                        @php
                                            $positiveCount = collect($screening->answers ?? [])->filter(fn ($answer) => $answer === 'ya')->count();
                                        @endphp
                                        <tr>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span class="text-sm fw-semibold">{{ $screening->patient->name }}</span>
                                                    <span class="text-xs text-muted">{{ $screening->patient->detail->address ?? '-' }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span class="text-sm">{{ $screening->kader->name ?? 'Mandiri' }}</span>
                                                    <span class="text-xs text-muted">{{ $screening->kader->phone ?? '-' }}</span>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-gradient-{{ $positiveCount ? 'danger' : 'success' }}">{{ $positiveCount }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-xs text-muted">{{ $screening->created_at->format('d M Y H:i') }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            {{ $recentScreenings->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @elseif ($recentScreenings)
        <div class="row mt-4">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center py-5">
                        <i class="fa-solid fa-list text-muted fa-2x mb-3"></i>
                        <p class="text-muted mb-0">Belum ada aktivitas skrining terbaru.</p>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
