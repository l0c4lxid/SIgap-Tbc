<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\PatientScreening;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user()->loadMissing(['detail.supervisor.detail.supervisor']);
        $role = $user->role;

        $cards = [];
        $recentScreenings = null;
        $dashboardCharts = null;
        $recentLimit = 3;

        $baseScreeningQuery = PatientScreening::query()
            ->with('kader')
            ->latest();

        $chartMonths = collect(range(0, 11))
            ->map(fn($i) => now()->startOfMonth()->subMonths($i))
            ->sort()
            ->values();

        $buildCharts = function ($screenings) use ($chartMonths) {
            $monthlyAggregates = [];
            foreach ($screenings as $screening) {
                $key = $screening->created_at->format('Y-m');
                if (!isset($monthlyAggregates[$key])) {
                    $monthlyAggregates[$key] = ['screening' => 0, 'suspect' => 0];
                }
                $monthlyAggregates[$key]['screening']++;
                $positive = collect($screening->answers ?? [])
                    ->filter(fn($ans, $key) => str_starts_with((string) $key, 'gejala_') && $ans === 'ya')
                    ->count();
                if ($positive >= 1) {
                    $monthlyAggregates[$key]['suspect']++;
                }
            }

            return [
                'screening' => $chartMonths->map(fn($date) => [
                    'label' => $date->format('M Y'),
                    'value' => $monthlyAggregates[$date->format('Y-m')]['screening'] ?? 0,
                ])->values(),
                'tbc_cases' => $chartMonths->map(fn($date) => [
                    'label' => $date->format('M Y'),
                    'value' => $monthlyAggregates[$date->format('Y-m')]['suspect'] ?? 0,
                ])->values(),
                'suspect_split' => $chartMonths->map(function ($date) use ($monthlyAggregates) {
                    $key = $date->format('Y-m');
                    $suspect = $monthlyAggregates[$key]['suspect'] ?? 0;
                    $total = $monthlyAggregates[$key]['screening'] ?? 0;
                    $nonSuspect = max($total - $suspect, 0);
                    return [
                        'label' => $date->format('M Y'),
                        'suspect' => $suspect,
                        'non_suspect' => $nonSuspect,
                    ];
                })->values(),
            ];
        };

        $uniquePatientCount = function ($screenings) {
            return $screenings
                ->map(function ($screening) {
                    if (!empty($screening->patient_nik)) {
                        return 'nik:' . $screening->patient_nik;
                    }
                    if (!empty($screening->patient_phone)) {
                        return 'phone:' . $screening->patient_phone;
                    }
                    $name = Str::lower(trim($screening->patient_name ?? ''));
                    $address = Str::lower(trim($screening->patient_address ?? ''));
                    return 'name:' . $name . '|addr:' . $address;
                })
                ->filter()
                ->unique()
                ->count();
        };

        switch ($role) {
            case UserRole::Pemda:
                $totalUsers = User::count();
                $activeUsers = User::where('is_active', true)->count();
                $inactiveUsers = $totalUsers - $activeUsers;
                $puskesmasCount = User::where('role', UserRole::Puskesmas->value)->count();
                $kaderCount = User::where('role', UserRole::Kader->value)->count();

                $screenings = PatientScreening::query()->get();
                $totalScreenings = $screenings->count();
                $uniquePatients = $uniquePatientCount($screenings);

                $cards = [
                    [
                        'label' => 'Pengguna Aktif',
                        'value' => number_format($activeUsers),
                        'subtitle' => 'Total ' . number_format($totalUsers) . ' akun',
                        'trend' => $inactiveUsers . ' menunggu verifikasi',
                        'icon' => 'fa-solid fa-users',
                        'color' => 'primary',
                        'url' => route('pemda.verification'),
                    ],
                    [
                        'label' => 'Puskesmas',
                        'value' => number_format($puskesmasCount),
                        'subtitle' => 'Kemitraan Terdaftar',
                        'trend' => $kaderCount . ' kader aktif',
                        'icon' => 'fa-solid fa-hospital',
                        'color' => 'success',
                        'url' => route('pemda.verification', ['role' => UserRole::Puskesmas->value]),
                    ],
                    [
                        'label' => 'Skrining Tercatat',
                        'value' => number_format($totalScreenings),
                        'subtitle' => number_format($uniquePatients) . ' pasien terdata',
                        'trend' => 'Pantau laporan terbaru',
                        'icon' => 'fa-solid fa-notes-medical',
                        'color' => 'warning',
                        'url' => route('pemda.screenings'),
                    ],
                ];

                $recentScreenings = $baseScreeningQuery->limit($recentLimit)->get();

                $screeningsInRange = PatientScreening::where('created_at', '>=', $chartMonths->first())
                    ->get();

                $monthStart = now()->startOfMonth();
                $monthEnd = now()->endOfMonth();
                $screeningsThisMonth = PatientScreening::query()
                    ->whereBetween('created_at', [$monthStart, $monthEnd])
                    ->get();

                $kelurahanGroups = $screeningsThisMonth
                    ->groupBy(fn($screening) => $screening->patient_address_kelurahan ?: 'Tidak diketahui');
                $kelurahanTotals = $kelurahanGroups
                    ->map(fn($items) => $items->count())
                    ->sortDesc();
                $kelurahanLabels = $kelurahanTotals->keys()->values();
                $kelurahanValues = $kelurahanTotals->values();

                $dashboardCharts = array_merge($buildCharts($screeningsInRange), [
                    'kelurahan_labels' => $kelurahanLabels->values(),
                    'kelurahan_values' => $kelurahanValues,
                    'period_label' => now()->format('M Y'),
                ]);
                break;

            case UserRole::Kelurahan:
                $kelurahan = $user;
                $kelurahan->loadMissing('detail');

                $puskesmasIds = collect(optional($kelurahan->detail)->supervisor_id ? [$kelurahan->detail->supervisor_id] : []);
                $kelurahanName = optional($kelurahan->detail)->organization ?? $kelurahan->name;
                $kelurahanKeyword = Str::of($kelurahanName)->replace('Kelurahan', '')->trim()->lower()->value()
                    ?: Str::of($kelurahanName)->trim()->lower()->value();

                $kaderIds = $puskesmasIds->isEmpty()
                    ? collect()
                    : User::query()
                        ->where('role', UserRole::Kader->value)
                        ->whereHas('detail', fn($detail) => $detail->whereIn('supervisor_id', $puskesmasIds))
                        ->pluck('id');

                $screeningsQuery = PatientScreening::query()
                    ->when($kaderIds->isNotEmpty(), fn($query) => $query->whereIn('kader_id', $kaderIds))
                    ->when($kelurahanKeyword, fn($query) => $query->whereRaw('LOWER(patient_address_kelurahan) LIKE ?', ['%' . $kelurahanKeyword . '%']));

                $screenings = $kaderIds->isEmpty() ? collect() : $screeningsQuery->get();
                $totalScreenings = $screenings->count();
                $uniquePatients = $uniquePatientCount($screenings);

                $monthStart = now()->startOfMonth();
                $monthEnd = now()->endOfMonth();

                $suspectThisMonth = $kaderIds->isEmpty()
                    ? 0
                    : PatientScreening::query()
                        ->when($kaderIds->isNotEmpty(), fn($query) => $query->whereIn('kader_id', $kaderIds))
                        ->when($kelurahanKeyword, fn($query) => $query->whereRaw('LOWER(patient_address_kelurahan) LIKE ?', ['%' . $kelurahanKeyword . '%']))
                        ->whereBetween('created_at', [$monthStart, $monthEnd])
                        ->get()
                        ->filter(function ($screening) {
                            $positive = collect($screening->answers ?? [])
                                ->filter(fn($ans, $key) => str_starts_with((string) $key, 'gejala_') && $ans === 'ya')
                                ->count();
                            return $positive >= 1;
                        })
                        ->count();

                $cards = [
                    [
                        'label' => 'Puskesmas Mitra',
                        'value' => number_format($puskesmasIds->count()),
                        'subtitle' => 'Terhubung ke kelurahan ini',
                        'trend' => $kaderIds->count() . ' kader aktif',
                        'icon' => 'fa-solid fa-house-medical',
                        'color' => 'primary',
                    ],
                    [
                        'label' => 'Skrining Tercatat',
                        'value' => number_format($totalScreenings),
                        'subtitle' => number_format($uniquePatients) . ' pasien terdata',
                        'trend' => 'Pantau laporan wilayah',
                        'icon' => 'fa-solid fa-heart-pulse',
                        'color' => 'info',
                    ],
                    [
                        'label' => 'Suspek Bulan Ini',
                        'value' => number_format($suspectThisMonth),
                        'subtitle' => 'Laporan indikasi TBC',
                        'trend' => now()->format('M Y'),
                        'icon' => 'fa-solid fa-triangle-exclamation',
                        'color' => 'warning',
                    ],
                ];

                $recentScreenings = $kaderIds->isEmpty()
                    ? collect()
                    : $baseScreeningQuery
                        ->when($kaderIds->isNotEmpty(), fn($query) => $query->whereIn('kader_id', $kaderIds))
                        ->when($kelurahanKeyword, fn($query) => $query->whereRaw('LOWER(patient_address_kelurahan) LIKE ?', ['%' . $kelurahanKeyword . '%']))
                        ->orderByDesc('created_at')
                        ->limit(50)
                        ->get()
                        ->unique('kader_id')
                        ->take(3)
                        ->values();

                $screeningsThisMonth = $kaderIds->isEmpty()
                    ? collect()
                    : PatientScreening::query()
                        ->with('kader')
                        ->when($kaderIds->isNotEmpty(), fn($query) => $query->whereIn('kader_id', $kaderIds))
                        ->when($kelurahanKeyword, fn($query) => $query->whereRaw('LOWER(patient_address_kelurahan) LIKE ?', ['%' . $kelurahanKeyword . '%']))
                        ->whereBetween('created_at', [$monthStart, $monthEnd])
                        ->get();

                $dailyChartSeed = collect();
                for ($cursor = $monthStart->copy(); $cursor->lte($monthEnd); $cursor->addDay()) {
                    $key = $cursor->toDateString();
                    $dailyChartSeed[$key] = [
                        'label' => $cursor->format('d M'),
                        'value' => 0,
                    ];
                }

                $dailyChart = $screeningsThisMonth
                    ->groupBy(fn($screening) => $screening->created_at->toDateString())
                    ->reduce(function ($carry, $items, $dateKey) {
                        if (!$carry->has($dateKey)) {
                            return $carry;
                        }
                        $carry->put($dateKey, [
                            'label' => $carry[$dateKey]['label'],
                            'value' => $items->count(),
                        ]);
                        return $carry;
                    }, $dailyChartSeed)
                    ->values();

                $extractNumber = static fn(?string $value) => (int) preg_replace('/\D+/', '', (string) $value);
                $rwChart = $screeningsThisMonth
                    ->groupBy(fn($screening) => $screening->patient_address_rw ?? '-')
                    ->map(function ($items, $rw) {
                        $suspectCount = $items->filter(function ($screening) {
                            $positive = collect($screening->answers ?? [])
                                ->filter(fn($ans, $answerKey) => str_starts_with((string) $answerKey, 'gejala_') && $ans === 'ya')
                                ->count();
                            return $positive >= 1;
                        })->count();

                        return [
                            'label' => 'RW ' . $rw,
                            'rw' => $rw,
                            'suspect' => $suspectCount,
                            'non_suspect' => $items->count() - $suspectCount,
                        ];
                    })
                    ->values()
                    ->sortBy(function ($row) use ($extractNumber) {
                        return $extractNumber($row['rw']);
                    })
                    ->values();

                $rtChartByRw = $screeningsThisMonth
                    ->groupBy(fn($screening) => $screening->patient_address_rw ?? '-')
                    ->map(function ($items, $rw) use ($extractNumber) {
                        return $items
                            ->groupBy(fn($screening) => $screening->patient_address_rt ?? '-')
                            ->map(function ($rtItems, $rt) {
                                $suspectCount = $rtItems->filter(function ($screening) {
                                    $positive = collect($screening->answers ?? [])
                                        ->filter(fn($ans, $answerKey) => str_starts_with((string) $answerKey, 'gejala_') && $ans === 'ya')
                                        ->count();
                                    return $positive >= 1;
                                })->count();

                                return [
                                    'label' => 'RT ' . $rt,
                                    'rt' => $rt,
                                    'suspect' => $suspectCount,
                                    'non_suspect' => $rtItems->count() - $suspectCount,
                                ];
                            })
                            ->values()
                            ->sortBy(fn($row) => $extractNumber($row['rt']))
                            ->values();
                    })
                    ->toArray();

                $kaderChart = $screeningsThisMonth
                    ->groupBy(fn($screening) => $screening->kader?->name ?? 'Tidak diketahui')
                    ->map(fn($items) => $items->count())
                    ->sortDesc()
                    ->map(fn($count, $label) => ['label' => $label, 'value' => $count])
                    ->values();

                $dashboardCharts = [
                    'daily_screening' => $dailyChart,
                    'rw_split' => $rwChart,
                    'rt_split' => $rtChartByRw,
                    'kader_screening' => $kaderChart,
                    'period_label' => now()->format('M Y'),
                ];
                break;

            case UserRole::Puskesmas:
                $kaderIds = User::query()
                    ->where('role', UserRole::Kader->value)
                    ->whereHas('detail', fn($detail) => $detail->where('supervisor_id', $user->id))
                    ->pluck('id');

                $screeningsQuery = PatientScreening::query()->whereIn('kader_id', $kaderIds);
                $screenings = $kaderIds->isEmpty() ? collect() : $screeningsQuery->get();

                $cards = [
                    [
                        'label' => 'Kader Aktif',
                        'value' => number_format($kaderIds->count()),
                        'subtitle' => 'Terhubung ke puskesmas ini',
                        'trend' => 'Koordinasikan kegiatan lapangan',
                        'icon' => 'fa-solid fa-people-group',
                        'color' => 'info',
                        'url' => route('puskesmas.kaders'),
                    ],
                    [
                        'label' => 'Skrining Tercatat',
                        'value' => number_format($screenings->count()),
                        'subtitle' => number_format($uniquePatientCount($screenings)) . ' pasien terdata',
                        'trend' => 'Pantau laporan kader',
                        'icon' => 'fa-solid fa-notes-medical',
                        'color' => 'primary',
                        'url' => route('puskesmas.screenings'),
                    ],
                ];

                $recentScreenings = $kaderIds->isEmpty()
                    ? null
                    : $baseScreeningQuery->whereIn('kader_id', $kaderIds)->limit($recentLimit)->get();

                $screeningsInRange = $kaderIds->isEmpty()
                    ? collect()
                    : $screeningsQuery->where('created_at', '>=', $chartMonths->first())->get();
                $dashboardCharts = $buildCharts($screeningsInRange);
                break;

            case UserRole::Kader:
                $screeningsQuery = PatientScreening::query()->where('kader_id', $user->id);
                $screenings = $screeningsQuery->get();

                $cards = [
                    [
                        'label' => 'Skrining Dicatat',
                        'value' => number_format($screenings->count()),
                        'subtitle' => number_format($uniquePatientCount($screenings)) . ' pasien terdata',
                        'trend' => 'Input laporan baru setiap kunjungan',
                        'icon' => 'fa-solid fa-user-nurse',
                        'color' => 'primary',
                    ],
                    [
                        'label' => 'Status Akun',
                        'value' => $user->is_active ? 'Aktif' : 'Tidak Aktif',
                        'subtitle' => 'Anda dapat melakukan skrining',
                        'trend' => $user->is_active ? 'Tetap pantau pasien' : 'Hubungi admin',
                        'icon' => 'fa-solid fa-shield-heart',
                        'color' => $user->is_active ? 'success' : 'warning',
                    ],
                ];

                $recentScreenings = $screenings->isEmpty()
                    ? collect()
                    : $baseScreeningQuery->where('kader_id', $user->id)->limit(3)->get();

                $screeningsInRange = (clone $screeningsQuery)
                    ->where('created_at', '>=', $chartMonths->first())
                    ->get();
                $dashboardCharts = $buildCharts($screeningsInRange);
                break;

            default:
                $cards = [
                    [
                        'label' => 'Pengguna Aktif',
                        'value' => number_format(User::where('is_active', true)->count()),
                        'subtitle' => 'Statistik umum',
                        'trend' => 'Pantau perkembangan aplikasi',
                        'icon' => 'fa-solid fa-users',
                        'color' => 'primary',
                    ],
                ];
                $recentScreenings = $baseScreeningQuery->limit($recentLimit)->get();
                $screeningsInRange = PatientScreening::where('created_at', '>=', $chartMonths->first())->get();
                $dashboardCharts = $buildCharts($screeningsInRange);
                break;
        }

        return view('dashboard', [
            'user' => $user,
            'cards' => $cards,
            'recentScreenings' => $recentScreenings,
            'dashboardCharts' => $dashboardCharts,
        ]);
    }
}
