<?php

namespace App\Http\Controllers\Kader;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class KelurahanController extends Controller
{
    public function index(Request $request)
    {
        abort_if($request->user()->role !== UserRole::Kader, 403);

        $user = $request->user();
        $user->loadMissing('detail');
        $puskesmasId = optional($user->detail)->supervisor_id;

        $kelurahan = null;
        $puskesmas = null;
        if ($puskesmasId) {
            $puskesmas = User::query()
                ->with('detail')
                ->where('role', UserRole::Puskesmas->value)
                ->where('id', $puskesmasId)
                ->first();

            $kelurahanName = optional($user->detail)->organization ?? $user->name;

            $kelurahan = User::query()
                ->with('detail')
                ->where('role', UserRole::Kelurahan->value)
                ->whereHas('detail', fn($query) => $query->where('supervisor_id', $puskesmasId))
                ->when($kelurahanName, function ($query) use ($kelurahanName) {
                    $query->where(function ($sub) use ($kelurahanName) {
                        $sub->where('name', 'like', '%' . $kelurahanName . '%')
                            ->orWhereHas('detail', fn($detail) => $detail->where('organization', 'like', '%' . $kelurahanName . '%'));
                    });
                })
                ->orderBy('name')
                ->first();

            if (!$kelurahan) {
                $kelurahan = User::query()
                    ->with('detail')
                    ->where('role', UserRole::Kelurahan->value)
                    ->whereHas('detail', fn($query) => $query->where('supervisor_id', $puskesmasId))
                    ->orderBy('name')
                    ->first();
            }
        }

        return view('kader.kelurahan', [
            'kelurahan' => $kelurahan,
            'hasPuskesmas' => (bool) $puskesmasId,
            'puskesmas' => $puskesmas,
        ]);
    }
}
