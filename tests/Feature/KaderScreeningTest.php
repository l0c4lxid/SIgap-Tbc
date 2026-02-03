<?php

use App\Models\User;
use App\Models\PatientScreening;
use App\Enums\UserRole;

test('kader can view screening index', function () {
    $user = User::factory()->create([
        'role' => UserRole::Kader,
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)->get(route('kader.screening.index'));

    $response->assertStatus(200);
});

test('kader can view create screening page', function () {
    $user = User::factory()->create([
        'role' => UserRole::Kader,
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)->get(route('kader.screening.create'));

    $response->assertStatus(200);
});

test('kader can store new screening', function () {
    $user = User::factory()->create([
        'role' => UserRole::Kader,
        'is_active' => true,
    ]);

    $data = [
        'patient_is_wni' => '1',
        'patient_name' => 'Test Patient',
        'patient_nik' => '1234567890123456',
        'patient_phone' => '081234567890',
        'patient_gender' => 'L',
        'patient_birth_place' => 'Jakarta',
        'patient_birth_date' => '1990-01-01',
        'patient_age' => 30,
        'patient_address_ktp' => 'Jl. Test KTP',
        'patient_address_domisili' => 'Jl. Test Domisili',
        'patient_address_rt' => '001',
        'patient_address_rw' => '002',
        'patient_address_kelurahan' => 'Kelurahan Test',
        'patient_weight' => 60,
        'patient_height' => 170,
        // Risk Questions
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
        // Symptom Questions
        'gejala_batuk' => 'ya',
        'gejala_bb_turun' => 'tidak',
        'gejala_demam_hilang_timbul' => 'tidak',
        'gejala_berkeringat_malam' => 'tidak',
        'gejala_kelenjar' => 'tidak',
    ];

    $response = $this->actingAs($user)->post(route('kader.screening.store'), $data);

    $response->assertRedirect(route('kader.screening.index'));
    $this->assertDatabaseHas('patient_screenings', [
        'patient_name' => 'Test Patient',
        'patient_nik' => '1234567890123456',
    ]);
});

test('kader cannot store screening with invalid nik', function () {
    $user = User::factory()->create([
        'role' => UserRole::Kader,
        'is_active' => true,
    ]);

    $data = [
        'patient_is_wni' => '1',
        'patient_name' => 'Test Patient',
        'patient_nik' => 'invalid-nik', // Invalid
    ];

    $response = $this->actingAs($user)->post(route('kader.screening.store'), $data);

    $response->assertSessionHasErrors(['patient_nik']);
});

test('user cannot view screening of another kader', function () {
    $kader1 = User::factory()->create(['role' => UserRole::Kader]);
    $kader2 = User::factory()->create(['role' => UserRole::Kader]);

    $screening = PatientScreening::factory()->create(['kader_id' => $kader1->id]);

    $response = $this->actingAs($kader2)->get(route('kader.screening.show', $screening));

    $response->assertStatus(403);
});

test('kader can delete own screening', function () {
    $user = User::factory()->create(['role' => UserRole::Kader]);
    $screening = PatientScreening::factory()->create(['kader_id' => $user->id]);

    $response = $this->actingAs($user)->delete(route('kader.screening.destroy', $screening));

    $response->assertRedirect(route('kader.screening.index'));
    $this->assertDatabaseMissing('patient_screenings', ['id' => $screening->id]);
});
