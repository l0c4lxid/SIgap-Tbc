<?php

namespace App\Http\Controllers\Puskesmas;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\PatientScreening;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Facades\Excel;

class ScreeningController extends Controller
{
    public function index(Request $request)
    {
        abort_if($request->user()->role !== UserRole::Puskesmas, 403);

        $perPage = 10;
        $kaderIds = User::query()
            ->where('role', UserRole::Kader->value)
            ->whereHas('detail', fn($detail) => $detail->where('supervisor_id', $request->user()->id))
            ->pluck('id');

        $screenings = $kaderIds->isEmpty()
            ? new LengthAwarePaginator([], 0, $perPage, 1, [
                'path' => $request->url(),
                'query' => $request->query(),
            ])
            : PatientScreening::query()
                ->with('kader')
                ->whereIn('kader_id', $kaderIds)
                ->when($request->filled('from'), fn($query) => $query->whereDate('created_at', '>=', $request->date('from')))
                ->when($request->filled('to'), fn($query) => $query->whereDate('created_at', '<=', $request->date('to')))
                ->when($request->filled('q'), function ($query) use ($request) {
                    $term = '%' . $request->input('q') . '%';
                    $query->where(function ($sub) use ($term) {
                        $sub->where('patient_name', 'like', $term)
                            ->orWhere('patient_phone', 'like', $term)
                            ->orWhere('patient_nik', 'like', $term)
                            ->orWhere('patient_address', 'like', $term)
                            ->orWhere('patient_address_ktp', 'like', $term)
                            ->orWhere('patient_address_domisili', 'like', $term)
                            ->orWhere('patient_address_kelurahan', 'like', $term)
                            ->orWhere('patient_address_rt', 'like', $term)
                            ->orWhere('patient_address_rw', 'like', $term);
                    });
                })
                ->orderBy('patient_address_kelurahan')
                ->orderBy('patient_address_rw')
                ->orderBy('patient_address_rt')
                ->orderByDesc('created_at')
                ->paginate($perPage)
                ->withQueryString();

        return view('puskesmas.screenings', [
            'screenings' => $screenings,
            'search' => $request->input('q', ''),
            'filters' => [
                'from' => $request->input('from', ''),
                'to' => $request->input('to', ''),
            ],
        ]);
    }

    public function show(Request $request, PatientScreening $screening)
    {
        abort_if($request->user()->role !== UserRole::Puskesmas, 403);

        $screening->loadMissing('kader.detail');
        $kader = $screening->kader;
        $puskesmasId = optional($kader?->detail)->supervisor_id;
        abort_if($puskesmasId !== $request->user()->id, 403);

        [$riskQuestions, $symptomQuestions] = $this->questionSets();

        return view('puskesmas.screening-detail', [
            'screening' => $screening,
            'kader' => $kader,
            'riskQuestions' => $riskQuestions,
            'symptomQuestions' => $symptomQuestions,
        ]);
    }

    public function exportExcel(Request $request)
    {
        abort_if($request->user()->role !== UserRole::Puskesmas, 403);

        $questionLabels = [
            'riwayat_kontak_tbc' => 'Riwayat Kontak TBC',
            'sakit_tbc' => 'Pernah Diagnosis TBC',
            'kekurangan_gizi' => 'Kekurangan Gizi',
            'merokok' => 'Merokok',
            'perokok_pasif' => 'Perokok Pasif',
            'kencing_manis' => 'Kencing Manis',
            'hiv' => 'HIV',
            'lansia' => 'Lansia (>65 Tahun)',
            'warga_binaan' => 'Warga Binaan',
            'wilayah_miskin' => 'Wilayah Miskin/Rentan',
            'gejala_batuk' => 'Gejala - Batuk',
            'gejala_bb_turun' => 'Gejala - BB Turun',
            'gejala_demam_hilang_timbul' => 'Gejala - Demam Hilang Timbul',
            'gejala_berkeringat_malam' => 'Gejala - Berkeringat Malam',
            'gejala_kelenjar' => 'Gejala - Pembesaran Kelenjar',
        ];

        $kaderIds = User::query()
            ->where('role', UserRole::Kader->value)
            ->whereHas('detail', fn($detail) => $detail->where('supervisor_id', $request->user()->id))
            ->pluck('id');

        $screenings = $kaderIds->isEmpty()
            ? collect()
            : PatientScreening::query()
                ->with('kader')
                ->whereIn('kader_id', $kaderIds)
                ->when($request->filled('from'), fn($query) => $query->whereDate('created_at', '>=', $request->date('from')))
                ->when($request->filled('to'), fn($query) => $query->whereDate('created_at', '<=', $request->date('to')))
                ->when($request->filled('q'), function ($query) use ($request) {
                    $term = '%' . $request->input('q') . '%';
                    $query->where(function ($sub) use ($term) {
                        $sub->where('patient_name', 'like', $term)
                            ->orWhere('patient_phone', 'like', $term)
                            ->orWhere('patient_nik', 'like', $term)
                            ->orWhere('patient_address', 'like', $term)
                            ->orWhere('patient_address_ktp', 'like', $term)
                            ->orWhere('patient_address_domisili', 'like', $term)
                            ->orWhere('patient_address_kelurahan', 'like', $term)
                            ->orWhere('patient_address_rt', 'like', $term)
                            ->orWhere('patient_address_rw', 'like', $term);
                    });
                })
                ->orderBy('patient_address_kelurahan')
                ->orderBy('patient_address_rw')
                ->orderBy('patient_address_rt')
                ->orderByDesc('created_at')
                ->get();

        $export = new class($screenings, $questionLabels) implements FromCollection, WithHeadings {
            public function __construct(private $screenings, private $questionLabels)
            {
            }

            public function collection()
            {
                return $this->screenings->values()->map(function ($screening, $index) {
                    $answers = $screening->answers ?? [];
                    $getAnswer = function ($key) use ($answers) {
                        $value = $answers[$key] ?? null;
                        return $value === 'ya' ? 'Ya' : ($value === 'tidak' ? 'Tidak' : ($value ?? '-'));
                    };

                    $row = [
                        'No' => $index + 1,
                        'Nama' => $screening->patient_name ?? '-',
                        'WNI' => $screening->patient_is_wni ? 'Ya' : 'Tidak',
                        'NIK' => $screening->patient_nik ?? '-',
                        'Nomor HP' => $screening->patient_phone ?? '-',
                        'Alamat' => $screening->patient_address ?? '-',
                        'Jenis Kelamin' => $screening->patient_gender ?? '-',
                        'Tempat Lahir' => $screening->patient_birth_place ?? '-',
                        'Tanggal Lahir' => optional($screening->patient_birth_date)?->format('d/m/Y') ?? '-',
                        'Umur' => $screening->patient_age ?? '-',
                        'Alamat KTP' => $screening->patient_address_ktp ?? '-',
                        'Alamat Domisili' => $screening->patient_address_domisili ?? '-',
                        'RT' => $screening->patient_address_rt ?? '-',
                        'RW' => $screening->patient_address_rw ?? '-',
                        'Kelurahan' => $screening->patient_address_kelurahan ?? '-',
                        'BB (kg)' => $screening->patient_weight ?? '-',
                        'TB (cm)' => $screening->patient_height ?? '-',
                        'Kader' => $screening->kader?->name ?? '-',
                        'Tanggal Skrining' => optional($screening->created_at)?->format('d/m/Y H:i') ?? '-',
                    ];

                    foreach ($this->questionLabels as $key => $label) {
                        $row[$label] = $getAnswer($key);
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
                        'WNI',
                        'NIK',
                        'Nomor HP',
                        'Alamat',
                        'Jenis Kelamin',
                        'Tempat Lahir',
                        'Tanggal Lahir',
                        'Umur',
                        'Alamat KTP',
                        'Alamat Domisili',
                        'RT',
                        'RW',
                        'Kelurahan',
                        'BB (kg)',
                        'TB (cm)',
                        'Kader',
                        'Tanggal Skrining',
                    ],
                    array_values($this->questionLabels),
                );
            }
        };

        return Excel::download($export, 'skrining-pasien.xlsx');
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
