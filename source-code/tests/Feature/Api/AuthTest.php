<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('user can register and receive token', function () {
    $response = $this->postJson('/api/v1/authorization/register', [
        'name' => 'Dev',
        'email' => ' alex@test.com ',
        'password' => 'strongPass123',
        'password_confirmation' => 'strongPass123',
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure(['success', 'token'])
        ->assertJsonPath('success', true);

    $this->assertDatabaseHas('users', ['email' => 'alex@test.com']);
});

test('registration fails without password confirmation', function () {
    $response = $this->postJson('/api/v1/authorization/register', [
        'name' => 'Alex',
        'email' => 'alex@test.com',
        'password' => 'strongPass123',
    ]);

    $response->assertStatus(422);
});

test('user can login with correct credentials', function () {
    $email = 'login@test.com';

    User::factory()->create([
        'email' => $email,
        'password' => bcrypt('secret123'),
    ]);

    $response = $this->postJson('/api/v1/authorization/login', [
        'email' => $email,
        'password' => 'secret123',
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('success', true)
        ->assertJsonStructure(['token']);
});

test('login fails with wrong password', function () {
    User::factory()->create(['password' => bcrypt('correct')]);

    $response = $this->postJson('/api/v1/authorization/login', [
        'email' => 'login@test.com',
        'password' => 'wrong',
    ]);

    $response->assertStatus(401)
        ->assertJsonPath('success', false);
});

test('authenticated user can logout', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/logout');

    $response->assertStatus(200)
        ->assertJsonPath('success', true);
});
