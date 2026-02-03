<?php

use App\Models\User;
use App\Models\NewsPost;
use App\Enums\UserRole;

test('public can view blog index', function () {
    $response = $this->get(route('blog.index'));
    $response->assertStatus(200);
});

test('public can view published post', function () {
    $post = NewsPost::factory()->published()->create();

    $response = $this->get(route('blog.show', $post));
    $response->assertStatus(200);
    $response->assertSee($post->title);
});

test('public cannot view pending post', function () {
    $post = NewsPost::factory()->create(['status' => 'pending']);

    $response = $this->get(route('blog.show', $post));
    $response->assertStatus(404);
});

test('user can create news post', function () {
    $user = User::factory()->create(['role' => UserRole::Puskesmas]);

    $response = $this->actingAs($user)->post(route('news.store'), [
        'title' => 'Test News',
        'content' => 'Content here',
    ]);

    $response->assertRedirect(route('news.index'));
    $this->assertDatabaseHas('news_posts', [
        'title' => 'Test News',
        'status' => 'pending',
    ]);
});

test('pemda can publish post', function () {
    $pemda = User::factory()->create(['role' => UserRole::Pemda]);
    $author = User::factory()->create(['role' => UserRole::Puskesmas]);
    $post = NewsPost::factory()->create(['user_id' => $author->id, 'status' => 'pending']);

    $response = $this->actingAs($pemda)->post(route('news.publish', $post));

    $response->assertRedirect(route('news.index'));
    $this->assertDatabaseHas('news_posts', [
        'id' => $post->id,
        'status' => 'published',
    ]);
});

test('unauthorized user cannot publish post', function () {
    $kader = User::factory()->create(['role' => UserRole::Kader]);
    $post = NewsPost::factory()->create(['status' => 'pending']);

    $response = $this->actingAs($kader)->post(route('news.publish', $post));

    $response->assertStatus(403);
});
