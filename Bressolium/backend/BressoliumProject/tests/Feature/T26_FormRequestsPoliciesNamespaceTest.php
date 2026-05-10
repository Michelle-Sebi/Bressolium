<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BoardController;
use App\Http\Controllers\Api\GameController;
use App\Http\Controllers\Api\TileController;
use App\Http\Requests\CreateGameRequest;
use App\Http\Requests\ExploreActionRequest;
use App\Http\Requests\JoinGameRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\UpgradeActionRequest;
use App\Models\Game;
use App\Models\User;
use App\Policies\GamePolicy;
use App\Policies\TilePolicy;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

// ==========================================
// TEST FOR: TASK 26
// Title: [Refactor] Form Requests, Policies y Namespace de Controladores API
// ==========================================

// ─── Form Request classes existen en App\Http\Requests ────────────────────────

test('RegisterRequest existe en App\Http\Requests', function () {
    expect(class_exists(RegisterRequest::class))->toBeTrue();
});

test('LoginRequest existe en App\Http\Requests', function () {
    expect(class_exists(LoginRequest::class))->toBeTrue();
});

test('CreateGameRequest existe en App\Http\Requests', function () {
    expect(class_exists(CreateGameRequest::class))->toBeTrue();
});

test('JoinGameRequest existe en App\Http\Requests', function () {
    expect(class_exists(JoinGameRequest::class))->toBeTrue();
});

test('ExploreActionRequest existe en App\Http\Requests', function () {
    expect(class_exists(ExploreActionRequest::class))->toBeTrue();
});

test('UpgradeActionRequest existe en App\Http\Requests', function () {
    expect(class_exists(UpgradeActionRequest::class))->toBeTrue();
});

// ─── Form Requests extienden Illuminate\Foundation\Http\FormRequest ───────────

test('RegisterRequest extiende FormRequest', function () {
    expect(is_a(RegisterRequest::class, FormRequest::class, true))->toBeTrue();
});

test('LoginRequest extiende FormRequest', function () {
    expect(is_a(LoginRequest::class, FormRequest::class, true))->toBeTrue();
});

test('CreateGameRequest extiende FormRequest', function () {
    expect(is_a(CreateGameRequest::class, FormRequest::class, true))->toBeTrue();
});

test('JoinGameRequest extiende FormRequest', function () {
    expect(is_a(JoinGameRequest::class, FormRequest::class, true))->toBeTrue();
});

test('ExploreActionRequest extiende FormRequest', function () {
    expect(is_a(ExploreActionRequest::class, FormRequest::class, true))->toBeTrue();
});

test('UpgradeActionRequest extiende FormRequest', function () {
    expect(is_a(UpgradeActionRequest::class, FormRequest::class, true))->toBeTrue();
});

// ─── Form Requests declaran reglas de validación correctas ────────────────────

test('RegisterRequest declara reglas para name, email y password', function () {
    $rules = (new RegisterRequest)->rules();

    expect($rules)
        ->toHaveKey('name')
        ->toHaveKey('email')
        ->toHaveKey('password');
});

test('LoginRequest declara reglas para email y password', function () {
    $rules = (new LoginRequest)->rules();

    expect($rules)
        ->toHaveKey('email')
        ->toHaveKey('password');
});

test('CreateGameRequest declara reglas para team_name', function () {
    $rules = (new CreateGameRequest)->rules();

    expect($rules)->toHaveKey('team_name');
});

test('JoinGameRequest declara reglas para team_name', function () {
    $rules = (new JoinGameRequest)->rules();

    expect($rules)->toHaveKey('team_name');
});

// ─── Policy classes existen en App\Policies ───────────────────────────────────

test('GamePolicy existe en App\Policies', function () {
    expect(class_exists(GamePolicy::class))->toBeTrue();
});

test('TilePolicy existe en App\Policies', function () {
    expect(class_exists(TilePolicy::class))->toBeTrue();
});

// ─── Policies declaran los métodos de autorización requeridos ─────────────────

test('GamePolicy declara método view para controlar acceso a partida', function () {
    $methods = collect((new ReflectionClass(GamePolicy::class))->getMethods(ReflectionMethod::IS_PUBLIC))
        ->map(fn ($m) => $m->getName())
        ->all();

    expect($methods)->toContain('view');
});

test('TilePolicy declara métodos explore y upgrade para autorización sobre casillas', function () {
    $methods = collect((new ReflectionClass(TilePolicy::class))->getMethods(ReflectionMethod::IS_PUBLIC))
        ->map(fn ($m) => $m->getName())
        ->all();

    expect($methods)
        ->toContain('explore')
        ->toContain('upgrade');
});

// ─── Controladores en namespace App\Http\Controllers\Api ─────────────────────

test('AuthController existe en App\Http\Controllers\Api', function () {
    expect(class_exists(AuthController::class))->toBeTrue();
});

test('GameController existe en App\Http\Controllers\Api', function () {
    expect(class_exists(GameController::class))->toBeTrue();
});

test('TileController existe en App\Http\Controllers\Api', function () {
    expect(class_exists(TileController::class))->toBeTrue();
});

test('BoardController existe en App\Http\Controllers\Api', function () {
    expect(class_exists(BoardController::class))->toBeTrue();
});

// ─── Controladores inyectan Form Requests en lugar de Illuminate\Http\Request ──

test('AuthController::register acepta RegisterRequest como primer parámetro', function () {
    $method = (new ReflectionClass(AuthController::class))->getMethod('register');
    $type = $method->getParameters()[0]->getType()->getName();

    expect($type)->toBe(RegisterRequest::class);
});

test('AuthController::login acepta LoginRequest como primer parámetro', function () {
    $method = (new ReflectionClass(AuthController::class))->getMethod('login');
    $type = $method->getParameters()[0]->getType()->getName();

    expect($type)->toBe(LoginRequest::class);
});

test('GameController::create acepta CreateGameRequest como primer parámetro', function () {
    $method = (new ReflectionClass(GameController::class))->getMethod('create');
    $type = $method->getParameters()[0]->getType()->getName();

    expect($type)->toBe(CreateGameRequest::class);
});

test('GameController::join acepta JoinGameRequest como primer parámetro', function () {
    $method = (new ReflectionClass(GameController::class))->getMethod('join');
    $type = $method->getParameters()[0]->getType()->getName();

    expect($type)->toBe(JoinGameRequest::class);
});

test('TileController::explore acepta ExploreActionRequest como primer parámetro', function () {
    $method = (new ReflectionClass(TileController::class))->getMethod('explore');
    $type = $method->getParameters()[0]->getType()->getName();

    expect($type)->toBe(ExploreActionRequest::class);
});

test('TileController::upgrade acepta UpgradeActionRequest como primer parámetro', function () {
    $method = (new ReflectionClass(TileController::class))->getMethod('upgrade');
    $type = $method->getParameters()[0]->getType()->getName();

    expect($type)->toBe(UpgradeActionRequest::class);
});

// ─── Comportamiento: Form Request rechaza datos inválidos con 422 ─────────────

describe('validaciones y autorización (requiere DB)', function () {
    uses(RefreshDatabase::class);
    test('POST /api/register sin name devuelve 422', function () {
        $this->postJson('/api/v1/register', [
            'email' => 'test@example.com',
            'password' => 'secret123',
        ])->assertStatus(422);
    });

    test('POST /api/register con email inválido devuelve 422', function () {
        $this->postJson('/api/v1/register', [
            'name' => 'Test User',
            'email' => 'not-an-email',
            'password' => 'secret123',
        ])->assertStatus(422);
    });

    test('POST /api/register con password menor de 8 caracteres devuelve 422', function () {
        $this->postJson('/api/v1/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'short',
        ])->assertStatus(422);
    });

    test('POST /api/login sin campos devuelve 422', function () {
        $this->postJson('/api/v1/login', [])->assertStatus(422);
    });

    test('POST /api/game/create sin team_name devuelve 422', function () {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/v1/game/create', [])
            ->assertStatus(422);
    });

    test('POST /api/game/join sin team_name devuelve 422', function () {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/v1/game/join', [])
            ->assertStatus(422);
    });

    // ─── Comportamiento: Policy deniega acceso a partida ajena ───────────────────

    test('GET /api/board/{gameId} de partida ajena devuelve 403', function () {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $token = $other->createToken('test')->plainTextToken;

        $game = Game::factory()->create();
        $game->users()->attach($owner->id);

        $this->withToken($token)
            ->getJson('/api/v1/board/'.$game->id)
            ->assertStatus(403);
    });
});
