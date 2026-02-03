<?php

use App\Models\User;
use App\Models\UserDetail;
use App\Models\PatientScreening;
use App\Enums\UserRole;

test('pemda can view dashboard with correct cards', function () {
    $user = User::factory()->create([
        'role' => UserRole::Pemda,
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertStatus(200);
    $response->assertViewIs('dashboard');
    $response->assertViewHas('cards', function ($cards) {
        return count($cards) === 4 
            && $cards[0]['label'] === 'Pengguna'
            && $cards[1]['label'] === 'Skrining Total';
    });
});

test('puskesmas can view dashboard with correct cards', function () {
    $user = User::factory()->create([
        'role' => UserRole::Puskesmas,
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertStatus(200);
    $response->assertViewHas('cards', function ($cards) {
        return count($cards) === 4 
            && $cards[0]['label'] === 'Kader Aktif';
    });
});

test('kader can view dashboard with correct cards', function () {
    $user = User::factory()->create([
        'role' => UserRole::Kader,
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertStatus(200);
    $response->assertViewHas('cards', function ($cards) {

        return count($cards) === 3
            && $cards[0]['label'] === 'Skrining Dicatat';
    });
});

test('kelurahan without supervisor sees empty state', function () {
    // Create Kelurahan without supervisor
    $user = User::factory()->create([
        'role' => UserRole::Kelurahan,
        'is_active' => true,
    ]);
    // Ensure detail exists but no supervisor
    UserDetail::factory()->create([
        'user_id' => $user->id,
        'supervisor_id' => null,
    ]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertStatus(200);
    $response->assertViewHas('cards', function ($cards) {
        // Should show placeholders
        return $cards[0]['value'] === 0 && $cards[0]['trend'] === 'Menunggu persetujuan';
    });
});

test('kelurahan with supervisor sees data', function () {
    // dd(\Illuminate\Support\Facades\DB::getDatabaseName());
    // 1. Create Puskesmas (Supervisor)
    $puskesmas = User::factory()->create(['role' => UserRole::Puskesmas]);

    // 2. Create Kelurahan attached to Puskesmas
    $kelurahan = User::factory()->create(['role' => UserRole::Kelurahan]);
    UserDetail::factory()->create([
        'user_id' => $kelurahan->id,
        'supervisor_id' => $puskesmas->id,
        'organization' => 'Kelurahan Test',
    ]);

    // 3. Create Kader attached to Puskesmas (same supervisor chain)
    $kader = User::factory()->create(['role' => UserRole::Kader]);
    UserDetail::factory()->create([
        'user_id' => $kader->id,
        'supervisor_id' => $puskesmas->id,
    ]);

    // 4. Create Screening for this Kader
    PatientScreening::factory()->create([
        'kader_id' => $kader->id,
        'patient_address_kelurahan' => 'Kelurahan Test',
    ]);

    $response = $this->actingAs($kelurahan)->get(route('dashboard'));

    $response->assertStatus(200);
    $response->assertViewHas('cards', function ($cards) {
        // First card is 'Skrining Tercatat', should be > 0
        return $cards[0]['label'] === 'Skrining Tercatat';
    });
});
