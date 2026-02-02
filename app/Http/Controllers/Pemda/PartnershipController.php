<?php

namespace App\Http\Controllers\Pemda;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class PartnershipController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('q');

        $kelurahans = User::query()
            ->where('role', UserRole::Kelurahan)
            ->with(['detail.supervisor', 'detail'])
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhereHas('detail', function ($q) use ($search) {
                        $q->where('address', 'like', "%{$search}%");
                    });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $puskesmasList = User::where('role', UserRole::Puskesmas)->orderBy('name')->get();

        return view('pemda.partnership.index', [
            'kelurahans' => $kelurahans,
            'puskesmasList' => $puskesmasList,
            'search' => $search,
        ]);
    }

    public function edit(User $kelurahan)
    {
        $kelurahan->load(['detail.supervisor', 'detail']);
        $puskesmasList = User::where('role', UserRole::Puskesmas)->orderBy('name')->get();

        return view('pemda.partnership.edit', [
            'kelurahan' => $kelurahan,
            'puskesmasList' => $puskesmasList,
        ]);
    }

    public function update(Request $request, User $kelurahan)
    {
        $request->validate([
            'puskesmas_id' => 'required|exists:users,id',
        ]);

        $puskesmas = User::where('id', $request->puskesmas_id)
            ->where('role', UserRole::Puskesmas)
            ->firstOrFail();

        $kelurahan->detail()->update([
            'supervisor_id' => $puskesmas->id,
            'pending_supervisor_id' => null, // Clear pending if force assigning
        ]);

        return redirect()->route('pemda.partnership.edit', $kelurahan)
            ->with('status', "Berhasil menghubungkan {$kelurahan->name} dengan {$puskesmas->name}");
    }

    public function detach(Request $request, User $kelurahan)
    {
        $kelurahan->detail()->update([
            'supervisor_id' => null,
        ]);

        return back()->with('status', "Berhasil melepas kemitraan {$kelurahan->name}");
    }
}
