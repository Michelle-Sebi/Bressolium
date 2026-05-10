<?php

use App\DTOs\ExploreActionDTO;
use App\DTOs\UpgradeActionDTO;
use App\DTOs\VoteDTO;
use App\Events\GameFinished;
use App\Events\InventionBuilt;
use App\Events\MaterialsProduced;
use App\Events\RoundClosed;
use App\Events\TileExplored;
use App\Events\TileUpgraded;
use App\Events\VoteCast;
use App\Exceptions\VoteValidationException;
use App\Jobs\CloseRoundJob;
use App\Listeners\AuditEventListener;
use App\Listeners\LogDomainEventListener;
use App\Listeners\NotifyPlayersListener;
use App\Models\Game;
use App\Models\Invention;
use App\Models\Technology;
use App\Models\Tile;
use App\Models\TileType;
use App\Models\User;
use App\Services\ActionService;
use App\Services\VoteService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

// ============================================================
// T39 — Eventos y Listeners de Dominio
// ============================================================

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->game = Game::factory()->create();
    $this->round = $this->game->rounds()->create(['number' => 1]);
    $this->game->users()->attach($this->user->id, ['is_afk' => false]);
    $this->round->users()->attach($this->user->id, ['actions_spent' => 0]);
});

// ---
// DoD: Los 7 eventos de dominio existen como clases
// ---

test('los 7 eventos de dominio existen como clases en App\\Events', function () {
    expect(class_exists(TileExplored::class))->toBeTrue();
    expect(class_exists(TileUpgraded::class))->toBeTrue();
    expect(class_exists(RoundClosed::class))->toBeTrue();
    expect(class_exists(MaterialsProduced::class))->toBeTrue();
    expect(class_exists(GameFinished::class))->toBeTrue();
    expect(class_exists(VoteCast::class))->toBeTrue();
    expect(class_exists(InventionBuilt::class))->toBeTrue();
});

// ---
// DoD: Listeners desacoplados existen (notificación, auditoría, log estructurado)
// ---

test('existen listeners de notificación, auditoría y log estructurado en App\\Listeners', function () {
    expect(class_exists(NotifyPlayersListener::class))->toBeTrue();
    expect(class_exists(AuditEventListener::class))->toBeTrue();
    expect(class_exists(LogDomainEventListener::class))->toBeTrue();
});

// ---
// DoD: Los listeners costosos implementan ShouldQueue
// ---

test('el listener de notificación a jugadores implementa ShouldQueue', function () {
    expect(NotifyPlayersListener::class)
        ->toImplement(ShouldQueue::class);
});

// ---
// DoD: ActionService.explore emite TileExplored
// ---

test('explorar una casilla emite el evento TileExplored con la casilla correcta', function () {
    Event::fake();

    $tileType = TileType::factory()->create(['base_type' => 'bosque', 'level' => 1]);
    $tile = Tile::factory()->create([
        'game_id' => $this->game->id,
        'tile_type_id' => $tileType->id,
        'explored' => false,
        'coord_x' => 5,
        'coord_y' => 5,
    ]);

    app(ActionService::class)->explore(
        new ExploreActionDTO(tileId: $tile->id, userId: $this->user->id)
    );

    Event::assertDispatched(TileExplored::class, fn ($e) => $e->tile->id === $tile->id);
});

test('explorar una casilla no emite TileUpgraded', function () {
    Event::fake();

    $tileType = TileType::factory()->create(['base_type' => 'bosque', 'level' => 1]);
    $tile = Tile::factory()->create([
        'game_id' => $this->game->id,
        'tile_type_id' => $tileType->id,
        'explored' => false,
    ]);

    app(ActionService::class)->explore(
        new ExploreActionDTO(tileId: $tile->id, userId: $this->user->id)
    );

    Event::assertNotDispatched(TileUpgraded::class);
});

// ---
// DoD: ActionService.upgrade emite TileUpgraded
// ---

test('evolucionar una casilla emite el evento TileUpgraded con la casilla correcta', function () {
    Event::fake();

    $typeNv1 = TileType::factory()->create(['base_type' => 'bosque', 'level' => 1]);
    $typeNv2 = TileType::factory()->create(['base_type' => 'bosque', 'level' => 2]);
    $tile = Tile::factory()->create([
        'game_id' => $this->game->id,
        'tile_type_id' => $typeNv1->id,
        'explored' => true,
        'coord_x' => 3,
        'coord_y' => 3,
    ]);

    app(ActionService::class)->upgrade(
        new UpgradeActionDTO(tileId: $tile->id, userId: $this->user->id)
    );

    Event::assertDispatched(TileUpgraded::class, fn ($e) => $e->tile->id === $tile->id);
});

test('evolucionar una casilla no emite TileExplored', function () {
    Event::fake();

    $typeNv1 = TileType::factory()->create(['base_type' => 'bosque', 'level' => 1]);
    $typeNv2 = TileType::factory()->create(['base_type' => 'bosque', 'level' => 2]);
    $tile = Tile::factory()->create([
        'game_id' => $this->game->id,
        'tile_type_id' => $typeNv1->id,
        'explored' => true,
    ]);

    app(ActionService::class)->upgrade(
        new UpgradeActionDTO(tileId: $tile->id, userId: $this->user->id)
    );

    Event::assertNotDispatched(TileExplored::class);
});

// ---
// DoD: CloseRoundJob emite RoundClosed al cerrar la jornada
// ---

test('cerrar una jornada emite RoundClosed con la partida correcta', function () {
    Event::fake();

    CloseRoundJob::dispatchSync($this->game->id);

    Event::assertDispatched(RoundClosed::class, fn ($e) => $e->game->id === $this->game->id);
});

// ---
// DoD: CloseRoundJob emite MaterialsProduced tras producción de casillas exploradas
// ---

test('cerrar una jornada emite MaterialsProduced', function () {
    Event::fake();

    $tileType = TileType::factory()->create(['base_type' => 'bosque', 'level' => 1]);
    Tile::factory()->create([
        'game_id' => $this->game->id,
        'tile_type_id' => $tileType->id,
        'explored' => true,
        'coord_x' => 1,
        'coord_y' => 1,
    ]);

    CloseRoundJob::dispatchSync($this->game->id);

    Event::assertDispatched(MaterialsProduced::class, fn ($e) => $e->game->id === $this->game->id);
});

// ---
// DoD: CloseRoundJob emite InventionBuilt cuando un invento gana la votación
// ---

test('cerrar una jornada emite InventionBuilt cuando un invento gana con los materiales suficientes', function () {
    Event::fake();

    $invention = Invention::factory()->create();
    $this->game->inventions()->attach($invention->id, ['quantity' => 0]);
    $this->round->votes()->create([
        'user_id' => $this->user->id,
        'invention_id' => $invention->id,
    ]);

    CloseRoundJob::dispatchSync($this->game->id);

    Event::assertDispatched(
        InventionBuilt::class,
        fn ($e) => $e->invention->id === $invention->id
    );
});

test('cerrar una jornada no emite InventionBuilt si no hay votos de inventos', function () {
    Event::fake();

    CloseRoundJob::dispatchSync($this->game->id);

    Event::assertNotDispatched(InventionBuilt::class);
});

// ---
// DoD: VoteService.vote emite VoteCast al registrar un voto
// ---

test('registrar un voto de tecnología emite el evento VoteCast', function () {
    Event::fake();

    $tech = Technology::factory()->create();

    app(VoteService::class)->vote(new VoteDTO(
        gameId: $this->game->id,
        userId: $this->user->id,
        technologyId: $tech->id,
        inventionId: null,
    ));

    Event::assertDispatched(VoteCast::class, fn ($e) => $e->userId === $this->user->id);
});

test('un voto rechazado por duplicado no emite VoteCast', function () {
    Event::fake();

    $tech = Technology::factory()->create();

    // Primer voto OK
    $this->round->votes()->create([
        'user_id' => $this->user->id,
        'technology_id' => $tech->id,
    ]);

    // Segundo voto debe lanzar excepción y no emitir
    expect(fn () => app(VoteService::class)->vote(new VoteDTO(
        gameId: $this->game->id,
        userId: $this->user->id,
        technologyId: $tech->id,
        inventionId: null,
    )))->toThrow(VoteValidationException::class);

    Event::assertNotDispatched(VoteCast::class);
});

// ---
// DoD: GameFinished existe (T15 emitirá el evento cuando se implemente)
// ---

test('la clase App\\Events\\GameFinished existe para cuando T15 la emita', function () {
    expect(class_exists(GameFinished::class))->toBeTrue();
});
