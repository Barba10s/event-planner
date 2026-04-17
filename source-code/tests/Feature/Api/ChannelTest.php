<?php

use App\Models\Channel\Casts\ChannelRole;
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

test('channel creator gets owner role in pivot', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/channels', ['name' => 'My Group']);
    $channelId = $response->json('channel.id');

    $this->assertDatabaseHas('channel_user', [
        'channel_id' => $channelId,
        'user_id' => $user->id,
        'role' => ChannelRole::OWNER,
    ]);
});

test('joined user gets member role in pivot', function () {
    $owner = User::factory()->create();
    $guest = User::factory()->create();
    Sanctum::actingAs($guest);

    $channel = $owner->ownedChannels()->create([
        'name' => 'Test Channel',
        'invite_token' => Str::random(32),
    ]);

    $this->postJson("/api/v1/channels/join/{$channel->invite_token}")->assertStatus(200);

    $this->assertDatabaseHas('channel_user', [
        'channel_id' => $channel->id,
        'user_id' => $guest->id,
        'role' => ChannelRole::MEMBER->value,
    ]);
});

test('pivot role is correctly cast to ChannelRole enum instance', function () {
    $owner = User::factory()->create();
    $guest = User::factory()->create();

    $channel = $owner->ownedChannels()->create([
        'name' => 'Enum Test',
        'invite_token' => Str::random(32),
    ]);

    $channel->members()->attach($guest, [
        'role' => ChannelRole::MEMBER,
        'joined_at' => now(),
    ]);

    $channel->refresh();
    $memberRecord = $channel->members->firstWhere('id', $guest->id);
    $pivotRole = $memberRecord->pivot->role;

    expect($pivotRole)->toBeInstanceOf(ChannelRole::class)
        ->and($pivotRole)->toBe(ChannelRole::MEMBER);
});
