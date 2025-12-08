<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Kader\MaterialController as KaderMaterialController;
use App\Http\Controllers\Kader\PatientController as KaderPatientController;
use App\Http\Controllers\Kader\PuskesmasController as KaderPuskesmasController;
use App\Http\Controllers\Kelurahan\MonitoringController as KelurahanMonitoringController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\Patient\PatientController as PatientSelfController;
use App\Http\Controllers\Pemda\PatientController as PemdaPatientController;
use App\Http\Controllers\Pemda\ProfileController as PemdaProfileController;
use App\Http\Controllers\Pemda\UserVerificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Puskesmas\KaderController as PuskesmasKaderController;
use App\Http\Controllers\Puskesmas\KelurahanController as PuskesmasKelurahanController;
use App\Http\Controllers\Puskesmas\PatientController as PuskesmasPatientController;
use App\Http\Controllers\Puskesmas\ScreeningController as PuskesmasScreeningController;
use App\Http\Controllers\Puskesmas\TreatmentController as PuskesmasTreatmentController;
use App\Models\User;
use App\Enums\UserRole;
use App\Models\NewsPost;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $puskesmasCount = User::where('role', UserRole::Puskesmas->value)->count();
    $kelurahanCount = User::where('role', UserRole::Kelurahan->value)->count();

    return view('landing', [
        'puskesmasCount' => $puskesmasCount,
        'kelurahanCount' => $kelurahanCount,
    ]);
})->name('home');

Route::get('/robots.txt', function () {
    $base = rtrim(config('app.url') ?: request()->getSchemeAndHttpHost(), '/');
    $lines = [
        'User-agent: *',
        'Allow: /',
        "Sitemap: {$base}/sitemap.xml",
    ];

    return response(implode("\n", $lines), 200, ['Content-Type' => 'text/plain']);
});

Route::get('/sitemap.xml', function () {
    $base = rtrim(config('app.url') ?: request()->getSchemeAndHttpHost(), '/');
    $posts = NewsPost::query()
        ->where('status', 'published')
        ->orderByDesc('published_at')
        ->get();

    return response()
        ->view('sitemap', [
            'base' => $base,
            'posts' => $posts,
        ])
        ->header('Content-Type', 'application/xml');
});
Route::get('/blog', [NewsController::class, 'publicIndex'])->name('blog.index');
Route::get('/blog/{newsPost}', [NewsController::class, 'publicShow'])->name('blog.show');

Route::middleware('auth')->group(function () {
    Route::get('/berita', [NewsController::class, 'index'])->name('news.index');
    Route::get('/berita/create', [NewsController::class, 'create'])->name('news.create');
    Route::post('/berita', [NewsController::class, 'store'])->name('news.store');
    Route::get('/berita/{newsPost}/edit', [NewsController::class, 'edit'])->name('news.edit');
    Route::put('/berita/{newsPost}', [NewsController::class, 'update'])->name('news.update');
    Route::delete('/berita/{newsPost}', [NewsController::class, 'destroy'])->name('news.destroy');
    Route::post('/berita/{newsPost}/publish', [NewsController::class, 'publish'])->name('news.publish');
    Route::post('/berita/{newsPost}/unpublish', [NewsController::class, 'unpublish'])->name('news.unpublish');

    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/pemda/verifikasi', [UserVerificationController::class, 'index'])
        ->name('pemda.verification');
    Route::post('/pemda/verifikasi/bulk/status', [UserVerificationController::class, 'bulkStatus'])
        ->name('pemda.verification.bulk-status');
    Route::get('/pemda/verifikasi/{user}', [UserVerificationController::class, 'show'])
        ->whereNumber('user')
        ->name('pemda.verification.show');
    Route::put('/pemda/verifikasi/{user}', [UserVerificationController::class, 'updateInfo'])
        ->whereNumber('user')
        ->name('pemda.verification.update');
    Route::put('/pemda/verifikasi/{user}/credentials', [UserVerificationController::class, 'updateCredentials'])
        ->whereNumber('user')
        ->name('pemda.verification.credentials');
    Route::delete('/pemda/verifikasi/{user}', [UserVerificationController::class, 'destroy'])
        ->whereNumber('user')
        ->name('pemda.verification.destroy');
    Route::post('/pemda/verifikasi/{user}/status', [UserVerificationController::class, 'updateStatus'])
        ->whereNumber('user')
        ->name('pemda.verification.status');

    Route::get('/pemda/profil', [PemdaProfileController::class, 'edit'])
        ->name('pemda.profile.edit');
    Route::put('/pemda/profil', [PemdaProfileController::class, 'update'])
        ->name('pemda.profile.update');

    Route::get('/puskesmas/pasien', [PuskesmasPatientController::class, 'index'])
        ->name('puskesmas.patients');
    Route::get('/puskesmas/pasien/{patient}/anggota', [PuskesmasPatientController::class, 'family'])
        ->name('puskesmas.patient.family');
    Route::post('/puskesmas/pasien/{patient}/anggota', [PuskesmasPatientController::class, 'storeFamily'])
        ->name('puskesmas.patient.family.store');
    Route::post('/puskesmas/pasien/{patient}/anggota/{member}', [PuskesmasPatientController::class, 'updateFamilyMember'])
        ->name('puskesmas.patient.family.update');

    Route::get('/puskesmas/skrining', [PuskesmasScreeningController::class, 'index'])
        ->name('puskesmas.screenings');
    Route::get('/puskesmas/skrining/{patient}', [PuskesmasScreeningController::class, 'show'])
        ->name('puskesmas.screenings.show');
    Route::get('/puskesmas/skrining-export/excel', [PuskesmasScreeningController::class, 'exportExcel'])
        ->name('puskesmas.screenings.export.excel');

    Route::get('/puskesmas/berobat', [PuskesmasTreatmentController::class, 'index'])
        ->name('puskesmas.treatment');
    Route::get('/puskesmas/berobat/{patient}', [PuskesmasTreatmentController::class, 'show'])
        ->name('puskesmas.treatment.show');
    Route::post('/puskesmas/berobat/{patient}', [PuskesmasTreatmentController::class, 'updateStatus'])
        ->name('puskesmas.treatment.update');
    Route::post('/puskesmas/berobat', [PuskesmasTreatmentController::class, 'store'])
        ->name('puskesmas.treatment.store');
    Route::get('/puskesmas/berobat-export/excel', [PuskesmasTreatmentController::class, 'exportExcel'])
        ->name('puskesmas.treatment.export.excel');

    Route::get('/puskesmas/kelurahan', [PuskesmasKelurahanController::class, 'index'])
        ->name('puskesmas.kelurahan');
    Route::get('/puskesmas/kelurahan/{kelurahan}', [PuskesmasKelurahanController::class, 'show'])
        ->name('puskesmas.kelurahan.show');
    Route::delete('/puskesmas/kelurahan/{kelurahan}', [PuskesmasKelurahanController::class, 'destroy'])
        ->name('puskesmas.kelurahan.destroy');
    Route::post('/puskesmas/kelurahan/{kelurahan}/approve', [PuskesmasKelurahanController::class, 'approveRequest'])
        ->name('puskesmas.kelurahan.approve');

    Route::get('/puskesmas/kader', [PuskesmasKaderController::class, 'index'])
        ->name('puskesmas.kaders');
    Route::get('/puskesmas/kader/{kader}', [PuskesmasKaderController::class, 'show'])
        ->name('puskesmas.kaders.show');
    Route::post('/puskesmas/kader/{kader}/status', [PuskesmasKaderController::class, 'updateStatus'])
        ->name('puskesmas.kaders.status');
    Route::get('/puskesmas/kader-export/pdf', [PuskesmasKaderController::class, 'exportPdf'])
        ->name('puskesmas.kaders.export.pdf');
    Route::get('/puskesmas/kader-export/excel', [PuskesmasKaderController::class, 'exportExcel'])
        ->name('puskesmas.kaders.export.excel');

    Route::get('/kelurahan/puskesmas', [KelurahanMonitoringController::class, 'puskesmas'])
        ->name('kelurahan.puskesmas');
    Route::post('/kelurahan/puskesmas/{puskesmas}/request', [KelurahanMonitoringController::class, 'requestPuskesmas'])
        ->name('kelurahan.puskesmas.request');
    Route::get('/kelurahan/kader', [KelurahanMonitoringController::class, 'kaders'])
        ->name('kelurahan.kaders');
    Route::get('/kelurahan/kader/{kader}', [KelurahanMonitoringController::class, 'showKader'])
        ->name('kelurahan.kaders.show');
    Route::post('/kelurahan/kader/{kader}/status', [KelurahanMonitoringController::class, 'updateKaderStatus'])
        ->name('kelurahan.kaders.status');
    Route::get('/kelurahan/kader-export/excel', [KelurahanMonitoringController::class, 'exportKadersExcel'])
        ->name('kelurahan.kaders.export.excel');
    Route::get('/kelurahan/pasien', [KelurahanMonitoringController::class, 'patients'])
        ->name('kelurahan.patients');
    Route::get('/kelurahan/pasien/{patient}', [KelurahanMonitoringController::class, 'showPatient'])
        ->name('kelurahan.patients.show');

    Route::get('/kader/materi', [KaderMaterialController::class, 'index'])
        ->name('kader.materi');
    Route::get('/kader/puskesmas', [KaderPuskesmasController::class, 'show'])
        ->name('kader.puskesmas');
    Route::get('/kader/pasien', [KaderPatientController::class, 'index'])
        ->name('kader.patients');
    Route::get('/kader/skrining', [KaderPatientController::class, 'screeningIndex'])
        ->name('kader.screening.index');
    Route::get('/kader/pasien/create', [KaderPatientController::class, 'create'])
        ->name('kader.patients.create');
    Route::post('/kader/pasien', [KaderPatientController::class, 'store'])
        ->name('kader.patients.store');
    Route::get('/kader/pasien/{patient}', [KaderPatientController::class, 'show'])
        ->name('kader.patients.show');
    Route::post('/kader/pasien/{patient}/family', [KaderPatientController::class, 'storeFamily'])
        ->name('kader.patients.family.store');
    Route::get('/kader/pasien/{patient}/keluarga', [KaderPatientController::class, 'family'])
        ->name('kader.patients.family');
    Route::get('/kader/pasien/{patient}/screening', [KaderPatientController::class, 'screeningForm'])
        ->name('kader.patients.screening');
    Route::post('/kader/pasien/{patient}/screening', [KaderPatientController::class, 'storeScreening'])
        ->name('kader.patients.screening.store');
    Route::post('/kader/pasien/{patient}/status', [KaderPatientController::class, 'updateStatus'])
        ->name('kader.patients.status');

    Route::get('/pasien/materi', [PatientSelfController::class, 'materials'])
        ->name('patient.materi');
    Route::get('/pasien/skrining', [PatientSelfController::class, 'screeningForm'])
        ->name('patient.screening');
    Route::post('/pasien/skrining', [PatientSelfController::class, 'storeScreening'])
        ->name('patient.screening.store');
    Route::get('/pasien/anggota', [PatientSelfController::class, 'family'])
        ->name('patient.family');
    Route::post('/pasien/anggota', [PatientSelfController::class, 'storeFamily'])
        ->name('patient.family.store');
    Route::get('/pasien/anggota/{member}/skrining', [PatientSelfController::class, 'familyScreening'])
        ->name('patient.family.screening');
    Route::post('/pasien/anggota/{member}/skrining', [PatientSelfController::class, 'storeFamilyScreening'])
        ->name('patient.family.screening.store');
    Route::get('/pasien/puskesmas', [PatientSelfController::class, 'puskesmasInfo'])
        ->name('patient.puskesmas.info');

    Route::get('/pemda/pasien', [PemdaPatientController::class, 'index'])
        ->name('pemda.patients');
    Route::get('/pemda/pasien/{patient}', [PemdaPatientController::class, 'show'])
        ->name('pemda.patients.show');
});

require __DIR__ . '/auth.php';
