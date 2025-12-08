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
        $mutedFollowUps = collect();
        $hasSelfScreening = false;
        $recentLimit = 3;

        $baseScreeningQuery = PatientScreening::query()
            ->with([
                'patient',
                'patient.detail',
                'kader',
            ])
            ->latest();

        $dashboardCharts = null;

        switch ($role) {
            case UserRole::Pemda:
                $totalUsers = User::count();
                $activeUsers = User::where('is_active', true)->count();
                $inactiveUsers = $totalUsers - $activeUsers;
                $puskesmasCount = User::where('role', UserRole::Puskesmas->value)->count();
                $kaderCount = User::where('role', UserRole::Kader->value)->count();
                $patientCount = User::where('role', UserRole::Pasien->value)->count();
                $activePatients = User::query()
                    ->where('role', UserRole::Pasien->value)
                    ->where('is_active', true)
                    ->get(['id', 'created_at']);
                $activePatientIds = $activePatients->pluck('id');
                $patientCreatedAt = $activePatients->mapWithKeys(fn($patient) => [$patient->id => $patient->created_at]);
                $totalScreenings = PatientScreening::count();

                $cards = [
                    [
                        'label' => 'Pengguna Aktif',
                        'value' => number_format($activeUsers),
                        'subtitle' => 'Total ' . number_format($totalUsers) . ' akun',
                        'trend' => $inactiveUsers . ' menunggu verifikasi',
                        'icon' => 'fa-solid fa-users',
                        'color' => 'primary',
                    ],
                    [
                        'label' => 'Puskesmas',
                        'value' => number_format($puskesmasCount),
                        'subtitle' => 'Kemitraan Terdaftar',
                        'trend' => $kaderCount . ' kader aktif',
                        'icon' => 'fa-solid fa-hospital',
                        'color' => 'success',
                    ],
                    [
                        'label' => 'Pasien Terpantau',
                        'value' => number_format($patientCount),
                        'subtitle' => 'Seluruh kota',
                        'trend' => $totalScreenings . ' skrining tercatat',
                        'icon' => 'fa-solid fa-user-shield',
                        'color' => 'warning',
                    ],
                ];

                $recentScreenings = $baseScreeningQuery->paginate($recentLimit);

                $chartMonths = collect(range(0, 11))
                    ->map(fn($i) => now()->startOfMonth()->subMonths($i))
                    ->sort()
                    ->values();

                $screeningsInRange = PatientScreening::where('created_at', '>=', $chartMonths->first())
                    ->get();

                $monthlyAggregates = [];
                foreach ($screeningsInRange as $screening) {
                    $key = $screening->created_at->format('Y-m');
                    if (!isset($monthlyAggregates[$key])) {
                        $monthlyAggregates[$key] = ['screening' => 0, 'suspect' => 0, 'patients' => []];
                    }
                    $monthlyAggregates[$key]['screening']++;
                    $positive = collect($screening->answers ?? [])->filter(fn($ans) => $ans === 'ya')->count();
                    if ($positive >= 2) {
                        $monthlyAggregates[$key]['suspect']++;
                    }
                    if ($activePatientIds->contains($screening->patient_id)) {
                        $monthlyAggregates[$key]['patients'][$screening->patient_id] = true;
                    }
                }

                $dashboardCharts = [
                    'screening' => $chartMonths->map(fn($date) => [
                        'label' => $date->format('M Y'),
                        'value' => $monthlyAggregates[$date->format('Y-m')]['screening'] ?? 0,
                    ])->values(),
                    'tbc_cases' => $chartMonths->map(fn($date) => [
                        'label' => $date->format('M Y'),
                        'value' => $monthlyAggregates[$date->format('Y-m')]['suspect'] ?? 0,
                    ])->values(),
                    'coverage' => $chartMonths->map(function ($date) use ($monthlyAggregates, $activePatients, $patientCreatedAt) {
                        $key = $date->format('Y-m');
                        $cutoff = $date->endOfMonth();
                        $activeCount = $activePatients->filter(fn($patient) => $patient->created_at->lte($cutoff))->count();
                        $done = isset($monthlyAggregates[$key]['patients'])
                            ? collect(array_keys($monthlyAggregates[$key]['patients']))
                                ->filter(fn($id) => isset($patientCreatedAt[$id]) && $patientCreatedAt[$id]->lte($cutoff))
                                ->count()
                            : 0;
                        $pending = max($activeCount - $done, 0);

                        return [
                            'label' => $date->format('M Y'),
                            'done' => $done,
                            'pending' => $pending,
                        ];
                    })->values(),
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
                break;

            case UserRole::Kelurahan:
                $kelurahan = $user;
                $kelurahan->loadMissing('detail');

                $puskesmasIds = collect(optional($kelurahan->detail)->supervisor_id ? [$kelurahan->detail->supervisor_id] : []);
                $kelurahanName = optional($kelurahan->detail)->organization ?? $kelurahan->name;
                $kelurahanKeyword = Str::of($kelurahanName)->replace('Kelurahan', '')->trim()->lower()->value() ?: Str::of($kelurahanName)->trim()->lower()->value();

                $kaderIds = $puskesmasIds->isEmpty()
                    ? collect()
                    : User::query()
                        ->where('role', UserRole::Kader->value)
                        ->whereHas('detail', fn($detail) => $detail->whereIn('supervisor_id', $puskesmasIds))
                        ->pluck('id');

                $activePatients = $kaderIds->isEmpty()
                    ? collect()
                    : User::query()
                        ->where('role', UserRole::Pasien->value)
                        ->where('is_active', true)
                        ->whereHas('detail', function ($detail) use ($kaderIds, $kelurahanKeyword) {
                            $detail->whereIn('supervisor_id', $kaderIds)
                                ->when($kelurahanKeyword, fn($q) => $q->whereRaw('LOWER(address) LIKE ?', ['%' . $kelurahanKeyword . '%']));
                        })
                        ->get(['id', 'created_at']);
                $patientIds = $activePatients instanceof \Illuminate\Support\Collection ? $activePatients->pluck('id') : collect();
                $patientCreatedAt = $activePatients instanceof \Illuminate\Support\Collection
                    ? $activePatients->mapWithKeys(fn($patient) => [$patient->id => $patient->created_at])
                    : collect();

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
                        'label' => 'Pasien Terpantau',
                        'value' => number_format($patientIds->count()),
                        'subtitle' => 'Lewat kader lapangan',
                        'trend' => $patientIds->isEmpty() ? 'Belum ada pasien' : 'Pantau progres skrining',
                        'icon' => 'fa-solid fa-people-group',
                        'color' => 'info',
                    ],
                    [
                        'label' => 'Skrining Bulan Ini',
                        'value' => number_format(PatientScreening::whereIn('patient_id', $patientIds)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count()),
                        'subtitle' => 'Update aktivitas terbaru',
                        'trend' => now()->format('M Y'),
                        'icon' => 'fa-solid fa-heart-pulse',
                        'color' => 'warning',
                    ],
                ];

                $recentScreenings = $patientIds->isEmpty()
                    ? null
                    : $baseScreeningQuery->whereIn('patient_id', $patientIds)->paginate($recentLimit);

                $chartMonths = collect(range(0, 11))
                    ->map(fn($i) => now()->startOfMonth()->subMonths($i))
                    ->sort()
                    ->values();

                $screeningsInRange = $patientIds->isEmpty()
                    ? collect()
                    : PatientScreening::whereIn('patient_id', $patientIds)
                        ->where('created_at', '>=', $chartMonths->first())
                        ->get();

                $monthlyAggregates = [];
                foreach ($screeningsInRange as $screening) {
                    $key = $screening->created_at->format('Y-m');
                    if (!isset($monthlyAggregates[$key])) {
                        $monthlyAggregates[$key] = ['screening' => 0, 'suspect' => 0, 'patients' => []];
                    }
                    $monthlyAggregates[$key]['screening']++;
                    $positive = collect($screening->answers ?? [])->filter(fn($ans) => $ans === 'ya')->count();
                    if ($positive >= 2) {
                        $monthlyAggregates[$key]['suspect']++;
                    }
                    $monthlyAggregates[$key]['patients'][$screening->patient_id] = true;
                }

                $dashboardCharts = [
                    'screening' => $chartMonths->map(fn($date) => [
                        'label' => $date->format('M Y'),
                        'value' => $monthlyAggregates[$date->format('Y-m')]['screening'] ?? 0,
                    ])->values(),
                    'tbc_cases' => $chartMonths->map(fn($date) => [
                        'label' => $date->format('M Y'),
                        'value' => $monthlyAggregates[$date->format('Y-m')]['suspect'] ?? 0,
                    ])->values(),
                    'coverage' => $chartMonths->map(function ($date) use ($monthlyAggregates, $activePatients, $patientCreatedAt) {
                        $key = $date->format('Y-m');
                        $cutoff = $date->endOfMonth();
                        $activeCount = $activePatients->filter(fn($patient) => $patient->created_at->lte($cutoff))->count();
                        $done = isset($monthlyAggregates[$key]['patients'])
                            ? collect(array_keys($monthlyAggregates[$key]['patients']))
                                ->filter(fn($id) => isset($patientCreatedAt[$id]) && $patientCreatedAt[$id]->lte($cutoff))
                                ->count()
                            : 0;
                        $pending = max($activeCount - $done, 0);

                        return [
                            'label' => $date->format('M Y'),
                            'done' => $done,
                            'pending' => $pending,
                        ];
                    })->values(),
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
                break;

            case UserRole::Puskesmas:
                $kaderIds = User::query()
                    ->where('role', UserRole::Kader->value)
                    ->whereHas('detail', fn($detail) => $detail->where('supervisor_id', $user->id))
                    ->pluck('id');

                $activePatients = User::query()
                    ->where('role', UserRole::Pasien->value)
                    ->where('is_active', true)
                    ->whereHas('detail', fn($detail) => $detail->whereIn('supervisor_id', $kaderIds))
                    ->get(['id', 'created_at']);
                $patientIds = $activePatients->pluck('id');
                $patientCreatedAt = $activePatients->mapWithKeys(fn($patient) => [$patient->id => $patient->created_at]);

                $totalKader = $kaderIds->count();
                $totalPatients = $patientIds->count();
                $screeningsCount = $patientIds->isEmpty()
                    ? 0
                    : PatientScreening::whereIn('patient_id', $patientIds)->count();

                $cards = [
                    [
                        'label' => 'Kader Aktif',
                        'value' => number_format($totalKader),
                        'subtitle' => 'Terhubung ke puskesmas ini',
                        'trend' => 'Koordinasikan kegiatan lapangan',
                        'icon' => 'fa-solid fa-people-group',
                        'color' => 'info',
                    ],
                    [
                        'label' => 'Pasien Binaan',
                        'value' => number_format($totalPatients),
                        'subtitle' => 'Melalui kader mitra',
                        'trend' => $screeningsCount . ' skrining dicatat',
                        'icon' => 'fa-solid fa-users-line',
                        'color' => 'primary',
                    ],
                ];

                $recentScreenings = $patientIds->isEmpty()
                    ? null
                    : $baseScreeningQuery->whereIn('patient_id', $patientIds)->paginate($recentLimit);

                $mutedFollowUps = User::query()
                    ->with(['detail', 'familyMembers'])
                    ->whereIn('id', $patientIds)
                    ->whereHas('detail', fn($detail) => $detail->where('family_card_number', '!=', null))
                    ->get()
                    ->filter(function ($patient) {
                        $kk = $patient->detail->family_card_number;
                        if (!$kk) {
                            return false;
                        }
                        $suspectFamily = User::query()
                            ->where('id', '!=', $patient->id)
                            ->whereHas('detail', fn($detail) => $detail->where('family_card_number', $kk))
                            ->whereDoesntHave('treatments')
                            ->exists();
                        return $suspectFamily;
                    });

                $chartMonths = collect(range(0, 11))
                    ->map(fn($i) => now()->startOfMonth()->subMonths($i))
                    ->sort()
                    ->values();

                $screeningsInRange = $patientIds->isEmpty()
                    ? collect()
                    : PatientScreening::whereIn('patient_id', $patientIds)
                        ->where('created_at', '>=', $chartMonths->first())
                        ->get();

                $monthlyAggregates = [];
                foreach ($screeningsInRange as $screening) {
                    $key = $screening->created_at->format('Y-m');
                    if (!isset($monthlyAggregates[$key])) {
                        $monthlyAggregates[$key] = ['screening' => 0, 'suspect' => 0, 'patients' => []];
                    }
                    $monthlyAggregates[$key]['screening']++;
                    $positive = collect($screening->answers ?? [])->filter(fn($ans) => $ans === 'ya')->count();
                    if ($positive >= 2) {
                        $monthlyAggregates[$key]['suspect']++;
                    }
                    $monthlyAggregates[$key]['patients'][$screening->patient_id] = true;
                }

                $dashboardCharts = [
                    'screening' => $chartMonths->map(fn($date) => [
                        'label' => $date->format('M Y'),
                        'value' => $monthlyAggregates[$date->format('Y-m')]['screening'] ?? 0,
                    ])->values(),
                    'tbc_cases' => $chartMonths->map(fn($date) => [
                        'label' => $date->format('M Y'),
                        'value' => $monthlyAggregates[$date->format('Y-m')]['suspect'] ?? 0,
                    ])->values(),
                    'coverage' => $chartMonths->map(function ($date) use ($monthlyAggregates, $patientIds, $activePatients, $patientCreatedAt) {
                        $key = $date->format('Y-m');
                        $cutoff = $date->endOfMonth();
                        $activeCount = $activePatients->filter(fn($patient) => $patient->created_at->lte($cutoff))->count();
                        $done = isset($monthlyAggregates[$key]['patients'])
                            ? collect(array_keys($monthlyAggregates[$key]['patients']))
                                ->filter(fn($id) => isset($patientCreatedAt[$id]) && $patientCreatedAt[$id]->lte($cutoff))
                                ->count()
                            : 0;
                        $pending = max($activeCount - $done, 0);
                        return [
                            'label' => $date->format('M Y'),
                            'done' => $done,
                            'pending' => $pending,
                        ];
                    })->values(),
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
                break;

            case UserRole::Kader:
                $patientIds = User::query()
                    ->where('role', UserRole::Pasien->value)
                    ->whereRelation('detail', 'supervisor_id', $user->id)
                    ->pluck('id');

                $patientsCount = $patientIds->count();
                $screeningsCount = $patientIds->isEmpty()
                    ? 0
                    : PatientScreening::whereIn('patient_id', $patientIds)->count();

                $cards = [
                    [
                        'label' => 'Pasien Binaan',
                        'value' => number_format($patientsCount),
                        'subtitle' => 'Terdaftar dengan Anda',
                        'trend' => $screeningsCount . ' skrining tercatat',
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

                $recentScreenings = $patientIds->isEmpty()
                    ? null
                    : $baseScreeningQuery->whereIn('patient_id', $patientIds)->paginate($recentLimit);
                break;

            case UserRole::Pasien:
                $latestScreening = $user->screenings()->latest()->first();
                $hasSelfScreening = (bool) $latestScreening;
                $cards = [
                    [
                        'label' => 'Status Akun',
                        'value' => $user->is_active ? 'Aktif' : 'Tidak Aktif',
                        'subtitle' => 'Gunakan akun untuk skrining',
                        'trend' => $user->is_active ? 'Terhubung ke kader' : 'Tunggu verifikasi',
                        'icon' => 'fa-solid fa-user-check',
                        'color' => $user->is_active ? 'success' : 'warning',
                    ],
                    [
                        'label' => 'Skrining Mandiri',
                        'value' => $latestScreening ? 'Sudah' : 'Belum',
                        'subtitle' => $latestScreening ? $latestScreening->created_at->format('d M Y') : 'Segera lakukan skrining',
                        'trend' => $latestScreening ? 'Terima kasih telah melapor' : 'Klik menu Skrining',
                        'icon' => 'fa-solid fa-heartbeat',
                        'color' => $latestScreening ? 'primary' : 'danger',
                    ],
                ];

                $recentScreenings = null;
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
                $recentScreenings = $baseScreeningQuery->paginate($recentLimit);
                break;
        }

        $treatmentReminder = null;
        if ($role === UserRole::Pasien) {
            $activeTreatment = $user->treatments()
                ->with(['kader.detail.supervisor'])
                ->whereIn('status', ['contacted', 'scheduled', 'in_treatment'])
                ->latest()
                ->first();

            if ($activeTreatment) {
                $statusLabels = [
                    'contacted' => 'Perlu Konfirmasi',
                    'scheduled' => 'Terjadwal',
                    'in_treatment' => 'Sedang Berobat',
                    'recovered' => 'Selesai',
                ];

                $kader = $activeTreatment->kader;
                $puskesmas = optional(optional($kader?->detail)->supervisor);
                if (!$puskesmas) {
                    $puskesmas = optional(optional(optional($user->detail)->supervisor)->detail)->supervisor;
                }

                $treatmentReminder = [
                    'status' => $activeTreatment->status,
                    'status_label' => $statusLabels[$activeTreatment->status] ?? ucfirst(str_replace('_', ' ', $activeTreatment->status)),
                    'schedule' => $activeTreatment->next_follow_up_at,
                    'notes' => $activeTreatment->notes,
                    'puskesmas_name' => $puskesmas?->name,
                    'kader_name' => $kader?->name,
                    'kader_phone' => $kader?->phone,
                ];
            }
        }

        return view('dashboard', [
            'user' => $user,
            'cards' => $cards,
            'recentScreenings' => $recentScreenings,
            'treatmentReminder' => $treatmentReminder,
            'dashboardCharts' => $dashboardCharts,
            'hasSelfScreening' => $hasSelfScreening,
        ]);
    }
}
