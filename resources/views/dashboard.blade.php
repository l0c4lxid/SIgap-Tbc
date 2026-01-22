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
                <div class="card position-relative">
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
                                    @if (!empty($card['action_url']) && !empty($card['action_label']))
                                        <a href="{{ $card['action_url'] }}" class="d-inline-flex d-lg-none align-items-center gap-1 text-xs text-primary mt-1 position-relative z-1">
                                            <span>{{ $card['action_label'] }}</span>
                                            <i class="fa-solid fa-arrow-right text-xs"></i>
                                        </a>
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
                        @if (!empty($card['url']))
                            <a href="{{ $card['url'] }}" class="stretched-link" aria-label="{{ $card['label'] }}"></a>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @if ($user->role === \App\Enums\UserRole::Kelurahan && $dashboardCharts && count($dashboardCharts['daily_screening'] ?? []))
        <div class="row g-4">
            <div class="col-12 col-xl-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header">
                        <h6 class="mb-0">Skrining Harian</h6>
                        <p class="text-sm text-muted mb-0">Rincian per hari bulan {{ $dashboardCharts['period_label'] ?? now()->format('M Y') }}.</p>
                    </div>
                    <div class="card-body">
                        <canvas id="dailyScreeningChart" height="260"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-12 col-xl-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header">
                        <h6 class="mb-0">Kader dengan Skrining Terbanyak</h6>
                        <p class="text-sm text-muted mb-0">Urutan kader berdasarkan jumlah input bulan ini.</p>
                    </div>
                    <div class="card-body">
                        <canvas id="kaderScreeningChart" height="260"></canvas>
                    </div>
                </div>
            </div>
        </div>
        @if (count($dashboardCharts['rw_split'] ?? []))
            <div class="row g-4 mt-1">
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">Persebaran Skrining per RW</h6>
                                <p class="text-sm text-muted mb-0">Suspek vs tidak suspek per RW bulan {{ $dashboardCharts['period_label'] ?? now()->format('M Y') }}.</p>
                            </div>
                            <div class="rw-filter align-items-center gap-2">
                                <label class="text-xs text-muted mb-0" for="rwSelect">Filter RW</label>
                                <select id="rwSelect" class="form-select form-select-sm">
                                    <option value="all">Semua RW</option>
                                </select>
                            </div>
                        </div>
                        <div class="card-body">
                            <canvas id="kaderRtRwChart" height="420"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @elseif ($user->role === \App\Enums\UserRole::Pemda && $dashboardCharts && count($dashboardCharts['kelurahan_values'] ?? []))
        <div class="row g-4">
            <div class="col-12 col-xl-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header">
                        <h6 class="mb-0">Persebaran Skrining per Kelurahan</h6>
                        <p class="text-sm text-muted mb-0">Kelurahan dengan skrining terbanyak bulan {{ $dashboardCharts['period_label'] ?? now()->format('M Y') }}.</p>
                    </div>
                    <div class="card-body">
                        <canvas id="pemdaKelurahanChart" height="260"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-12 col-xl-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header">
                        <h6 class="mb-0">Kasus Suspek TBC</h6>
                        <p class="text-sm text-muted mb-0">Perbandingan suspek vs tidak suspek per bulan.</p>
                    </div>
                    <div class="card-body">
                        <canvas id="pemdaTbcChart" height="260"></canvas>
                    </div>
                </div>
            </div>
        </div>
    @elseif ($dashboardCharts && count($dashboardCharts['screening'] ?? []))
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
                        <p class="text-sm text-muted mb-0">Perbandingan suspek vs tidak suspek per bulan.</p>
                    </div>
                    <div class="card-body">
                        <canvas id="pemdaTbcChart" height="260"></canvas>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @php
        $recentIsPaginator = $recentScreenings instanceof \Illuminate\Contracts\Pagination\Paginator;
        $recentSuspectCount = $recentScreenings
            ? $recentScreenings->filter(function ($screening) {
                $positive = collect($screening->answers ?? [])
                    ->filter(fn ($answer, $key) => str_starts_with((string) $key, 'gejala_') && $answer === 'ya')
                    ->count();
                return $positive >= 1;
            })->count()
            : 0;
    @endphp

    @if ($recentScreenings && $recentScreenings->count())
        <div class="row mt-4">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header d-flex flex-wrap justify-content-between align-items-center pb-0">
                        <div>
                            <h6 class="mb-0">
                                {{ $user->role === \App\Enums\UserRole::Kelurahan ? 'Kader Terakhir Input' : 'Aktivitas Skrining Terbaru' }}
                            </h6>
                            <p class="text-sm text-muted mb-0">
                                {{ $user->role === \App\Enums\UserRole::Kelurahan ? '3 kader terakhir yang menginput skrining.' : 'Pantau laporan skrining yang masuk dari kader.' }}
                            </p>
                        </div>
                        <span class="badge bg-gradient-primary text-white">
                            @if ($user->role === \App\Enums\UserRole::Kelurahan)
                                {{ $recentIsPaginator ? $recentScreenings->total() : $recentScreenings->count() }} total
                            @else
                                {{ $recentSuspectCount }} suspek
                            @endif
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table align-items-center table-hover mb-0">
                                <thead>
                                    <tr>
                                        @if ($user->role === \App\Enums\UserRole::Kelurahan)
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Kader</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Kontak</th>
                                            <th
                                                class="text-end text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                Waktu</th>
                                        @else
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Pasien</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Kader</th>
                                            <th
                                                class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                Jawaban Ya</th>
                                            <th
                                                class="text-end text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                Waktu</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($recentScreenings as $screening)
                                        @php
                                            $positiveCount = collect($screening->answers ?? [])
                                                ->filter(fn($answer, $key) => str_starts_with((string) $key, 'gejala_') && $answer === 'ya')
                                                ->count();
                                        @endphp
                                        <tr>
                                            @if ($user->role === \App\Enums\UserRole::Kelurahan)
                                                <td>
                                                    <div class="d-flex flex-column">
                                                        <span class="text-sm fw-semibold">{{ $screening->kader->name ?? 'Mandiri' }}</span>
                                                        <span class="text-xs text-muted">{{ $screening->kader->detail->organization ?? '-' }}</span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="text-sm">{{ $screening->kader->phone ?? '-' }}</span>
                                                </td>
                                                <td class="text-end">
                                                    <span class="text-xs text-muted">{{ $screening->created_at->format('d M Y') }}</span>
                                                    <div class="text-xs text-muted">{{ $screening->created_at->format('H:i') }}</div>
                                                </td>
                                            @else
                                                <td>
                                                    <div class="d-flex flex-column">
                                                        @if ($user->role === \App\Enums\UserRole::Kader)
                                                            <a href="{{ route('kader.screening.show', $screening) }}" class="text-sm fw-semibold text-decoration-none">
                                                                {{ $screening->patient_name }}
                                                            </a>
                                                        @elseif ($user->role === \App\Enums\UserRole::Pemda)
                                                            <a href="{{ route('pemda.screenings.show', $screening) }}" class="text-sm fw-semibold text-decoration-none">
                                                                {{ $screening->patient_name }}
                                                            </a>
                                                        @elseif ($user->role === \App\Enums\UserRole::Puskesmas)
                                                            <a href="{{ route('puskesmas.screenings.show', $screening) }}" class="text-sm fw-semibold text-decoration-none">
                                                                {{ $screening->patient_name }}
                                                            </a>
                                                        @else
                                                            <span class="text-sm fw-semibold">{{ $screening->patient_name }}</span>
                                                        @endif
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
                                                <td class="text-end">
                                                    <span class="text-xs text-muted">{{ $screening->created_at->format('d M Y') }}</span>
                                                    <div class="text-xs text-muted">{{ $screening->created_at->format('H:i') }}</div>
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if ($recentIsPaginator)
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3">
                                <span class="text-xs text-muted">
                                    Menampilkan {{ $recentScreenings->firstItem() ?? 0 }}-{{ $recentScreenings->lastItem() ?? 0 }}
                                    dari {{ $recentScreenings->total() }} data
                                </span>
                                {{ $recentScreenings->onEachSide(1)->links('pagination::bootstrap-5') }}
                            </div>
                        @endif
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
    @if ($user->role === \App\Enums\UserRole::Kelurahan && $dashboardCharts && count($dashboardCharts['daily_screening'] ?? []))
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const dailyDataset = @json($dashboardCharts['daily_screening'] ?? []);
                const kaderDataset = @json($dashboardCharts['kader_screening'] ?? []);
                const rwDataset = @json($dashboardCharts['rw_split'] ?? []);
                const rtDatasetByRw = @json($dashboardCharts['rt_split'] ?? []);

                const dailyCtx = document.getElementById('dailyScreeningChart');
                if (dailyCtx && dailyDataset.length) {
                    const dailyValues = dailyDataset.map(item => item.value);
                    const dailyMax = Math.max(...dailyValues, 0);
                    const dailyStep = dailyMax <= 10 ? 1 : (dailyMax <= 50 ? 5 : (dailyMax <= 100 ? 10 : 50));
                    const dailySuggestedMax = dailyStep ? Math.ceil(dailyMax / dailyStep) * dailyStep : dailyMax;

                    new Chart(dailyCtx, {
                        type: 'line',
                        data: {
                            labels: dailyDataset.map(item => item.label),
                            datasets: [{
                                label: 'Jumlah Skrining',
                                data: dailyDataset.map(item => item.value),
                                backgroundColor: 'rgba(25, 135, 84, 0.2)',
                                borderColor: 'rgba(25, 135, 84, 0.85)',
                                borderWidth: 2,
                                tension: 0.3,
                                fill: true,
                                pointRadius: 4,
                                pointHoverRadius: 6,
                            }],
                        },
                        plugins: [ChartDataLabels],
                        options: {
                            responsive: true,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    suggestedMax: dailySuggestedMax || undefined,
                                    title: {
                                        display: true,
                                        text: 'Jumlah Skrining',
                                    },
                                    ticks: {
                                        stepSize: dailyStep || undefined,
                                        precision: 0,
                                    },
                                },
                            },
                            plugins: {
                                datalabels: {
                                    align: 'left',
                                    anchor: 'center',
                                    offset: 6,
                                    color: '#198754',
                                    font: { weight: '600', size: 10 },
                                    formatter: (value) => (value ? value : ''),
                                },
                                tooltip: {
                                    callbacks: {
                                        label: (context) => `Jumlah: ${context.parsed.y}`,
                                    },
                                },
                            },
                        },
                    });
                }

                const kaderCtx = document.getElementById('kaderScreeningChart');
                if (kaderCtx && kaderDataset.length) {
                    new Chart(kaderCtx, {
                        type: 'bar',
                        data: {
                            labels: kaderDataset.map(item => item.label),
                            datasets: [{
                                label: 'Jumlah Skrining',
                                data: kaderDataset.map(item => item.value),
                                backgroundColor: 'rgba(13, 110, 253, 0.65)',
                                borderRadius: 6,
                            }],
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: { boxWidth: 12 },
                                },
                                tooltip: {
                                    callbacks: {
                                        label: (context) => `${context.dataset.label}: ${context.parsed.y ?? context.parsed}`,
                                    },
                                },
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: { precision: 0 },
                                },
                            },
                        },
                    });
                }

                const kaderRtRwCtx = document.getElementById('kaderRtRwChart');
                if (kaderRtRwCtx && rwDataset.length) {
                    const rwSelect = document.getElementById('rwSelect');
                    if (rwSelect) {
                        rwDataset.forEach(item => {
                            const option = document.createElement('option');
                            option.value = item.rw;
                            option.textContent = item.label;
                            rwSelect.appendChild(option);
                        });
                    }

                    const buildChartData = (data) => ({
                        labels: data.map(item => item.label),
                        datasets: [
                            {
                                label: 'Suspek',
                                data: data.map(item => item.suspect),
                                backgroundColor: 'rgba(220, 53, 69, 0.75)',
                            },
                            {
                                label: 'Tidak Suspek',
                                data: data.map(item => item.non_suspect),
                                backgroundColor: 'rgba(25, 135, 84, 0.7)',
                            },
                        ],
                    });

                    const rwChart = new Chart(kaderRtRwCtx, {
                        type: 'bar',
                        data: buildChartData(rwDataset),
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            indexAxis: 'y',
                            scales: {
                                x: { beginAtZero: true, stacked: true, ticks: { precision: 0 } },
                                y: {
                                    stacked: true,
                                    ticks: { autoSkip: false },
                                },
                            },
                            plugins: {
                                legend: { position: 'bottom' },
                                tooltip: {
                                    callbacks: {
                                        label: (context) => `${context.dataset.label}: ${context.parsed.x}`,
                                    },
                                },
                            },
                            datasets: {
                                bar: {
                                    barThickness: 12,
                                    maxBarThickness: 14,
                                    categoryPercentage: 0.7,
                                    barPercentage: 0.8,
                                },
                            },
                        },
                    });

                    if (rwSelect) {
                        rwSelect.addEventListener('change', (event) => {
                            const selected = event.target.value;
                            const nextData = selected === 'all'
                                ? rwDataset
                                : (rtDatasetByRw[selected] ?? []);
                            rwChart.data = buildChartData(nextData);
                            rwChart.options.scales.y = { stacked: true };
                            rwChart.update();
                        });
                    }
                }
            });
        </script>
    @elseif ($user->role === \App\Enums\UserRole::Pemda && $dashboardCharts && count($dashboardCharts['kelurahan_values'] ?? []))
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const kelurahanLabels = @json($dashboardCharts['kelurahan_labels'] ?? []);
                const kelurahanValues = @json($dashboardCharts['kelurahan_values'] ?? []);
                const suspectSplitDataset = @json($dashboardCharts['suspect_split'] ?? []);

                const kelurahanCtx = document.getElementById('pemdaKelurahanChart');
                if (kelurahanCtx && kelurahanLabels.length && kelurahanValues.length) {
                    new Chart(kelurahanCtx, {
                        type: 'bar',
                        data: {
                            labels: kelurahanLabels,
                            datasets: [{
                                label: 'Skrining',
                                data: kelurahanValues,
                                backgroundColor: '#0ea5a0',
                                borderRadius: 6,
                                maxBarThickness: 42,
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
                if (tbcCtx && suspectSplitDataset.length) {
                    new Chart(tbcCtx, {
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
                                    label: 'Tidak Suspek',
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
    @elseif ($dashboardCharts && count($dashboardCharts['screening'] ?? []))
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const screeningDataset = @json($dashboardCharts['screening'] ?? []);
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
                if (tbcCtx && suspectSplitDataset.length) {
                    new Chart(tbcCtx, {
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
                                    label: 'Tidak Suspek',
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
