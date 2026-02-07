<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WaOtp;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules\Password;

class PasswordResetWaController extends Controller
{
    protected WhatsAppService $waService;

    public function __construct(WhatsAppService $waService)
    {
        $this->waService = $waService;
    }

    /**
     * Show the form to request OTP
     */
    public function showRequestForm()
    {
        return view('auth.password-wa-request');
    }

    /**
     * Send OTP to phone number
     */
    public function sendOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
        ]);

        // Normalisasi input jadi beberapa kandidat agar tidak gagal hanya karena awalan 0/62/8
        $digits = preg_replace('/\D/', '', $request->phone);
        $candidates = [];
        // as entered
        $candidates[] = $digits;
        // prefix 0
        if (!str_starts_with($digits, '0')) {
            $candidates[] = '0' . $digits;
        }
        // prefix 62 (pakai normalizePhone agar konsisten)
        $candidates[] = $this->waService->normalizePhone($request->phone);
        // jika user ketik tanpa 0 tapi sudah mulai 8, tambahkan 62 langsung
        if (str_starts_with($digits, '8')) {
            $candidates[] = '62' . $digits;
        }
        $candidates = array_values(array_unique(array_filter($candidates)));

        $userCandidates = User::whereIn('phone', $candidates)->get();

        if ($userCandidates->count() > 1) {
            return back()->withErrors([
                'phone' => 'Nomor ini terdaftar pada lebih dari satu akun. Hubungi admin untuk perbaikan data.'
            ]);
        }

        $user = $userCandidates->first();

        if (!$user) {
            return back()->withErrors(['phone' => 'Nomor WhatsApp tidak terdaftar.']);
        }

        // Normalize phone once (used for rate limit + sending)
        $normalizedPhone = $this->waService->normalizePhone($user->phone);

        // Rate limiting - per phone
        $phoneKey = 'otp_phone:' . $normalizedPhone;
        if (RateLimiter::tooManyAttempts($phoneKey, 3)) {
            $seconds = RateLimiter::availableIn($phoneKey);
            return back()->withErrors([
                'phone' => "Terlalu banyak permintaan. Silakan coba lagi dalam " . ceil($seconds / 60) . " menit."
            ]);
        }

        // Rate limiting - per IP
        $ipKey = 'otp_ip:' . $request->ip();
        if (RateLimiter::tooManyAttempts($ipKey, 10)) {
            $seconds = RateLimiter::availableIn($ipKey);
            return back()->withErrors([
                'phone' => "Terlalu banyak permintaan dari IP ini. Silakan coba lagi dalam " . ceil($seconds / 60) . " menit."
            ]);
        }

        // Generate OTP code
        $code = WaOtp::generateCode();
        $codeHash = WaOtp::hashCode($code);

        // Create OTP record
        WaOtp::create([
            'user_id' => $user->id,
            'phone' => $normalizedPhone,
            'purpose' => 'reset_password',
            'code_hash' => $codeHash,
            'expires_at' => now()->addMinutes(5),
            'ip_address' => $request->ip(),
        ]);

        // Queue WhatsApp message
        $senderName = config('services.whatsapp.sender_name', 'SITUBA');
        $message = "{$senderName} – Kode OTP reset password: {$code}. Berlaku 5 menit. Jangan berikan kode ini ke siapa pun.";
        
        $this->waService->queueMessage(
            $normalizedPhone,
            $message,
            'otp',
            0, // Send immediately
            [
                'user_id' => $user->id,
                'purpose' => 'reset_password',
            ]
        );

        // Hit rate limiters
        RateLimiter::hit($phoneKey, 600); // 10 minutes
        RateLimiter::hit($ipKey, 600); // 10 minutes

        // Store phone in session for verification step
        session(['otp_phone' => $normalizedPhone]);

        return redirect()->route('password.wa.verify')
            ->with('success', 'Kode OTP telah dikirim ke WhatsApp Anda. Silakan cek pesan masuk.');
    }

    /**
     * Show the form to verify OTP and reset password
     */
    public function showVerifyForm()
    {
        if (!session('otp_phone')) {
            return redirect()->route('password.wa')
                ->withErrors(['phone' => 'Silakan request OTP terlebih dahulu.']);
        }

        return view('auth.password-wa-verify');
    }

    /**
     * Verify OTP code
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $phone = session('otp_phone');
        if (!$phone) {
            return back()->withErrors(['code' => 'Session expired. Silakan request OTP ulang.']);
        }

        // Find active OTP for this phone
        $otp = WaOtp::forPhone($phone, 'reset_password')
            ->active()
            ->latest()
            ->first();

        if (!$otp) {
            return back()->withErrors(['code' => 'Kode OTP tidak valid atau sudah expired.']);
        }

        // Check if can verify (not expired, not used, under attempt limit)
        if (!$otp->canVerify()) {
            return back()->withErrors(['code' => 'Kode OTP sudah expired atau terlalu banyak percobaan.']);
        }

        // Verify the code
        if (!$otp->verify($request->code)) {
            $remainingAttempts = 5 - $otp->attempts;
            
            if ($remainingAttempts <= 0) {
                return back()->withErrors(['code' => 'Terlalu banyak percobaan gagal. Silakan request OTP baru.']);
            }

            return back()->withErrors([
                'code' => "Kode OTP salah. Sisa percobaan: {$remainingAttempts}"
            ]);
        }

        // Extra guard: ensure OTP user matches the phone's user
        $localFromSession = str_starts_with($phone, '62') ? '0' . substr($phone, 2) : $phone;
        $userForPhone = User::where('phone', $localFromSession)->first();
        if (!$userForPhone && $localFromSession !== $phone) {
            $userForPhone = User::where('phone', $phone)->first();
        }
        if (!$userForPhone || $userForPhone->id !== $otp->user_id) {
            return back()->withErrors(['code' => 'OTP tidak cocok dengan akun ini. Silakan minta OTP baru.']);
        }

        // OTP verified successfully, store verification token
        $verificationToken = bin2hex(random_bytes(32));
        Cache::put("otp_verified:{$verificationToken}", [
            'user_id' => $otp->user_id,
            'phone' => $otp->phone,
            'otp_id' => $otp->id,
        ], now()->addMinutes(10));

        session([
            'otp_verified_token' => $verificationToken,
            'otp_phone' => $phone,
        ]);

        return view('auth.password-wa-reset', [
            'verificationToken' => $verificationToken,
            'verifiedPhone' => $phone,
        ])->with('otp_success', 'Kode OTP berhasil diverifikasi. Silakan buat password baru.');
    }

    /**
     * Reset password after OTP verification
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'password' => ['required', 'confirmed', Password::defaults()],
            'verification_token' => ['required', 'string'],
            'phone' => ['required', 'string'],
        ]);

        $verificationToken = $request->input('verification_token') ?? session('otp_verified_token');
        $cached = Cache::get("otp_verified:{$verificationToken}");

        if (!$cached || !isset($cached['user_id'], $cached['phone'])) {
            return redirect()->route('password.wa')
                ->withErrors(['password' => 'Verification expired. Silakan ulangi proses reset password.']);
        }

        // Ensure phone matches
        $inputPhone = preg_replace('/\D/', '', $request->phone);
        if (str_starts_with($inputPhone, '62')) {
            $inputPhone = '0' . substr($inputPhone, 2);
        }
        $normalizedInput = $this->waService->normalizePhone($request->phone);

        if (!in_array($cached['phone'], [$normalizedInput, $inputPhone])) {
            return redirect()->route('password.wa')
                ->withErrors(['password' => 'Nomor tidak sesuai dengan OTP. Silakan ulangi proses reset password.']);
        }

        $user = User::find($cached['user_id']);
        if (!$user) {
            return redirect()->route('password.wa')
                ->withErrors(['password' => 'User tidak ditemukan.']);
        }

        // Update password
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // Remove bootstrap password if any (defence in depth)
        if ($user->detail && $user->detail->initial_password) {
            $user->detail->forceFill(['initial_password' => null])->save();
        }

        // Clear session and cache
        Cache::forget("otp_verified:{$verificationToken}");
        session()->forget(['otp_phone', 'otp_verified_token']);

        return redirect()->route('login')
            ->with('success', 'Password berhasil direset. Silakan login dengan password baru Anda.');
    }
}
