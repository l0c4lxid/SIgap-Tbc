@extends('layouts.soft')

@section('content')
    @php
        $cardCount = count($cards);
        $cardColumns = [];
        if ($cardCount === 1) {
            $cardColumns = ['col-12 mb-4'];
        } elseif ($cardCount === 2) {
            $cardColumns = ['col-lg-6 col-md-6 col-12 mb-4', 'col-lg-6 col-md-6 col-12 mb-4'];
        } elseif ($cardCount === 3) {
            $cardColumns = ['col-lg-6 col-md-6 col-12 mb-4', 'col-lg-3 col-md-6 col-12 mb-4', 'col-lg-3 col-md-6 col-12 mb-4'];
        } else {
            $cardColumns = array_fill(0, $cardCount, 'col-lg-3 col-md-6 col-12 mb-4');
        }
    @endphp

    <div class="row">
        @foreach ($cards as $card)
            @php $colClass = $cardColumns[$loop->index] ?? 'col-lg-3 col-md-6 col-12 mb-4'; @endphp
            <div class="{{ $colClass }}">
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
                                    @if (!empty($card['trend']))
                                        <span
                                            class="text-xs text-{{ ($card['color'] ?? 'primary') === 'danger' ? 'danger' : 'success' }} font-weight-bolder">
                                            {{ $card['trend'] }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div
                                    class="icon icon-shape bg-gradient-{{ $card['color'] ?? 'primary' }} shadow text-center border-radius-md">
                                    <i class="{{ $card['icon'] ?? 'fa-solid fa-circle-info' }} text-white text-lg"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @if ($dashboardCharts && count($dashboardCharts['screening'] ?? []))
        <div class="row g-4">
            <div class="col-12 col-xl-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header">
                        <h6 class="mb-0">Skrining per Bulan</h6>
                        <p class="text-sm text-muted mb-0">
                            Total skrining 12 bulan terakhir
                            ({{ $user->role === \App\Enums\UserRole::Pemda ? 'seluruh kota' : 'kelurahan ini' }}).
                        </p>
                    </div>
                    <div class="card-body">
                        <canvas id="pemdaScreeningChart" height="260"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-12 col-xl-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header">
                        <h6 class="mb-0">Kasus Suspek TBC</h6>
                        <p class="text-sm text-muted mb-0">Jumlah pasien indicasi ≥ 1 jawaban "Ya" per bulan.</p>
                    </div>
                    <div class="card-body">
                        <canvas id="pemdaTbcChart" height="260"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="row g-4">
            <div class="col-12 col-xl-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header">
                        <h6 class="mb-0">Suspek vs Non Suspek</h6>
                        <p class="text-sm text-muted mb-0">Distribusi hasil skrining (≥1 "Ya" dianggap suspek).</p>
                    </div>
                    <div class="card-body">
                        <canvas id="suspectSplitChart" height="260"></canvas>
                    </div>
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
                            <p class="text-sm text-muted mb-0">Pantau laporan skrining yang masuk dari kader.</p>
                        </div>
                        <span class="badge bg-gradient-primary text-white">{{ $recentScreenings->total() }} total</span>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table align-items-center mb-0">
                                <thead>
                                    <tr>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Pasien</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                            Kader</th>
                                        <th
                                            class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Jawaban Ya</th>
                                        <th
                                            class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Waktu</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($recentScreenings as $screening)
                                        @php
                                            $positiveCount = collect($screening->answers ?? [])->filter(fn($answer) => $answer === 'ya')->count();
                                        @endphp
                                        <tr>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span class="text-sm fw-semibold">{{ $screening->patient_name }}</span>
                                                    <span
                                                        class="text-xs text-muted">{{ $screening->patient_address_domisili ?? $screening->patient_address ?? '-' }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span class="text-sm">{{ $screening->kader->name ?? 'Mandiri' }}</span>
                                                    <span class="text-xs text-muted">{{ $screening->kader->phone ?? '-' }}</span>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span
                                                    class="badge bg-gradient-{{ $positiveCount ? 'danger' : 'success' }}">{{ $positiveCount }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span
                                                    class="text-xs text-muted">{{ $screening->created_at->format('d M Y H:i') }}</span>
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

@push('scripts')
    @if ($dashboardCharts && count($dashboardCharts['screening'] ?? []))
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const screeningDataset = @json($dashboardCharts['screening'] ?? []);
                const tbcDataset = @json($dashboardCharts['tbc_cases'] ?? []);
                const suspectSplitDataset = @json($dashboardCharts['suspect_split'] ?? []);

                const screeningCtx = document.getElementById('pemdaScreeningChart');
                if (screeningCtx && screeningDataset.length) {
                    new Chart(screeningCtx, {
                        type: 'line',
                        data: {
                            labels: screeningDataset.map(item => item.label),
                            datasets: [{
                                label: 'Skrining',
                                data: screeningDataset.map(item => item.value),
                                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                                borderColor: 'rgba(54, 162, 235, 1)',
                                borderWidth: 2,
                                tension: 0.3,
                                fill: true,
                            }],
                        },
                        options: {
                            responsive: true,
                            scales: {
                                y: { beginAtZero: true },
                            },
                        },
                    });
                }

                const tbcCtx = document.getElementById('pemdaTbcChart');
                if (tbcCtx && tbcDataset.length) {
                    new Chart(tbcCtx, {
                        type: 'bar',
                        data: {
                            labels: tbcDataset.map(item => item.label),
                            datasets: [{
                                label: 'Kasus Suspek',
                                data: tbcDataset.map(item => item.value),
                                backgroundColor: 'rgba(220, 53, 69, 0.6)',
                                borderColor: 'rgba(220, 53, 69, 1)',
                                borderWidth: 1,
                            }],
                        },
                        options: {
                            responsive: true,
                            scales: {
                                y: { beginAtZero: true },
                            },
                        },
                    });
                }

                const suspectCtx = document.getElementById('suspectSplitChart');
                if (suspectCtx && suspectSplitDataset.length) {
                    new Chart(suspectCtx, {
                        type: 'bar',
                        data: {
                            labels: suspectSplitDataset.map(item => item.label),
                            datasets: [
                                {
                                    label: 'Suspek (≥1 Ya)',
                                    data: suspectSplitDataset.map(item => item.suspect),
                                    backgroundColor: 'rgba(220, 53, 69, 0.75)',
                                },
                                {
                                    label: 'Non Suspek',
                                    data: suspectSplitDataset.map(item => item.non_suspect),
                                    backgroundColor: 'rgba(54, 162, 235, 0.75)',
                                },
                            ],
                        },
                        options: {
                            responsive: true,
                            scales: {
                                y: { beginAtZero: true, stacked: true },
                                x: { stacked: true },
                            },
                        },
                    });
                }
            });
        </script>
    @endif
@endpush
