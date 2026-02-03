<?php

use App\Models\User;
use App\Models\PatientScreening;
use App\Enums\UserRole;



test('pemda can view screening list', function () {
    $pemda = User::factory()->create([
        'role' => UserRole::Pemda,
        'is_active' => true,
    ]);

    $response = $this->actingAs($pemda)
        ->get(route('pemda.screenings'));

    $response->assertStatus(200);
});

test('pemda can view edit screening page', function () {
    $pemda = User::factory()->create([
        'role' => UserRole::Pemda,
        'is_active' => true,
    ]);
    
    // Create a screening associated with a kader (factory will create user)
    $screening = PatientScreening::factory()->create();

    $response = $this->actingAs($pemda)
        ->get(route('pemda.screenings.edit', $screening));

    $response->assertStatus(200);
    $response->assertSee($screening->patient_name);
});

test('pemda can update screening', function () {
    $pemda = User::factory()->create([
        'role' => UserRole::Pemda,
        'is_active' => true,
    ]);
    
    $screening = PatientScreening::factory()->create([
        'patient_name' => 'Old Name',
    ]);

    $newData = [
        'patient_is_wni' => '1',
        'patient_name' => 'Updated Name',
        'patient_nik' => '3301010101010001',
        'patient_gender' => 'L',
        'patient_birth_place' => 'Solo',
        'patient_birth_date' => '1990-01-01',
        'patient_age' => 30,
        'patient_address_ktp' => 'Jalan A',
        'patient_address_domisili' => 'Jalan B',
        'patient_address_rt' => '001',
        'patient_address_rw' => '002',
        'patient_address_kelurahan' => 'Kelurahan Baru',
        'patient_weight' => 60,
        'patient_height' => 170,
        // Mock required answers
        'riwayat_kontak_tbc' => 'tidak',
        'sakit_tbc' => 'tidak',
        'kekurangan_gizi' => 'tidak',
        'merokok' => 'tidak',
        'perokok_pasif' => 'tidak',
        'kencing_manis' => 'tidak',
        'hiv' => 'tidak',
        'lansia' => 'tidak',
        'warga_binaan' => 'tidak',
        'wilayah_miskin' => 'tidak',
        'gejala_batuk' => 'tidak',
        'gejala_bb_turun' => 'tidak',
        'gejala_demam_hilang_timbul' => 'tidak',
        'gejala_berkeringat_malam' => 'tidak',
        'gejala_kelenjar' => 'tidak',
    ];

    $response = $this->actingAs($pemda)
        ->put(route('pemda.screenings.update', $screening), $newData);

    $response->assertRedirect(route('pemda.screenings.show', $screening));
    $response->assertSessionHas('success');
    
    $this->assertDatabaseHas('patient_screenings', [
        'id' => $screening->id,
        'patient_name' => 'Updated Name',
    ]);
});

test('pemda can delete screening', function () {
    $pemda = User::factory()->create([
        'role' => UserRole::Pemda,
        'is_active' => true,
    ]);
    
    $screening = PatientScreening::factory()->create();

    $response = $this->actingAs($pemda)
        ->delete(route('pemda.screenings.destroy', $screening));

    $response->assertRedirect(route('pemda.screenings'));
    $response->assertSessionHas('success');
    
    $this->assertDatabaseMissing('patient_screenings', [
        'id' => $screening->id,
    ]);
});

test('kader cannot access pemda screening edit', function () {
    $kader = User::factory()->create([
        'role' => UserRole::Kader,
        'is_active' => true,
    ]);
    
    $screening = PatientScreening::factory()->create();

    $response = $this->actingAs($kader)
        ->get(route('pemda.screenings.edit', $screening));

    $response->assertStatus(403);
});
