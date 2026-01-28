@extends('layouts.soft')

@section('subjudul', 'Ringkasan Layanan SITUBA')

@section('content')
    {{-- Hero Section --}}
    <div class="glass-card p-8 mb-8 relative overflow-hidden">
        <div class="relative z-10 grid lg:grid-cols-2 gap-8 items-center">
            <div>
                <span class="inline-block py-1 px-3 rounded-full bg-[var(--color-glass-primary)]/10 text-[var(--color-glass-primary)] text-xs font-bold mb-4 border border-[var(--color-glass-primary)]/20">
                    Dashboard Overview
                </span>
                <h1 class="text-4xl font-bold mb-4 bg-clip-text text-transparent bg-gradient-to-r from-gray-800 to-gray-600">
                    Selamat Datang, {{ explode(' ', auth()->user()->name)[0] }}!
                </h1>
                <p class="text-lg text-gray-600 mb-6 leading-relaxed">
                    Pantau kinerja tim TBC dan kelola data skrining secara real-time. 
                    Anda memiliki akses penuh ke laporan terbaru hari ini.
                </p>
                <div class="flex flex-wrap gap-4">
                    @if(auth()->user()->role === \App\Enums\UserRole::Kader)
                        <a href="{{ route('kader.screening.create') }}" class="glass-button-cta px-6 py-3 rounded-xl font-bold no-underline inline-flex items-center gap-2">
                            <i class="ri-add-circle-line text-xl"></i>
                            Mulai Skrining Baru
                        </a>
                    @else
                        <a href="#charts-section" class="glass-button px-6 py-3 rounded-xl font-bold no-underline inline-flex items-center gap-2">
                            <i class="ri-bar-chart-groupped-line text-xl"></i>
                            Lihat Statistik
                        </a>
                    @endif
                </div>
            </div>
            <div class="hidden lg:block relative">
                {{-- Abstract decorative elements --}}
                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-64 h-64 bg-blue-500/20 rounded-full blur-3xl"></div>
                <div class="glass-panel p-6 rounded-2xl rotate-3 absolute top-0 right-10 w-fit max-w-[280px]">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 rounded-full bg-orange-100 flex items-center justify-center text-orange-600">
                            <i class="ri-notification-3-line text-xl"></i>
                        </div>
                        <div>
                            <h6 class="font-bold text-sm m-0">Notifikasi</h6>
                            <span class="text-xs text-gray-500">{{ $notification['time'] }}</span>
                        </div>
                    </div>
                    <p class="text-sm text-gray-600 m-0">{{ $notification['text'] }}</p>
                </div>
                <div class="glass-panel p-6 rounded-2xl -rotate-2 absolute bottom-0 left-10 w-fit max-w-[280px]">
                     <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center text-green-600">
                            <i class="ri-check-double-line text-xl"></i>
                        </div>
                        <div>
                            <h6 class="font-bold text-sm m-0">Target Tercapai</h6>
                            <span class="text-xs text-gray-500">Bulan ini</span>
                        </div>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-green-500 h-2 rounded-full" style="width: 85%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Features Section (Cards) --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        @foreach ($cards as $card)
            <div class="glass-card p-6 flex flex-col h-full hover-lift">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <p class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-1">{{ $card['label'] }}</p>
                        <h3 class="text-2xl font-bold text-gray-800 m-0">{{ $card['value'] }}</h3>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-[var(--color-glass-primary)] to-[var(--color-glass-secondary)] flex items-center justify-center text-white shadow-lg">
                        <i class="{{ $card['icon'] ?? 'ri-file-info-line' }} text-xl"></i>
                    </div>
                </div>
                <div class="mt-auto">
                    <p class="text-sm text-gray-600 mb-0 flex items-center gap-1.5">
                        @if (!empty($card['trend']))
                            <span class="font-bold {{ str_contains(($card['color'] ?? ''), 'success') ? 'text-green-600' : (str_contains(($card['color'] ?? ''), 'danger') ? 'text-red-600' : 'text-blue-600') }}">
                                {{ $card['trend'] }}
                            </span>
                        @endif
                        <span class="text-xs">{{ $card['subtitle'] ?? '' }}</span>
                    </p>
                </div>
                @if (!empty($card['url']))
                    <a href="{{ $card['url'] }}" class="absolute inset-0" aria-label="{{ $card['label'] }}"></a>
                @endif
            </div>
        @endforeach
    </div>

    {{-- Charts Section --}}
    <div id="charts-section" class="scroll-mt-24">
    @if ($user->role === \App\Enums\UserRole::Kelurahan && $dashboardCharts && count($dashboardCharts['daily_screening'] ?? []))
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-8">
            <div class="glass-card p-6">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h6 class="font-bold text-lg text-gray-800 m-0">Skrining Harian</h6>
                        <p class="text-sm text-gray-500 m-0">Rincian per hari bulan {{ $dashboardCharts['period_label'] ?? now()->format('M Y') }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600">
                        <i class="ri-line-chart-line text-xl"></i>
                    </div>
                </div>
                <div class="relative h-[260px]">
                    <canvas id="dailyScreeningChart"></canvas>
                </div>
            </div>
            <div class="glass-card p-6">
                 <div class="flex justify-between items-center mb-6">
                    <div>
                        <h6 class="font-bold text-lg text-gray-800 m-0">Top Kader Screening</h6>
                        <p class="text-sm text-gray-500 m-0">Kinerja input skrining bulan ini</p>
                    </div>
                    <div class="w-10 h-10 rounded-lg bg-green-50 flex items-center justify-center text-green-600">
                        <i class="ri-trophy-line text-xl"></i>
                    </div>
                </div>
                <div class="relative h-[260px]">
                    <canvas id="kaderScreeningChart"></canvas>
                </div>
            </div>
        </div>
        @if (count($dashboardCharts['rw_split'] ?? []))
            <div class="grid grid-cols-1 gap-6 mb-8">
                <div class="glass-card p-6">
                    <div class="flex flex-wrap justify-between items-center mb-6 gap-4">
                        <div>
                            <h6 class="font-bold text-lg text-gray-800 m-0">Persebaran Skrining per RW</h6>
                            <p class="text-sm text-gray-500 m-0">Analisis suspek per wilayah</p>
                        </div>
                        <div class="flex items-center gap-2">
                             <div class="relative">
                                <select id="rwSelect" class="pl-4 pr-10 py-2 rounded-lg bg-gray-50 border-none text-sm font-medium focus:ring-2 focus:ring-blue-500 outline-none appearance-none cursor-pointer">
                                    <option value="all">Semua RW</option>
                                </select>
                                <i class="ri-arrow-down-s-line absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                            </div>
                        </div>
                    </div>
                    <div class="relative h-[420px]">
                        <canvas id="kaderRtRwChart"></canvas>
                    </div>
                </div>
            </div>
        @endif
    @elseif ($user->role === \App\Enums\UserRole::Pemda && $dashboardCharts && count($dashboardCharts['kelurahan_values'] ?? []))
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-8">
            <div class="glass-card p-6">
                <div class="flex justify-between items-center mb-6">
                     <div>
                        <h6 class="font-bold text-lg text-gray-800 m-0">Persebaran per Kelurahan</h6>
                        <p class="text-sm text-gray-500 m-0">Volume skrining tertinggi</p>
                    </div>
                </div>
                <div class="relative h-[260px]">
                    <canvas id="pemdaKelurahanChart"></canvas>
                </div>
            </div>
            <div class="glass-card p-6">
                <div class="flex justify-between items-center mb-6">
                     <div>
                        <h6 class="font-bold text-lg text-gray-800 m-0">Tren Suspek TBC</h6>
                        <p class="text-sm text-gray-500 m-0">Perbandingan kasus suspek</p>
                    </div>
                </div>
                <div class="relative h-[260px]">
                    <canvas id="pemdaTbcChart"></canvas>
                </div>
            </div>
        </div>
    @elseif ($dashboardCharts && count($dashboardCharts['screening'] ?? []))
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-8">
            <div class="glass-card p-6">
                 <div class="flex justify-between items-center mb-6">
                     <div>
                        <h6 class="font-bold text-lg text-gray-800 m-0">Tren Skrining Bulanan</h6>
                        <p class="text-sm text-gray-500 m-0">Total skrining 12 bulan terakhir</p>
                    </div>
                </div>
                <div class="relative h-[260px]">
                    <canvas id="pemdaScreeningChart"></canvas>
                </div>
            </div>
            <div class="glass-card p-6">
                 <div class="flex justify-between items-center mb-6">
                     <div>
                        <h6 class="font-bold text-lg text-gray-800 m-0">Tren Suspek TBC</h6>
                        <p class="text-sm text-gray-500 m-0">Perbandingan kasus suspek</p>
                    </div>
                </div>
                <div class="relative h-[260px]">
                    <canvas id="pemdaTbcChart"></canvas>
                </div>
            </div>
        </div>
    @endif
    </div>

    {{-- Recent Screenings Table --}}
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
        <div class="glass-card overflow-hidden mb-8">
            <div class="p-6 border-b border-gray-100/50 flex flex-wrap justify-between items-center gap-4">
                <div>
                    <h6 class="font-bold text-lg text-gray-800 m-0">
                        {{ $user->role === \App\Enums\UserRole::Kelurahan ? 'Aktivitas Kader Terbaru' : 'Aktivitas Skrining Terbaru' }}
                    </h6>
                    <p class="text-sm text-gray-500 m-0">
                        {{ $user->role === \App\Enums\UserRole::Kelurahan ? 'Kader yang baru saja melakukan input.' : 'Laporan masuk secara real-time.' }}
                    </p>
                </div>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-[var(--color-glass-primary)] text-white shadow-sm">
                    @if ($user->role === \App\Enums\UserRole::Kelurahan)
                        {{ $recentIsPaginator ? $recentScreenings->total() . ' Total Input' : 'Aktivitas Terkini' }}
                    @else
                        {{ $recentSuspectCount }} Suspek Terdeteksi
                    @endif
                </span>
            </div>
            <div class="overflow-x-auto">
                <table class="glass-table">
                    <thead>
                        <tr>
                            @if ($user->role === \App\Enums\UserRole::Kelurahan)
                                <th>Kader / Petugas</th>
                                <th>Kontak</th>
                                <th class="text-right">Waktu Input</th>
                            @else
                                <th>Identitas Pasien</th>
                                <th>Petugas Pemeriksa</th>
                                <th class="text-center">Gejala (Ya)</th>
                                <th class="text-right">Waktu Input</th>
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
                                        <div class="flex flex-col">
                                            <span class="font-semibold text-gray-800">{{ $screening->kader->name ?? 'Mandiri' }}</span>
                                            <span class="text-xs text-gray-500">{{ $screening->kader->detail->organization ?? '-' }}</span>
                                        </div>
                                    </td>
                                    <td class="text-gray-600">
                                        {{ $screening->kader->phone ?? '-' }}
                                    </td>
                                    <td class="text-right">
                                        <div class="flex flex-col items-end">
                                            <span class="font-medium text-gray-800">{{ $screening->created_at->format('d M Y') }}</span>
                                            <span class="text-xs text-gray-400">{{ $screening->created_at->format('H:i') }} WIB</span>
                                        </div>
                                    </td>
                                @else
                                    <td>
                                        <div class="flex flex-col">
                                            @php
                                                $routeName = match($user->role) {
                                                    \App\Enums\UserRole::Kader => 'kader.screening.show',
                                                    \App\Enums\UserRole::Pemda => 'pemda.screenings.show',
                                                    \App\Enums\UserRole::Puskesmas => 'puskesmas.screenings.show',
                                                    default => null,
                                                };
                                            @endphp
                                            @if($routeName)
                                                <a href="{{ route($routeName, $screening) }}" class="font-semibold text-[var(--color-glass-primary)] hover:underline">
                                                    {{ $screening->patient_name }}
                                                </a>
                                            @else
                                                <span class="font-semibold text-gray-800">{{ $screening->patient_name }}</span>
                                            @endif
                                            <span class="text-xs text-gray-500">{{ $screening->patient_address_domisili ?? $screening->patient_address ?? '-' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="flex flex-col">
                                            <span class="font-medium text-gray-700">{{ $screening->kader->name ?? 'Mandiri' }}</span>
                                            <span class="text-xs text-gray-400">{{ $screening->kader->phone ?? '-' }}</span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full text-xs font-bold
                                            {{ $positiveCount > 0 ? 'bg-red-100 text-red-600' : 'bg-green-100 text-green-600' }}">
                                            {{ $positiveCount }}
                                        </span>
                                    </td>
                                    <td class="text-right">
                                        <div class="flex flex-col items-end">
                                            <span class="font-medium text-gray-800">{{ $screening->created_at->format('d M Y') }}</span>
                                            <span class="text-xs text-gray-400">{{ $screening->created_at->format('H:i') }} WIB</span>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($recentIsPaginator)
                <div class="p-4 border-t border-gray-100/50 bg-gray-50/30 flex flex-wrap justify-between items-center gap-4">
                     <span class="text-xs text-gray-500">
                        Menampilkan {{ $recentScreenings->firstItem() ?? 0 }}-{{ $recentScreenings->lastItem() ?? 0 }}
                        dari {{ $recentScreenings->total() }} data
                    </span>
                    {{ $recentScreenings->onEachSide(1)->links('pagination.glass') }}
                </div>
            @endif
        </div>
    @elseif ($recentScreenings)
        <div class="glass-card p-12 text-center mb-8">
            <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-4 text-gray-400">
                <i class="ri-file-list-3-line text-3xl"></i>
            </div>
            <h6 class="text-lg font-semibold text-gray-700">Belum ada aktivitas terbaru</h6>
            <p class="text-gray-500">Data skrining akan muncul di sini setelah diinputkan.</p>
        </div>
    @endif

    {{-- CTA Section --}}
    <div class="glass-card p-8 text-center relative overflow-hidden">
        <div class="relative z-10">
             <h2 class="text-2xl font-bold mb-3 text-gray-800">Tingkatkan Performa Layanan Kesehatan</h2>
             <p class="text-gray-600 mb-6 max-w-2xl mx-auto">
                 Pastikan setiap pasien mendapatkan penanganan yang tepat dan cepat. Gunakan data ini untuk pengambilan keputusan yang lebih baik.
             </p>
             <div class="flex justify-center gap-4">
                 @if(auth()->user()->role === \App\Enums\UserRole::Kader)
                    <a href="{{ route('kader.screening.create') }}" class="glass-button-cta px-8 py-3 rounded-xl font-bold no-underline shadow-lg hover:shadow-xl transition-all">
                        Input Laporan Baru
                    </a>
                 @else
                    <a href="{{ route('news.index') }}" class="glass-button px-8 py-3 rounded-xl font-bold no-underline shadow-lg hover:shadow-xl transition-all">
                        Baca Berita Terkini
                    </a>
                 @endif
             </div>
        </div>
        {{-- Background blur spot --}}
         <div class="absolute top-0 right-0 w-64 h-64 bg-orange-500/10 rounded-full blur-3xl -mr-16 -mt-16"></div>
         <div class="absolute bottom-0 left-0 w-64 h-64 bg-blue-500/10 rounded-full blur-3xl -ml-16 -mb-16"></div>
    </div>

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
                                backgroundColor: 'rgba(16, 185, 129, 0.1)', // Primary glass color low opacity
                                borderColor: '#10B981', // Emerald-500
                                borderWidth: 3,
                                tension: 0.4,
                                fill: true,
                                pointBackgroundColor: '#fff',
                                pointBorderColor: '#10B981',
                        // ... (rest of chart config)
                                pointBorderWidth: 2,
                                pointRadius: 4,
                                pointHoverRadius: 6,
                            }],
                        },
                        plugins: [ChartDataLabels],
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    suggestedMax: dailySuggestedMax || undefined,
                                    grid: { color: 'rgba(0,0,0,0.05)', borderDash: [5, 5] },
                                    ticks: { stepSize: dailyStep || undefined, precision: 0, font: {family: 'Inter'} },
                                },
                                x: {
                                    grid: { display: false },
                                    ticks: { font: {family: 'Inter'} }
                                }
                            },
                            plugins: {
                                legend: { display: false },
                                datalabels: {
                                    align: 'top',
                                    anchor: 'start',
                                    offset: 4,
                                    color: '#059669',
                                    font: { weight: 'bold', size: 10, family: 'Inter' },
                                    formatter: (value) => (value ? value : ''),
                                },
                                tooltip: {
                                    backgroundColor: 'rgba(255, 255, 255, 0.9)',
                                    titleColor: '#1e293b',
                                    bodyColor: '#475569',
                                    borderColor: '#e2e8f0',
                                    borderWidth: 1,
                                    padding: 10,
                                    boxPadding: 4,
                                }
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
                                backgroundColor: '#10B981', // Success color
                                borderRadius: 8,
                                borderSkipped: false,
                            }],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    backgroundColor: 'rgba(255, 255, 255, 0.9)',
                                    titleColor: '#1e293b',
                                    bodyColor: '#475569',
                                    borderColor: '#e2e8f0',
                                    borderWidth: 1,
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: { precision: 0, font: {family: 'Inter'} },
                                    grid: { color: 'rgba(0,0,0,0.05)', borderDash: [5, 5] },
                                },
                                x: {
                                    grid: { display: false },
                                    ticks: { font: {family: 'Inter'} }
                                }
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
                                backgroundColor: '#EF4444', // Danger
                                borderRadius: 4,
                            },
                            {
                                label: 'Tidak Suspek',
                                data: data.map(item => item.non_suspect),
                                backgroundColor: '#10B981', // Success
                                borderRadius: 4,
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
                                x: { 
                                    beginAtZero: true, 
                                    stacked: true, 
                                    ticks: { precision: 0, font: {family: 'Inter'} },
                                    grid: { color: 'rgba(0,0,0,0.05)' }
                                },
                                y: {
                                    stacked: true,
                                    ticks: { autoSkip: false, font: {family: 'Inter'} },
                                    grid: { display: false }
                                },
                            },
                            plugins: {
                                legend: { position: 'bottom', labels: { font: {family: 'Inter'} } },
                                tooltip: {
                                    backgroundColor: 'rgba(255, 255, 255, 0.9)',
                                    titleColor: '#1e293b',
                                    bodyColor: '#475569',
                                    borderColor: '#e2e8f0',
                                    borderWidth: 1,
                                }
                            },
                            datasets: {
                                bar: {
                                    barThickness: 20,
                                    maxBarThickness: 24,
                                }
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
                                backgroundColor: '#10B981',
                                borderRadius: 6,
                            }],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false },
                            },
                            scales: {
                                y: { beginAtZero: true },
                                x: { grid: { display: false } }
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
                                    backgroundColor: '#EF4444',
                                },
                                {
                                    label: 'Tidak Suspek',
                                    data: suspectSplitDataset.map(item => item.non_suspect),
                                    backgroundColor: '#10B981',
                                },
                            ],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: { beginAtZero: true, stacked: true },
                                x: { stacked: true, grid: { display: false } },
                            },
                            plugins: {
                                legend: { position: 'bottom' }
                            }
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
                                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                                borderColor: '#10B981',
                                borderWidth: 3,
                                tension: 0.4,
                                fill: true,
                                pointBackgroundColor: '#fff',
                                pointBorderWidth: 2,
                            }],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { display: false } },
                            scales: {
                                y: { beginAtZero: true, grid: { borderDash: [5, 5] } },
                                x: { grid: { display: false } }
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
                                    backgroundColor: '#EF4444',
                                },
                                {
                                    label: 'Tidak Suspek',
                                    data: suspectSplitDataset.map(item => item.non_suspect),
                                    backgroundColor: '#10B981',
                                },
                            ],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: { beginAtZero: true, stacked: true },
                                x: { stacked: true, grid: { display: false } },
                            },
                            plugins: { legend: { position: 'bottom' } }
                        },
                    });
                }
            });
        </script>
    @endif
@endpush
