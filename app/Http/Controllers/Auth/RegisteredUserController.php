<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserDetail;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        $roleOptions = UserRole::options([UserRole::Pemda]);

        $puskesmasOptions = User::query()
            ->select('id', 'name')
            ->where('role', UserRole::Puskesmas->value)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $kaderOptions = User::query()
            ->select('id', 'name')
            ->where('role', UserRole::Kader->value)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('auth.register', [
            'roleOptions' => $roleOptions,
            'puskesmasOptions' => $puskesmasOptions,
            'kaderOptions' => $kaderOptions,
        ]);
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $selectedRole = UserRole::tryFrom($request->input('role') ?? '');

        $baseRules = [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:25', 'unique:'.User::class],
            'role' => ['required', Rule::enum(UserRole::class), Rule::notIn([UserRole::Pemda->value])],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ];

        $activePuskesmasRule = Rule::exists('users', 'id')->where(function ($query) {
            $query->where('role', UserRole::Puskesmas->value)
                ->where('is_active', true);
        });

        $activeKaderRule = Rule::exists('users', 'id')->where(function ($query) {
            $query->where('role', UserRole::Kader->value)
                ->where('is_active', true);
        });

        $roleSpecificRules = match ($selectedRole) {
            UserRole::Kelurahan => [
                'kelurahan_name' => ['required', 'string', 'max:255'],
                'kelurahan_address' => ['required', 'string', 'max:255'],
                'kelurahan_phone' => ['required', 'string', 'max:30'],
            ],
            UserRole::Puskesmas => [
                'puskesmas_name' => ['required', 'string', 'max:255'],
                'puskesmas_address' => ['required', 'string', 'max:255'],
                'puskesmas_phone' => ['required', 'string', 'max:30'],
            ],
            UserRole::Kader => [
                'kader_phone' => ['required', 'string', 'max:30'],
                'kader_puskesmas_id' => ['required', $activePuskesmasRule],
            ],
            UserRole::Pasien => [
                'pasien_kk' => ['required', 'string', 'max:30'],
                'pasien_address' => ['required', 'string', 'max:255'],
                'pasien_kader_id' => ['required', $activeKaderRule],
            ],
            default => [],
        };

        $validated = $request->validate(array_merge($baseRules, $roleSpecificRules));

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['phone'].'@sigap-tbc.local',
            'phone' => $validated['phone'],
            'role' => $request->enum('role', UserRole::class),
            'password' => Hash::make($validated['password']),
            'is_active' => false,
        ]);

        $detailPayload = match ($selectedRole) {
            UserRole::Kelurahan => [
                'organization' => $validated['kelurahan_name'],
                'address' => $validated['kelurahan_address'],
                'phone' => $validated['kelurahan_phone'],
            ],
            UserRole::Puskesmas => [
                'organization' => $validated['puskesmas_name'],
                'address' => $validated['puskesmas_address'],
                'phone' => $validated['puskesmas_phone'],
            ],
            UserRole::Kader => [
                'phone' => $validated['kader_phone'],
                'supervisor_id' => $validated['kader_puskesmas_id'],
            ],
            UserRole::Pasien => [
                'family_card_number' => $validated['pasien_kk'],
                'address' => $validated['pasien_address'],
                'supervisor_id' => $validated['pasien_kader_id'],
            ],
            default => [],
        };

        UserDetail::create(array_merge(['user_id' => $user->id], $detailPayload));

        event(new Registered($user));

        return redirect()->route('login')->with('status', 'Akun berhasil dibuat. Tunggu verifikasi dari Pemda sebelum bisa login.');
    }
}
