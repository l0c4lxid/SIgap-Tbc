<?php

namespace App\Http\Controllers\Kader;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\PatientScreening;
use Illuminate\Http\Request;

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

        $questions = [
            'riwayat_kontak_tbc' => 'Apakah pernah kontak erat dengan pasien TBC?',
            'sakit_tbc' => 'Apakah pernah didiagnosis TBC sebelumnya?',
            'kekurangan_gizi' => 'Apakah memiliki riwayat kekurangan gizi?',
            'merokok' => 'Apakah saat ini merokok?',
            'perokok_pasif' => 'Apakah sering terpapar asap rokok (perokok pasif)?',
            'kencing_manis' => 'Apakah memiliki riwayat diabetes/kencing manis?',
            'hiv' => 'Apakah memiliki riwayat HIV?',
            'lansia' => 'Apakah berusia > 65 tahun (lansia)?',
            'warga_binaan' => 'Apakah termasuk warga binaan?',
            'wilayah_miskin' => 'Apakah tinggal di wilayah miskin/rentan?',
            'gejala_batuk' => 'Apakah mengalami batuk?',
            'gejala_bb_turun' => 'Apakah mengalami penurunan berat badan?',
            'gejala_demam_hilang_timbul' => 'Apakah mengalami demam hilang timbul?',
            'gejala_berkeringat_malam' => 'Apakah berkeringat pada malam hari?',
            'gejala_kelenjar' => 'Apakah ada pembesaran kelenjar getah bening?',
        ];

        return view('kader.screening-create', [
            'questions' => $questions,
        ]);
    }

    public function store(Request $request)
    {
        abort_if($request->user()->role !== UserRole::Kader, 403);

        $questions = [
            'riwayat_kontak_tbc' => 'Apakah pernah kontak erat dengan pasien TBC?',
            'sakit_tbc' => 'Apakah pernah didiagnosis TBC sebelumnya?',
            'kekurangan_gizi' => 'Apakah memiliki riwayat kekurangan gizi?',
            'merokok' => 'Apakah saat ini merokok?',
            'perokok_pasif' => 'Apakah sering terpapar asap rokok (perokok pasif)?',
            'kencing_manis' => 'Apakah memiliki riwayat diabetes/kencing manis?',
            'hiv' => 'Apakah memiliki riwayat HIV?',
            'lansia' => 'Apakah berusia > 65 tahun (lansia)?',
            'warga_binaan' => 'Apakah termasuk warga binaan?',
            'wilayah_miskin' => 'Apakah tinggal di wilayah miskin/rentan?',
            'gejala_batuk' => 'Apakah mengalami batuk?',
            'gejala_bb_turun' => 'Apakah mengalami penurunan berat badan?',
            'gejala_demam_hilang_timbul' => 'Apakah mengalami demam hilang timbul?',
            'gejala_berkeringat_malam' => 'Apakah berkeringat pada malam hari?',
            'gejala_kelenjar' => 'Apakah ada pembesaran kelenjar getah bening?',
        ];

        $rules = [
            'patient_is_wni' => ['required', 'boolean'],
            'patient_name' => ['required', 'string', 'max:255'],
            'patient_nik' => ['nullable', 'string', 'max:30', 'required_if:patient_is_wni,1'],
            'patient_phone' => ['nullable', 'string', 'max:25'],
            'patient_gender' => ['required', 'string', 'max:20'],
            'patient_birth_place' => ['required', 'string', 'max:255'],
            'patient_birth_date' => ['required', 'date'],
            'patient_age' => ['required', 'integer', 'min:0', 'max:150'],
            'patient_address_ktp' => ['required', 'string', 'max:255'],
            'patient_address_domisili' => ['required', 'string', 'max:255'],
            'patient_address_rt' => ['required', 'string', 'max:5'],
            'patient_address_rw' => ['required', 'string', 'max:5'],
            'patient_address_kelurahan' => ['required', 'string', 'max:100'],
            'patient_weight' => ['required', 'numeric', 'min:0'],
            'patient_height' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];

        foreach ($questions as $key => $label) {
            $rules[$key] = ['required', 'in:ya,tidak'];
        }

        $validated = $request->validate($rules);

        $answers = collect($questions)
            ->keys()
            ->mapWithKeys(fn($key) => [$key => $validated[$key]])
            ->toArray();

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
            'patient_address_kelurahan' => $validated['patient_address_kelurahan'],
            'patient_weight' => $validated['patient_weight'],
            'patient_height' => $validated['patient_height'],
            'answers' => $answers,
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('kader.screening.index')->with('status', 'Skrining pasien telah dicatat.');
    }
}
