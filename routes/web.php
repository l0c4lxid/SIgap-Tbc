<?php

use App\Enums\UserRole;
use App\Http\Controllers\ProfileController;
use App\Models\User;
use App\Models\UserDetail;
use App\Models\PatientScreening;
use App\Models\FamilyMember;
use App\Models\PatientTreatment;
use Illuminate\Support\Facades\Hash;

if (!function_exists('ensureFamilyTreatment')) {
    function ensureFamilyTreatment(User $patient, string $status = 'contacted', ?string $nextFollowUp = null, ?string $notes = null): void
    {
        $patient->loadMissing(['detail', 'treatments']);
        $detail = $patient->detail;
        if (!$detail) {
            return;
        }

        if (!$patient->treatments->count()) {
            $patient->treatments()->create([
                'kader_id' => $detail->supervisor_id,
                'status' => $status,
                'next_follow_up_at' => $nextFollowUp,
                'notes' => $notes,
            ]);
        }

        $kk = $detail->family_card_number;
        if (!$kk) {
            return;
        }

        $relatedPatients = User::query()
            ->with(['detail', 'treatments'])
            ->where('role', UserRole::Pasien->value)
            ->where('id', '!=', $patient->id)
            ->whereHas('detail', fn($query) => $query->where('family_card_number', $kk))
            ->get();

        foreach ($relatedPatients as $relative) {
            if (!$relative->treatments->count()) {
                $relative->treatments()->create([
                    'kader_id' => optional($relative->detail)->supervisor_id,
                    'status' => $status,
                    'next_follow_up_at' => $nextFollowUp,
                    'notes' => $notes,
                ]);
            }
        }
    }
}
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\Pemda\ProfileController as PemdaProfileController;

Route::redirect('/', '/login')->name('home');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function (Request $request) {
        $user = $request->user()->loadMissing(['detail.supervisor.detail.supervisor']);
        $role = $user->role;

        $cards = [];
        $recentScreenings = null;
        $mutedFollowUps = collect();

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
                        'label' => 'Puskesmas Terdaftar',
                        'value' => number_format($puskesmasCount),
                        'subtitle' => 'Kemitraan wilayah Surakarta',
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

                $recentScreenings = $baseScreeningQuery->paginate(5);

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
                        $monthlyAggregates[$key] = ['screening' => 0, 'suspect' => 0];
                    }
                    $monthlyAggregates[$key]['screening']++;
                    $positive = collect($screening->answers ?? [])->filter(fn($ans) => $ans === 'ya')->count();
                    if ($positive >= 2) {
                        $monthlyAggregates[$key]['suspect']++;
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
                ];
                break;

            case UserRole::Kelurahan:
                $kelurahan = $user;
                $puskesmasIds = User::query()
                    ->where('role', UserRole::Puskesmas->value)
                    ->whereHas('detail', fn($detail) => $detail->where('supervisor_id', $kelurahan->id))
                    ->pluck('id');

                $kaderIds = $puskesmasIds->isEmpty()
                    ? collect()
                    : User::query()
                        ->where('role', UserRole::Kader->value)
                        ->whereHas('detail', fn($detail) => $detail->whereIn('supervisor_id', $puskesmasIds))
                        ->pluck('id');

                $patientIds = $kaderIds->isEmpty()
                    ? collect()
                    : User::query()
                        ->where('role', UserRole::Pasien->value)
                        ->whereHas('detail', fn($detail) => $detail->whereIn('supervisor_id', $kaderIds))
                        ->pluck('id');

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
                    : $baseScreeningQuery->whereIn('patient_id', $patientIds)->paginate(5);

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
                        $monthlyAggregates[$key] = ['screening' => 0, 'suspect' => 0];
                    }
                    $monthlyAggregates[$key]['screening']++;
                    $positive = collect($screening->answers ?? [])->filter(fn($ans) => $ans === 'ya')->count();
                    if ($positive >= 2) {
                        $monthlyAggregates[$key]['suspect']++;
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
                ];
                break;

            case UserRole::Puskesmas:
                $kaderIds = User::query()
                    ->where('role', UserRole::Kader->value)
                    ->whereHas('detail', fn($detail) => $detail->where('supervisor_id', $user->id))
                    ->pluck('id');

                $patientIds = User::query()
                    ->where('role', UserRole::Pasien->value)
                    ->whereHas('detail', fn($detail) => $detail->whereIn('supervisor_id', $kaderIds))
                    ->pluck('id');

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
                    : $baseScreeningQuery->whereIn('patient_id', $patientIds)->paginate(5);

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
                    : $baseScreeningQuery->whereIn('patient_id', $patientIds)->paginate(5);
                break;

            case UserRole::Pasien:
                $latestScreening = $user->screenings()->latest()->first();
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
                $recentScreenings = $baseScreeningQuery->paginate(5);
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
        ]);
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/pemda/verifikasi', function (Request $request) {
        abort_if(auth()->user()->role !== UserRole::Pemda, 403);

        $pendingUsers = User::query()
            ->with('detail')
            ->where('role', '!=', UserRole::Pemda->value)
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%' . $request->input('q') . '%';
                $query->where(function ($sub) use ($term) {
                    $sub->where('name', 'like', $term)
                        ->orWhere('phone', 'like', $term)
                        ->orWhereHas('detail', function ($detail) use ($term) {
                            $detail->where('organization', 'like', $term)
                                ->orWhere('address', 'like', $term);
                        });
                });
            })
            ->latest()
            ->get();

        return view('pemda.verification', [
            'records' => $pendingUsers,
            'search' => $request->input('q', ''),
        ]);
    })->name('pemda.verification');

    Route::post('/pemda/verifikasi/{user}/status', function (Request $request, User $user) {
        abort_if(auth()->user()->role !== UserRole::Pemda, 403);

        $validated = $request->validate([
            'status' => ['required', 'in:active,inactive'],
        ]);

        $user->is_active = $validated['status'] === 'active';
        $user->save();

        return back()->with('status', 'Status pengguna berhasil diperbarui.');
    })->name('pemda.verification.status');

    Route::post('/pemda/verifikasi/bulk/status', function (Request $request) {
        abort_if(auth()->user()->role !== UserRole::Pemda, 403);

        $validated = $request->validate([
            'status' => ['required', 'in:active,inactive'],
        ]);

        User::where('role', '!=', UserRole::Pemda->value)
            ->update(['is_active' => $validated['status'] === 'active']);

        return back()->with('status', 'Semua status pengguna berhasil diperbarui.');
    })->name('pemda.verification.bulk-status');

    Route::get('/pemda/profil', [PemdaProfileController::class, 'edit'])
        ->name('pemda.profile.edit');
    Route::put('/pemda/profil', [PemdaProfileController::class, 'update'])
        ->name('pemda.profile.update');

    Route::get('/puskesmas/pasien', function (Request $request) {
        abort_if($request->user()->role !== UserRole::Puskesmas, 403);

        $kaderIds = User::query()
            ->where('role', UserRole::Kader->value)
            ->whereHas('detail', fn($detail) => $detail->where('supervisor_id', $request->user()->id))
            ->pluck('id')
            ->all();

        $patients = empty($kaderIds)
            ? collect()
            : User::query()
                ->with(['detail', 'detail.supervisor'])
                ->where('role', UserRole::Pasien->value)
                ->whereHas('detail', fn($detail) => $detail->whereIn('supervisor_id', $kaderIds))
                ->when($request->filled('q'), function ($query) use ($request) {
                    $term = '%' . $request->input('q') . '%';
                    $query->where(function ($sub) use ($term) {
                        $sub->where('name', 'like', $term)
                            ->orWhere('phone', 'like', $term)
                            ->orWhereHas('detail', function ($detail) use ($term) {
                                $detail->where('address', 'like', $term)
                                    ->orWhere('nik', 'like', $term);
                            });
                    });
                })
                ->latest()
                ->get();

        return view('puskesmas.patients', [
            'patients' => $patients,
            'search' => $request->input('q', ''),
        ]);
    })->name('puskesmas.patients');

    Route::get('/puskesmas/pasien/{patient}/anggota', function (Request $request, User $patient) {
        abort_if($request->user()->role !== UserRole::Puskesmas, 403);
        abort_if($patient->role !== UserRole::Pasien, 404);

        $patient->loadMissing(['detail.supervisor.detail', 'familyMembers']);

        $kader = optional($patient->detail)->supervisor;
        abort_if(!$kader, 404);
        abort_if(optional($kader->detail)->supervisor_id !== $request->user()->id, 403);

        return view('puskesmas.patient-family', [
            'patient' => $patient,
            'familyMembers' => $patient->familyMembers()->latest()->get(),
        ]);
    })->name('puskesmas.patient.family');

    Route::post('/puskesmas/pasien/{patient}/anggota/{member}', function (Request $request, User $patient, \App\Models\FamilyMember $member) {
        abort_if($request->user()->role !== UserRole::Puskesmas, 403);
        abort_if($patient->role !== UserRole::Pasien, 404);
        abort_if($member->patient_id !== $patient->id, 404);

        $patient->loadMissing(['detail.supervisor.detail']);
        $kader = optional($patient->detail)->supervisor;
        abort_if(!$kader, 404);
        abort_if(optional($kader->detail)->supervisor_id !== $request->user()->id, 403);

        $validated = $request->validate([
            'screening_status' => ['required', 'in:pending,in_progress,suspect,clear'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $member->update($validated);

        return back()->with('status', 'Status anggota keluarga diperbarui.');
    })->name('puskesmas.patient.family.update');

    Route::get('/puskesmas/skrining', function (Request $request) {
        abort_if($request->user()->role !== UserRole::Puskesmas, 403);

        $kaderIds = User::query()
            ->where('role', UserRole::Kader->value)
            ->whereHas('detail', fn($detail) => $detail->where('supervisor_id', $request->user()->id))
            ->pluck('id');

        $patients = $kaderIds->isEmpty()
            ? collect()
            : User::query()
                ->with([
                    'detail',
                    'detail.supervisor',
                    'screenings' => fn($query) => $query->latest()->limit(1),
                ])
                ->where('role', UserRole::Pasien->value)
                ->whereHas('detail', fn($detail) => $detail->whereIn('supervisor_id', $kaderIds))
                ->when($request->filled('q'), function ($query) use ($request) {
                    $term = '%' . $request->input('q') . '%';
                    $query->where(function ($sub) use ($term) {
                        $sub->where('name', 'like', $term)
                            ->orWhere('phone', 'like', $term)
                            ->orWhereHas('detail', function ($detail) use ($term) {
                                $detail->where('address', 'like', $term)
                                    ->orWhere('nik', 'like', $term);
                            });
                    });
                })
                ->latest()
                ->get();

        return view('puskesmas.screenings', [
            'patients' => $patients,
            'search' => $request->input('q', ''),
        ]);
    })->name('puskesmas.screenings');

    Route::get('/puskesmas/berobat', function (Request $request) {
        abort_if($request->user()->role !== UserRole::Puskesmas, 403);

        $kaderIds = User::query()
            ->where('role', UserRole::Kader->value)
            ->whereHas('detail', fn($detail) => $detail->where('supervisor_id', $request->user()->id))
            ->pluck('id');

        $statuses = [
            'contacted' => 'Perlu Konfirmasi',
            'scheduled' => 'Terjadwal',
            'in_treatment' => 'Sedang Berobat',
            'recovered' => 'Selesai',
        ];

        $familyStatuses = [
            'pending' => ['label' => 'Belum Ditindaklanjuti', 'badge' => 'bg-gradient-secondary'],
            'in_progress' => ['label' => 'Dalam Pemantauan', 'badge' => 'bg-gradient-warning text-dark'],
            'suspect' => ['label' => 'Suspek TBC', 'badge' => 'bg-gradient-danger'],
            'clear' => ['label' => 'Tidak Ada Gejala', 'badge' => 'bg-gradient-success'],
        ];

        $statusParam = $request->input('status');
        $search = $request->input('q', '');

        if ($kaderIds->isEmpty()) {
            $counts = collect(array_fill_keys(array_keys($statuses), 0));
            $treatments = collect();
        } else {
            $baseQuery = PatientTreatment::query()
                ->with([
                    'patient.detail',
                    'patient.detail.supervisor',
                    'patient.screenings' => fn($query) => $query->latest()->limit(1),
                    'patient.familyMembers' => fn($query) => $query->orderBy('name'),
                ])
                ->whereHas('patient.detail', function ($detail) use ($kaderIds) {
                    $detail->whereIn('supervisor_id', $kaderIds);
                });

            $counts = collect();
            foreach (array_keys($statuses) as $status) {
                $counts[$status] = (clone $baseQuery)->where('status', $status)->count();
            }

            $treatmentsQuery = (clone $baseQuery)
                ->when($statusParam && array_key_exists($statusParam, $statuses), function ($query) use ($statusParam) {
                    $query->where('status', $statusParam);
                })

                ->when($search !== '', function ($query) use ($search) {
                    $term = '%' . $search . '%';
                    $query->whereHas('patient', function ($patient) use ($term) {
                        $patient->where('name', 'like', $term)
                            ->orWhere('phone', 'like', $term)
                            ->orWhereHas('detail', function ($detail) use ($term) {
                                $detail->where('address', 'like', $term)
                                    ->orWhere('nik', 'like', $term);
                            });
                    });
                });

            $treatments = $treatmentsQuery
                ->latest()
                ->get();
        }

        return view('puskesmas.treatment', [
            'treatments' => $treatments,
            'statuses' => $statuses,
            'counts' => $counts,
            'activeStatus' => $statusParam,
            'familyStatuses' => $familyStatuses,
            'search' => $search,
        ]);
    })->name('puskesmas.treatment');

    Route::post('/puskesmas/berobat/{patient}', function (Request $request, User $patient) {
        abort_if($request->user()->role !== UserRole::Puskesmas, 403);
        abort_if($patient->role !== UserRole::Pasien, 404);

        $patient->loadMissing(['detail', 'treatments']);
        abort_if(!$patient->detail, 422);

        $validated = $request->validate([
            'status' => ['required', 'in:contacted,scheduled,in_treatment,recovered'],
            'next_follow_up_at' => ['nullable', 'date'],
            'treatment_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $treatment = $patient->treatments()->latest()->first();
        abort_if(!$treatment, 404);

        $treatment->update([
            'status' => $validated['status'],
            'next_follow_up_at' => $validated['next_follow_up_at'] ?? null,
            'notes' => $validated['treatment_notes'] ?? null,
        ]);

        return back()->with('status', 'Status pengobatan pasien diperbarui.');
    })->name('puskesmas.treatment.update');

    Route::post('/puskesmas/berobat', function (Request $request) {
        abort_if($request->user()->role !== UserRole::Puskesmas, 403);

        $validated = $request->validate([
            'patient_id' => ['required', 'exists:users,id'],
            'status' => ['required', 'in:contacted,scheduled,in_treatment,recovered'],
            'next_follow_up_at' => ['nullable', 'date'],
            'treatment_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $patient = User::query()
            ->with(['detail.supervisor.detail', 'treatments'])
            ->where('role', UserRole::Pasien->value)
            ->findOrFail($validated['patient_id']);

        $kader = optional($patient->detail)->supervisor;
        abort_if(!$kader, 404);
        abort_if(optional($kader->detail)->supervisor_id !== $request->user()->id, 403);

        abort_if($patient->treatments()->exists(), 422, 'Pasien sudah ada dalam daftar pengobatan.');

        ensureFamilyTreatment(
            $patient,
            $validated['status'],
            $validated['next_follow_up_at'] ?? null,
            $validated['treatment_notes'] ?? null,
        );

        return back()->with('status', 'Pasien ditambahkan ke daftar pengobatan.');
    })->name('puskesmas.treatment.store');

    Route::get('/puskesmas/kader', function (Request $request) {
        abort_if($request->user()->role !== UserRole::Puskesmas, 403);

        $kaders = User::query()
            ->with('detail')
            ->where('role', UserRole::Kader->value)
            ->whereHas('detail', fn($detail) => $detail->where('supervisor_id', $request->user()->id))
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%' . $request->input('q') . '%';
                $query->where(function ($sub) use ($term) {
                    $sub->where('name', 'like', $term)
                        ->orWhere('phone', 'like', $term)
                        ->orWhereHas('detail', fn($detail) => $detail->where('notes', 'like', $term));
                });
            })
            ->latest()
            ->get();

        return view('puskesmas.kaders', [
            'kaders' => $kaders,
            'search' => $request->input('q', ''),
        ]);
    })->name('puskesmas.kaders');

    Route::get('/kelurahan/puskesmas', function (Request $request) {
        abort_if($request->user()->role !== UserRole::Kelurahan, 403);

        $puskesmasList = User::query()
            ->with('detail')
            ->where('role', UserRole::Puskesmas->value)
            ->whereRelation('detail', 'supervisor_id', $request->user()->id)
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%' . $request->input('q') . '%';
                $query->where(function ($sub) use ($term) {
                    $sub->where('name', 'like', $term)
                        ->orWhereHas('detail', function ($detail) use ($term) {
                            $detail->where('address', 'like', $term)
                                ->orWhere('organization', 'like', $term);
                        });
                });
            })
            ->latest()
            ->get();

        return view('kelurahan.puskesmas', [
            'puskesmasList' => $puskesmasList,
            'search' => $request->input('q', ''),
        ]);
    })->name('kelurahan.puskesmas');

    Route::get('/kelurahan/kader', function (Request $request) {
        abort_if($request->user()->role !== UserRole::Kelurahan, 403);

        $kelurahan = $request->user();
        $puskesmasIds = User::query()
            ->where('role', UserRole::Puskesmas->value)
            ->whereHas('detail', fn($detail) => $detail->where('supervisor_id', $kelurahan->id))
            ->pluck('id');

        $kaders = $puskesmasIds->isEmpty()
            ? collect()
            : User::query()
                ->with(['detail.supervisor'])
                ->where('role', UserRole::Kader->value)
                ->whereHas('detail', fn($detail) => $detail->whereIn('supervisor_id', $puskesmasIds))
                ->when($request->filled('q'), function ($query) use ($request) {
                    $term = '%' . $request->input('q') . '%';
                    $query->where(function ($sub) use ($term) {
                        $sub->where('name', 'like', $term)
                            ->orWhere('phone', 'like', $term)
                            ->orWhereHas('detail', fn($detail) => $detail->where('area', 'like', $term));
                    });
                })
                ->latest()
                ->get();

        return view('kelurahan.kaders', [
            'kaders' => $kaders,
            'search' => $request->input('q', ''),
        ]);
    })->name('kelurahan.kaders');

    Route::post('/kelurahan/kader/{kader}/status', function (Request $request, User $kader) {
        abort_if($request->user()->role !== UserRole::Kelurahan, 403);
        abort_if($kader->role !== UserRole::Kader, 404);

        $kelurahan = $request->user();
        $kader->loadMissing('detail.supervisor.detail.supervisor');
        $kelurahanOwner = optional(optional(optional($kader->detail)->supervisor)->detail)->supervisor;
        abort_if(optional($kelurahanOwner)->id !== $kelurahan->id, 403);

        $validated = $request->validate([
            'status' => ['required', 'in:active,inactive'],
        ]);

        $kader->is_active = $validated['status'] === 'active';
        $kader->save();

        return back()->with('status', 'Status kader diperbarui.');
    })->name('kelurahan.kaders.status');

    Route::get('/kelurahan/pasien', function (Request $request) {
        abort_if($request->user()->role !== UserRole::Kelurahan, 403);

        $kelurahan = $request->user();
        $puskesmasIds = User::query()
            ->where('role', UserRole::Puskesmas->value)
            ->whereHas('detail', fn($detail) => $detail->where('supervisor_id', $kelurahan->id))
            ->pluck('id');

        $patients = $puskesmasIds->isEmpty()
            ? collect()
            : User::query()
                ->with([
                    'detail.supervisor.detail.supervisor',
                    'screenings' => fn($query) => $query->latest()->limit(1),
                    'treatments' => fn($query) => $query->latest()->limit(1),
                ])
                ->where('role', UserRole::Pasien->value)
                ->whereHas('detail.supervisor.detail', fn($detail) => $detail->whereIn('supervisor_id', $puskesmasIds))
                ->when($request->filled('q'), function ($query) use ($request) {
                    $term = '%' . $request->input('q') . '%';
                    $query->where(function ($sub) use ($term) {
                        $sub->where('name', 'like', $term)
                            ->orWhere('phone', 'like', $term)
                            ->orWhereHas('detail', fn($detail) => $detail->where('address', 'like', $term));
                    });
                })
                ->latest()
                ->get();

        $stats = [
            'total' => $patients->count(),
            'screened' => $patients->filter(fn($patient) => $patient->screenings->isNotEmpty())->count(),
            'unscreened' => $patients->filter(fn($patient) => $patient->screenings->isEmpty())->count(),
        ];

        return view('kelurahan.patients', [
            'patients' => $patients,
            'search' => $request->input('q', ''),
            'stats' => $stats,
        ]);
    })->name('kelurahan.patients');

    Route::get('/kelurahan/pasien/{patient}', function (Request $request, User $patient) {
        abort_if($request->user()->role !== UserRole::Kelurahan, 403);
        abort_if($patient->role !== UserRole::Pasien, 404);

        $patient->loadMissing([
            'detail.supervisor.detail.supervisor',
            'screenings' => fn($query) => $query->latest()->limit(5),
            'treatments' => fn($query) => $query->latest()->limit(5),
            'familyMembers' => fn($query) => $query->latest(),
        ]);

        $kader = optional($patient->detail)->supervisor;
        $puskesmas = optional($kader?->detail)->supervisor;
        $kelurahan = optional(optional($puskesmas?->detail)->supervisor);

        abort_if(optional($kelurahan)->id !== $request->user()->id, 403);

        return view('kelurahan.patient-detail', [
            'patient' => $patient,
            'puskesmas' => $puskesmas,
        ]);
    })->name('kelurahan.patients.show');

    Route::get('/kader/puskesmas', function (Request $request) {
        abort_if($request->user()->role !== UserRole::Kader, 403);

        $request->user()->loadMissing('detail');
        $puskesmasId = optional($request->user()->detail)->supervisor_id;

        $puskesmas = null;
        if ($puskesmasId) {
            $puskesmas = User::query()
                ->with('detail')
                ->where('role', UserRole::Puskesmas->value)
                ->where('id', $puskesmasId)
                ->first();
        }

        return view('kader.puskesmas', [
            'puskesmas' => $puskesmas,
        ]);
    })->name('kader.puskesmas');

    Route::get('/pemda/pasien', function (Request $request) {
        abort_if($request->user()->role !== UserRole::Pemda, 403);

        $puskesmasOptions = User::query()
            ->where('role', UserRole::Puskesmas->value)
            ->orderBy('name')
            ->get(['id', 'name']);

        $kelurahanOptions = User::query()
            ->where('role', UserRole::Kelurahan->value)
            ->orderBy('name')
            ->get(['id', 'name']);

        $months = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        $currentYear = now()->year;
        $years = [];
        for ($i = 0; $i < 5; $i++) {
            $years[] = $currentYear - $i;
        }

        $patientsQuery = User::query()
            ->with([
                'detail',
                'detail.supervisor',
                'detail.supervisor.detail',
                'detail.supervisor.detail.supervisor',
                'screenings' => fn($query) => $query->latest()->limit(1),
                'treatments' => fn($query) => $query->latest()->limit(1),
            ])
            ->where('role', UserRole::Pasien->value)
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%' . $request->input('q') . '%';
                $query->where(function ($sub) use ($term) {
                    $sub->where('name', 'like', $term)
                        ->orWhere('phone', 'like', $term)
                        ->orWhereHas('detail', function ($detail) use ($term) {
                            $detail->where('address', 'like', $term)
                                ->orWhere('nik', 'like', $term);
                        });
                });
            })
            ->when($request->filled('puskesmas_id'), function ($query) use ($request) {
                $puskesmasId = $request->input('puskesmas_id');
                $query->whereHas('detail.supervisor.detail.supervisor', function ($puskesmas) use ($puskesmasId) {
                    $puskesmas->where('id', $puskesmasId);
                });
            })
            ->when($request->filled('kelurahan_id'), function ($query) use ($request) {
                $kelurahanId = $request->input('kelurahan_id');
                $query->whereHas('detail.supervisor.detail.supervisor.detail.supervisor', function ($kelurahan) use ($kelurahanId) {
                    $kelurahan->where('id', $kelurahanId);
                });
            })
            ->when($request->filled('month'), function ($query) use ($request) {
                $query->whereMonth('created_at', $request->input('month'));
            })
            ->when($request->filled('year'), function ($query) use ($request) {
                $query->whereYear('created_at', $request->input('year'));
            })
            ->latest();

        $patients = $patientsQuery->get();

        $stats = [
            'total' => $patients->count(),
            'belum_skrining' => $patients->filter(fn($patient) => $patient->screenings->isEmpty())->count(),
            'sudah_skrining' => $patients->filter(fn($patient) => $patient->screenings->isNotEmpty())->count(),
            'suspect' => $patients->filter(function ($patient) {
                $latest = $patient->screenings->first();
                if (!$latest) {
                    return false;
                }
                $positive = collect($latest->answers ?? [])->filter(fn($ans) => $ans === 'ya')->count();
                return $positive >= 2;
            })->count(),
        ];

        return view('pemda.patients', [
            'patients' => $patients,
            'search' => $request->input('q', ''),
            'filters' => [
                'puskesmas_id' => $request->input('puskesmas_id', ''),
                'kelurahan_id' => $request->input('kelurahan_id', ''),
                'month' => $request->input('month', ''),
                'year' => $request->input('year', ''),
            ],
            'stats' => $stats,
            'puskesmasOptions' => $puskesmasOptions,
            'kelurahanOptions' => $kelurahanOptions,
            'months' => $months,
            'years' => $years,
        ]);
    })->name('pemda.patients');

    Route::get('/pemda/pasien/{patient}', function (Request $request, User $patient) {
        abort_if($request->user()->role !== UserRole::Pemda, 403);
        abort_if($patient->role !== UserRole::Pasien, 404);

        $patient->loadMissing([
            'detail.supervisor.detail.supervisor',
            'screenings' => fn($query) => $query->latest()->limit(5),
            'treatments' => fn($query) => $query->latest()->limit(5),
            'familyMembers' => fn($query) => $query->latest(),
        ]);

        return view('pemda.patient-detail', [
            'patient' => $patient,
            'kader' => optional($patient->detail)->supervisor,
            'puskesmas' => optional(optional($patient->detail)->supervisor)->detail->supervisor,
        ]);
    })->name('pemda.patients.show');

    Route::get('/kader/pasien', function (Request $request) {
        abort_if(auth()->user()->role !== UserRole::Kader, 403);

        $patients = User::query()
            ->with(['detail', 'screenings' => fn($query) => $query->latest()])
            ->where('role', UserRole::Pasien->value)
            ->whereRelation('detail', 'supervisor_id', $request->user()->id)
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%' . $request->input('q') . '%';
                $query->where(function ($sub) use ($term) {
                    $sub->where('name', 'like', $term)
                        ->orWhere('phone', 'like', $term)
                        ->orWhereHas('detail', function ($detail) use ($term) {
                            $detail->where('address', 'like', $term)
                                ->orWhere('nik', 'like', $term);
                        });
                });
            })
            ->latest()
            ->get();

        return view('kader.patients', [
            'patients' => $patients,
            'search' => $request->input('q', ''),
        ]);
    })->name('kader.patients');

    Route::get('/kader/skrining', function (Request $request) {
        abort_if(auth()->user()->role !== UserRole::Kader, 403);

        $patientsQuery = User::query()
            ->with([
                'detail.supervisor.detail.supervisor',
                'screenings' => fn($query) => $query->latest()->limit(1),
                'treatments' => fn($query) => $query->latest()->limit(1),
                'familyMembers' => fn($query) => $query->orderBy('name'),
            ])
            ->where('role', UserRole::Pasien->value)
            ->whereRelation('detail', 'supervisor_id', $request->user()->id)
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%' . $request->input('q') . '%';
                $query->where(function ($sub) use ($term) {
                    $sub->where('name', 'like', $term)
                        ->orWhere('phone', 'like', $term)
                        ->orWhereHas('detail', function ($detail) use ($term) {
                            $detail->where('address', 'like', $term)
                                ->orWhere('nik', 'like', $term);
                        });
                });
            });

        $status = $request->input('status');
        if ($status === 'belum') {
            $patientsQuery->doesntHave('screenings');
        } elseif ($status === 'sudah') {
            $patientsQuery->has('screenings');
        }

        $patients = $patientsQuery->latest()->get();

        return view('kader.screening-index', [
            'patients' => $patients,
            'search' => $request->input('q', ''),
            'status' => $status,
        ]);
    })->name('kader.screening.index');

    Route::get('/kader/pasien/create', function (Request $request) {
        abort_if(auth()->user()->role !== UserRole::Kader, 403);

        $kader = $request->user();

        return view('kader.patients-create', [
            'kader' => $kader,
        ]);
    })->name('kader.patients.create');

    Route::post('/kader/pasien', function (Request $request) {
        abort_if(auth()->user()->role !== UserRole::Kader, 403);

        $kader = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:25', Rule::unique('users', 'phone')],
            'nik' => ['required', 'string', 'max:30', Rule::unique('user_details', 'nik')],
            'address' => ['required', 'string', 'max:255'],
        ]);

        $password = 'tbc' . random_int(1000, 9999);

        $user = User::create([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'role' => UserRole::Pasien,
            'password' => Hash::make($password),
            'is_active' => true,
        ]);

        UserDetail::create([
            'user_id' => $user->id,
            'nik' => $validated['nik'],
            'address' => $validated['address'],
            'supervisor_id' => $kader->id,
            'initial_password' => $password,
        ]);

        return redirect()->route('kader.patients')->with('status', 'Pasien baru berhasil dibuat. Password sementara: ' . $password);
    })->name('kader.patients.store');

    Route::get('/kader/pasien/{patient}', function (Request $request, User $patient) {
        abort_if($request->user()->role !== UserRole::Kader, 403);
        abort_if($patient->role !== UserRole::Pasien, 404);

        $patient->loadMissing(['detail', 'familyMembers']);
        abort_if(optional($patient->detail)->supervisor_id !== $request->user()->id, 404);

        return view('kader.patients-show', [
            'patient' => $patient,
        ]);
    })->name('kader.patients.show');

    Route::post('/kader/pasien/{patient}/family', function (Request $request, User $patient) {
        abort_if($request->user()->role !== UserRole::Kader, 403);
        abort_if($patient->role !== UserRole::Pasien, 404);

        $patient->loadMissing('detail');
        abort_if(optional($patient->detail)->supervisor_id !== $request->user()->id, 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'relation' => ['nullable', 'string', 'max:100'],
            'nik' => ['nullable', 'string', 'max:30'],
            'phone' => ['nullable', 'string', 'max:25'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $patient->familyMembers()->create($validated);

        return back()->with('status', 'Anggota keluarga risiko berhasil ditambahkan.');
    })->name('kader.patients.family.store');

    Route::get('/kader/pasien/{patient}/keluarga', function (Request $request, User $patient) {
        abort_if($request->user()->role !== UserRole::Kader, 403);
        abort_if($patient->role !== UserRole::Pasien, 404);

        $patient->loadMissing(['detail', 'familyMembers' => fn($query) => $query->latest()]);
        abort_if(optional($patient->detail)->supervisor_id !== $request->user()->id, 404);

        return view('kader.patient-family', [
            'patient' => $patient,
            'familyMembers' => $patient->familyMembers,
        ]);
    })->name('kader.patients.family');

    Route::get('/kader/pasien/{patient}/screening', function (Request $request, User $patient) {
        abort_if($request->user()->role !== UserRole::Kader, 403);
        abort_if($patient->role !== UserRole::Pasien, 404);

        $patient->loadMissing('detail');
        abort_if(optional($patient->detail)->supervisor_id !== $request->user()->id, 404);

        if ($patient->screenings()->exists()) {
            return redirect()->route('kader.screening.index')->with('status', 'Pasien ini sudah pernah dilakukan skrining.');
        }

        $questions = [
            'batuk_kronis' => 'Apakah pasien batuk lebih dari 2 minggu?',
            'dahak_darah' => 'Apakah batuk mengeluarkan dahak berdarah?',
            'berat_badan' => 'Apakah berat badan turun tanpa sebab jelas?',
            'demam_malam' => 'Apakah sering demam atau berkeringat di malam hari?',
        ];

        return view('kader.patients-screening', [
            'patient' => $patient,
            'questions' => $questions,
        ]);
    })->name('kader.patients.screening');

    Route::post('/kader/pasien/{patient}/screening', function (Request $request, User $patient) {
        abort_if($request->user()->role !== UserRole::Kader, 403);
        abort_if($patient->role !== UserRole::Pasien, 404);

        $patient->loadMissing('detail');
        abort_if(optional($patient->detail)->supervisor_id !== $request->user()->id, 404);

        if ($patient->screenings()->exists()) {
            return redirect()->route('kader.screening.index')->with('status', 'Pasien ini sudah pernah dilakukan skrining.');
        }

        $questions = [
            'batuk_kronis' => 'Apakah pasien batuk lebih dari 2 minggu?',
            'dahak_darah' => 'Apakah batuk mengeluarkan dahak berdarah?',
            'berat_badan' => 'Apakah berat badan turun tanpa sebab jelas?',
            'demam_malam' => 'Apakah sering demam atau berkeringat di malam hari?',
        ];

        $rules = [];
        foreach ($questions as $key => $label) {
            $rules[$key] = ['required', 'in:ya,tidak'];
        }

        $validated = $request->validate($rules);

        $detail = $patient->detail ?? UserDetail::create(['user_id' => $patient->id, 'supervisor_id' => $request->user()->id]);

        PatientScreening::create([
            'patient_id' => $patient->id,
            'kader_id' => $request->user()->id,
            'answers' => $validated,
            'notes' => null,
        ]);

        $positiveCount = collect($validated)->filter(fn($answer) => $answer === 'ya')->count();
        if ($positiveCount >= 2) {
            ensureFamilyTreatment($patient, 'contacted');
        }

        return redirect()->route('kader.patients')->with('status', 'Skrining pasien telah dicatat.');
    })->name('kader.patients.screening.store');

    Route::get('/pasien/skrining', function (Request $request) {
        abort_if($request->user()->role !== UserRole::Pasien, 403);

        $questions = [
            'batuk_kronis' => 'Apakah Anda batuk lebih dari 2 minggu?',
            'dahak_darah' => 'Apakah batuk Anda mengeluarkan dahak berdarah?',
            'berat_badan' => 'Apakah berat badan Anda turun tanpa sebab jelas?',
            'demam_malam' => 'Apakah Anda sering demam atau berkeringat di malam hari?',
        ];

        $latestScreening = $request->user()->screenings()->latest()->first();

        return view('patient.screening', [
            'questions' => $questions,
            'screening' => $latestScreening,
        ]);
    })->name('patient.screening');

    Route::post('/pasien/skrining', function (Request $request) {
        abort_if($request->user()->role !== UserRole::Pasien, 403);

        $user = $request->user()->loadMissing('detail');

        abort_if($user->screenings()->exists(), 403, 'Anda sudah melakukan skrining mandiri.');

        $kaderId = optional($user->detail)->supervisor_id;
        abort_if(empty($kaderId), 422, 'Data kader pendamping belum tersedia.');

        $questions = [
            'batuk_kronis',
            'dahak_darah',
            'berat_badan',
            'demam_malam',
        ];

        $rules = [];
        foreach ($questions as $key) {
            $rules[$key] = ['required', 'in:ya,tidak'];
        }

        $validated = $request->validate($rules);

        PatientScreening::create([
            'patient_id' => $user->id,
            'kader_id' => $kaderId,
            'answers' => $validated,
            'notes' => null,
        ]);

        $positiveCount = collect($validated)->filter(fn($answer) => $answer === 'ya')->count();
        if ($positiveCount >= 2) {
            ensureFamilyTreatment($user, 'contacted');
        }

        return redirect()->route('patient.screening')->with('status', 'Terima kasih, skrining mandiri berhasil dikirim.');
    })->name('patient.screening.store');

    Route::post('/kader/pasien/{patient}/status', function (Request $request, User $patient) {
        abort_if($request->user()->role !== UserRole::Kader, 403);
        abort_if($patient->role !== UserRole::Pasien, 404);

        $patient->loadMissing('detail');
        abort_if(optional($patient->detail)->supervisor_id !== $request->user()->id, 404);

        $validated = $request->validate([
            'status' => ['required', 'in:active,inactive'],
        ]);

        $patient->is_active = $validated['status'] === 'active';
        $patient->save();

        return back()->with('status', 'Status akun pasien diperbarui.');
    })->name('kader.patients.status');

    Route::get('/pasien/anggota', function (Request $request) {
        abort_if($request->user()->role !== UserRole::Pasien, 403);

        $patient = $request->user()->loadMissing('familyMembers');

        return view('patient.family-members', [
            'patient' => $patient,
            'familyMembers' => $patient->familyMembers()->latest()->get(),
        ]);
    })->name('patient.family');

    Route::post('/pasien/anggota', function (Request $request) {
        abort_if($request->user()->role !== UserRole::Pasien, 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'relation' => ['nullable', 'string', 'max:100'],
            'nik' => ['nullable', 'string', 'max:30'],
            'phone' => ['nullable', 'string', 'max:25'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $request->user()->familyMembers()->create($validated);

        return back()->with('status', 'Anggota keluarga berhasil ditambahkan.');
    })->name('patient.family.store');

    Route::get('/pasien/anggota/{member}/skrining', function (Request $request, FamilyMember $member) {
        abort_if($request->user()->role !== UserRole::Pasien, 403);
        abort_if($member->patient_id !== $request->user()->id, 404);

        $questions = [
            'batuk_kronis' => 'Apakah anggota batuk lebih dari 2 minggu?',
            'dahak_darah' => 'Apakah batuk mengeluarkan dahak berdarah?',
            'berat_badan' => 'Apakah berat badan turun tanpa sebab jelas?',
            'demam_malam' => 'Apakah sering demam/keringat malam?',
        ];

        return view('patient.family-screening', [
            'member' => $member,
            'questions' => $questions,
        ]);
    })->name('patient.family.screening');

    Route::post('/pasien/anggota/{member}/skrining', function (Request $request, FamilyMember $member) {
        abort_if($request->user()->role !== UserRole::Pasien, 403);
        abort_if($member->patient_id !== $request->user()->id, 404);

        $questions = [
            'batuk_kronis',
            'dahak_darah',
            'berat_badan',
            'demam_malam',
        ];

        $rules = [];
        foreach ($questions as $key) {
            $rules[$key] = ['required', 'in:ya,tidak'];
        }

        $validated = $request->validate($rules);

        $positive = collect($validated)->filter(fn($answer) => $answer === 'ya')->count();
        $status = $positive >= 2 ? 'suspect' : ($positive === 1 ? 'in_progress' : 'clear');

        $member->update([
            'screening_status' => $status,
            'last_screening_answers' => $validated,
            'last_screened_at' => now(),
        ]);

        return redirect()->route('patient.family')->with('status', 'Hasil skrining anggota disimpan.');
    })->name('patient.family.screening.store');

    Route::get('/pasien/puskesmas', function (Request $request) {
        abort_if($request->user()->role !== UserRole::Pasien, 403);

        $patient = $request->user()->loadMissing([
            'detail.supervisor.detail.supervisor',
            'treatments' => fn($query) => $query->latest(),
        ]);

        $kader = optional($patient->detail)->supervisor;
        $puskesmas = optional($kader?->detail)->supervisor;
        if (!$puskesmas) {
            $puskesmas = optional(optional($patient->detail)->supervisor)->detail->supervisor;
        }

        return view('patient.puskesmas-info', [
            'patient' => $patient,
            'kader' => $kader,
            'puskesmas' => $puskesmas,
            'latestTreatment' => $patient->treatments->first(),
        ]);
    })->name('patient.puskesmas.info');
});

require __DIR__ . '/auth.php';
