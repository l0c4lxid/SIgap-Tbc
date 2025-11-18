<?php

namespace App\Http\Controllers\Pemda;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\UserDetail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    protected function ensurePemda(Request $request): void
    {
        abort_if($request->user()?->role !== UserRole::Pemda, 403);
    }

    public function edit(Request $request): View
    {
        $this->ensurePemda($request);

        $user = $request->user()->loadMissing('detail');

        return view('pemda.profile', [
            'user' => $user,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $this->ensurePemda($request);

        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:25', Rule::unique('users', 'phone')->ignore($user->id)],
            'organization' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'password' => ['nullable', 'confirmed', 'min:8'],
        ]);

        $user->name = $validated['name'];
        $user->phone = $validated['phone'];
        $user->save();

        UserDetail::updateOrCreate(
            ['user_id' => $user->id],
            [
                'organization' => $validated['organization'] ?? null,
                'address' => $validated['address'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ],
        );

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
            $user->save();
        }

        return back()->with('status', 'Profil Pemda berhasil diperbarui.');
    }
}
