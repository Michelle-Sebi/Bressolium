<?php

// ==========================================
// TEST FOR: TASK 29 — Tests Unitarios de Backend
// Repository: Eloquent\UserRepository
// ==========================================

use App\Models\User;
use App\Repositories\Eloquent\UserRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('UserRepository::create persiste un usuario en BD con password hasheada', function () {
    $repo = new UserRepository;
    $user = $repo->create([
        'name' => 'Bárbara',
        'email' => 'b@test.com',
        'password' => 'secret123',
    ]);

    expect($user)->toBeInstanceOf(User::class)
        ->and($user->name)->toBe('Bárbara')
        ->and($user->email)->toBe('b@test.com')
        ->and(Hash::check('secret123', $user->password))->toBeTrue()
        ->and($user->password)->not->toBe('secret123');
});

test('UserRepository::create persiste el usuario en la BD', function () {
    $repo = new UserRepository;
    $repo->create(['name' => 'Test', 'email' => 'test@x.com', 'password' => 'pass']);

    expect(User::count())->toBe(1);
});

test('UserRepository::findByEmail devuelve el usuario con ese email', function () {
    User::factory()->create(['email' => 'target@x.com']);
    User::factory()->create(['email' => 'other@x.com']);

    $repo = new UserRepository;
    $result = $repo->findByEmail('target@x.com');

    expect($result)->toBeInstanceOf(User::class)
        ->and($result->email)->toBe('target@x.com');
});

test('UserRepository::findByEmail devuelve null si el email no existe', function () {
    $repo = new UserRepository;
    expect($repo->findByEmail('noexiste@x.com'))->toBeNull();
});
