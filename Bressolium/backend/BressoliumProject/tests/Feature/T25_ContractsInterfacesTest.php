<?php

use App\Repositories\Contracts\GameRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Contracts\RoundRepositoryInterface;
use App\Repositories\Contracts\BoardRepositoryInterface;
use App\Repositories\Contracts\TileRepositoryInterface;
use App\Repositories\Eloquent\GameRepository as EloquentGameRepository;
use App\Repositories\Eloquent\UserRepository as EloquentUserRepository;
use App\Repositories\Eloquent\RoundRepository as EloquentRoundRepository;
use App\Repositories\Eloquent\BoardRepository as EloquentBoardRepository;
use App\Repositories\Eloquent\TileRepository as EloquentTileRepository;
use App\Services\GameService;
use App\Services\AuthService;
use App\Services\BoardService;
use App\Services\ActionService;

// ==========================================
// TEST FOR: TASK 25
// Title: [Refactor] Contracts, Interfaces y Service Providers
// ==========================================

// ─── Interfaces existen en el namespace correcto ──────────────────────────────

test('GameRepositoryInterface existe en App\Repositories\Contracts', function () {
    expect(interface_exists(GameRepositoryInterface::class))->toBeTrue();
});

test('UserRepositoryInterface existe en App\Repositories\Contracts', function () {
    expect(interface_exists(UserRepositoryInterface::class))->toBeTrue();
});

test('RoundRepositoryInterface existe en App\Repositories\Contracts', function () {
    expect(interface_exists(RoundRepositoryInterface::class))->toBeTrue();
});

test('BoardRepositoryInterface existe en App\Repositories\Contracts', function () {
    expect(interface_exists(BoardRepositoryInterface::class))->toBeTrue();
});

test('TileRepositoryInterface existe en App\Repositories\Contracts', function () {
    expect(interface_exists(TileRepositoryInterface::class))->toBeTrue();
});

// ─── Implementaciones Eloquent existen en el namespace correcto ───────────────

test('Eloquent GameRepository existe en App\Repositories\Eloquent', function () {
    expect(class_exists(EloquentGameRepository::class))->toBeTrue();
});

test('Eloquent UserRepository existe en App\Repositories\Eloquent', function () {
    expect(class_exists(EloquentUserRepository::class))->toBeTrue();
});

test('Eloquent RoundRepository existe en App\Repositories\Eloquent', function () {
    expect(class_exists(EloquentRoundRepository::class))->toBeTrue();
});

test('Eloquent BoardRepository existe en App\Repositories\Eloquent', function () {
    expect(class_exists(EloquentBoardRepository::class))->toBeTrue();
});

test('Eloquent TileRepository existe en App\Repositories\Eloquent', function () {
    expect(class_exists(EloquentTileRepository::class))->toBeTrue();
});

// ─── Cada implementación declara que implementa su interfaz ───────────────────

test('EloquentGameRepository implementa GameRepositoryInterface', function () {
    expect(is_a(EloquentGameRepository::class, GameRepositoryInterface::class, true))->toBeTrue();
});

test('EloquentUserRepository implementa UserRepositoryInterface', function () {
    expect(is_a(EloquentUserRepository::class, UserRepositoryInterface::class, true))->toBeTrue();
});

test('EloquentRoundRepository implementa RoundRepositoryInterface', function () {
    expect(is_a(EloquentRoundRepository::class, RoundRepositoryInterface::class, true))->toBeTrue();
});

test('EloquentBoardRepository implementa BoardRepositoryInterface', function () {
    expect(is_a(EloquentBoardRepository::class, BoardRepositoryInterface::class, true))->toBeTrue();
});

test('EloquentTileRepository implementa TileRepositoryInterface', function () {
    expect(is_a(EloquentTileRepository::class, TileRepositoryInterface::class, true))->toBeTrue();
});

// ─── El IoC Container resuelve cada interfaz a su implementación Eloquent ─────

test('IoC container resuelve GameRepositoryInterface a EloquentGameRepository', function () {
    expect(app(GameRepositoryInterface::class))->toBeInstanceOf(EloquentGameRepository::class);
});

test('IoC container resuelve UserRepositoryInterface a EloquentUserRepository', function () {
    expect(app(UserRepositoryInterface::class))->toBeInstanceOf(EloquentUserRepository::class);
});

test('IoC container resuelve RoundRepositoryInterface a EloquentRoundRepository', function () {
    expect(app(RoundRepositoryInterface::class))->toBeInstanceOf(EloquentRoundRepository::class);
});

test('IoC container resuelve BoardRepositoryInterface a EloquentBoardRepository', function () {
    expect(app(BoardRepositoryInterface::class))->toBeInstanceOf(EloquentBoardRepository::class);
});

test('IoC container resuelve TileRepositoryInterface a EloquentTileRepository', function () {
    expect(app(TileRepositoryInterface::class))->toBeInstanceOf(EloquentTileRepository::class);
});

// ─── Cada interfaz declara todos los métodos del contrato ─────────────────────

test('GameRepositoryInterface declara todos los métodos del contrato', function () {
    $methods = collect((new ReflectionClass(GameRepositoryInterface::class))->getMethods())
        ->map(fn ($m) => $m->getName())
        ->all();

    expect($methods)
        ->toContain('create')
        ->toContain('findByName')
        ->toContain('findAvailableRandom')
        ->toContain('getAllAvailableGames')
        ->toContain('getGamesByUserId');
});

test('UserRepositoryInterface declara todos los métodos del contrato', function () {
    $methods = collect((new ReflectionClass(UserRepositoryInterface::class))->getMethods())
        ->map(fn ($m) => $m->getName())
        ->all();

    expect($methods)
        ->toContain('create')
        ->toContain('findByEmail');
});

test('RoundRepositoryInterface declara todos los métodos del contrato', function () {
    $methods = collect((new ReflectionClass(RoundRepositoryInterface::class))->getMethods())
        ->map(fn ($m) => $m->getName())
        ->all();

    expect($methods)
        ->toContain('create')
        ->toContain('getLatestRoundForGame');
});

test('BoardRepositoryInterface declara todos los métodos del contrato', function () {
    $methods = collect((new ReflectionClass(BoardRepositoryInterface::class))->getMethods())
        ->map(fn ($m) => $m->getName())
        ->all();

    expect($methods)
        ->toContain('getTilesByGameId')
        ->toContain('isUserInGame')
        ->toContain('createMany');
});

test('TileRepositoryInterface declara todos los métodos del contrato', function () {
    $methods = collect((new ReflectionClass(TileRepositoryInterface::class))->getMethods())
        ->map(fn ($m) => $m->getName())
        ->all();

    expect($methods)
        ->toContain('find')
        ->toContain('isUserInGame')
        ->toContain('getCurrentRound')
        ->toContain('getActionsSpent')
        ->toContain('incrementActionsSpent')
        ->toContain('markExplored')
        ->toContain('findNextTileType')
        ->toContain('getUpgradeCosts')
        ->toContain('hasSufficientMaterials')
        ->toContain('deductMaterials')
        ->toContain('upgradeTile');
});

// ─── Los servicios inyectan interfaces, no clases concretas ───────────────────

test('GameService inyecta GameRepositoryInterface y RoundRepositoryInterface', function () {
    $types = collect((new ReflectionClass(GameService::class))->getConstructor()->getParameters())
        ->map(fn ($p) => $p->getType()?->getName())
        ->all();

    expect($types)
        ->toContain(GameRepositoryInterface::class)
        ->toContain(RoundRepositoryInterface::class);
});

test('AuthService inyecta UserRepositoryInterface', function () {
    $types = collect((new ReflectionClass(AuthService::class))->getConstructor()->getParameters())
        ->map(fn ($p) => $p->getType()?->getName())
        ->all();

    expect($types)->toContain(UserRepositoryInterface::class);
});

test('BoardService inyecta BoardRepositoryInterface', function () {
    $types = collect((new ReflectionClass(BoardService::class))->getConstructor()->getParameters())
        ->map(fn ($p) => $p->getType()?->getName())
        ->all();

    expect($types)->toContain(BoardRepositoryInterface::class);
});

test('ActionService inyecta TileRepositoryInterface', function () {
    $types = collect((new ReflectionClass(ActionService::class))->getConstructor()->getParameters())
        ->map(fn ($p) => $p->getType()?->getName())
        ->all();

    expect($types)->toContain(TileRepositoryInterface::class);
});

// ─── RepositoryServiceProvider registrado y activo ────────────────────────────

test('RepositoryServiceProvider existe como clase', function () {
    expect(class_exists(\App\Providers\RepositoryServiceProvider::class))->toBeTrue();
});

test('RepositoryServiceProvider está cargado en el contenedor de Laravel', function () {
    $loaded = array_keys(app()->getLoadedProviders());
    expect($loaded)->toContain(\App\Providers\RepositoryServiceProvider::class);
});
