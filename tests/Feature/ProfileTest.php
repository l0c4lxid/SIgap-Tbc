<?php

use App\Models\User;
use App\Models\UserDetail;
use App\Enums\UserRole;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

test('kader can view profile page', function () {
    $user = User::factory()->create(['role' => UserRole::Kader]);

    $response = $this->actingAs($user)->get(route('profile.edit'));

    $response->assertStatus(200);
});

test('user can update profile information', function () {
    $user = User::factory()->create(['role' => UserRole::Kader]);
    UserDetail::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->patch(route('profile.update'), [
        'name' => 'Updated Name',
        'phone' => '087654321000',
        'address' => 'Updated Address',
        'organization' => 'Updated Org',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Updated Name']);
    $this->assertDatabaseHas('user_details', ['user_id' => $user->id, 'address' => 'Updated Address']);
});

test('user can delete account', function () {
    $user = User::factory()->create(['role' => UserRole::Kader, 'password' => Hash::make('password')]);

    $response = $this->actingAs($user)->delete(route('profile.destroy'), [
        'password' => 'password',
    ]);

    $response->assertRedirect('/');
    $this->assertSoftDeleted('users', ['id' => $user->id]);
});

test('pemda can view own profile page', function () {
    $pemda = User::factory()->create(['role' => UserRole::Pemda]);

    $response = $this->actingAs($pemda)->get(route('pemda.profile.edit'));

    $response->assertStatus(200);
});

test('pemda can update own profile', function () {
    $pemda = User::factory()->create(['role' => UserRole::Pemda]);
    UserDetail::factory()->create(['user_id' => $pemda->id]);

    $response = $this->actingAs($pemda)->put(route('pemda.profile.update'), [
        'name' => 'Pemda Update',
        'phone' => '080000000000',
        'organization' => 'Pemda Org', 
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('users', ['id' => $pemda->id, 'name' => 'Pemda Update']);
    $this->assertDatabaseHas('user_details', ['user_id' => $pemda->id, 'organization' => 'Pemda Org']);
});

test('non-pemda cannot access pemda profile', function () {
    $kader = User::factory()->create(['role' => UserRole::Kader]);

    $response = $this->actingAs($kader)->get(route('pemda.profile.edit'));

    $response->assertStatus(403);
});
