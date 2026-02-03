<?php

namespace App\Http\Controllers\Pemda;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Collection;
use Illuminate\Support\Arr;

class UserVerificationController extends Controller
{
    public function index(Request $request)
    {
        abort_if(auth()->user()->role !== UserRole::Pemda, 403);

        $availableRoles = collect([
            UserRole::Kader,
            UserRole::Puskesmas,
            UserRole::Kelurahan,
        ]);

        $selectedRole = $request->string('role')->toString();
        $roleFilter = $availableRoles
            ->map(fn(UserRole $role) => $role->value)
            ->contains($selectedRole) ? $selectedRole : null;

        $pendingUsers = User::query()
            ->with('detail')
            ->where('role', '!=', UserRole::Pemda->value)
            ->when($roleFilter, fn($query) => $query->where('role', $roleFilter))
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
            ->paginate(10);

        return view('pemda.verification', [
            'records' => $pendingUsers,
            'search' => $request->input('q', ''),
            'selectedRole' => $roleFilter,
            'roleOptions' => $availableRoles->map(fn(UserRole $role) => [
                'value' => $role->value,
                'label' => $role->label(),
            ]),
        ]);
    }

    public function show(User $user)
    {
        abort_if(auth()->user()->role !== UserRole::Pemda, 403);

        $user->loadMissing(['detail.supervisor.detail']);

        return view('pemda.verification-detail', [
            'user' => $user,
            'supervisorOptions' => $this->supervisorOptions($user->role),
            'supervisorLabel' => $this->supervisorLabel($user->role),
        ]);
    }

    public function updateInfo(Request $request, User $user): RedirectResponse
    {
        abort_if(auth()->user()->role !== UserRole::Pemda, 403);
        abort_if($user->role === UserRole::Pemda, 403);

        $baseRules = [
            'name' => ['required', 'string', 'max:255'],
            'nik' => ['nullable', 'string', 'max:20'],
            'family_card_number' => ['nullable', 'string', 'max:20'],
            'organization' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'rt_code' => ['nullable', 'string', 'max:5'],
            'rw_code' => ['nullable', 'string', 'max:5'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'supervisor_id' => ['nullable', 'integer'],
        ];

        $detailRules = match ($user->role) {
            UserRole::Kelurahan => [
                'organization' => ['required', 'string', 'max:255'],
                'address' => ['required', 'string', 'max:255'],
            ],
            UserRole::Puskesmas => [
                'organization' => ['required', 'string', 'max:255'],
                'address' => ['required', 'string', 'max:255'],
                'supervisor_id' => [
                    'required',
                    Rule::exists('users', 'id')->where(function ($query) {
                        $query->where('role', UserRole::Kelurahan->value)
                            ->where('is_active', true);
                    }),
                ],
            ],
            UserRole::Kader => [
                'supervisor_id' => [
                    'required',
                    Rule::exists('users', 'id')->where(function ($query) {
                        $query->where('role', UserRole::Puskesmas->value)
                            ->where('is_active', true);
                    }),
                ],
            ],
            default => [],
        };

        $validated = $request->validate(array_merge($baseRules, $detailRules));

        $user->update([
            'name' => $validated['name'],
            'is_active' => $request->boolean('is_active'),
        ]);

        $detailData = Arr::only($validated, [
            'nik',
            'family_card_number',
            'organization',
            'address',
            'rt_code',
            'rw_code',
            'notes',
            'supervisor_id',
        ]);

        $user->detail()->updateOrCreate(
            ['user_id' => $user->id],
            $detailData,
        );

        return back()->with('status', 'Data pengguna berhasil diperbarui.');
    }

    public function updateCredentials(Request $request, User $user): RedirectResponse
    {
        abort_if(auth()->user()->role !== UserRole::Pemda, 403);
        abort_if($user->role === UserRole::Pemda, 403);

        $validated = $request->validate([
            'phone' => ['required', 'string', 'max:25', Rule::unique('users', 'phone')->ignore($user->id)],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        ]);

        $payload = [
            'phone' => $validated['phone'],
        ];

        if (!empty($validated['password'])) {
            $payload['password'] = Hash::make($validated['password']);
        }

        $user->update($payload);

        return back()->with('status', 'Username / password berhasil diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        abort_if(auth()->user()->role !== UserRole::Pemda, 403);
        abort_if($user->role === UserRole::Pemda, 403);

        $name = $user->name;
        $user->delete();

        return redirect()->route('pemda.verification')->with('status', "Pengguna {$name} berhasil dihapus.");
    }

    public function updateStatus(Request $request, User $user): RedirectResponse
    {
        abort_if(auth()->user()->role !== UserRole::Pemda, 403);

        $validated = $request->validate([
            'status' => ['required', 'in:active,inactive'],
        ]);

        $user->is_active = $validated['status'] === 'active';
        $user->save();

        return back()->with('status', 'Status pengguna berhasil diperbarui.');
    }

    public function bulkStatus(Request $request): RedirectResponse
    {
        abort_if(auth()->user()->role !== UserRole::Pemda, 403);

        $validated = $request->validate([
            'status' => ['required', 'in:active,inactive'],
            'role' => ['nullable', Rule::in([
                UserRole::Kader->value,
                UserRole::Puskesmas->value,
                UserRole::Kelurahan->value,
            ])],
        ]);

        $ids = User::query()
            ->select('id')
            ->where('role', '!=', UserRole::Pemda->value)
            ->when($validated['role'] ?? null, fn ($query, $role) => $query->where('role', $role))
            ->pluck('id');

        if ($ids->isEmpty()) {
            return back()->with('status', 'Tidak ada pengguna yang diubah.');
        }

        User::whereIn('id', $ids)->update(['is_active' => $validated['status'] === 'active']);

        return back()->with('status', 'Semua status pengguna berhasil diperbarui.');
    }

    private function supervisorOptions(UserRole $role): Collection
    {
        return match ($role) {
            UserRole::Puskesmas => User::query()
                ->select('id', 'name')
                ->where('role', UserRole::Kelurahan->value)
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
            UserRole::Kader => User::query()
                ->select('id', 'name')
                ->where('role', UserRole::Puskesmas->value)
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
            default => collect(),
        };
    }

    private function supervisorLabel(UserRole $role): ?string
    {
        return match ($role) {
            UserRole::Puskesmas => 'Kelurahan Pembina',
            UserRole::Kader => 'Puskesmas Pembina',
            default => null,
        };
    }
}
