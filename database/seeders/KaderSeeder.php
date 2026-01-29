<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use App\Models\UserDetail;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class KaderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Get Kelurahan User (Assuming one exists from PuskesmasKelurahanSeeder or create one)
        $kelurahanRoleValue = UserRole::Kelurahan->value;
        $kelurahanUser = User::where('role', $kelurahanRoleValue)->first();

        if (!$kelurahanUser) {
             $kelurahanUser = User::factory()->create([
                'name' => 'Kelurahan Admin',
                'role' => UserRole::Kelurahan,
            ]);
            UserDetail::create([
                'user_id' => $kelurahanUser->id,
                'organization' => 'Kelurahan Default',
                'address' => 'Alamat Default',
            ]);
        }

        // 2. Define RW/RT Structure
        $rwStructure = [
            '001' => 5,
            '002' => 4,
            '003' => 6,
            '004' => 6,
            '005' => 5,
            '006' => 3,
            '007' => 9,
            '008' => 6,
            '009' => 7,
            '010' => 5,
        ];

        // 3. Create Kaders
        foreach ($rwStructure as $rw => $rtCount) {
            $this->command->info("Seeding RW {$rw} with {$rtCount} RTs...");
            
            for ($rt = 1; $rt <= $rtCount; $rt++) {
                $rtCode = str_pad((string) $rt, 3, '0', STR_PAD_LEFT);
                
                // Create 2 Kaders per RT to avoid too many users
                for ($k = 1; $k <= 2; $k++) {
                    $kaderName = "Kader RW {$rw} RT {$rtCode} - {$k}";
                    $phoneSuffix = md5("{$rw}-{$rtCode}-{$k}");
                    $phone = '08' . substr(preg_replace('/[^0-9]/', '', $phoneSuffix), 0, 10);
                    
                    if (User::where('phone', $phone)->exists()) {
                        continue;
                    }

                    $kader = User::create([
                        'name' => $kaderName,
                        'phone' => $phone,
                        'password' => Hash::make('password'),
                        'role' => UserRole::Kader,
                        'is_active' => true,
                    ]);

                    UserDetail::create([
                        'user_id' => $kader->id,
                        'kelurahan_user_id' => $kelurahanUser->id,
                        'supervisor_id' => $kelurahanUser->detail?->supervisor_id,
                        'rw_code' => $rw,
                        'rt_code' => $rtCode,
                        'address' => "RW {$rw} / RT {$rtCode}",
                        'initial_password' => 'password',
                    ]);
                }
            }
        }
    }
}
