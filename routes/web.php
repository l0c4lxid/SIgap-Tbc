<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Kader\MaterialController as KaderMaterialController;
use App\Http\Controllers\Kader\ScreeningController as KaderScreeningController;
use App\Http\Controllers\Kader\PuskesmasController as KaderPuskesmasController;
use App\Http\Controllers\Kader\KelurahanController as KaderKelurahanController;
use App\Http\Controllers\Kelurahan\MonitoringController as KelurahanMonitoringController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\Pemda\ProfileController as PemdaProfileController;
use App\Http\Controllers\Pemda\PartnershipController as PemdaPartnershipController;
use App\Http\Controllers\Pemda\ScreeningController as PemdaScreeningController;
use App\Http\Controllers\Pemda\UserVerificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Puskesmas\KaderController as PuskesmasKaderController;
use App\Http\Controllers\Puskesmas\KelurahanController as PuskesmasKelurahanController;
use App\Http\Controllers\Puskesmas\ScreeningController as PuskesmasScreeningController;
use App\Models\PatientScreening;
use App\Models\User;
use App\Enums\UserRole;
use App\Models\NewsPost;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;



Route::get('/', function () {
    $puskesmasCount = User::where('role', UserRole::Puskesmas->value)->count();
    $kelurahanCount = User::where('role', UserRole::Kelurahan->value)->count();
    $screeningsLast30Days = PatientScreening::query()
        ->where('created_at', '>=', now()->subDays(30))
        ->get();
    $screeningsLast30DaysCount = $screeningsLast30Days->count();
    $screeningsPrev30DaysCount = PatientScreening::query()
        ->whereBetween('created_at', [now()->subDays(60), now()->subDays(30)])
        ->count();
    $screeningsMoMChange = $screeningsPrev30DaysCount > 0
        ? (int) round((($screeningsLast30DaysCount - $screeningsPrev30DaysCount) / $screeningsPrev30DaysCount) * 100)
        : 0;
    $suspectLast30DaysCount = $screeningsLast30Days
        ->filter(function ($screening) {
            $positive = collect($screening->answers ?? [])
                ->filter(fn ($answer, $key) => str_starts_with((string) $key, 'gejala_') && $answer === 'ya')
                ->count();
            return $positive >= 1;
        })
        ->count();
    $followUpRate = $screeningsLast30DaysCount > 0
        ? (int) round(($suspectLast30DaysCount / $screeningsLast30DaysCount) * 100)
        : 0;
    $priorityKelurahan = PatientScreening::query()
        ->whereNotNull('patient_address_kelurahan')
        ->where('patient_address_kelurahan', '!=', '')
        ->where('created_at', '>=', now()->subDays(30))
        ->select('patient_address_kelurahan')
        ->get()
        ->map(function ($screening) {
            $name = trim($screening->patient_address_kelurahan);
            return \Illuminate\Support\Str::startsWith(\Illuminate\Support\Str::lower($name), 'kelurahan ')
                ? \Illuminate\Support\Str::title($name)
                : 'Kelurahan ' . \Illuminate\Support\Str::title($name);
        })
        ->countBy()
        ->sortDesc()
        ->take(3)
        ->keys();
    $latestScreeningAt = PatientScreening::query()
        ->latest('created_at')
        ->value('created_at');
    $latestPuskesmasValidationAt = User::query()
        ->where('role', UserRole::Puskesmas->value)
        ->latest('updated_at')
        ->value('updated_at');

    return view('landing', [
        'puskesmasCount' => $puskesmasCount,
        'kelurahanCount' => $kelurahanCount,
        'screeningsLast30DaysCount' => $screeningsLast30DaysCount,
        'screeningsMoMChange' => $screeningsMoMChange,
        'followUpRate' => $followUpRate,
        'criticalAlertsCount' => $suspectLast30DaysCount,
        'priorityKelurahan' => $priorityKelurahan,
        'latestScreeningAt' => $latestScreeningAt,
        'latestPuskesmasValidationAt' => $latestPuskesmasValidationAt,
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
Route::view('/kebijakan-privasi', 'privacy')->name('privacy');
Route::view('/bantuan', 'help')->name('help');


// Public Material Access
Route::get('/materi-edukasi', [KaderMaterialController::class, 'publicIndex'])->name('public.materi');

// Password Reset via WhatsApp OTP (Public)
Route::get('/lupa-password', [\App\Http\Controllers\Auth\PasswordResetWaController::class, 'showRequestForm'])->name('password.wa');
Route::post('/lupa-password/kirim-otp', [\App\Http\Controllers\Auth\PasswordResetWaController::class, 'sendOtp'])->name('password.wa.request');
Route::get('/lupa-password/verifikasi', [\App\Http\Controllers\Auth\PasswordResetWaController::class, 'showVerifyForm'])->name('password.wa.verify');
Route::post('/lupa-password/verifikasi', [\App\Http\Controllers\Auth\PasswordResetWaController::class, 'verifyOtp'])->name('password.wa.verify.post');
Route::post('/lupa-password/reset', [\App\Http\Controllers\Auth\PasswordResetWaController::class, 'resetPassword'])->name('password.wa.reset');



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

    // Pemda WhatsApp Center
    Route::prefix('pemda')->group(function () {
        Route::get('/whatsapp', [\App\Http\Controllers\Admin\AdminWhatsAppController::class, 'index'])->name('pemda.whatsapp.index');
        Route::get('/whatsapp/create', [\App\Http\Controllers\Admin\AdminWhatsAppController::class, 'create'])->name('pemda.whatsapp.create');
        Route::post('/whatsapp/send', [\App\Http\Controllers\Admin\AdminWhatsAppController::class, 'send'])->name('pemda.whatsapp.send');
        Route::get('/whatsapp/inbox/{messageId}/media', [\App\Http\Controllers\Admin\AdminWhatsAppController::class, 'inboxMedia'])
            ->name('pemda.whatsapp.inbox.media');
        Route::get('/whatsapp/{outbox}', [\App\Http\Controllers\Admin\AdminWhatsAppController::class, 'show'])->name('pemda.whatsapp.show');
        Route::post('/whatsapp/{outbox}/retry', [\App\Http\Controllers\Admin\AdminWhatsAppController::class, 'retry'])->name('pemda.whatsapp.retry');
        Route::post('/whatsapp/{outbox}/cancel', [\App\Http\Controllers\Admin\AdminWhatsAppController::class, 'cancel'])->name('pemda.whatsapp.cancel');
        Route::delete('/whatsapp/{outbox}', [\App\Http\Controllers\Admin\AdminWhatsAppController::class, 'destroy'])->name('pemda.whatsapp.destroy');
        Route::delete('/whatsapp/inbox/{inbox}', [\App\Http\Controllers\Admin\AdminWhatsAppController::class, 'destroyInbox'])->name('pemda.whatsapp.inbox.destroy');
    });



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
    Route::get('/pemda/skrining', [PemdaScreeningController::class, 'index'])
        ->name('pemda.screenings');
    Route::delete('/pemda/skrining/{screening}', [PemdaScreeningController::class, 'destroy'])->name('pemda.screenings.destroy');
    Route::get('/pemda/skrining/{screening}/edit', [PemdaScreeningController::class, 'edit'])->name('pemda.screenings.edit');
    Route::put('/pemda/skrining/{screening}', [PemdaScreeningController::class, 'update'])->name('pemda.screenings.update');
    Route::get('/pemda/skrining/export/excel', [PemdaScreeningController::class, 'exportExcel'])
        ->name('pemda.screenings.export.excel');
    Route::get('/pemda/skrining/{screening}', [PemdaScreeningController::class, 'show'])
        ->name('pemda.screenings.show');

    Route::get('/pemda/profil', [PemdaProfileController::class, 'edit'])
        ->name('pemda.profile.edit');
    Route::put('/pemda/profil', [PemdaProfileController::class, 'update'])
        ->name('pemda.profile.update');

    Route::get('/pemda/kemitraan', [PemdaPartnershipController::class, 'index'])
        ->name('pemda.partnership.index');
    Route::get('/pemda/kemitraan/{kelurahan}', [PemdaPartnershipController::class, 'edit'])
        ->name('pemda.partnership.edit');
    Route::put('/pemda/kemitraan/{kelurahan}', [PemdaPartnershipController::class, 'update'])
        ->name('pemda.partnership.update');
    Route::delete('/pemda/kemitraan/{kelurahan}', [PemdaPartnershipController::class, 'detach'])
        ->name('pemda.partnership.detach');

    Route::get('/puskesmas/skrining', [PuskesmasScreeningController::class, 'index'])
        ->name('puskesmas.screenings');
    Route::get('/puskesmas/skrining/{screening}', [PuskesmasScreeningController::class, 'show'])
        ->name('puskesmas.screenings.show');
    Route::get('/puskesmas/skrining-export/excel', [PuskesmasScreeningController::class, 'exportExcel'])
        ->name('puskesmas.screenings.export.excel');

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
    Route::post('/kelurahan/puskesmas/{puskesmas}/detach', [KelurahanMonitoringController::class, 'detachPuskesmas'])
        ->name('kelurahan.puskesmas.detach');
    Route::get('/kelurahan/kader', [KelurahanMonitoringController::class, 'kaders'])
        ->name('kelurahan.kaders');
    Route::get('/kelurahan/kader/{kader}', [KelurahanMonitoringController::class, 'showKader'])
        ->name('kelurahan.kaders.show');
    Route::post('/kelurahan/kader/{kader}/status', [KelurahanMonitoringController::class, 'updateKaderStatus'])
        ->name('kelurahan.kaders.status');
    Route::get('/kelurahan/kader-export/excel', [KelurahanMonitoringController::class, 'exportKadersExcel'])
        ->name('kelurahan.kaders.export.excel');
    Route::get('/pemda/materi', [KaderMaterialController::class, 'index'])
        ->name('pemda.materi');
    Route::get('/puskesmas/materi', [KaderMaterialController::class, 'index'])
        ->name('puskesmas.materi');
    Route::get('/kelurahan/materi', [KaderMaterialController::class, 'index'])
        ->name('kelurahan.materi');
    Route::get('/kader/materi', [KaderMaterialController::class, 'index'])
        ->name('kader.materi');
    Route::get('/kader/puskesmas', [KaderPuskesmasController::class, 'show'])
        ->name('kader.puskesmas');
    Route::get('/kader/mitra', [KaderKelurahanController::class, 'index'])
        ->name('kader.mitra');
    Route::get('/kader/kelurahan', [KaderKelurahanController::class, 'index'])
        ->name('kader.kelurahan');
    Route::get('/kader/skrining', [KaderScreeningController::class, 'index'])
        ->name('kader.screening.index');
    Route::get('/kader/skrining/create', [KaderScreeningController::class, 'create'])
        ->name('kader.screening.create');
    Route::post('/kader/skrining', [KaderScreeningController::class, 'store'])
        ->name('kader.screening.store');
    Route::get('/kader/skrining-export/excel', [KaderScreeningController::class, 'exportExcel'])
        ->name('kader.screening.export.excel');
    Route::get('/kader/skrining/{screening}', [KaderScreeningController::class, 'show'])
        ->name('kader.screening.show');
    Route::put('/kader/skrining/{screening}', [KaderScreeningController::class, 'update'])
        ->name('kader.screening.update');
    Route::delete('/kader/skrining/{screening}', [KaderScreeningController::class, 'destroy'])
        ->name('kader.screening.destroy');
});

require __DIR__ . '/auth.php';
