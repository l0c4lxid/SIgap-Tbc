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

        $faker = \Faker\Factory::create('id_ID');

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

                PatientScreening::create([
                    'kader_id' => $kader->id,
                    'patient_is_wni' => true,
                    'patient_name' => $faker->name,
                    'patient_nik' => $faker->nik,
                    'patient_phone' => $faker->phoneNumber,
                    'patient_address' => $faker->address,
                    
                    // Populate Location Fields
                    'patient_address_kelurahan' => $kelurahanName,
                    'patient_address_rw' => $rw,
                    'patient_address_rt' => $rt,

                    'patient_gender' => rand(0, 1) ? 'L' : 'P',
                    'patient_birth_place' => $faker->city,
                    'patient_birth_date' => $faker->date('Y-m-d', '-20 years'),
                    'patient_age' => rand(20, 70),
                    'patient_address_ktp' => $faker->address,
                    'patient_address_domisili' => $faker->address,
                    
                    // Allow created_at to be this month for dashboard charts
                    'created_at' => Carbon::now()->subDays(rand(0, 20)),
                    'updated_at' => Carbon::now(),
                    
                    'answers' => $answers,
                ]);
            }
        }
    }
}
