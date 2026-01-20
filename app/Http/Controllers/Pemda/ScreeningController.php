<?php

namespace App\Http\Controllers\Pemda;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\PatientScreening;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ScreeningController extends Controller
{
    public function index(Request $request)
    {
        abort_if($request->user()->role !== UserRole::Pemda, 403);

        $filters = [
            'from' => $request->input('from'),
            'to' => $request->input('to'),
            'q' => $request->input('q'),
        ];

        $baseQuery = $this->buildQuery($request);
        $screenings = $baseQuery
            ->paginate(10)
            ->withQueryString();

        $screeningCount = (clone $baseQuery)->count();
        $rtCount = (clone $baseQuery)
            ->whereNotNull('patient_address_rt')
            ->where('patient_address_rt', '!=', '')
            ->whereNotNull('patient_address_rw')
            ->where('patient_address_rw', '!=', '')
            ->whereNotNull('patient_address_kelurahan')
            ->where('patient_address_kelurahan', '!=', '')
            ->distinct()
            ->count(DB::raw("concat_ws('-', patient_address_kelurahan, patient_address_rw, patient_address_rt)"));
        $rwCount = (clone $baseQuery)
            ->whereNotNull('patient_address_rw')
            ->where('patient_address_rw', '!=', '')
            ->whereNotNull('patient_address_kelurahan')
            ->where('patient_address_kelurahan', '!=', '')
            ->distinct()
            ->count(DB::raw("concat_ws('-', patient_address_kelurahan, patient_address_rw)"));
        $kelurahanCount = (clone $baseQuery)
            ->whereNotNull('patient_address_kelurahan')
            ->where('patient_address_kelurahan', '!=', '')
            ->distinct()
            ->count(DB::raw('patient_address_kelurahan'));

        return view('pemda.screenings-index', [
            'screenings' => $screenings,
            'filters' => $filters,
            'search' => $filters['q'],
            'summary' => [
                'screenings' => $screeningCount,
                'rt' => $rtCount,
                'rw' => $rwCount,
                'kelurahan' => $kelurahanCount,
            ],
        ]);
    }

    public function show(Request $request, PatientScreening $screening)
    {
        abort_if($request->user()->role !== UserRole::Pemda, 403);

        $screening->loadMissing('kader');
        [$riskQuestions, $symptomQuestions] = $this->questionSets();

        return view('pemda.screenings-detail', [
            'screening' => $screening,
            'riskQuestions' => $riskQuestions,
            'symptomQuestions' => $symptomQuestions,
        ]);
    }

    public function exportExcel(Request $request)
    {
        abort_if($request->user()->role !== UserRole::Pemda, 403);

        $screenings = $this->buildQuery($request)->get();

        $export = new class($screenings) implements FromCollection, WithHeadings {
            public function __construct(private $screenings)
            {
            }

            public function collection()
            {
                return $this->screenings->values()->map(function ($screening, $index) {
                    $addressParts = array_filter([
                        $screening->patient_address_domisili ?? $screening->patient_address ?? null,
                        $screening->patient_address_rt ? 'RT ' . $screening->patient_address_rt : null,
                        $screening->patient_address_rw ? 'RW ' . $screening->patient_address_rw : null,
                        $screening->patient_address_kelurahan ?? null,
                    ]);

                    return [
                        $index + 1,
                        $screening->patient_name ?? '-',
                        $addressParts ? implode(', ', $addressParts) : '-',
                        $screening->kader?->name ?? '-',
                        optional($screening->created_at)?->format('d/m/Y H:i') ?? '-',
                    ];
                });
            }

            public function headings(): array
            {
                return ['No', 'Nama', 'Alamat', 'Kader PJ', 'Tanggal Skrining'];
            }
        };

        return Excel::download($export, 'skrining-pemda.xlsx');
    }

    private function buildQuery(Request $request)
    {
        $query = PatientScreening::query()
            ->with('kader')
            ->latest();

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->input('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->input('to'));
        }

        if ($request->filled('q')) {
            $term = '%' . $request->input('q') . '%';
            $query->where(function ($sub) use ($term) {
                $sub->where('patient_name', 'like', $term)
                    ->orWhere('patient_nik', 'like', $term)
                    ->orWhere('patient_phone', 'like', $term)
                    ->orWhere('patient_address', 'like', $term)
                    ->orWhere('patient_address_domisili', 'like', $term)
                    ->orWhere('patient_address_ktp', 'like', $term)
                    ->orWhere('patient_address_rt', 'like', $term)
                    ->orWhere('patient_address_rw', 'like', $term)
                    ->orWhere('patient_address_kelurahan', 'like', $term)
                    ->orWhereHas('kader', function ($kader) use ($term) {
                        $kader->where('name', 'like', $term)
                            ->orWhere('phone', 'like', $term);
                    });
            });
        }

        return $query;
    }

    private function questionSets(): array
    {
        $riskQuestions = [
            'riwayat_kontak_tbc' => 'Apakah pernah kontak erat dengan pasien TBC?',
            'sakit_tbc' => 'Apakah pernah didiagnosis TBC sebelumnya?',
            'kekurangan_gizi' => 'Apakah pernah mengalami kekurangan gizi?',
            'merokok' => 'Apakah saat ini merokok?',
            'perokok_pasif' => 'Apakah sering terpapar asap rokok (perokok pasif)?',
            'kencing_manis' => 'Apakah memiliki riwayat diabetes/kencing manis?',
            'hiv' => 'Apakah memiliki riwayat HIV?',
            'lansia' => 'Apakah berusia lebih dari 65 tahun (lansia)?',
            'warga_binaan' => 'Apakah termasuk warga binaan?',
            'wilayah_miskin' => 'Apakah tinggal di wilayah miskin atau rentan?',
        ];

        $symptomQuestions = [
            'gejala_batuk' => 'Apakah saat ini mengalami batuk?',
            'gejala_bb_turun' => 'Apakah mengalami penurunan berat badan tanpa sebab jelas?',
            'gejala_demam_hilang_timbul' => 'Apakah mengalami demam yang hilang timbul?',
            'gejala_berkeringat_malam' => 'Apakah berkeringat pada malam hari?',
            'gejala_kelenjar' => 'Apakah ada pembesaran kelenjar getah bening?',
        ];

        return [$riskQuestions, $symptomQuestions];
    }
}
