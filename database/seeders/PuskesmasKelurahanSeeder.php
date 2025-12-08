<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use App\Models\UserDetail;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PuskesmasKelurahanSeeder extends Seeder
{
    public function run(): void
    {
        // Keep phone generation reproducible while still avoiding collisions.
        mt_srand(20240608);

        $puskesmasCounter = 1;
        $kelurahanCounter = 1;
        $patientCounter = 1;
        $kaderPhonePool = range(1, 99);
        shuffle($kaderPhonePool);

        $puskesmasList = [
            [
                'name' => 'Puskesmas Pajang',
                'kecamatan' => 'Laweyan',
                'kelurahans' => ['Pajang', 'Sondakan', 'Laweyan', 'Karangasem'],
            ],
            [
                'name' => 'Puskesmas Penumping',
                'kecamatan' => 'Laweyan',
                'kelurahans' => ['Penumping', 'Panularan', 'Sriwedari', 'Bumi'],
            ],
            [
                'name' => 'Puskesmas Purwosari',
                'kecamatan' => 'Laweyan',
                'kelurahans' => ['Purwosari', 'Kerten', 'Jajar'],
            ],
            [
                'name' => 'Puskesmas Jayengan',
                'kecamatan' => 'Serengan',
                'kelurahans' => ['Kemlayan', 'Jayengan', 'Tipes', 'Serengan'],
            ],
            [
                'name' => 'Puskesmas Kratonan',
                'kecamatan' => 'Serengan',
                'kelurahans' => ['Danukusuman', 'Kratonan', 'Joyotakan'],
            ],
            [
                'name' => 'Puskesmas Gajahan',
                'kecamatan' => 'Pasar Kliwon',
                'kelurahans' => ['Joyosuran', 'Pasar Kliwon', 'Gajahan', 'Baluwarti', 'Kauman', 'Kampung Baru'],
            ],
            [
                'name' => 'Puskesmas Sangkrah',
                'kecamatan' => 'Pasar Kliwon',
                'kelurahans' => ['Sangkrah', 'Semanggi', 'Kedung Lumbu', 'Mojo'],
            ],
            [
                'name' => 'Puskesmas Sibela',
                'kecamatan' => 'Jebres',
                'kelurahans' => ['Mojosongo'],
            ],
            [
                'name' => 'Puskesmas Ngoresan',
                'kecamatan' => 'Jebres',
                'kelurahans' => ['Jebres'],
            ],
            [
                'name' => 'Puskemas Purwodiningratan',
                'kecamatan' => 'Jebres',
                'kelurahans' => ['Sudiroprajan', 'Gandekan', 'Tegalharjo', 'Purwodiningratan', 'Kepatihan Wetan', 'Kepatihan Kulon'],
            ],
            [
                'name' => 'Puskesmas Pucangsawit',
                'kecamatan' => 'Jebres',
                'kelurahans' => ['Jagalan', 'Pucangsawit', 'Sewu'],
            ],
            [
                'name' => 'Puskesmas Gilingan',
                'kecamatan' => 'Banjarsari',
                'kelurahans' => ['Gilingan', 'Ketelan', 'Punggawan'],
            ],
            [
                'name' => 'Puskesmas Gambirsari',
                'kecamatan' => 'Banjarsari',
                'kelurahans' => ['Kadipiro', 'Banjarsari', 'Joglo'],
            ],
            [
                'name' => 'Puskesmas Setabelan',
                'kecamatan' => 'Banjarsari',
                'kelurahans' => ['Setabelan', 'Keprabon', 'Ketalan', 'Timuran'],
            ],
            [
                'name' => 'Puskesmas Banyuanyar',
                'kecamatan' => 'Banjarsari',
                'kelurahans' => ['Banyuanyar', 'Sumber'],
            ],
            [
                'name' => 'Puskesmas Manahan',
                'kecamatan' => 'Banjarsari',
                'kelurahans' => ['Manahan', 'Mangkubumen'],
            ],
            [
                'name' => 'Puskesmas Nusukan',
                'kecamatan' => 'Banjarsari',
                'kelurahans' => ['Nusukan'],
            ],
        ];

        foreach ($puskesmasList as $puskesmasData) {
            $kelurahanUsers = [];

            foreach ($puskesmasData['kelurahans'] as $kelurahan) {
                $kelurahanUsers[] = $this->createUser(
                    [
                        'name' => "Kelurahan {$kelurahan}",
                        'phone' => sprintf('04%02d', $kelurahanCounter),
                        'role' => UserRole::Kelurahan,
                    ],
                    [
                        'organization' => "Kelurahan {$kelurahan}",
                        'notes' => "Wilayah kerja {$puskesmasData['name']}",
                        'initial_password' => 'password123',
                    ]
                );

                $kelurahanCounter++;
            }

            $pembinaKelurahanId = $kelurahanUsers[0]->id ?? null;

            $puskesmasUser = $this->createUser(
                [
                    'name' => $puskesmasData['name'],
                    'phone' => sprintf('03%02d', $puskesmasCounter),
                    'role' => UserRole::Puskesmas,
                ],
                [
                    'organization' => $puskesmasData['name'],
                    'notes' => "Kecamatan {$puskesmasData['kecamatan']}",
                    'supervisor_id' => $pembinaKelurahanId,
                    'initial_password' => 'password123',
                ]
            );

            $kaderPhoneSuffix = array_pop($kaderPhonePool);
            $primaryKelurahan = $puskesmasData['kelurahans'][0] ?? $puskesmasData['kecamatan'];

            $kaderUser = $this->createUser(
                [
                    'name' => "Kader {$puskesmasData['name']}",
                    'phone' => sprintf('02%02d', $kaderPhoneSuffix),
                    'role' => UserRole::Kader,
                ],
                [
                    'organization' => $puskesmasData['name'],
                    'address' => "Kelurahan {$primaryKelurahan}, Kecamatan {$puskesmasData['kecamatan']}",
                    'notes' => "Kader wilayah {$primaryKelurahan}",
                    'supervisor_id' => $puskesmasUser->id,
                    'initial_password' => 'password123',
                ]
            );

            $puskesmasCounter++;

            foreach ($kelurahanUsers as $kelurahanUser) {
                $kelurahanUser->detail()->update(['supervisor_id' => $puskesmasUser->id]);
            }

            $kelurahanCount = count($puskesmasData['kelurahans']);
            for ($i = 0; $i < 2; $i++) {
                $kelurahanName = $puskesmasData['kelurahans'][$i % $kelurahanCount] ?? $primaryKelurahan;

                $this->createUser(
                    [
                        'name' => "Pasien {$kelurahanName} " . ($i + 1),
                        'phone' => sprintf('05%02d', $patientCounter),
                        'role' => UserRole::Pasien,
                    ],
                    [
                        'address' => "Kelurahan {$kelurahanName}, Kecamatan {$puskesmasData['kecamatan']}",
                        'notes' => "Dampingan {$kaderUser->name}",
                        'supervisor_id' => $kaderUser->id,
                        'initial_password' => 'password123',
                        'nik' => sprintf('337200%010d', $patientCounter),
                    ]
                );

                $patientCounter++;
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
