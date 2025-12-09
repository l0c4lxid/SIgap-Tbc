<?php

namespace App\Http\Controllers\Puskesmas;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class KelurahanController extends Controller
{
    public function index(Request $request)
    {
        abort_if($request->user()->role !== UserRole::Puskesmas, 403);

        $puskesmas = $request->user();
        $perPage = 10;
        $search = $request->input('q', '');

        $baseQuery = User::query()
            ->with(['detail', 'detail.supervisor'])
            ->where('role', UserRole::Kelurahan->value)
            ->whereHas('detail', fn($detail) => $detail->where(function ($q) use ($puskesmas) {
                $q->where('supervisor_id', $puskesmas->id)
                    ->orWhere('pending_supervisor_id', $puskesmas->id);
            }))
            ->when($search !== '', function ($query) use ($search) {
                $term = '%' . $search . '%';
                $query->where(function ($sub) use ($term) {
                    $sub->where('name', 'like', $term)
                        ->orWhereHas('detail', fn($detail) => $detail->where('address', 'like', $term)->orWhere('organization', 'like', $term));
                });
            })
            ->latest();

        $kelurahan = (clone $baseQuery)->paginate($perPage)->withQueryString();

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'active' => (clone $baseQuery)->whereHas('detail', fn($detail) => $detail->where('supervisor_id', $puskesmas->id))->count(),
            'pending' => (clone $baseQuery)->whereHas('detail', fn($detail) => $detail->where('pending_supervisor_id', $puskesmas->id))->count(),
        ];

        return view('puskesmas.kelurahan', [
            'kelurahan' => $kelurahan,
            'search' => $search,
            'stats' => $stats,
        ]);
    }

    public function show(Request $request, User $kelurahan)
    {
        abort_if($request->user()->role !== UserRole::Puskesmas, 403);
        abort_if($kelurahan->role !== UserRole::Kelurahan, 404);

        $kelurahan->loadMissing('detail.supervisor');
        abort_if(optional($kelurahan->detail)->supervisor_id !== $request->user()->id, 403);

        $perPage = 10;
        $search = $request->input('q', '');
        $keywords = array_values(array_filter([
            $kelurahan->name,
            optional($kelurahan->detail)->organization,
        ]));

        $patients = empty($keywords)
            ? new LengthAwarePaginator([], 0, $perPage, 1, [
                'path' => $request->url(),
                'query' => $request->query(),
            ])
            : User::query()
                ->with(['detail.supervisor.detail'])
                ->where('role', UserRole::Pasien->value)
                ->whereHas('detail.supervisor.detail', fn($detail) => $detail->where('supervisor_id', $request->user()->id))
                ->whereHas('detail', function ($detail) use ($keywords) {
                    $detail->where(function ($sub) use ($keywords) {
                        foreach ($keywords as $index => $keyword) {
                            $method = $index === 0 ? 'where' : 'orWhere';
                            $sub->{$method}('address', 'like', '%' . $keyword . '%');
                        }
                    });
                })
                ->when($search !== '', function ($query) use ($search) {
                    $term = '%' . $search . '%';
                    $query->where(function ($sub) use ($term) {
                        $sub->where('name', 'like', $term)
                            ->orWhere('phone', 'like', $term)
                            ->orWhereHas('detail', fn($detail) => $detail->where('address', 'like', $term));
                    });
                })
                ->latest()
                ->paginate($perPage)
                ->withQueryString();

        return view('puskesmas.kelurahan-detail', [
            'kelurahan' => $kelurahan,
            'patients' => $patients,
            'search' => $search,
        ]);
    }

    public function destroy(Request $request, User $kelurahan)
    {
        abort_if($request->user()->role !== UserRole::Puskesmas, 403);
        abort_if($kelurahan->role !== UserRole::Kelurahan, 404);

        $kelurahan->loadMissing('detail');
        abort_if(optional($kelurahan->detail)->supervisor_id !== $request->user()->id, 403);

        // Lepas kemitraan tanpa menghapus akun agar kelurahan bisa memilih induk puskesmas baru.
        $kelurahan->detail?->update([
            'supervisor_id' => null,
            'pending_supervisor_id' => null,
        ]);

        return redirect()
            ->route('puskesmas.kelurahan')
            ->with('status', 'Kelurahan telah dilepas dari kemitraan. Kelurahan dapat memilih induk puskesmas baru.');
    }

    public function approveRequest(Request $request, User $kelurahan)
    {
        abort_if($request->user()->role !== UserRole::Puskesmas, 403);
        abort_if($kelurahan->role !== UserRole::Kelurahan, 404);

        $kelurahan->loadMissing('detail');

        $detail = $kelurahan->detail;

        // Pastikan permintaan memang ditujukan ke puskesmas ini.
        if ($detail && $detail->pending_supervisor_id && $detail->pending_supervisor_id !== $request->user()->id) {
            return back()->with('status', 'Permintaan tidak ditujukan ke puskesmas ini.');
        }

        $detail?->update([
            'supervisor_id' => $request->user()->id,
            'pending_supervisor_id' => null,
        ]);

        return back()->with('status', 'Permintaan kemitraan kelurahan disetujui.');
    }
}
