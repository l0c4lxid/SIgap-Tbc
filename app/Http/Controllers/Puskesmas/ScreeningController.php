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
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

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

        // Get question labels like Pemda logic to be consistent
        [$riskQuestions, $symptomQuestions] = $this->questionSets();
        $questionLabels = $riskQuestions + $symptomQuestions;

        // Same Logic as Pemda
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
                ->latest()
                ->get();



// ... inside constructor ...
        $export = new class($screenings, $questionLabels) extends DefaultValueBinder implements 
            \Maatwebsite\Excel\Concerns\FromCollection, 
            \Maatwebsite\Excel\Concerns\WithHeadings, 
            \Maatwebsite\Excel\Concerns\WithColumnFormatting, 
            \Maatwebsite\Excel\Concerns\WithStyles, 
            \Maatwebsite\Excel\Concerns\WithColumnWidths, 
            \Maatwebsite\Excel\Concerns\ShouldAutoSize,
            WithCustomValueBinder
        {
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

                    // Calculate Status
                    $positiveCount = collect($answers)
                        ->filter(fn($answer, $key) => str_starts_with((string) $key, 'gejala_') && $answer === 'ya')
                        ->count();
                    $status = $positiveCount > 0 ? 'Suspek TBC' : 'Tidak Suspek';

                    $asText = fn($value) => ($value === null || $value === '') ? '-' : (string) $value;

                    $row = [
                        'No' => $index + 1,
                        'Kader PJ' => $screening->kader?->name ?? '-',
                        'Status Skrining' => $status,
                        'Total Gejala' => $positiveCount,
                        'Nama' => $screening->patient_name ?? '-',
                        'WNI' => $screening->patient_is_wni ? 'Ya' : 'Tidak',
                        'NIK' => $asText($screening->patient_nik),
                        'Nomor HP' => $asText($screening->patient_phone),
                        'Alamat' => $screening->patient_address ?? '-',
                        'Jenis Kelamin' => $screening->patient_gender ?? '-',
                        'Tempat Lahir' => $screening->patient_birth_place ?? '-',
                        'Tanggal Lahir' => optional($screening->patient_birth_date)?->format('d/m/Y') ?? '-',
                        'Umur' => $screening->patient_age ?? '-',
                        'Alamat KTP' => $screening->patient_address_ktp ?? '-',
                        'Alamat Domisili' => $screening->patient_address_domisili ?? '-',
                        'RT' => $asText($screening->patient_address_rt),
                        'RW' => $asText($screening->patient_address_rw),
                        'Kelurahan' => $screening->patient_address_kelurahan ?? '-',
                        'BB (kg)' => $screening->patient_weight ?? '-',
                        'TB (cm)' => $screening->patient_height ?? '-',
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
                        'Kader PJ',
                        'Status Skrining',
                        'Total Gejala',
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
                        'Tanggal Skrining',
                    ],
                    array_values($this->questionLabels)
                );
            }

            public function columnFormats(): array
            {
                return [
                    'G' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT, // NIK
                    'H' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT, // HP
                    'P' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT, // RT
                    'Q' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT, // RW
                ];
            }

            public function bindValue(Cell $cell, $value)
            {
                $column = $cell->getColumn();
                
                // Columns: G (NIK), H (HP), P (RT), Q (RW)
                if (in_array($column, ['G', 'H', 'P', 'Q'])) {
                    $cell->setValueExplicit($value, DataType::TYPE_STRING);
                    return true;
                }

                // Else return default behavior
                return parent::bindValue($cell, $value);
            }

            public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
            {
                // Bold Header
                $sheet->getStyle('1')->getFont()->setBold(true);
                
                // Borders for all cells
                $sheet->getStyle($sheet->calculateWorksheetDimension())
                      ->getBorders()
                      ->getAllBorders()
                      ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

                // Vertical Alignment Center
                $sheet->getStyle($sheet->calculateWorksheetDimension())
                      ->getAlignment()
                      ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            }

            public function columnWidths(): array
            {
                return [
                    'B' => 25, // Kader PJ
                    'C' => 15, // Status
                    'E' => 30, // Nama
                    'G' => 20, // NIK
                    'H' => 15, // HP
                    'I' => 45, // Alamat
                    'N' => 35, // Alamat KTP
                    'O' => 35, // Alamat Domisili
                ];
            }

           // ... existing methods ...

           

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
