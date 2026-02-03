<?php

use App\Models\User;
use App\Models\UserDetail;
use App\Enums\UserRole;

test('pemda can view verification list', function () {
    $pemda = User::factory()->create(['role' => UserRole::Pemda]);
    
    // Create pending users
    User::factory()->count(3)->create(['role' => UserRole::Kader, 'is_active' => false]);

    $response = $this->actingAs($pemda)->get(route('pemda.verification'));

    $response->assertStatus(200);
    $response->assertViewHas('records');
});

test('pemda can approve a user', function () {
    $pemda = User::factory()->create(['role' => UserRole::Pemda]);
    $kader = User::factory()->create(['role' => UserRole::Kader, 'is_active' => false]);

    $response = $this->actingAs($pemda)->post(route('pemda.verification.status', $kader), [
        'status' => 'active',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('users', ['id' => $kader->id, 'is_active' => true]);
});

test('pemda can update user info', function () {
    $pemda = User::factory()->create(['role' => UserRole::Pemda]);
    $kader = User::factory()->create(['role' => UserRole::Kader]);
    UserDetail::factory()->create(['user_id' => $kader->id]);

    $supervisor = User::factory()->create(['role' => UserRole::Puskesmas, 'is_active' => true]);

    $response = $this->actingAs($pemda)->put(route('pemda.verification.update', $kader), [
        'name' => 'Updated Name',
        'is_active' => true, 
        'organization' => 'New Org',
        'address' => 'New Address',
        'supervisor_id' => $supervisor->id,
    ]);

    $response->assertRedirect();
    $response->assertSessionHasNoErrors();
    $this->assertDatabaseHas('users', ['id' => $kader->id, 'name' => 'Updated Name']);
    $this->assertDatabaseHas('user_details', ['user_id' => $kader->id, 'organization' => 'New Org']);
});

test('pemda can bulk approve users', function () {
    $pemda = User::factory()->create(['role' => UserRole::Pemda]);
    $kaders = User::factory()->count(3)->create(['role' => UserRole::Kader, 'is_active' => false]);

    $response = $this->actingAs($pemda)->post(route('pemda.verification.bulk-status'), [
        'role' => UserRole::Kader->value,
        'status' => 'active',
    ]);

    $response->assertRedirect();
    $this->assertEquals(3, User::where('role', UserRole::Kader)->where('is_active', true)->count());
});

test('non-pemda cannot access verification', function () {
    $kader = User::factory()->create(['role' => UserRole::Kader]);
    
    $response = $this->actingAs($kader)->get(route('pemda.verification'));

    $response->assertStatus(403);
});
