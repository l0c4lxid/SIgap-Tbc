<?php

use App\Models\User;
use App\Enums\UserRole;

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create([
        'role' => UserRole::Pemda,
        'is_active' => true,
    ]);

    $response = $this->post('/login', [
        'phone' => $user->phone,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard'));
});

test('inactive users cannot authenticate', function () {
    $user = User::factory()->create([
        'role' => UserRole::Pemda,
        'is_active' => false,
    ]);

    $response = $this->post('/login', [
        'phone' => $user->phone,
        'password' => 'password',
    ]);

    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create([
        'role' => UserRole::Pemda,
        'is_active' => true,
    ]);

    $this->actingAs($user);

    $response = $this->post('/logout');

    $this->assertGuest();
    $response->assertRedirect(route('login'));
});
