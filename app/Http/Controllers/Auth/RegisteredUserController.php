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

        $kelurahanOptions = User::query()
            ->select('id', 'name')
            ->where('role', UserRole::Kelurahan->value)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $puskesmasOptions = User::query()
            ->select('id', 'name')
            ->where('role', UserRole::Puskesmas->value)
            ->where('is_active', true)
            ->with('detail')
            ->orderBy('name')
            ->get();

        return view('auth.register', [
            'roleOptions' => $roleOptions,
            'kelurahanOptions' => $kelurahanOptions,
            'puskesmasOptions' => $puskesmasOptions,
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

        $roleSpecificRules = match ($selectedRole) {
            UserRole::Kelurahan => [
                'kelurahan_name' => ['required', 'string', 'max:255'],
                'kelurahan_address' => ['required', 'string', 'max:255'],
                'kelurahan_puskesmas_id' => [
                    'required',
                    Rule::exists('users', 'id')->where(function ($query) {
                        $query->where('role', UserRole::Puskesmas->value)
                              ->where('is_active', true);
                    }),
                ],
            ],
            UserRole::Puskesmas => [
                'puskesmas_name' => ['required', 'string', 'max:255'],
                'puskesmas_address' => ['required', 'string', 'max:255'],
            ],
            UserRole::Kader => [
                'kader_kelurahan_id' => [
                    'required',
                    Rule::exists('users', 'id')->where(function ($query) {
                        $query->where('role', UserRole::Kelurahan->value)
                              ->where('is_active', true);
                    }),
                ],
                'rw_code' => ['required', 'numeric', 'digits_between:1,3'],
                'rt_code' => ['required', 'numeric', 'digits_between:1,3'],
            ],
            default => [],
        };

        $validated = $request->validate(array_merge($baseRules, $roleSpecificRules));

        $user = User::create([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'role' => $request->enum('role', UserRole::class),
            'password' => Hash::make($validated['password']),
            'is_active' => false,
        ]);

        $detailPayload = match ($selectedRole) {
            UserRole::Kelurahan => [
                'organization' => $validated['kelurahan_name'],
                'address' => $validated['kelurahan_address'],
                'supervisor_id' => $validated['kelurahan_puskesmas_id'],
            ],
            UserRole::Puskesmas => [
                'organization' => $validated['puskesmas_name'],
                'address' => $validated['puskesmas_address'],
            ],
            UserRole::Kader => $this->getKaderDetailPayload($validated),
            default => [],
        };

        UserDetail::create(array_merge(['user_id' => $user->id], $detailPayload));

        event(new Registered($user));

        return redirect()->route('login')->with('status', 'Akun berhasil dibuat. Tunggu verifikasi dari yang berwenang sebelum bisa login.');
    }

    private function getKaderDetailPayload(array $validated): array
    {
        $kelurahan = User::find($validated['kader_kelurahan_id']);
        // Kelurahan supervisor is the Puskesmas
        $puskesmasId = $kelurahan->detail?->supervisor_id;

        return [
            'kelurahan_user_id' => $validated['kader_kelurahan_id'],
            'supervisor_id' => $puskesmasId,
            'rw_code' => $validated['rw_code'],
            'rt_code' => $validated['rt_code'],
        ];
    }
}
