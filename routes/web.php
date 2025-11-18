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
    Route::view('/dashboard', 'dashboard')->name('dashboard');

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

    Route::get('/kader/pasien', function (Request $request) {
        abort_if(auth()->user()->role !== UserRole::Kader, 403);

        $patients = User::query()
            ->with('detail')
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

        $patient->loadMissing('detail');
        abort_if(optional($patient->detail)->supervisor_id !== $request->user()->id, 404);

        return view('kader.patients-show', [
            'patient' => $patient,
        ]);
    })->name('kader.patients.show');

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

        $detail->screening_started_at = $detail->screening_started_at ?? now();
        $detail->save();

        return redirect()->route('kader.patients')->with('status', 'Skrining pasien telah dicatat.');
    })->name('kader.patients.screening.store');
});

require __DIR__.'/auth.php';
