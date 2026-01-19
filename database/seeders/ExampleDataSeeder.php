<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use App\Models\UserDetail;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ExampleDataSeeder extends Seeder
{
    public function run(): void
    {
        $pemda = $this->createUser(
            [
                'name' => 'Dinas Kesehatan Kota Surakarta',
                'phone' => '0271642911',
                'role' => UserRole::Pemda,
                'password' => 'password123',
            ],
            [
                'organization' => 'Dinas Kesehatan Kota Surakarta',
                'address' => 'Jl. Menteri Supeno No.7, Manahan, Surakarta',
                'notes' => 'Akun utama Pemda/Diskes Surakarta',
            ],
        );

        $kelurahanPayloads = [
            [
                'key' => 'manahan',
                'name' => 'Kelurahan Manahan',
                'phone' => '0271741001',
                'address' => 'Jl. MT Haryono No.10, Manahan, Banjarsari',
            ],
            [
                'key' => 'gajahan',
                'name' => 'Kelurahan Gajahan',
                'phone' => '0271715516',
                'address' => 'Jl. Slamet Riyadi No.375, Pasar Kliwon',
            ],
            [
                'key' => 'purwosari',
                'name' => 'Kelurahan Purwosari',
                'phone' => '0271714299',
                'address' => 'Jl. Slamet Riyadi No.260, Laweyan',
            ],
        ];

        $kelurahanMap = [];
        foreach ($kelurahanPayloads as $payload) {
            $kelurahanMap[$payload['key']] = $this->createUser(
                [
                    'name' => $payload['name'],
                    'phone' => $payload['phone'],
                    'role' => UserRole::Kelurahan,
                    'password' => 'password123',
                ],
                [
                    'organization' => $payload['name'],
                    'address' => $payload['address'],
                ],
            );
        }

        $puskesmasPayloads = [
            [
                'key' => 'gajahan',
                'name' => 'Puskesmas Gajahan',
                'phone' => '0271714174',
                'address' => 'Jl. Kapten Mulyadi No.459, Pasar Kliwon',
                'kelurahan_key' => 'gajahan',
            ],
            [
                'key' => 'nguter',
                'name' => 'Puskesmas Ngoresan',
                'phone' => '0271739821',
                'address' => 'Jl. Ir. Sutami No.58, Jebres',
                'kelurahan_key' => 'manahan',
            ],
            [
                'key' => 'purwosari',
                'name' => 'Puskesmas Purwosari',
                'phone' => '0271714529',
                'address' => 'Jl. Slamet Riyadi No.260, Laweyan',
                'kelurahan_key' => 'purwosari',
            ],
        ];

        $puskesmasMap = [];
        foreach ($puskesmasPayloads as $payload) {
            $kelurahan = $kelurahanMap[$payload['kelurahan_key']];
            $puskesmasMap[$payload['key']] = $this->createUser(
                [
                    'name' => $payload['name'],
                    'phone' => $payload['phone'],
                    'role' => UserRole::Puskesmas,
                    'password' => 'password123',
                ],
                [
                    'organization' => $payload['name'],
                    'address' => $payload['address'],
                    'supervisor_id' => $kelurahan->id,
                ],
            );
        }

        $kaderPayloads = [
            [
                'key' => 'kader-larasati',
                'name' => 'Larasati Wulandari',
                'phone' => '081328761111',
                'notes' => 'RW 01 Gajahan',
                'puskesmas_key' => 'gajahan',
            ],
            [
                'key' => 'kader-surya',
                'name' => 'Surya Pranata',
                'phone' => '081246552222',
                'notes' => 'RW 02 Gajahan',
                'puskesmas_key' => 'gajahan',
            ],
            [
                'key' => 'kader-anindita',
                'name' => 'Anindita Dewi',
                'phone' => '081399113333',
                'notes' => 'RW 03 Manahan',
                'puskesmas_key' => 'nguter',
            ],
            [
                'key' => 'kader-yusuf',
                'name' => 'Yusuf Santoso',
                'phone' => '081277554444',
                'notes' => 'RW 04 Manahan',
                'puskesmas_key' => 'nguter',
            ],
            [
                'key' => 'kader-ratri',
                'name' => 'Ratri Kusuma',
                'phone' => '081355115555',
                'notes' => 'RW 05 Purwosari',
                'puskesmas_key' => 'purwosari',
            ],
            [
                'key' => 'kader-dimas',
                'name' => 'Dimas Nugroho',
                'phone' => '081299886666',
                'notes' => 'RW 06 Purwosari',
                'puskesmas_key' => 'purwosari',
            ],
        ];

        foreach ($kaderPayloads as $payload) {
            $puskesmas = $puskesmasMap[$payload['puskesmas_key']];
            $this->createUser(
                [
                    'name' => $payload['name'],
                    'phone' => $payload['phone'],
                    'role' => UserRole::Kader,
                    'password' => 'password123',
                ],
                [
                    'supervisor_id' => $puskesmas->id,
                    'notes' => $payload['notes'],
                ],
            );
        }
    }

    protected function createUser(array $userData, array $detail = []): User
    {
        $attributes = [
            'name' => $userData['name'],
            'phone' => $userData['phone'],
            'role' => $userData['role'],
            'password' => Hash::make($userData['password'] ?? 'password123'),
            'is_active' => $userData['is_active'] ?? true,
        ];

        $user = User::create($attributes);

        UserDetail::create(array_merge(['user_id' => $user->id], $detail));

        return $user;
    }
}
