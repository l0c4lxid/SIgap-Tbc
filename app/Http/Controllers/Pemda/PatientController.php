<?php

namespace App\Http\Controllers\Pemda;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Facades\Excel;

class PatientController extends Controller
{
    public function index(Request $request)
    {
        abort_if($request->user()->role !== UserRole::Pemda, 403);

        $kelurahanPuskesmasId = $this->getKelurahanPuskesmasId($request);

        $puskesmasOptions = User::query()
            ->where('role', UserRole::Puskesmas->value)
            ->orderBy('name')
            ->get(['id', 'name']);

        $kelurahanOptions = User::query()
            ->where('role', UserRole::Kelurahan->value)
            ->orderBy('name')
            ->get(['id', 'name']);

        $months = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        $currentYear = now()->year;
        $years = [];
        for ($i = 0; $i < 5; $i++) {
            $years[] = $currentYear - $i;
        }

        $perPage = 10;

        $patientsQuery = $this->buildPatientsQuery($request, $kelurahanPuskesmasId);

        $patientsForStats = (clone $patientsQuery)->get();
        $patients = $patientsQuery->paginate($perPage)->withQueryString();

        $stats = [
            'total' => $patientsForStats->count(),
            'belum_skrining' => $patientsForStats->filter(fn($patient) => $patient->screenings->isEmpty())->count(),
            'sudah_skrining' => $patientsForStats->filter(fn($patient) => $patient->screenings->isNotEmpty())->count(),
            'suspect' => $patientsForStats->filter(function ($patient) {
                $latest = $patient->screenings->first();
                if (!$latest) {
                    return false;
                }
                $positive = collect($latest->answers ?? [])->filter(fn($ans) => $ans === 'ya')->count();
                return $positive >= 2;
            })->count(),
        ];

        return view('pemda.patients', [
            'patients' => $patients,
            'search' => $request->input('q', ''),
            'filters' => [
                'puskesmas_id' => $request->input('puskesmas_id', ''),
                'kelurahan_id' => $request->input('kelurahan_id', ''),
                'month' => $request->input('month', ''),
                'year' => $request->input('year', ''),
            ],
            'stats' => $stats,
            'puskesmasOptions' => $puskesmasOptions,
            'kelurahanOptions' => $kelurahanOptions,
            'months' => $months,
            'years' => $years,
        ]);
    }

    public function show(Request $request, User $patient)
    {
        abort_if($request->user()->role !== UserRole::Pemda, 403);
        abort_if($patient->role !== UserRole::Pasien, 404);

        $patient->loadMissing([
            'detail.supervisor.detail',
            'screenings' => fn($query) => $query->latest()->limit(5),
            'treatments' => fn($query) => $query->latest()->limit(5),
            'familyMembers' => fn($query) => $query->latest(),
        ]);

        return view('pemda.patient-detail', [
            'patient' => $patient,
            'kader' => optional($patient->detail)->supervisor,
            'puskesmas' => optional(optional($patient->detail)->supervisor)->detail->supervisor,
        ]);
    }

    public function exportExcel(Request $request)
    {
        abort_if($request->user()->role !== UserRole::Pemda, 403);

        $questionLabels = [
            'batuk_kronis' => 'Batuk Kronis',
            'dahak_darah' => 'Dahak Darah',
            'berat_badan' => 'Berat Badan',
            'demam_malam' => 'Demam Malam',
        ];

        $patients = $this->buildPatientsQuery(
            $request,
            $this->getKelurahanPuskesmasId($request)
        )->get();

        $treatmentStatuses = [
            'contacted' => 'Perlu Konfirmasi',
            'scheduled' => 'Terjadwal',
            'in_treatment' => 'Sedang Berobat',
            'recovered' => 'Selesai',
        ];

        $export = new class($patients, $questionLabels, $treatmentStatuses) implements FromCollection, WithHeadings {
            public function __construct(private $patients, private $questionLabels, private $treatmentStatuses)
            {
            }

            public function collection()
            {
                return $this->patients->values()->map(function ($patient, $index) {
                    $detail = optional($patient)->detail;
                    $kader = optional($detail)->supervisor;
                    $puskesmas = optional($kader?->detail)->supervisor;
                    $latestScreening = $patient->screenings->first();
                    $answers = $latestScreening?->answers ?? [];
                    $positiveCount = collect($answers)->filter(fn($ans) => $ans === 'ya')->count();

                    $screeningStatus = $latestScreening
                        ? ($positiveCount >= 2 ? 'Suspek TBC' : ($positiveCount === 1 ? 'Perlu Observasi' : 'Negatif'))
                        : 'Belum Skrining';

                    $treatment = $patient->treatments->first();
                    $treatmentStatus = $treatment
                        ? ($this->treatmentStatuses[$treatment->status] ?? ucfirst(str_replace('_', ' ', $treatment->status)))
                        : 'Belum masuk daftar';

                    $row = [
                        'No' => $index + 1,
                        'Nama' => $patient->name,
                        'NIK' => $detail?->nik ?? '-',
                        'Nomor HP' => $patient->phone ?? '-',
                        'Alamat' => $detail?->address ?? '-',
                        'Kader' => $kader?->name ?? '-',
                        'Puskesmas' => $puskesmas?->name ?? '-',
                        'Tanggal Skrining Terakhir' => optional($latestScreening?->created_at)?->format('d/m/Y H:i') ?? '-',
                        'Status Skrining' => $screeningStatus,
                        'Status Pengobatan' => $treatmentStatus,
                        'Jadwal Kontrol' => optional($treatment?->next_follow_up_at)?->format('d/m/Y') ?? '-',
                        'Catatan Pengobatan' => $treatment?->notes ?? '-',
                    ];

                    foreach ($this->questionLabels as $key => $label) {
                        $value = $answers[$key] ?? null;
                        $row[$label] = $value === 'ya' ? 'Ya' : ($value === 'tidak' ? 'Tidak' : ($value ?? '-'));
                    }

                    return $row;
                });
            }

            public function headings(): array
            {
                return array_merge(
                    [
                        'No',
                        'Nama',
                        'NIK',
                        'Nomor HP',
                        'Alamat',
                        'Kader',
                        'Puskesmas',
                        'Tanggal Skrining Terakhir',
                        'Status Skrining',
                        'Status Pengobatan',
                        'Jadwal Kontrol',
                        'Catatan Pengobatan',
                    ],
                    array_values($this->questionLabels),
                );
            }
        };

        return Excel::download($export, 'data-skrining-pasien.xlsx');
    }

    private function buildPatientsQuery(Request $request, ?int $kelurahanPuskesmasId)
    {
        return User::query()
            ->with([
                'detail',
                'detail.supervisor',
                'detail.supervisor.detail',
                'screenings' => fn($query) => $query->latest()->limit(1),
                'treatments' => fn($query) => $query->latest()->limit(1),
            ])
            ->where('role', UserRole::Pasien->value)
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%' . $request->input('q') . '%';
                $query->where(function ($sub) use ($term) {
                    $sub->where('name', 'like', $term)
                        ->orWhere('phone', 'like', $term)
                        ->orWhereHas('detail', function ($detail) use ($term) {
                            $detail->where('address', 'like', $term)
                                ->orWhere('nik', 'like', $term);
                        });
                });
            })
            ->when($request->filled('puskesmas_id'), function ($query) use ($request) {
                $puskesmasId = $request->input('puskesmas_id');
                $query->whereHas('detail.supervisor.detail', fn(Builder $detail) => $detail->where('supervisor_id', $puskesmasId));
            })
            ->when($request->filled('kelurahan_id'), function ($query) use ($kelurahanPuskesmasId) {
                if (!$kelurahanPuskesmasId) {
                    $query->whereRaw('0 = 1');
                    return;
                }
                $query->whereHas('detail.supervisor.detail', fn(Builder $detail) => $detail->where('supervisor_id', $kelurahanPuskesmasId));
            })
            ->when($request->filled('month'), function ($query) use ($request) {
                $query->whereMonth('created_at', $request->input('month'));
            })
            ->when($request->filled('year'), function ($query) use ($request) {
                $query->whereYear('created_at', $request->input('year'));
            })
            ->latest();
    }

    private function getKelurahanPuskesmasId(Request $request): ?int
    {
        if (!$request->filled('kelurahan_id')) {
            return null;
        }

        $kelurahan = User::query()
            ->with('detail')
            ->where('role', UserRole::Kelurahan->value)
            ->find($request->input('kelurahan_id'));

        return optional($kelurahan?->detail)->supervisor_id;
    }
}
