<?php

use App\Enums\UserRole;
use App\Http\Controllers\ProfileController;
use App\Models\User;
use App\Models\UserDetail;
use App\Models\PatientScreening;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\Pemda\ProfileController as PemdaProfileController;

Route::redirect('/', '/login')->name('home');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function (Request $request) {
        $user = $request->user()->loadMissing('detail');
        $role = $user->role;

        $cards = [];
        $recentScreenings = null;

        $baseScreeningQuery = PatientScreening::query()
            ->with([
                'patient',
                'patient.detail',
                'kader',
            ])
            ->latest();

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
                        'subtitle' => 'Total '.number_format($totalUsers).' akun',
                        'trend' => $inactiveUsers.' menunggu verifikasi',
                        'icon' => 'fa-solid fa-users',
                        'color' => 'primary',
                    ],
                    [
                        'label' => 'Puskesmas Terdaftar',
                        'value' => number_format($puskesmasCount),
                        'subtitle' => 'Kemitraan wilayah Surakarta',
                        'trend' => $kaderCount.' kader aktif',
                        'icon' => 'fa-solid fa-hospital',
                        'color' => 'success',
                    ],
                    [
                        'label' => 'Pasien Terpantau',
                        'value' => number_format($patientCount),
                        'subtitle' => 'Seluruh kota',
                        'trend' => $totalScreenings.' skrining tercatat',
                        'icon' => 'fa-solid fa-user-shield',
                        'color' => 'warning',
                    ],
                ];

                $recentScreenings = $baseScreeningQuery->paginate(5);
                break;

            case UserRole::Puskesmas:
                $kaderIds = User::query()
                    ->where('role', UserRole::Kader->value)
                    ->whereHas('detail', fn ($detail) => $detail->where('supervisor_id', $user->id))
                    ->pluck('id');

                $patientIds = User::query()
                    ->where('role', UserRole::Pasien->value)
                    ->whereHas('detail', fn ($detail) => $detail->whereIn('supervisor_id', $kaderIds))
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
                        'trend' => $screeningsCount.' skrining dicatat',
                        'icon' => 'fa-solid fa-users-line',
                        'color' => 'primary',
                    ],
                ];

                $recentScreenings = $patientIds->isEmpty()
                    ? null
                    : $baseScreeningQuery->whereIn('patient_id', $patientIds)->paginate(5);
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
                        'trend' => $screeningsCount.' skrining tercatat',
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

        return view('dashboard', [
            'user' => $user,
            'cards' => $cards,
            'recentScreenings' => $recentScreenings,
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
                $term = '%'.$request->input('q').'%';
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
            ->whereHas('detail', fn ($detail) => $detail->where('supervisor_id', $request->user()->id))
            ->pluck('id')
            ->all();

        $patients = empty($kaderIds)
            ? collect()
            : User::query()
                ->with(['detail', 'detail.supervisor'])
                ->where('role', UserRole::Pasien->value)
                ->whereHas('detail', fn ($detail) => $detail->whereIn('supervisor_id', $kaderIds))
                ->when($request->filled('q'), function ($query) use ($request) {
                    $term = '%'.$request->input('q').'%';
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

    Route::get('/puskesmas/skrining', function (Request $request) {
        abort_if($request->user()->role !== UserRole::Puskesmas, 403);

        $kaderIds = User::query()
            ->where('role', UserRole::Kader->value)
            ->whereHas('detail', fn ($detail) => $detail->where('supervisor_id', $request->user()->id))
            ->pluck('id');

        $patients = $kaderIds->isEmpty()
            ? collect()
            : User::query()
                ->with([
                    'detail',
                    'detail.supervisor',
                    'screenings' => fn ($query) => $query->latest()->limit(1),
                ])
                ->where('role', UserRole::Pasien->value)
                ->whereHas('detail', fn ($detail) => $detail->whereIn('supervisor_id', $kaderIds))
                ->when($request->filled('q'), function ($query) use ($request) {
                    $term = '%'.$request->input('q').'%';
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
            ->whereHas('detail', fn ($detail) => $detail->where('supervisor_id', $request->user()->id))
            ->pluck('id');

        $patients = $kaderIds->isEmpty()
            ? collect()
            : User::query()
                ->with(['detail', 'detail.supervisor'])
                ->where('role', UserRole::Pasien->value)
                ->whereHas('detail', function ($detail) use ($kaderIds) {
                    $detail->whereIn('supervisor_id', $kaderIds)
                        ->where('treatment_status', '!=', 'none');
                })
                ->get();

        return view('puskesmas.treatment', [
            'patients' => $patients,
        ]);
    })->name('puskesmas.treatment');

    Route::post('/puskesmas/berobat/{patient}', function (Request $request, User $patient) {
        abort_if($request->user()->role !== UserRole::Puskesmas, 403);
        abort_if($patient->role !== UserRole::Pasien, 404);

        $patient->loadMissing('detail');
        abort_if(! $patient->detail, 422);

        $validated = $request->validate([
            'status' => ['required', 'in:contacted,scheduled,in_treatment,recovered'],
            'next_follow_up_at' => ['nullable', 'date'],
            'treatment_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $patient->detail->update([
            'treatment_status' => $validated['status'],
            'next_follow_up_at' => $validated['next_follow_up_at'] ?? null,
            'treatment_notes' => $validated['treatment_notes'] ?? null,
        ]);

        return back()->with('status', 'Status pengobatan pasien diperbarui.');
    })->name('puskesmas.treatment.update');

    Route::get('/puskesmas/kader', function (Request $request) {
        abort_if($request->user()->role !== UserRole::Puskesmas, 403);

        $kaders = User::query()
            ->with('detail')
            ->where('role', UserRole::Kader->value)
            ->whereHas('detail', fn ($detail) => $detail->where('supervisor_id', $request->user()->id))
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%'.$request->input('q').'%';
                $query->where(function ($sub) use ($term) {
                    $sub->where('name', 'like', $term)
                        ->orWhere('phone', 'like', $term)
                        ->orWhereHas('detail', fn ($detail) => $detail->where('notes', 'like', $term));
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
                $term = '%'.$request->input('q').'%';
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

        $patients = User::query()
            ->with([
                'detail',
                'detail.supervisor',
                'detail.supervisor.detail',
                'detail.supervisor.detail.supervisor',
            ])
            ->where('role', UserRole::Pasien->value)
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%'.$request->input('q').'%';
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

        return view('pemda.patients', [
            'patients' => $patients,
            'search' => $request->input('q', ''),
        ]);
    })->name('pemda.patients');

    Route::get('/kader/pasien', function (Request $request) {
        abort_if(auth()->user()->role !== UserRole::Kader, 403);

        $patients = User::query()
            ->with(['detail', 'screenings' => fn ($query) => $query->latest()])
            ->where('role', UserRole::Pasien->value)
            ->whereRelation('detail', 'supervisor_id', $request->user()->id)
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%'.$request->input('q').'%';
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

        $password = 'tbc'.random_int(1000, 9999);

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

        return redirect()->route('kader.patients')->with('status', 'Pasien baru berhasil dibuat. Password sementara: '.$password);
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
            'phone' => ['nullable', 'string', 'max:25'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $patient->familyMembers()->create($validated);

        return back()->with('status', 'Anggota keluarga risiko berhasil ditambahkan.');
    })->name('kader.patients.family.store');

    Route::get('/kader/pasien/{patient}/screening', function (Request $request, User $patient) {
        abort_if($request->user()->role !== UserRole::Kader, 403);
        abort_if($patient->role !== UserRole::Pasien, 404);

        $patient->loadMissing('detail');
        abort_if(optional($patient->detail)->supervisor_id !== $request->user()->id, 404);

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

        $positiveCount = collect($validated)->filter(fn ($answer) => $answer === 'ya')->count();
        if ($positiveCount >= 2 && $patient->detail) {
            $patient->detail->update(['treatment_status' => 'contacted']);
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

        if ($user->screenings()->exists()) {
            return redirect()->route('patient.screening')->with('status', 'Anda sudah melakukan skrining mandiri.');
        }

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

        $positiveCount = collect($validated)->filter(fn ($answer) => $answer === 'ya')->count();
        if ($positiveCount >= 2 && $user->detail) {
            $user->detail->update(['treatment_status' => 'contacted']);
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
            'phone' => ['nullable', 'string', 'max:25'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $request->user()->familyMembers()->create($validated);

        return back()->with('status', 'Anggota keluarga berhasil ditambahkan.');
    })->name('patient.family.store');
});

require __DIR__.'/auth.php';
