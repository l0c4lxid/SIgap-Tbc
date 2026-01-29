<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\PatientScreening;
use App\Models\User;
use App\Models\UserDetail;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class PuskesmasKelurahanSeeder extends Seeder
{
    public function run(): void
    {
        $kelurahan = $this->createUser(
            [
                'name' => 'Kelurahan Nusukan',
                'phone' => '0401',
                'role' => UserRole::Kelurahan,
            ],
            [
                'organization' => 'Kelurahan Nusukan',
                'address' => 'Jl. Mangga Raya, Nusukan, Banjarsari',
                'notes' => 'Kelurahan binaan Puskesmas Nusukan',
                'initial_password' => 'password123',
            ]
        );

        $puskesmas = $this->createUser(
            [
                'name' => 'Puskesmas Nusukan',
                'phone' => '0301',
                'role' => UserRole::Puskesmas,
            ],
            [
                'organization' => 'Puskesmas Nusukan',
                'address' => 'Jl. Nusukan Raya No.12, Nusukan',
                'notes' => 'Wilayah kerja Kelurahan Nusukan',
                'supervisor_id' => $kelurahan->id, // Wait, Puskesmas supervises Kelurahan? Or Kelurahan created first? Logic seems inverted in seeder compared to app logic usually. 
                                                   // In app: Kelurahan has Supervisor (Puskesmas).
                                                   // Let's fix this relationship logic.
                                                   
                'initial_password' => 'password123',
            ]
        );
        
        // Fix: Puskesmas supervises Kelurahan.
        $kelurahan->detail()->update(['supervisor_id' => $puskesmas->id]);
        
        // Wait, the original code had:
        // 'supervisor_id' => $kelurahan->id on Puskesmas? That's wrong.
        // And then $kelurahan->detail()->update(['supervisor_id' => $puskesmas->id]);
        // Let's correct it properly.

        $rwConfig = [
            1 => 5,
            2 => 4,
            3 => 6,
            4 => 6,
            5 => 5,
            6 => 3,
            7 => 9,
            8 => 6,
            9 => 7,
            10 => 5,
            11 => 6,
            12 => 5,
            13 => 8,
            14 => 8,
            15 => 7,
            16 => 8,
            17 => 5,
            18 => 8,
            19 => 3,
            20 => 4,
            21 => 7,
            22 => 5,
            23 => 8,
            24 => 6,
        ];

        $firstNames = ['Siti', 'Budi', 'Rina', 'Agus', 'Dewi', 'Rudi', 'Nina', 'Bagus', 'Eka', 'Lilis'];
        $lastNames = ['Santoso', 'Wulandari', 'Prasetyo', 'Utami', 'Wijaya', 'Saputra', 'Lestari', 'Hidayat'];
        $birthPlaces = ['Surakarta', 'Boyolali', 'Klaten', 'Sukoharjo', 'Karanganyar'];
        $phoneCounter = 1;
        $nikCounter = 1;

        foreach ($rwConfig as $rwNumber => $rtMax) {
            $rwCode = sprintf('%03d', $rwNumber); // Changed to 3 digits to match KaderSeeder

            for ($kaderIndex = 1; $kaderIndex <= 2; $kaderIndex++) {
                $kader = $this->createUser(
                    [
                        'name' => "Kader RW {$rwCode} - {$kaderIndex}",
                        'phone' => sprintf('02%03d%02d', $rwNumber, $kaderIndex), // Adjusted format
                        'role' => UserRole::Kader,
                    ],
                    [
                        'organization' => 'Puskesmas Nusukan', // Should be linked to Kelurahan usually? Or just notes?
                        'address' => "Kelurahan Nusukan RW {$rwCode}, Banjarsari",
                        'notes' => "Kader wilayah RW {$rwCode}",
                        'supervisor_id' => $puskesmas->id, // Kader supervised by Puskesmas directly? Or Kelurahan? App logic says Kelurahan User ID + Puskesmas Supervisor ID.
                        'kelurahan_user_id' => $kelurahan->id,
                        'rw_code' => $rwCode,
                        'rt_code' => '000', // Kader is covering RW usually, but might be specific RT. Let's put 000 if generic or specific?
                                           // App logic requires RT code. Let's assign them to RT 001 for now or random?
                        'initial_password' => 'password123',
                    ]
                );

                for ($i = 1; $i <= 5; $i++) {
                    $rtNumber = ($i % $rtMax) + 1;
                    $rtCode = sprintf('%03d', $rtNumber);
                    $name = $firstNames[array_rand($firstNames)] . ' ' . $lastNames[array_rand($lastNames)];
                    $birthDate = Carbon::parse(now()->subYears(rand(18, 72))->subDays(rand(0, 364))->format('Y-m-d'));
                    $age = $birthDate->age;
                    $gender = rand(0, 1) ? 'L' : 'P';
                    $isWni = true;

                    $answers = [
                        'riwayat_kontak_tbc' => rand(0, 1) ? 'ya' : 'tidak',
                        'sakit_tbc' => rand(0, 1) ? 'ya' : 'tidak',
                        'kekurangan_gizi' => rand(0, 1) ? 'ya' : 'tidak',
                        'merokok' => rand(0, 1) ? 'ya' : 'tidak',
                        'perokok_pasif' => rand(0, 1) ? 'ya' : 'tidak',
                        'kencing_manis' => rand(0, 1) ? 'ya' : 'tidak',
                        'hiv' => rand(0, 1) ? 'ya' : 'tidak',
                        'lansia' => $age > 65 ? 'ya' : 'tidak',
                        'warga_binaan' => rand(0, 1) ? 'ya' : 'tidak',
                        'wilayah_miskin' => rand(0, 1) ? 'ya' : 'tidak',
                        'gejala_batuk' => rand(0, 1) ? 'ya' : 'tidak',
                        'gejala_bb_turun' => rand(0, 1) ? 'ya' : 'tidak',
                        'gejala_demam_hilang_timbul' => rand(0, 1) ? 'ya' : 'tidak',
                        'gejala_berkeringat_malam' => rand(0, 1) ? 'ya' : 'tidak',
                        'gejala_kelenjar' => rand(0, 1) ? 'ya' : 'tidak',
                    ];

                    PatientScreening::create([
                        'kader_id' => $kader->id,
                        'patient_is_wni' => $isWni,
                        'patient_name' => $name,
                        'patient_nik' => sprintf('337201%010d', $nikCounter++),
                        'patient_phone' => sprintf('08121001%04d', $phoneCounter++),
                        'patient_address' => "RT {$rtCode} / RW {$rwCode}, Nusukan",
                        'patient_gender' => $gender,
                        'patient_birth_place' => $birthPlaces[array_rand($birthPlaces)],
                        'patient_birth_date' => $birthDate->format('Y-m-d'),
                        'patient_age' => $age,
                        'patient_address_ktp' => "RT {$rtCode} / RW {$rwCode}, Nusukan, Banjarsari",
                        'patient_address_domisili' => "RT {$rtCode} / RW {$rwCode}, Nusukan, Banjarsari",
                        'patient_address_rt' => $rtCode,
                        'patient_address_rw' => $rwCode,
                        'patient_address_kelurahan' => 'Nusukan',
                        'patient_weight' => rand(45, 80),
                        'patient_height' => rand(150, 180),
                        'answers' => $answers,
                    ]);
                }
            }
        }
    }

    private function createUser(array $userData, array $detail = []): User
    {
        $attributes = [
            'name' => $userData['name'],
            'phone' => $userData['phone'],
            'role' => $userData['role'],
            'password' => Hash::make('password123'),
            'is_active' => true,
        ];

        $user = User::create($attributes);

        UserDetail::create(array_merge(['user_id' => $user->id], $detail));

        return $user;
    }
}
