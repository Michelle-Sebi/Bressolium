<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BoardController;
use App\Http\Controllers\Api\GameController;
use App\Http\Controllers\Api\TileController;
use App\Models\User;
use App\Support\ResponseBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\JsonResponse;

uses(RefreshDatabase::class);

// ==========================================
// TEST FOR: TASK 40
// Title: [Refactor] Response Builder Centralizado
// ==========================================

// ─── 1. ResponseBuilder existe en App\Support y es instanciable ───────────────

test('la clase App\\Support\\ResponseBuilder existe', function () {
    expect(class_exists(ResponseBuilder::class))->toBeTrue();
});

test('ResponseBuilder es instanciable (no estático)', function () {
    $rb = new ResponseBuilder;
    expect($rb)->toBeInstanceOf(ResponseBuilder::class);
});

test('ResponseBuilder se resuelve por el contenedor de Laravel', function () {
    $rb = app(ResponseBuilder::class);
    expect($rb)->toBeInstanceOf(ResponseBuilder::class);
});

test('los métodos success, error y paginated NO son estáticos', function () {
    $reflection = new ReflectionClass(ResponseBuilder::class);

    expect($reflection->getMethod('success')->isStatic())->toBeFalse()
        ->and($reflection->getMethod('error')->isStatic())->toBeFalse()
        ->and($reflection->getMethod('paginated')->isStatic())->toBeFalse();
});

// ─── 2. success(data, code) ───────────────────────────────────────────────────

test('success() devuelve JsonResponse con estructura {success:true, data, error:null} y código 200 por defecto', function () {
    $rb = new ResponseBuilder;
    $response = $rb->success(['foo' => 'bar']);

    expect($response)->toBeInstanceOf(JsonResponse::class)
        ->and($response->getStatusCode())->toBe(200);

    $payload = $response->getData(true);
    expect($payload)->toMatchArray([
        'success' => true,
        'data' => ['foo' => 'bar'],
        'error' => null,
    ]);
});

test('success() respeta un código HTTP custom', function () {
    $rb = new ResponseBuilder;
    $response = $rb->success(['id' => 1], 201);

    expect($response->getStatusCode())->toBe(201);
    expect($response->getData(true)['success'])->toBeTrue();
});

test('success() acepta data null y devuelve data:null en el payload', function () {
    $rb = new ResponseBuilder;
    $response = $rb->success(null);

    $payload = $response->getData(true);
    expect($payload)->toMatchArray([
        'success' => true,
        'data' => null,
        'error' => null,
    ]);
});

// ─── 3. error(message, code) ──────────────────────────────────────────────────

test('error() devuelve JsonResponse con estructura {success:false, data:null, error:msg} y código 500 por defecto', function () {
    $rb = new ResponseBuilder;
    $response = $rb->error('Algo falló');

    expect($response)->toBeInstanceOf(JsonResponse::class)
        ->and($response->getStatusCode())->toBe(500);

    expect($response->getData(true))->toMatchArray([
        'success' => false,
        'data' => null,
        'error' => 'Algo falló',
    ]);
});

test('error() respeta un código HTTP custom (ej. 404)', function () {
    $rb = new ResponseBuilder;
    $response = $rb->error('Not found', 404);

    expect($response->getStatusCode())->toBe(404);
    expect($response->getData(true)['error'])->toBe('Not found');
});

test('error() respeta otros códigos custom (ej. 401, 422)', function () {
    $rb = new ResponseBuilder;

    expect($rb->error('Unauthorized', 401)->getStatusCode())->toBe(401)
        ->and($rb->error('Unprocessable', 422)->getStatusCode())->toBe(422);
});

// ─── 4. paginated(query) ──────────────────────────────────────────────────────

test('paginated() acepta un Eloquent Builder y devuelve estructura {success:true, data, error:null}', function () {
    User::factory()->count(20)->create();

    $rb = new ResponseBuilder;
    $response = $rb->paginated(User::query());

    expect($response)->toBeInstanceOf(JsonResponse::class)
        ->and($response->getStatusCode())->toBe(200);

    $payload = $response->getData(true);
    expect($payload)->toHaveKeys(['success', 'data', 'error'])
        ->and($payload['success'])->toBeTrue()
        ->and($payload['error'])->toBeNull();
});

test('paginated() expone metadatos de paginación dentro de data', function () {
    User::factory()->count(20)->create();

    $rb = new ResponseBuilder;
    $response = $rb->paginated(User::query());
    $data = $response->getData(true)['data'];

    expect($data)->toHaveKeys(['items', 'current_page', 'last_page', 'per_page', 'total'])
        ->and($data['total'])->toBe(20)
        ->and($data['current_page'])->toBe(1)
        ->and($data['items'])->toBeArray();
});

test('paginated() devuelve solo las claves estándar {success, data, error} en el nivel raíz', function () {
    User::factory()->count(5)->create();

    $rb = new ResponseBuilder;
    $response = $rb->paginated(User::query());

    expect(array_keys($response->getData(true)))->toEqualCanonicalizing(['success', 'data', 'error']);
});

// ─── 5. Estandarización: respuestas siempre con las 3 claves {success, data, error} ─

test('endpoint /api/register devuelve las 3 claves {success, data, error} (success caso)', function () {
    $response = $this->postJson('/api/v1/register', [
        'name' => 'Tester40',
        'email' => 't40@bressolium.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(200);
    expect(array_keys($response->json()))->toEqualCanonicalizing(['success', 'data', 'error']);
    expect($response->json('error'))->toBeNull();
});

test('endpoint /api/login con credenciales inválidas devuelve las 3 claves (error caso)', function () {
    User::factory()->create([
        'email' => 'badlogin@t40.com',
        'password' => bcrypt('correcto'),
    ]);

    $response = $this->postJson('/api/v1/login', [
        'email' => 'badlogin@t40.com',
        'password' => 'incorrecto',
    ]);

    $response->assertStatus(401);
    expect(array_keys($response->json()))->toEqualCanonicalizing(['success', 'data', 'error']);
    expect($response->json('success'))->toBeFalse()
        ->and($response->json('data'))->toBeNull()
        ->and($response->json('error'))->not->toBeNull();
});

test('endpoint /api/games (create) devuelve las 3 claves en éxito', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/api/v1/game/create', [
        'team_name' => 'TeamT40',
    ]);

    $response->assertStatus(200);
    expect(array_keys($response->json()))->toEqualCanonicalizing(['success', 'data', 'error']);
    expect($response->json('error'))->toBeNull();
});

test('endpoint /api/games/join con nombre inexistente devuelve las 3 claves en error', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/api/v1/game/join', [
        'team_name' => 'NoExiste_'.uniqid(),
    ]);

    expect($response->status())->toBe(404);
    expect(array_keys($response->json()))->toEqualCanonicalizing(['success', 'data', 'error']);
    expect($response->json('success'))->toBeFalse()
        ->and($response->json('data'))->toBeNull();
});

// ─── 6. Refactor: los Controllers inyectan ResponseBuilder por DI (Opción A) ─

test('AuthController declara ResponseBuilder como dependencia en su constructor', function () {
    $reflection = new ReflectionClass(AuthController::class);
    $params = $reflection->getConstructor()->getParameters();
    $types = array_map(fn ($p) => $p->getType()?->getName(), $params);

    expect($types)->toContain(ResponseBuilder::class);
});

test('GameController declara ResponseBuilder como dependencia en su constructor', function () {
    $reflection = new ReflectionClass(GameController::class);
    $params = $reflection->getConstructor()->getParameters();
    $types = array_map(fn ($p) => $p->getType()?->getName(), $params);

    expect($types)->toContain(ResponseBuilder::class);
});

test('BoardController declara ResponseBuilder como dependencia en su constructor', function () {
    $reflection = new ReflectionClass(BoardController::class);
    $params = $reflection->getConstructor()->getParameters();
    $types = array_map(fn ($p) => $p->getType()?->getName(), $params);

    expect($types)->toContain(ResponseBuilder::class);
});

test('TileController declara ResponseBuilder como dependencia en su constructor', function () {
    $reflection = new ReflectionClass(TileController::class);
    $params = $reflection->getConstructor()->getParameters();
    $types = array_map(fn ($p) => $p->getType()?->getName(), $params);

    expect($types)->toContain(ResponseBuilder::class);
});

// ─── 7. Refactor: los Controllers ya NO devuelven response()->json([...]) ─────
// (cambios aditivos al diseño: el formato se delega al ResponseBuilder)

test('AuthController no llama directamente a response()->json en su código fuente', function () {
    $code = file_get_contents(
        base_path('app/Http/Controllers/Api/AuthController.php')
    );
    expect($code)->not->toContain('response()->json');
});

test('GameController no llama directamente a response()->json en su código fuente', function () {
    $code = file_get_contents(
        base_path('app/Http/Controllers/Api/GameController.php')
    );
    expect($code)->not->toContain('response()->json');
});

test('BoardController no llama directamente a response()->json en su código fuente', function () {
    $code = file_get_contents(
        base_path('app/Http/Controllers/Api/BoardController.php')
    );
    expect($code)->not->toContain('response()->json');
});

test('TileController no llama directamente a response()->json en su código fuente', function () {
    $code = file_get_contents(
        base_path('app/Http/Controllers/Api/TileController.php')
    );
    expect($code)->not->toContain('response()->json');
});
