<?php

namespace App\Http\Controllers\Kelurahan;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\PatientScreening;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Facades\Excel;

class MonitoringController extends Controller
{
    public function puskesmas(Request $request)
    {
        abort_if($request->user()->role !== UserRole::Kelurahan, 403);

        $kelurahan = $request->user()->loadMissing('detail');
        $puskesmasId = optional($kelurahan->detail)->supervisor_id;

        $puskesmasList = User::query()
            ->with('detail')
            ->where('role', UserRole::Puskesmas->value)
            ->when($puskesmasId, fn($q) => $q->where('id', $puskesmasId))
            ->when(!$puskesmasId, fn($q) => $q->orderBy('name'))
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%' . $request->input('q') . '%';
                $query->where(function ($sub) use ($term) {
                    $sub->where('name', 'like', $term)
                        ->orWhereHas('detail', function ($detail) use ($term) {
                            $detail->where('address', 'like', $term)
                                ->orWhere('organization', 'like', $term);
                        });
                });
            })
            ->paginate(10)
            ->withQueryString();

        return view('kelurahan.puskesmas', [
            'puskesmasList' => $puskesmasList,
            'search' => $request->input('q', ''),
            'currentPuskesmasId' => $puskesmasId,
            'kelurahan' => $kelurahan,
        ]);
    }

    public function requestPuskesmas(Request $request, User $puskesmas)
    {
        abort_if($request->user()->role !== UserRole::Kelurahan, 403);
        abort_if($puskesmas->role !== UserRole::Puskesmas, 404);

        $kelurahan = $request->user()->loadMissing('detail');

        $detail = $kelurahan->detail;

        // Jika sudah ada mitra aktif, minta lepas dulu.
        if ($detail && $detail->supervisor_id) {
            return back()->with('status', 'Lepas mitra aktif sebelum mengajukan puskesmas baru.');
        }

        $detail?->update([
            'pending_supervisor_id' => $puskesmas->id,
        ]);

        return back()->with('status', 'Permintaan puskesmas induk dikirim. Menunggu persetujuan puskesmas.');
    }

    public function detachPuskesmas(Request $request, User $puskesmas)
    {
        abort_if($request->user()->role !== UserRole::Kelurahan, 403);
        abort_if($puskesmas->role !== UserRole::Puskesmas, 404);

        $kelurahan = $request->user()->loadMissing('detail');
        $currentId = optional($kelurahan->detail)->supervisor_id;
        if ($currentId !== $puskesmas->id) {
            return back()->with('status', 'Tidak ada kemitraan aktif yang bisa dilepas.');
        }

        $kelurahan->detail?->update([
            'supervisor_id' => null,
            'pending_supervisor_id' => null,
        ]);

        return back()->with('status', 'Puskesmas mitra dilepas. Silakan ajukan mitra baru.');
    }

    public function kaders(Request $request)
    {
        abort_if($request->user()->role !== UserRole::Kelurahan, 403);

        $perPage = 10;
        $kaderQuery = $this->kaderQuery($request);
        $kaders = $kaderQuery
            ? $kaderQuery->paginate($perPage)->withQueryString()
            : new LengthAwarePaginator([], 0, $perPage, 1, [
                'path' => $request->url(),
                'query' => $request->query(),
            ]);

        return view('kelurahan.kaders', [
            'kaders' => $kaders,
            'search' => $request->input('q', ''),
        ]);
    }

    public function updateKaderStatus(Request $request, User $kader)
    {
        abort_if($request->user()->role !== UserRole::Kelurahan, 403);
        abort_if($kader->role !== UserRole::Kader, 404);

        $kelurahan = $request->user();
        $kader->loadMissing('detail.supervisor');
        $kaderPuskesmasId = optional($kader->detail)->supervisor_id;
        $kelurahanPuskesmasId = optional($kelurahan->detail)->supervisor_id;
        abort_if(!$kaderPuskesmasId || $kaderPuskesmasId !== $kelurahanPuskesmasId, 403);

        $validated = $request->validate([
            'status' => ['required', 'in:active,inactive'],
        ]);

        $kader->is_active = $validated['status'] === 'active';
        $kader->save();

        return back()->with('status', 'Status kader diperbarui.');
    }

    public function showKader(Request $request, User $kader)
    {
        abort_if($request->user()->role !== UserRole::Kelurahan, 403);
        abort_if($kader->role !== UserRole::Kader, 404);

        $kelurahanName = optional($request->user()->detail)->organization ?? $request->user()->name;
        $kelurahanKeyword = Str::of($kelurahanName)->replace('Kelurahan', '')->trim()->lower()->value()
            ?: Str::of($kelurahanName)->trim()->lower()->value();

        $kaderRecord = $this->kaderQuery($request)?->where('id', $kader->id)->first();
        abort_if(! $kaderRecord, 403);

        $kaderRecord->loadMissing(['detail.supervisor.detail']);

        $screeningsQuery = PatientScreening::query()
            ->where('kader_id', $kaderRecord->id)
            ->when($kelurahanKeyword, fn($query) => $query->whereRaw('LOWER(patient_address_kelurahan) LIKE ?', ['%' . $kelurahanKeyword . '%']))
            ->orderBy('patient_address_kelurahan')
            ->orderBy('patient_address_rw')
            ->orderBy('patient_address_rt')
            ->orderByDesc('created_at');

        $screenings = $screeningsQuery->get();
        $uniquePatients = $screenings
            ->map(function ($screening) {
                if (!empty($screening->patient_nik)) {
                    return 'nik:' . $screening->patient_nik;
                }
                if (!empty($screening->patient_phone)) {
                    return 'phone:' . $screening->patient_phone;
                }
                $name = Str::lower(trim($screening->patient_name ?? ''));
                $address = Str::lower(trim($screening->patient_address ?? ''));
                return 'name:' . $name . '|addr:' . $address;
            })
            ->filter()
            ->unique()
            ->count();

        $suspectCount = $screenings->filter(function ($screening) {
            $positive = collect($screening->answers ?? [])->filter(fn($ans) => $ans === 'ya')->count();
            return $positive >= 1;
        })->count();

        $recentScreenings = $screeningsQuery->take(5)->get();

        return view('kelurahan.kader-show', [
            'kader' => $kaderRecord,
            'screenings' => $recentScreenings,
            'screeningSummary' => [
                'total_patients' => $uniquePatients,
                'total_screenings' => $screenings->count(),
                'suspect' => $suspectCount,
            ],
        ]);
    }

    public function exportKadersExcel(Request $request)
    {
        abort_if($request->user()->role !== UserRole::Kelurahan, 403);

        $kaderQuery = $this->kaderQuery($request);
        abort_if(! $kaderQuery, 403);

        $kaders = $kaderQuery->get();

        $export = new class($kaders) implements FromCollection, WithHeadings {
            public function __construct(private $kaders)
            {
            }

            public function collection()
            {
                return $this->kaders->values()->map(function ($kader, $index) {
                    $detail = $kader->detail;
                    $address = $detail->address ?? $detail->notes ?? $detail->organization ?? '-';

                    return [
                        'No' => $index + 1,
                        'Nama' => $kader->name,
                        'Nomor HP' => $kader->phone,
                        'Alamat' => $address,
                    ];
                });
            }

            public function headings(): array
            {
                return ['No', 'Nama', 'Nomor HP', 'Alamat'];
            }
        };

        return Excel::download($export, 'kader-kelurahan.xlsx');
    }

    protected function kaderQuery(Request $request): ?\Illuminate\Database\Eloquent\Builder
    {
        $kelurahan = $request->user();
        $puskesmasIds = collect(optional($kelurahan->detail)->supervisor_id ? [$kelurahan->detail->supervisor_id] : []);

        if ($puskesmasIds->isEmpty()) {
            return null;
        }

        return User::query()
            ->with(['detail.supervisor'])
            ->where('role', UserRole::Kader->value)
            ->whereHas('detail', fn($detail) => $detail->whereIn('supervisor_id', $puskesmasIds))
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%' . $request->input('q') . '%';
                $query->where(function ($sub) use ($term) {
                    $sub->where('name', 'like', $term)
                        ->orWhere('phone', 'like', $term)
                        ->orWhereHas('detail', fn($detail) => $detail->where('address', 'like', $term)
                            ->orWhere('organization', 'like', $term)
                            ->orWhere('notes', 'like', $term));
                });
            })
            ->latest();
    }
}
