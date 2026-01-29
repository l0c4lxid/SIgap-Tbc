<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\PatientScreening;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class ScreeningSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Load kaders with their details to get kelurahan info
        $kaders = User::where('role', UserRole::Kader)
            ->with(['detail'])
            ->get();
            
        $this->command->info("Found {$kaders->count()} kaders. Generating screenings...");



        foreach ($kaders as $kader) {
            // Get Kelurahan Name
            $kelurahanName = 'Nusukan'; // Default fallback
            if ($kader->detail && $kader->detail->kelurahan_user_id) {
                $kelurahanUser = User::find($kader->detail->kelurahan_user_id);
                if ($kelurahanUser) {
                    // Remove "Kelurahan" prefix if present for cleaner data
                    $kelurahanName = trim(str_ireplace('Kelurahan', '', $kelurahanUser->name));
                }
            }
            
            // Generate 3-8 screenings per kader to ensure enough data
            $count = rand(3, 8);
            
            for ($i = 0; $i < $count; $i++) {
                $isSuspek = rand(0, 100) < 30; // 30% chance of suspek
                
                if ($isSuspek) {
                    // Suspect: Mix of symptoms, but usually Cough is main indicator or at least one is present
                    // For simplicity, let's say Suspects always have cough OR weight loss
                    $answers = [
                        'gejala_batuk' => rand(0, 100) < 80 ? 'ya' : 'tidak', // 80% cough
                        'gejala_bb_turun' => rand(0, 100) < 40 ? 'ya' : 'tidak',
                        'gejala_demam_hilang_timbul' => rand(0, 100) < 30 ? 'ya' : 'tidak',
                        'gejala_berkeringat_malam' => rand(0, 100) < 30 ? 'ya' : 'tidak',
                    ];
                    // Ensure at least one is 'ya' if we really want to guarantee strict "Suspect" definition
                    if (!in_array('ya', $answers)) {
                        $answers['gejala_batuk'] = 'ya';
                    }
                } else {
                    // Non-Suspect: No symptoms
                    $answers = [
                        'gejala_batuk' => 'tidak',
                        'gejala_bb_turun' => 'tidak',
                        'gejala_demam_hilang_timbul' => 'tidak',
                        'gejala_berkeringat_malam' => 'tidak',
                    ];
                }

                // Random RW/RT (Assuming specific format or just random numbers)
                // If Kader has RW assigned, maybe prioritize that, but for now random is fine for coverage
                $rw = $kader->detail->rw_code ?? str_pad(rand(1, 23), 3, '0', STR_PAD_LEFT); // Example RW
                $rt = $kader->detail->rt_code ?? str_pad(rand(1, 9), 3, '0', STR_PAD_LEFT); // Example RT

                // Generate Random Data without Faker (for Prod compat)
                $firstNames = ['Budi', 'Siti', 'Agus', 'Ratna', 'Joko', 'Sri', 'Wayan', 'Eka', 'Santoso', 'Lestari', 'Bambang', 'Wati'];
                $lastNames = ['Susanto', 'Widodo', 'Kusuma', 'Putri', 'Saputra', 'Indah', 'Pratama', 'Dewi', 'Setiawan', 'Ningsih'];
                
                $name = $firstNames[array_rand($firstNames)] . ' ' . $lastNames[array_rand($lastNames)];
                $nik = '3372' . rand(100000000000, 999999999999);
                $phone = '08' . rand(1000000000, 9999999999);
                $street = ['Jl. Slamet Riyadi', 'Jl. Urip Sumoharjo', 'Jl. Adi Sucipto', 'Jl. Dr. Radjiman', 'Jl. Veteran', 'Jl. Sutan Syahrir'][rand(0, 5)];
                $address = $street . ' No. ' . rand(1, 150) . ', Surakarta';

                PatientScreening::create([
                    'kader_id' => $kader->id,
                    'patient_is_wni' => true,
                    'patient_name' => $name,
                    'patient_nik' => $nik,
                    'patient_phone' => $phone,
                    'patient_address' => $address,
                    
                    // Populate Location Fields
                    'patient_address_kelurahan' => $kelurahanName,
                    'patient_address_rw' => $rw,
                    'patient_address_rt' => $rt,

                    'patient_gender' => rand(0, 1) ? 'L' : 'P',
                    'patient_birth_place' => 'Surakarta',
                    'patient_birth_date' => Carbon::now()->subYears(rand(20, 70))->subDays(rand(0, 365)),
                    'patient_age' => rand(20, 70),
                    'patient_address_ktp' => $address,
                    'patient_address_domisili' => $address,
                    
                    // Allow created_at to be this month for dashboard charts
                    'created_at' => Carbon::now()->subDays(rand(0, 20)),
                    'updated_at' => Carbon::now(),
                    
                    'answers' => $answers,
                ]);
            }
        }
    }
}
