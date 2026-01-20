<?php

namespace App\Http\Controllers\Puskesmas;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\PatientScreening;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class KaderController extends Controller
{
    public function index(Request $request)
    {
        abort_if($request->user()->role !== UserRole::Puskesmas, 403);

        $perPage = 10;

        $kaders = User::query()
            ->with('detail')
            ->where('role', UserRole::Kader->value)
            ->whereHas('detail', fn($detail) => $detail->where('supervisor_id', $request->user()->id))
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%' . $request->input('q') . '%';
                $query->where(function ($sub) use ($term) {
                    $sub->where('name', 'like', $term)
                        ->orWhere('phone', 'like', $term)
                        ->orWhereHas('detail', fn($detail) => $detail->where('notes', 'like', $term));
                });
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        return view('puskesmas.kaders', [
            'kaders' => $kaders,
            'search' => $request->input('q', ''),
        ]);
    }

    public function exportPdf(Request $request)
    {
        abort_if($request->user()->role !== UserRole::Puskesmas, 403);

        $kaders = $this->kaderQuery($request)->get();

        $pdf = Pdf::loadView('puskesmas.kaders-export', [
            'kaders' => $kaders,
            'title' => 'Daftar Kader Mitra',
        ])->setPaper('a4', 'portrait');

        return $pdf->download('kader-puskesmas.pdf');
    }

    public function exportExcel(Request $request)
    {
        abort_if($request->user()->role !== UserRole::Puskesmas, 403);

        $kaders = $this->kaderQuery($request)->get();

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

        return Excel::download($export, 'kader-puskesmas.xlsx');
    }

    public function show(Request $request, User $kader)
    {
        abort_if($request->user()->role !== UserRole::Puskesmas, 403);
        abort_if($kader->role !== UserRole::Kader, 404);

        $kader->loadMissing('detail.supervisor');

        abort_if(optional($kader->detail)->supervisor_id !== $request->user()->id, 403);

        $screenings = PatientScreening::query()
            ->where('kader_id', $kader->id)
            ->latest()
            ->paginate(5)
            ->withQueryString();
        $totalScreenings = PatientScreening::where('kader_id', $kader->id)->count();

        return view('puskesmas.kader-show', [
            'kader' => $kader,
            'screenings' => $screenings,
            'totalScreenings' => $totalScreenings,
        ]);
    }

    public function updateStatus(Request $request, User $kader)
    {
        abort_if($request->user()->role !== UserRole::Puskesmas, 403);
        abort_if($kader->role !== UserRole::Kader, 404);

        $kader->loadMissing('detail');

        abort_if(optional($kader->detail)->supervisor_id !== $request->user()->id, 403);

        $validated = $request->validate([
            'status' => ['required', 'in:active,inactive'],
        ]);

        $kader->is_active = $validated['status'] === 'active';
        $kader->save();

        return back()->with('status', 'Status kader diperbarui.');
    }

    protected function kaderQuery(Request $request)
    {
        return User::query()
            ->with('detail')
            ->where('role', UserRole::Kader->value)
            ->whereHas('detail', fn($detail) => $detail->where('supervisor_id', $request->user()->id))
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%' . $request->input('q') . '%';
                $query->where(function ($sub) use ($term) {
                    $sub->where('name', 'like', $term)
                        ->orWhere('phone', 'like', $term)
                        ->orWhereHas('detail', fn($detail) => $detail->where('notes', 'like', $term));
                });
            })
            ->latest();
    }
}
