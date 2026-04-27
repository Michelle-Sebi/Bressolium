<?php

use App\DTOs\CreateGameDTO;
use App\DTOs\JoinGameDTO;
use App\DTOs\ExploreActionDTO;
use App\DTOs\UpgradeActionDTO;
use App\Http\Resources\GameResource;
use App\Http\Resources\TileResource;
use App\Http\Resources\MaterialResource;
use App\Http\Resources\RoundResource;
use App\Http\Resources\UserResource;
use App\Models\Game;
use App\Models\Material;
use App\Models\Tile;
use App\Models\TileType;
use App\Models\User;
use App\Services\ActionService;
use App\Services\GameService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Resources\Json\JsonResource;

uses(RefreshDatabase::class);

// ==========================================
// TEST FOR: TASK 27
// Title: [Refactor] DTOs y API Resources
// Note: SyncResponseDTO and VoteDTO are deferred to T10 and T11
//       (their endpoints don't exist yet — see project memory).
// ==========================================

// ─── DTOs existen en App\DTOs ─────────────────────────────────────────────────

test('CreateGameDTO existe en App\DTOs', function () {
    expect(class_exists(CreateGameDTO::class))->toBeTrue();
});

test('JoinGameDTO existe en App\DTOs', function () {
    expect(class_exists(JoinGameDTO::class))->toBeTrue();
});

test('ExploreActionDTO existe en App\DTOs', function () {
    expect(class_exists(ExploreActionDTO::class))->toBeTrue();
});

test('UpgradeActionDTO existe en App\DTOs', function () {
    expect(class_exists(UpgradeActionDTO::class))->toBeTrue();
});

// ─── DTOs transportan los datos del flujo ─────────────────────────────────────

test('CreateGameDTO transporta teamName y userId', function () {
    $dto = new CreateGameDTO(teamName: 'Los Vikingos', userId: 'user-1');
    expect($dto->teamName)->toBe('Los Vikingos')
        ->and($dto->userId)->toBe('user-1');
});

test('JoinGameDTO transporta teamName y userId', function () {
    $dto = new JoinGameDTO(teamName: 'Los Vikingos', userId: 'user-1');
    expect($dto->teamName)->toBe('Los Vikingos')
        ->and($dto->userId)->toBe('user-1');
});

test('ExploreActionDTO transporta tileId y userId', function () {
    $dto = new ExploreActionDTO(tileId: 'tile-1', userId: 'user-1');
    expect($dto->tileId)->toBe('tile-1')
        ->and($dto->userId)->toBe('user-1');
});

test('UpgradeActionDTO transporta tileId y userId', function () {
    $dto = new UpgradeActionDTO(tileId: 'tile-1', userId: 'user-1');
    expect($dto->tileId)->toBe('tile-1')
        ->and($dto->userId)->toBe('user-1');
});

// ─── DTOs son inmutables (readonly) ───────────────────────────────────────────

test('CreateGameDTO es inmutable: no se pueden modificar sus campos', function () {
    $dto = new CreateGameDTO(teamName: 'Test', userId: 'u-1');
    expect(fn () => $dto->teamName = 'Otro')->toThrow(\Error::class);
});

test('JoinGameDTO es inmutable: no se pueden modificar sus campos', function () {
    $dto = new JoinGameDTO(teamName: 'Test', userId: 'u-1');
    expect(fn () => $dto->userId = 'otro')->toThrow(\Error::class);
});

test('ExploreActionDTO es inmutable: no se pueden modificar sus campos', function () {
    $dto = new ExploreActionDTO(tileId: 't-1', userId: 'u-1');
    expect(fn () => $dto->tileId = 'otro')->toThrow(\Error::class);
});

test('UpgradeActionDTO es inmutable: no se pueden modificar sus campos', function () {
    $dto = new UpgradeActionDTO(tileId: 't-1', userId: 'u-1');
    expect(fn () => $dto->tileId = 'otro')->toThrow(\Error::class);
});

// ─── API Resources existen en App\Http\Resources ──────────────────────────────

test('GameResource existe en App\Http\Resources', function () {
    expect(class_exists(GameResource::class))->toBeTrue();
});

test('TileResource existe en App\Http\Resources', function () {
    expect(class_exists(TileResource::class))->toBeTrue();
});

test('MaterialResource existe en App\Http\Resources', function () {
    expect(class_exists(MaterialResource::class))->toBeTrue();
});

test('RoundResource existe en App\Http\Resources', function () {
    expect(class_exists(RoundResource::class))->toBeTrue();
});

test('UserResource existe en App\Http\Resources', function () {
    expect(class_exists(UserResource::class))->toBeTrue();
});

// ─── API Resources extienden JsonResource ─────────────────────────────────────

test('GameResource extiende JsonResource', function () {
    expect(is_subclass_of(GameResource::class, JsonResource::class))->toBeTrue();
});

test('TileResource extiende JsonResource', function () {
    expect(is_subclass_of(TileResource::class, JsonResource::class))->toBeTrue();
});

test('MaterialResource extiende JsonResource', function () {
    expect(is_subclass_of(MaterialResource::class, JsonResource::class))->toBeTrue();
});

test('RoundResource extiende JsonResource', function () {
    expect(is_subclass_of(RoundResource::class, JsonResource::class))->toBeTrue();
});

test('UserResource extiende JsonResource', function () {
    expect(is_subclass_of(UserResource::class, JsonResource::class))->toBeTrue();
});

// ─── API Resources transforman correctamente los modelos ─────────────────────

test('GameResource expone id, name y status del modelo Game', function () {
    $game = Game::factory()->create(['name' => 'Los Vikingos', 'status' => 'WAITING']);

    $array = (new GameResource($game))->toArray(request());

    expect($array)->toHaveKeys(['id', 'name', 'status'])
        ->and($array['id'])->toBe($game->id)
        ->and($array['name'])->toBe('Los Vikingos')
        ->and($array['status'])->toBe('WAITING');
});

test('TileResource expone id, coord_x, coord_y, tile_type_id y explored', function () {
    $tileType = TileType::factory()->create(['base_type' => 'bosque', 'level' => 1]);
    $game     = Game::factory()->create();
    $tile     = Tile::factory()->create([
        'game_id'      => $game->id,
        'tile_type_id' => $tileType->id,
        'coord_x'      => 3,
        'coord_y'      => 5,
        'explored'     => true,
    ]);

    $array = (new TileResource($tile))->toArray(request());

    expect($array)->toHaveKeys(['id', 'coord_x', 'coord_y', 'tile_type_id', 'explored'])
        ->and($array['coord_x'])->toBe(3)
        ->and($array['coord_y'])->toBe(5)
        ->and($array['tile_type_id'])->toBe($tileType->id);
});

test('MaterialResource expone id, name, tier y group', function () {
    $material = Material::create(['name' => 'Roble', 'tier' => 0, 'group' => 'Bosque']);

    $array = (new MaterialResource($material))->toArray(request());

    expect($array)->toHaveKeys(['id', 'name', 'tier', 'group'])
        ->and($array['name'])->toBe('Roble')
        ->and($array['tier'])->toBe(0)
        ->and($array['group'])->toBe('Bosque');
});

test('RoundResource expone id, number, start_date y ended_at', function () {
    $game  = Game::factory()->create();
    $round = $game->rounds()->create(['number' => 1, 'start_date' => now()]);

    $array = (new RoundResource($round))->toArray(request());

    expect($array)->toHaveKeys(['id', 'number', 'start_date', 'ended_at'])
        ->and($array['number'])->toBe(1)
        ->and($array['ended_at'])->toBeNull();
});

test('UserResource expone id, name y email pero NO password', function () {
    $user = User::factory()->create(['name' => 'Bárbara', 'email' => 'b@x.com']);

    $array = (new UserResource($user))->toArray(request());

    expect($array)->toHaveKeys(['id', 'name', 'email'])
        ->and($array['name'])->toBe('Bárbara')
        ->and($array['email'])->toBe('b@x.com')
        ->and($array)->not->toHaveKey('password');
});

// ─── GameResource: anidación condicional (whenLoaded) ─────────────────────────

test('GameResource NO incluye rounds cuando la relación no está cargada', function () {
    $game = Game::factory()->create();

    $array = (new GameResource($game))->toArray(request());

    expect($array)->not->toHaveKey('rounds');
});

test('GameResource incluye rounds (RoundResource) cuando la relación está cargada', function () {
    $game = Game::factory()->create();
    $game->rounds()->create(['number' => 1, 'start_date' => now()]);
    $game->load('rounds');

    $array = (new GameResource($game))->toArray(request());

    expect($array)->toHaveKey('rounds');
});

// ─── Servicios aceptan DTOs en lugar de parámetros sueltos ────────────────────

test('GameService::createGame acepta CreateGameDTO como único parámetro', function () {
    $params = (new ReflectionClass(GameService::class))->getMethod('createGame')->getParameters();
    expect($params)->toHaveCount(1)
        ->and($params[0]->getType()->getName())->toBe(CreateGameDTO::class);
});

test('GameService::joinGame acepta JoinGameDTO como único parámetro', function () {
    $params = (new ReflectionClass(GameService::class))->getMethod('joinGame')->getParameters();
    expect($params)->toHaveCount(1)
        ->and($params[0]->getType()->getName())->toBe(JoinGameDTO::class);
});

test('ActionService::explore acepta ExploreActionDTO como único parámetro', function () {
    $params = (new ReflectionClass(ActionService::class))->getMethod('explore')->getParameters();
    expect($params)->toHaveCount(1)
        ->and($params[0]->getType()->getName())->toBe(ExploreActionDTO::class);
});

test('ActionService::upgrade acepta UpgradeActionDTO como único parámetro', function () {
    $params = (new ReflectionClass(ActionService::class))->getMethod('upgrade')->getParameters();
    expect($params)->toHaveCount(1)
        ->and($params[0]->getType()->getName())->toBe(UpgradeActionDTO::class);
});

// ─── Controladores devuelven Resources (no modelos crudos) ────────────────────

test('AuthController::register envuelve el user en UserResource (sin password ni timestamps)', function () {
    $response = $this->postJson('/api/register', [
        'name'     => 'Bárbara',
        'email'    => 'b@x.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(200);
    $userData = $response->json('data.user');

    expect(array_keys($userData))->toEqualCanonicalizing(['id', 'name', 'email']);
});

test('AuthController::login envuelve el user en UserResource (sin password ni timestamps)', function () {
    User::factory()->create(['email' => 'a@x.com', 'password' => bcrypt('password123')]);

    $response = $this->postJson('/api/login', [
        'email'    => 'a@x.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(200);
    $userData = $response->json('data.user');

    expect(array_keys($userData))->toEqualCanonicalizing(['id', 'name', 'email']);
});

test('GameController::create devuelve GameResource (sin timestamps)', function () {
    foreach (['bosque', 'cantera', 'rio', 'prado', 'mina', 'pueblo'] as $base) {
        TileType::create(['name' => ucfirst($base), 'level' => 1, 'base_type' => $base]);
    }

    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/api/game/create', [
        'team_name' => 'Los Vikingos',
    ]);

    $response->assertStatus(200);
    $gameData = $response->json('data');

    expect($gameData)->toHaveKeys(['id', 'name', 'status'])
        ->and($gameData)->not->toHaveKey('created_at')
        ->and($gameData)->not->toHaveKey('updated_at');
});

test('GameController::join devuelve GameResource', function () {
    $user = User::factory()->create();
    Game::factory()->create(['name' => 'Equipo Existente']);

    $response = $this->actingAs($user)->postJson('/api/game/join', [
        'team_name' => 'Equipo Existente',
    ]);

    $response->assertStatus(200);
    $gameData = $response->json('data');

    expect($gameData)->toHaveKeys(['id', 'name', 'status'])
        ->and($gameData)->not->toHaveKey('created_at');
});

test('GameController::myGames devuelve colección de GameResource', function () {
    $user  = User::factory()->create();
    $game1 = Game::factory()->create();
    $game2 = Game::factory()->create();
    $user->games()->attach($game1->id);
    $user->games()->attach($game2->id);

    $response = $this->actingAs($user)->getJson('/api/game/my');

    $response->assertStatus(200);
    $games = $response->json('data');

    expect($games)->toHaveCount(2);
    foreach ($games as $g) {
        expect($g)->toHaveKeys(['id', 'name', 'status'])
            ->and($g)->not->toHaveKey('updated_at');
    }
});

test('GameController::allGames devuelve colección de GameResource', function () {
    $user = User::factory()->create();
    Game::factory()->count(3)->create();

    $response = $this->actingAs($user)->getJson('/api/game/all');

    $response->assertStatus(200);
    $games = $response->json('data');

    foreach ($games as $g) {
        expect($g)->toHaveKeys(['id', 'name', 'status'])
            ->and($g)->not->toHaveKey('updated_at');
    }
});

test('BoardController::show devuelve colección de TileResource (sin timestamps)', function () {
    $user = User::factory()->create();
    $game = Game::factory()->create();
    $user->games()->attach($game->id);
    Tile::factory()->count(3)->create(['game_id' => $game->id]);

    $response = $this->actingAs($user)->getJson("/api/v1/board/{$game->id}");

    $response->assertStatus(200);
    $tiles = $response->json('data');

    foreach ($tiles as $t) {
        expect($t)->toHaveKeys(['id', 'coord_x', 'coord_y', 'tile_type_id', 'explored'])
            ->and($t)->not->toHaveKey('created_at')
            ->and($t)->not->toHaveKey('updated_at');
    }
});

test('TileController::explore devuelve TileResource (no modelo crudo)', function () {
    $user = User::factory()->create();
    $game = Game::factory()->create();
    $user->games()->attach($game->id);

    $round = $game->rounds()->create(['number' => 1, 'start_date' => now()]);
    $round->users()->attach($user->id, ['actions_spent' => 0]);

    $tileType = TileType::factory()->create(['level' => 1, 'base_type' => 'bosque']);
    $tile     = Tile::factory()->create([
        'game_id'      => $game->id,
        'tile_type_id' => $tileType->id,
        'explored'     => false,
    ]);

    $response = $this->actingAs($user)->postJson("/api/v1/tiles/{$tile->id}/explore");

    $response->assertStatus(200);
    $tileData = $response->json('data');

    expect($tileData)->toHaveKeys(['id', 'coord_x', 'coord_y', 'tile_type_id', 'explored'])
        ->and($tileData)->not->toHaveKey('updated_at');
});

test('TileController::upgrade devuelve TileResource (no modelo crudo)', function () {
    $user = User::factory()->create();
    $game = Game::factory()->create();
    $user->games()->attach($game->id);

    $round = $game->rounds()->create(['number' => 1, 'start_date' => now()]);
    $round->users()->attach($user->id, ['actions_spent' => 0]);

    $currentType = TileType::create(['name' => 'Bosque Nv1', 'level' => 1, 'base_type' => 'bosque']);
    $nextType    = TileType::create(['name' => 'Bosque Nv2', 'level' => 2, 'base_type' => 'bosque']);
    $material    = Material::create(['name' => 'Roble', 'tier' => 0, 'group' => 'Bosque']);
    $nextType->materials()->attach($material->id, ['quantity' => 5]);
    $game->materials()->attach($material->id, ['quantity' => 10]);

    $tile = Tile::factory()->create([
        'game_id'      => $game->id,
        'tile_type_id' => $currentType->id,
        'explored'     => true,
    ]);

    $response = $this->actingAs($user)->postJson("/api/v1/tiles/{$tile->id}/upgrade");

    $response->assertStatus(200);
    $tileData = $response->json('data');

    expect($tileData)->toHaveKeys(['id', 'coord_x', 'coord_y', 'tile_type_id', 'explored'])
        ->and($tileData)->not->toHaveKey('updated_at');
});
