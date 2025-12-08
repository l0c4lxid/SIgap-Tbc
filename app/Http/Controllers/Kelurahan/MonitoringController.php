<?php

namespace App\Http\Controllers\Kelurahan;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
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
        ]);
    }

    public function requestPuskesmas(Request $request, User $puskesmas)
    {
        abort_if($request->user()->role !== UserRole::Kelurahan, 403);
        abort_if($puskesmas->role !== UserRole::Puskesmas, 404);

        $kelurahan = $request->user()->loadMissing('detail');

        $kelurahan->detail?->update(['supervisor_id' => $puskesmas->id]);

        return back()->with('status', 'Permintaan puskesmas induk dikirim. Menunggu persetujuan.');
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
        $kelurahanKeyword = Str::of($kelurahanName)->replace('Kelurahan', '')->trim()->lower()->value() ?: Str::of($kelurahanName)->trim()->lower()->value();

        $kaderRecord = $this->kaderQuery($request)?->where('id', $kader->id)->first();
        abort_if(! $kaderRecord, 403);

        $kaderRecord->loadMissing(['detail.supervisor.detail']);

        $patientsQuery = User::query()
            ->with([
                'detail',
                'screenings' => fn($q) => $q->latest()->limit(1),
                'treatments' => fn($q) => $q->latest()->limit(1),
            ])
            ->where('role', UserRole::Pasien->value)
            ->whereHas('detail', function ($detail) use ($kaderRecord, $kelurahanKeyword) {
                $detail->where('supervisor_id', $kaderRecord->id)
                    ->when($kelurahanKeyword, fn($q) => $q->whereRaw('LOWER(address) LIKE ?', ['%' . $kelurahanKeyword . '%']));
            });

        $patientTotal = (clone $patientsQuery)->count();
        $patientScreened = (clone $patientsQuery)->whereHas('screenings')->count();
        $recentPatients = (clone $patientsQuery)->latest()->take(5)->get();

        return view('kelurahan.kader-show', [
            'kader' => $kaderRecord,
            'patients' => $recentPatients,
            'patientSummary' => [
                'total' => $patientTotal,
                'screened' => $patientScreened,
                'unscreened' => max(0, $patientTotal - $patientScreened),
            ],
        ]);
    }

    public function patients(Request $request)
    {
        abort_if($request->user()->role !== UserRole::Kelurahan, 403);

        $kelurahan = $request->user();
        $perPage = 10;
        $puskesmasIds = collect(optional($kelurahan->detail)->supervisor_id ? [$kelurahan->detail->supervisor_id] : []);
        $kelurahanName = optional($kelurahan->detail)->organization ?? $kelurahan->name;
        $kelurahanKeyword = Str::of($kelurahanName)->replace('Kelurahan', '')->trim()->lower()->value() ?: Str::of($kelurahanName)->trim()->lower()->value();

        $filterPatients = function ($query) use ($puskesmasIds, $request, $kelurahanKeyword) {
            return $query
                ->where('role', UserRole::Pasien->value)
                ->whereHas('detail.supervisor.detail', fn($detail) => $detail->whereIn('supervisor_id', $puskesmasIds))
                ->when($kelurahanKeyword, fn($q) => $q->whereHas('detail', function ($detail) use ($kelurahanKeyword) {
                    // Only show patients whose address mentions this kelurahan.
                    $detail->whereRaw('LOWER(address) LIKE ?', ['%' . $kelurahanKeyword . '%']);
                }))
                ->when($request->filled('q'), function ($query) use ($request) {
                    $term = '%' . $request->input('q') . '%';
                    $query->where(function ($sub) use ($term) {
                        $sub->where('name', 'like', $term)
                            ->orWhere('phone', 'like', $term)
                            ->orWhereHas('detail', fn($detail) => $detail->where('address', 'like', $term));
                    });
                });
        };

        if ($puskesmasIds->isEmpty()) {
            $patients = new LengthAwarePaginator([], 0, $perPage, 1, [
                'path' => $request->url(),
                'query' => $request->query(),
            ]);
            $stats = ['total' => 0, 'screened' => 0, 'unscreened' => 0];
        } else {
            $patientsQuery = $filterPatients(User::query())
                ->with([
                    'detail.supervisor.detail',
                    'screenings' => fn($query) => $query->latest()->limit(1),
                    'treatments' => fn($query) => $query->latest()->limit(1),
                ])
                ->latest();

            $patients = $patientsQuery->paginate($perPage)->withQueryString();

            $statsQuery = $filterPatients(User::query());
            $total = (clone $statsQuery)->count();
            $screened = (clone $statsQuery)->whereHas('screenings')->count();
            $stats = [
                'total' => $total,
                'screened' => $screened,
                'unscreened' => max(0, $total - $screened),
            ];
        }

        return view('kelurahan.patients', [
            'patients' => $patients,
            'search' => $request->input('q', ''),
            'stats' => $stats,
        ]);
    }

    public function showPatient(Request $request, User $patient)
    {
        abort_if($request->user()->role !== UserRole::Kelurahan, 403);
        abort_if($patient->role !== UserRole::Pasien, 404);

        $patient->loadMissing([
            'detail.supervisor.detail',
            'screenings' => fn($query) => $query->latest()->limit(5),
            'treatments' => fn($query) => $query->latest()->limit(5),
            'familyMembers' => fn($query) => $query->latest(),
        ]);

        $kader = optional($patient->detail)->supervisor;
        $puskesmas = optional($kader?->detail)->supervisor;
        $allowedPuskesmasId = optional($request->user()->detail)->supervisor_id;
        $kelurahanName = optional($request->user()->detail)->organization ?? $request->user()->name;
        $kelurahanKeyword = Str::of($kelurahanName)->replace('Kelurahan', '')->trim()->lower()->value() ?: Str::of($kelurahanName)->trim()->lower()->value();
        $patientAddress = Str::of(optional($patient->detail)->address)->lower()->value();

        $addressMatch = $kelurahanKeyword ? Str::contains($patientAddress, $kelurahanKeyword) : true;

        abort_if(!$puskesmas || $allowedPuskesmasId !== optional($puskesmas)->id || ! $addressMatch, 403);

        return view('kelurahan.patient-detail', [
            'patient' => $patient,
            'puskesmas' => $puskesmas,
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
