<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('guest cannot create channel', function () {
    $response = $this->postJson('/api/v1/channels', [
        'name' => 'Private Group',
    ]);

    $response->assertStatus(401);
});

test('authenticated user can create channel', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/channels', [
        'name' => 'Friday Board Games',
        'description' => 'Catan & Pizza',
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('success', true)
        ->assertJsonPath('channel.name', 'Friday Board Games')
        ->assertJsonPath('channel.owner_id', $user->id)
        ->assertJsonStructure([
            'success',
            'channel' => ['id', 'name', 'description', 'invite_link', 'owner_id', 'created_at']
        ]);
});

test('channel name is required', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/channels', [
        'description' => 'Just a description',
    ]);

    $response->assertStatus(422);
});

test('full flow: register → login → create channel', function () {
    $reg = $this->postJson('/api/v1/authorization/register', [
        'name' => 'Flow User',
        'email' => 'flow@example.com',
        'password' => 'pass12345',
        'password_confirmation' => 'pass12345',
    ]);
    $reg->assertStatus(201);
    $token = $reg->json('token');

    $channel = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
        'Accept' => 'application/json',
    ])->postJson('/api/v1/channels', [
        'name' => 'Integration Test Group',
    ]);

    $channel->assertStatus(201)
        ->assertJsonPath('success', true)
        ->assertJsonPath('channel.name', 'Integration Test Group');
});
