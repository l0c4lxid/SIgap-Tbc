<?php

namespace App\Http\Controllers\Kader;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\PatientScreening;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ScreeningController extends Controller
{
    public function index(Request $request)
    {
        abort_if($request->user()->role !== UserRole::Kader, 403);

        $perPage = 10;
        $screenings = PatientScreening::query()
            ->where('kader_id', $request->user()->id)
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%' . $request->input('q') . '%';
                $query->where(function ($sub) use ($term) {
                    $sub->where('patient_name', 'like', $term)
                        ->orWhere('patient_phone', 'like', $term)
                        ->orWhere('patient_nik', 'like', $term)
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

        return view('kader.screening-index', [
            'screenings' => $screenings,
            'search' => $request->input('q', ''),
        ]);
    }

    public function create(Request $request)
    {
        abort_if($request->user()->role !== UserRole::Kader, 403);

        [$riskQuestions, $symptomQuestions] = $this->questionSets();
        $kelurahanName = optional($request->user()->detail)->organization;
        $kelurahanOptions = User::query()
            ->with('detail')
            ->where('role', UserRole::Kelurahan->value)
            ->where('is_active', true)
            ->get()
            ->map(function ($user) {
                $name = trim($user->detail?->organization ?: ($user->name ?? ''));
                if ($name === '') {
                    return null;
                }
                // Normalize: Ensure starts with "Kelurahan "
                return Str::startsWith(Str::lower($name), 'kelurahan ')
                    ? Str::title($name)
                    : 'Kelurahan ' . Str::title($name);
            })
            ->filter() // Remove nulls
            ->unique()
            ->sort()
            ->values();

        return view('kader.screening-create', [
            'riskQuestions' => $riskQuestions,
            'symptomQuestions' => $symptomQuestions,
            'kelurahanName' => $kelurahanName,
            'kelurahanOptions' => $kelurahanOptions,
        ]);
    }

    public function store(Request $request)
    {
        abort_if($request->user()->role !== UserRole::Kader, 403);

        [$riskQuestions, $symptomQuestions] = $this->questionSets();
        $questions = $riskQuestions + $symptomQuestions;

        $rules = $this->buildRules($questions);

        $validated = $request->validate($rules, $this->buildMessages());

        $answers = $this->buildAnswers($questions, $validated);

        PatientScreening::create([
            'kader_id' => $request->user()->id,
            'patient_is_wni' => (bool) $validated['patient_is_wni'],
            'patient_name' => $validated['patient_name'],
            'patient_nik' => $validated['patient_nik'] ?? null,
            'patient_phone' => $validated['patient_phone'] ?? null,
            'patient_address' => $validated['patient_address_domisili'],
            'patient_gender' => $validated['patient_gender'],
            'patient_birth_place' => $validated['patient_birth_place'],
            'patient_birth_date' => $validated['patient_birth_date'],
            'patient_age' => $validated['patient_age'],
            'patient_address_ktp' => $validated['patient_address_ktp'],
            'patient_address_domisili' => $validated['patient_address_domisili'],
            'patient_address_rt' => $validated['patient_address_rt'],
            'patient_address_rw' => $validated['patient_address_rw'],
            'patient_address_kelurahan' => Str::startsWith($val = Str::title(trim($validated['patient_address_kelurahan'])), 'Kelurahan ') ? $val : 'Kelurahan ' . $val,
            'patient_weight' => $validated['patient_weight'],
            'patient_height' => $validated['patient_height'],
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'answers' => $answers,
        ]);

        return redirect()->route('kader.screening.index')->with('status', 'Skrining pasien telah dicatat.');
    }

    public function show(Request $request, PatientScreening $screening)
    {
        abort_if($request->user()->role !== UserRole::Kader, 403);
        abort_if($screening->kader_id !== $request->user()->id, 403);

        [$riskQuestions, $symptomQuestions] = $this->questionSets();

        return view('kader.screening-detail', [
            'screening' => $screening,
            'riskQuestions' => $riskQuestions,
            'symptomQuestions' => $symptomQuestions,
            'isEdit' => $request->boolean('edit'),
        ]);
    }

    public function update(Request $request, PatientScreening $screening)
    {
        abort_if($request->user()->role !== UserRole::Kader, 403);
        abort_if($screening->kader_id !== $request->user()->id, 403);

        [$riskQuestions, $symptomQuestions] = $this->questionSets();
        $questions = $riskQuestions + $symptomQuestions;

        $validated = $request->validate($this->buildRules($questions), $this->buildMessages());
        $answers = $this->buildAnswers($questions, $validated);

        $screening->update([
            'patient_is_wni' => (bool) $validated['patient_is_wni'],
            'patient_name' => $validated['patient_name'],
            'patient_nik' => $validated['patient_nik'] ?? null,
            'patient_phone' => $validated['patient_phone'] ?? null,
            'patient_address' => $validated['patient_address_domisili'],
            'patient_gender' => $validated['patient_gender'],
            'patient_birth_place' => $validated['patient_birth_place'],
            'patient_birth_date' => $validated['patient_birth_date'],
            'patient_age' => $validated['patient_age'],
            'patient_address_ktp' => $validated['patient_address_ktp'],
            'patient_address_domisili' => $validated['patient_address_domisili'],
            'patient_address_rt' => $validated['patient_address_rt'],
            'patient_address_rw' => $validated['patient_address_rw'],
            'patient_address_kelurahan' => Str::startsWith($val = Str::title(trim($validated['patient_address_kelurahan'])), 'Kelurahan ') ? $val : 'Kelurahan ' . $val,
            'patient_weight' => $validated['patient_weight'],
            'patient_height' => $validated['patient_height'],
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'answers' => $answers,
        ]);

        return redirect()
            ->route('kader.screening.show', $screening)
            ->with('status', 'Skrining pasien telah diperbarui.');
    }

    public function destroy(Request $request, PatientScreening $screening)
    {
        abort_if($request->user()->role !== UserRole::Kader, 403);
        abort_if($screening->kader_id !== $request->user()->id, 403);

        $screening->delete();

        return redirect()->route('kader.screening.index')->with('status', 'Skrining pasien telah dihapus.');
    }

    public function exportExcel(Request $request)
    {
        abort_if($request->user()->role !== UserRole::Kader, 403);

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

        $screenings = PatientScreening::query()
            ->where('kader_id', $request->user()->id)
            ->orderBy('patient_address_kelurahan')
            ->orderBy('patient_address_rw')
            ->orderBy('patient_address_rt')
            ->orderByDesc('created_at')
            ->get();

        $export = new class($screenings, $questionLabels, $request->user()->name) implements FromCollection, WithHeadings, WithColumnFormatting {
            public function __construct(private $screenings, private $questionLabels, private $kaderName)
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

                    $asText = fn($value) => ($value === null || $value === '') ? '-' : "'" . $value;

                    $row = [
                        'No' => $index + 1,
                        'Status Skrining' => $status, // Added Detail
                        'Total Gejala' => $positiveCount, // Added Detail
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
                        'Kader' => $this->kaderName ?? '-',
                        'Latitude' => $screening->latitude ?? $answers['latitude'] ?? '-',
                        'Longitude' => $screening->longitude ?? $answers['longitude'] ?? '-',
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
                        'Status Skrining', // Added Header
                        'Total Gejala', // Added Header
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
                        'Latitude',
                        'Longitude',
                        'Tanggal Skrining',
                    ],
                    array_values($this->questionLabels),
                );
            }

            public function columnFormats(): array
            {
                return [
                    'F' => NumberFormat::FORMAT_TEXT, // NIK shifted
                    'G' => NumberFormat::FORMAT_TEXT, // HP shifted
                    'O' => NumberFormat::FORMAT_TEXT, // RT shifted
                    'P' => NumberFormat::FORMAT_TEXT, // RW shifted
                ];
            }
        };

        return Excel::download($export, 'skrining-kader.xlsx');
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

    private function buildRules(array $questions): array
    {
        $rules = [
            'patient_is_wni' => ['required', 'boolean'],
            'patient_name' => ['required', 'string', 'max:255', "regex:/^[\\p{L}\\p{M}\\s\\.']+$/u"],
            'patient_nik' => ['nullable', 'regex:/^\\d+$/', 'required_if:patient_is_wni,1'],
            'patient_phone' => ['nullable', 'regex:/^\\d*$/'],
            'patient_gender' => ['required', 'string', 'max:20'],
            'patient_birth_place' => ['required', 'string', 'max:255', "regex:/^[\\p{L}\\p{M}\\s\\.']+$/u"],
            'patient_birth_date' => ['required', 'date'],
            'patient_age' => ['required', 'integer', 'min:0', 'max:150'],
            'patient_address_ktp' => ['required', 'string', 'max:255'],
            'patient_address_domisili' => ['required', 'string', 'max:255'],
            'patient_address_rt' => ['required', 'string', 'max:3', 'regex:/^\\d{1,3}$/'],
            'patient_address_rw' => ['required', 'string', 'max:3', 'regex:/^\\d{1,3}$/'],
            'patient_address_kelurahan' => ['required', 'string', 'max:100'],
            'patient_weight' => ['required', 'numeric', 'min:0'],
            'patient_height' => ['required', 'numeric', 'min:0'],
            'patient_weight' => ['required', 'numeric', 'min:0'],
            'patient_height' => ['required', 'numeric', 'min:0'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ];

        foreach ($questions as $key => $label) {
            $rules[$key] = ['required', 'in:ya,tidak'];
        }

        return $rules;
    }

    private function buildAnswers(array $questions, array $validated): array
    {
        return collect($questions)
            ->keys()
            ->mapWithKeys(fn($key) => [$key => $validated[$key]])
            ->toArray();
    }

    private function buildMessages(): array
    {
        return [
            'patient_nik.regex' => 'NIK harus berupa angka saja.',
            'patient_nik.required_if' => 'NIK wajib diisi jika pasien WNI.',
            'patient_address_rt.regex' => 'RT harus berupa angka saja.',
            'patient_address_rw.regex' => 'RW harus berupa angka saja.',
        ];
    }
}
