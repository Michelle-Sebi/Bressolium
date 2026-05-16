# Arquitectura del Proyecto Bressolium

> Documento de referencia técnica del proyecto **Bressolium** — videojuego de estrategia colaborativa multijugador desarrollado como Proyecto Final de DAW (curso 2025-2026).
>
> Este documento describe las decisiones arquitectónicas tomadas, los patrones aplicados y las convenciones que se siguen tanto en el **backend** (Laravel) como en el **frontend** (React). Está pensado para servir de referencia durante el desarrollo y como soporte durante la defensa del proyecto ante tribunal.

---

## Tabla de contenidos

1. [Visión general](#1-visión-general)
2. [Stack tecnológico](#2-stack-tecnológico)
3. [Arquitectura del Backend](#3-arquitectura-del-backend)
   - 3.1. [Patrón Controller → Service → Repository](#31-patrón-controller--service--repository)
   - 3.2. [Capa de Routing](#32-capa-de-routing)
   - 3.3. [Capa de Controllers](#33-capa-de-controllers)
   - 3.4. [Capa de Services](#34-capa-de-services)
   - 3.5. [Capa de Repositories y Contracts](#35-capa-de-repositories-y-contracts)
   - 3.6. [Inversión de Control con el IoC Container](#36-inversión-de-control-con-el-ioc-container)
   - 3.7. [Modelo de datos](#37-modelo-de-datos)
4. [Estructuras transversales del Backend](#4-estructuras-transversales-del-backend)
   - 4.1. [DTOs](#41-dtos-data-transfer-objects)
   - 4.2. [Form Requests](#42-form-requests)
   - 4.3. [Policies](#43-policies)
   - 4.4. [Resources](#44-resources)
   - 4.5. [Excepciones personalizadas y Handler global](#45-excepciones-personalizadas-y-handler-global)
   - 4.6. [Events y Listeners](#46-events-y-listeners)
   - 4.7. [ResponseBuilder centralizado](#47-responsebuilder-centralizado)
   - 4.8. [Middleware global](#48-middleware-global)
   - 4.9. [Documentación interactiva con Swagger / OpenAPI](#49-documentación-interactiva-con-swagger--openapi)
   - 4.10. [Sistema de Jobs y cierre de jornada](#410-sistema-de-jobs-y-cierre-de-jornada)
   - 4.11. [Estrategia de caché](#411-estrategia-de-caché)
   - 4.12. [Autenticación con Sanctum](#412-autenticación-con-sanctum)
   - 4.13. [UUIDs como claves primarias](#413-uuids-como-claves-primarias)
   - 4.14. [Seguridad](#414-seguridad)
   - 4.15. [Observabilidad](#415-observabilidad)
5. [Arquitectura del Frontend](#5-arquitectura-del-frontend)
   - 5.1. [Organización por features](#51-organización-por-features)
   - 5.2. [Pages, Features y Componentes UI](#52-pages-features-y-componentes-ui)
   - 5.3. [Routing y lazy loading](#53-routing-y-lazy-loading)
6. [Gestión del estado en Frontend](#6-gestión-del-estado-en-frontend)
   - 6.1. [Redux Toolkit (slices) — estado de UI](#61-redux-toolkit-slices--estado-de-ui)
   - 6.2. [RTK Query — caché de servidor](#62-rtk-query--caché-de-servidor)
   - 6.3. [Hooks customizados por feature](#63-hooks-customizados-por-feature)
   - 6.4. [Cliente HTTP centralizado](#64-cliente-http-centralizado)
   - 6.5. [Sincronización con polling](#65-sincronización-con-polling)
   - 6.6. [Manejo de errores hacia el usuario](#66-manejo-de-errores-hacia-el-usuario)
7. [Convenciones de nomenclatura](#7-convenciones-de-nomenclatura)
8. [Testing](#8-testing)
9. [Despliegue y CI/CD](#9-despliegue-y-cicd)
10. [Justificación de decisiones técnicas](#10-justificación-de-decisiones-técnicas)

---

## 1. Visión general

Bressolium es una aplicación cliente-servidor con arquitectura **desacoplada**: backend y frontend son dos proyectos independientes que se comunican únicamente a través de una **API REST** versionada (`/api/v1/...`). Esta separación permite desarrollar, testear y desplegar cada parte por separado.

```
┌──────────────────────────┐         HTTPS / JSON          ┌──────────────────────────┐
│      FRONTEND (SPA)      │ ◄────────────────────────────► │      BACKEND (API)       │
│  React + Redux + RTKQ    │      Bearer Token (Sanctum)    │  Laravel 11 + Eloquent   │
└──────────────────────────┘                                 └──────────────────────────┘
                                                                       │
                                                                       ▼
                                                            ┌──────────────────────┐
                                                            │   MySQL (testing/    │
                                                            │   producción)        │
                                                            └──────────────────────┘
```

La aplicación se ejecuta en contenedores Docker (Laravel Sail en desarrollo) y el código se aloja en GitHub con un pipeline de CI que ejecuta los tests en cada push.

---

## 2. Stack tecnológico

### Backend
| Tecnología                    | Uso                                              |
| ----------------------------- | ------------------------------------------------ |
| **PHP 8.4**                   | Lenguaje del backend                             |
| **Laravel 11**                | Framework web (routing, ORM, validación, etc.)   |
| **Eloquent ORM**              | Mapeo objeto-relacional sobre MySQL              |
| **Laravel Sanctum**           | Autenticación por tokens (Bearer)                |
| **Laravel Sail (Docker)**     | Entorno local containerizado                     |
| **Pest**                      | Framework de testing (sobre PHPUnit)             |
| **Laravel Telescope**         | Monitorización de requests, queries y errores    |
| **MySQL 8**                   | Base de datos relacional                         |

### Frontend
| Tecnología                    | Uso                                              |
| ----------------------------- | ------------------------------------------------ |
| **React 18**                  | Librería UI                                      |
| **Vite**                      | Bundler y servidor de desarrollo                 |
| **Redux Toolkit**             | Gestión de estado global (slices)                |
| **RTK Query**                 | Caché y sincronización con servidor              |
| **React Router v6**           | Routing del lado del cliente                     |
| **Axios**                     | Cliente HTTP                                     |
| **Tailwind CSS**              | Estilos (atomic CSS)                             |
| **Vitest + React Testing Library** | Testing                                     |

---

## 3. Arquitectura del Backend

### 3.1. Patrón Controller → Service → Repository

El backend implementa una arquitectura **en capas** que separa responsabilidades de forma estricta:

```
   HTTP Request
        │
        ▼
┌───────────────┐    valida entrada (FormRequest)
│   Controller  │    construye DTO
│               │    delega en Service
└───────┬───────┘
        │
        ▼
┌───────────────┐    aplica reglas de negocio
│    Service    │    orquesta validaciones
│               │    dispara Events
└───────┬───────┘
        │
        ▼
┌───────────────┐    accede a base de datos
│  Repository   │    encapsula queries
│  (interface)  │    devuelve modelos Eloquent
└───────┬───────┘
        │
        ▼
   Modelo Eloquent
```

**Cada capa tiene una responsabilidad única:**

- **Controller**: Recibe el HTTP request, lo valida (vía `FormRequest`), construye un DTO con los datos limpios y delega la lógica al Service. **No contiene lógica de negocio**.
- **Service**: Aplica todas las reglas de negocio (validar precondiciones, encadenar operaciones, lanzar excepciones de dominio, disparar eventos). **No conoce nada de HTTP**.
- **Repository**: Encapsula el acceso a base de datos. Devuelve modelos Eloquent o tipos primitivos. Puede ser sustituido por otra implementación sin afectar al Service.

Esta separación nos da:

- **Testabilidad**: Los Services se testean con repositorios *mockeados* sin tocar la base de datos.
- **Mantenibilidad**: Cambiar de ORM o de query no afecta al Service.
- **Reutilización**: Un mismo Service puede invocarse desde un Controller, una Job de cola o un comando Artisan.

---

### 3.2. Capa de Routing

Toda la API se versiona bajo el prefijo `/api/v1` y aplica `throttle:60,1` (60 peticiones por minuto). Las rutas autenticadas se agrupan bajo el middleware `auth:sanctum`.

**Ejemplo real** — `routes/api.php`:

```php
Route::prefix('v1')->middleware('throttle:60,1')->group(function () {

    // Rutas públicas
    Route::get('/stats', [StatsController::class, 'stats']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);

    // Rutas autenticadas
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/game/create',           [GameController::class, 'create']);
        Route::post('/game/join',             [GameController::class, 'join']);
        Route::post('/game/join-random',      [GameController::class, 'joinRandom']);
        Route::get('/game/my',                [GameController::class, 'myGames']);
        Route::get('/game/all',               [GameController::class, 'allGames']);
        Route::delete('/game/{gameId}/leave', [GameController::class, 'leave']);

        Route::get('/board/{gameId}',           [BoardController::class, 'show']);
        Route::get('/game/{gameId}/sync',       [SyncController::class,  'sync']);
        Route::post('/game/{gameId}/vote',      [VoteController::class,  'vote']);
        Route::post('/game/{gameId}/close-round', [RoundController::class, 'close']);

        Route::post('/tiles/{id}/explore', [TileController::class, 'explore']);
        Route::post('/tiles/{id}/upgrade', [TileController::class, 'upgrade']);
    });
});
```

---

### 3.3. Capa de Controllers

Los Controllers viven en `app/Http/Controllers/Api/` y son **finos**: su única responsabilidad es traducir el HTTP request a una invocación al Service y formatear la respuesta.

**Ejemplo real** — `TileController.php`:

```php
class TileController extends Controller
{
    public function __construct(
        private ActionService $actionService,
        private ResponseBuilder $rb,
    ) {}

    public function explore(ExploreActionRequest $request, string $id): JsonResponse
    {
        $dto  = new ExploreActionDTO(tileId: $id, userId: $request->user()->id);
        $tile = $this->actionService->explore($dto);

        return $this->rb->success((new TileResource($tile))->toArray($request));
    }

    public function upgrade(UpgradeActionRequest $request, string $id): JsonResponse
    {
        $dto  = new UpgradeActionDTO(tileId: $id, userId: $request->user()->id);
        $tile = $this->actionService->upgrade($dto);

        return $this->rb->success((new TileResource($tile))->toArray($request));
    }
}
```

**Puntos a destacar:**
- `ExploreActionRequest` (un FormRequest) ya ha validado el request antes de entrar al método.
- El controlador construye un **DTO inmutable** (`ExploreActionDTO`) con los datos del request y los pasa al Service.
- La respuesta se serializa con un **Resource** (`TileResource`) y se envuelve con el **ResponseBuilder** centralizado, garantizando un formato JSON uniforme.
- No hay ninguna llamada a Eloquent en el Controller.

---

### 3.4. Capa de Services

Los Services viven en `app/Services/` y contienen la **lógica de negocio**. Reciben DTOs, validan precondiciones, orquestan operaciones contra el Repository, lanzan excepciones de dominio cuando algo está mal y disparan eventos cuando ocurre algo notable.

**Ejemplo real** — Método `explore()` de `ActionService.php`:

```php
class ActionService
{
    public function __construct(
        private TileRepositoryInterface $tileRepo,
        private CacheService $cacheService,
    ) {}

    public function explore(ExploreActionDTO $dto): Tile
    {
        $tile = $this->tileRepo->find($dto->tileId);

        if (! $this->tileRepo->isUserInGame($dto->userId, $tile->game_id)) {
            throw new UserNotInGameException;
        }

        $round = $this->tileRepo->getCurrentRound($tile->game_id);
        if ($this->tileRepo->getActionsSpent($round, $dto->userId) >= 2) {
            throw new ActionLimitExceededException;
        }

        if ($tile->explored) {
            throw new TileAlreadyExploredException;
        }

        if (! $this->tileRepo->isAdjacentToUserExplored($tile, $dto->userId)) {
            throw new TileNotAdjacentException;
        }

        $this->tileRepo->markExplored($tile, $dto->userId);
        $this->tileRepo->incrementActionsSpent($round, $dto->userId);

        $tile->refresh()->load('type');
        TileExplored::dispatch($tile, $dto->userId);
        $this->cacheService->invalidateBoard($tile->game_id);
        $this->cacheService->invalidateSync($tile->game_id, $dto->userId);

        return $tile;
    }
}
```

**Puntos a destacar:**
- Las dependencias (`TileRepositoryInterface`, `CacheService`) se inyectan por el constructor — **no se instancian dentro del Service**.
- Se inyecta la **interfaz**, no la implementación concreta. Laravel resuelve esto automáticamente gracias al binding del IoC Container (ver §3.6).
- Las precondiciones se validan en orden y, si fallan, se lanzan excepciones del dominio (`UserNotInGameException`, `TileAlreadyExploredException`, etc.).
- Tras completar la acción, el Service dispara un evento (`TileExplored::dispatch`) e invalida tanto la caché del tablero (`invalidateBoard`) como la caché de estado del jugador que actuó (`invalidateSync`). Estos efectos secundarios son responsabilidad del Service.

---

### 3.5. Capa de Repositories y Contracts

Cada repositorio se compone de **dos archivos**: una **interfaz** (en `app/Repositories/Contracts/`) que define el contrato, y una **implementación** (en `app/Repositories/Eloquent/`) que lo cumple usando Eloquent.

**Ejemplo real** — `TileRepositoryInterface.php`:

```php
namespace App\Repositories\Contracts;

interface TileRepositoryInterface
{
    public function find(string $id): ?Tile;

    public function isUserInGame(string $userId, string $gameId): bool;

    public function getCurrentRound(string $gameId): ?Round;

    public function getActionsSpent(Round $round, string $userId): int;

    public function incrementActionsSpent(Round $round, string $userId): void;

    public function markExplored(Tile $tile, string $userId): void;

    public function findNextTileType(Tile $tile): ?TileType;

    public function getUpgradeCosts(TileType $nextType): Collection;

    public function hasSufficientMaterials(Game $game, Collection $costs): bool;

    public function deductMaterials(Game $game, Collection $costs): void;

    public function getRequiredTechnology(TileType $nextType): ?Technology;

    public function upgradeTile(Tile $tile, TileType $nextType): void;

    public function isAdjacentToUserExplored(Tile $tile, string $userId): bool;
}
```

**Implementación** — `TileRepository.php` (extracto):

```php
namespace App\Repositories\Eloquent;

class TileRepository implements TileRepositoryInterface
{
    public function find(string $id): ?Tile
    {
        return Tile::find($id);
    }

    public function markExplored(Tile $tile, string $userId): void
    {
        $tile->update([
            'explored'              => true,
            'explored_by_player_id' => $userId,
            'explored_at'           => now(),
        ]);
    }

    public function hasSufficientMaterials(Game $game, Collection $costs): bool
    {
        foreach ($costs as $material) {
            $required = $material->pivot->quantity;
            $stock = $game->materials()->where('material_id', $material->id)->first();
            if (! $stock || $stock->pivot->quantity < $required) {
                return false;
            }
        }

        return true;
    }
}
```

**¿Por qué dos archivos?** Trabajar contra una interfaz nos permite:

1. **Testear los Services con mocks** — el test pasa una implementación falsa de `TileRepositoryInterface` que devuelve los valores que necesite el caso de prueba, sin tocar la base de datos. Esto hace los tests unitarios rápidos y deterministas.
2. **Sustituir la implementación** — si en el futuro quisiéramos cambiar Eloquent por otro ORM (o por una API externa, una caché, etc.), bastaría con escribir una nueva implementación de la interfaz y actualizar el binding.

---

### 3.6. Inversión de Control con el IoC Container

Laravel resuelve automáticamente las dependencias del constructor leyendo los *type hints*. Para que pueda inyectar una **interfaz**, hay que decirle qué clase concreta debe usar. Esto se hace en un **Service Provider**.

**Ejemplo real** — `RepositoryServiceProvider.php`:

```php
namespace App\Providers;

use App\Repositories\Contracts\TileRepositoryInterface;
use App\Repositories\Eloquent\TileRepository;
// ... otros imports

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(GameRepositoryInterface::class,        GameRepository::class);
        $this->app->bind(UserRepositoryInterface::class,        UserRepository::class);
        $this->app->bind(TileRepositoryInterface::class,        TileRepository::class);
        $this->app->bind(RoundRepositoryInterface::class,       RoundRepository::class);
        $this->app->bind(SyncRepositoryInterface::class,        SyncRepository::class);
        $this->app->bind(CloseRoundRepositoryInterface::class,  CloseRoundRepository::class);
        // ... etc.
    }
}
```

**Cómo funciona:**

1. Cuando alguien pide una instancia de `ActionService`, Laravel inspecciona su constructor y ve que necesita un `TileRepositoryInterface` y un `CacheService`.
2. Para `TileRepositoryInterface`, Laravel mira en sus bindings y encuentra que está enlazado a `TileRepository`. Crea una instancia de `TileRepository` (resolviendo recursivamente sus propias dependencias) y la inyecta.
3. `CacheService` es una clase concreta, así que la instancia directamente.

Esto es **Inversión de Control (IoC)**: el código no decide qué clases concretas usar; lo decide el contenedor de dependencias en función de la configuración. El resultado es **bajo acoplamiento** entre capas.

El `AppServiceProvider` se usa además para registrar **Policies**:

```php
class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::policy(Game::class, GamePolicy::class);
        Gate::policy(Tile::class, TilePolicy::class);
    }
}
```

Los **listeners de eventos** no se registran explícitamente: Laravel 11 usa **Event Auto-Discovery**. El framework escanea `app/Listeners/` y, si el método `handle()` de un listener tiene un type-hint sobre un evento concreto (`VoteCast`, `TileExplored`…), lo enlaza automáticamente sin configuración adicional.

---

### 3.7. Modelo de datos

El dominio de Bressolium se modela en torno a tres conceptos principales: **partidas** (`games`), **jugadores** (`users`) y **tableros** (`tiles`). Sobre ellos se articulan las **jornadas** (`rounds`), los **votos** (`votes`) y todo el sistema de **catálogo** (tipos de casilla, materiales, tecnologías, inventos).

Toda la base de datos usa **UUIDs** como claves primarias (ver §4.13), y la integridad referencial se garantiza con foreign keys con `ON DELETE CASCADE` salvo en relaciones opcionales (donde se usa `SET NULL`).

#### Entidades principales

| Tabla            | Descripción                                                          | Columnas relevantes                                                            |
| ---------------- | -------------------------------------------------------------------- | ------------------------------------------------------------------------------ |
| `users`          | Jugadores registrados                                                | `id` (UUID), `name`, `email`, `password`                                       |
| `games`          | Partidas. Una partida tiene 1-5 jugadores y un tablero 15×15         | `id`, `name`, `status` (`WAITING`/`ACTIVE`/`FINISHED`)                          |
| `rounds`         | Jornadas de una partida. Cada partida arranca con la jornada 1       | `id`, `game_id`, `number`, `start_date`, `ended_at`, `no_consensus`, `last_built_invention_id`, `no_consensus_tech`, `last_activated_tech_id` |
| `tiles`          | Casillas del tablero (225 por partida)                               | `id`, `game_id`, `tile_type_id`, `coord_x`, `coord_y`, `explored`, `explored_by_player_id`, `explored_at` |
| `tile_types`     | Catálogo de tipos de casilla (con su nivel y tipo base)              | `id`, `name`, `base_type`, `level`                                              |
| `materials`      | Catálogo de materiales (madera, piedra, oro…)                        | `id`, `name`, `tier`, `group`                                                   |
| `technologies`   | Catálogo de tecnologías investigables                                | `id`, `name`, `prerequisite_id`                                                 |
| `inventions`     | Catálogo de inventos construibles. Algunos son finales (ganan)       | `id`, `name`, `is_final`, `technology_id`                                       |
| `votes`          | Votos emitidos en cada jornada por cada jugador                      | `id`, `round_id`, `user_id`, `technology_id`, `invention_id`                    |

#### Tablas pivote (relaciones N:M con datos extra)

| Tabla              | Relaciona            | Datos extra que aporta                                            |
| ------------------ | -------------------- | ----------------------------------------------------------------- |
| `game_user`        | partida ↔ jugador    | `is_afk`                                                          |
| `round_user`       | jornada ↔ jugador    | `actions_spent` (acciones gastadas, máx. 2), `finished_at` (timestamp cuando el jugador ha completado sus acciones y votos) |
| `game_material`    | partida ↔ material   | `quantity` (stock actual en la partida)                           |
| `game_technology`  | partida ↔ tecnología | `is_active` (si está investigada)                                  |
| `game_invention`   | partida ↔ invento    | `is_active`, `quantity`                                            |
| `material_tile_type` | tipo de casilla ↔ material | `quantity`, `tech_required`, `invention_required`             |

#### Tablas de catálogo extendido

El sistema de tecnologías e inventos se modela con varias tablas de soporte:

- `technology_prerequisites` y `invention_prerequisites` — qué se necesita previamente (otra tecnología, invento, etc.)
- `invention_costs` — qué materiales consume cada invento
- `technology_bonuses` y `invention_bonuses` — bonos que aplican
- `technology_unlocks` y `invention_unlocks` — qué desbloquean al activarse
- `recipes` — receta polimórfica (`recipeable_type` + `recipeable_id`) que asocia materiales a una entidad cualquiera

#### Diagrama lógico simplificado

```
              ┌─────────┐         ┌──────────┐         ┌──────────┐
              │  users  │ ◄──────►│game_user │◄──────► │  games   │
              └─────────┘         └──────────┘         └──────────┘
                   ▲                                         │
                   │                                         │
              ┌────┴─────┐                                   ▼
              │round_user│                              ┌──────────┐
              └──────────┘                              │  rounds  │
                   ▲                                    └──────────┘
                   │                                         │
              ┌────┴─────┐                                   ▼
              │  rounds  │ ────► votes ────► technologies/inventions
              └──────────┘
                                   ┌──────────┐
                                   │  tiles   │ ◄── games
                                   └──────────┘
                                        │
                                        ▼
                                   tile_types ──► materials (vía material_tile_type)
```

Entidades de catálogo (`tile_types`, `materials`, `technologies`, `inventions`) se cargan vía **seeders** y son comunes a todas las partidas; las pivote como `game_*` materializan el estado concreto de cada partida.

---

## 4. Estructuras transversales del Backend

### 4.1. DTOs (Data Transfer Objects)

Los DTOs viven en `app/DTOs/` y son **objetos inmutables** que transportan datos entre capas. Sustituyen al uso de arrays asociativos sueltos, dándonos tipado y autocompletado.

**Ejemplo real** — `ExploreActionDTO.php`:

```php
namespace App\DTOs;

final readonly class ExploreActionDTO
{
    public function __construct(
        public string $tileId,
        public string $userId,
    ) {}
}
```

**Por qué `readonly`:** Garantiza que un DTO, una vez creado, no se puede modificar accidentalmente. Si una capa quiere cambiar algún valor, debe construir un DTO nuevo.

**Por qué `final`:** Los DTOs no se extienden. Si necesitas un DTO distinto, créalo desde cero — la herencia entre DTOs suele indicar que estás mezclando responsabilidades.

---

### 4.2. Form Requests

Los FormRequests viven en `app/Http/Requests/` y encapsulan **validación de entrada** y, opcionalmente, **autorización**. Laravel los resuelve antes de invocar al Controller, así que si la validación falla, el método del Controller ni siquiera llega a ejecutarse.

**Ejemplo real** — `ExploreActionRequest.php`:

```php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExploreActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [];
    }
}
```

> En este caso concreto la acción no requiere parámetros en el body (el `tileId` viene en la URL y el `userId` se obtiene del token Sanctum), por eso `rules()` está vacío. La autorización a nivel de modelo se delega a la **Policy** correspondiente.

---

### 4.3. Policies

Las Policies viven en `app/Policies/` y resuelven la pregunta: *¿este usuario puede realizar esta acción sobre este modelo concreto?*

**Ejemplo real** — `TilePolicy.php`:

```php
namespace App\Policies;

class TilePolicy
{
    public function explore(User $user, Tile $tile): bool
    {
        return $tile->game->users()->where('users.id', $user->id)->exists();
    }

    public function upgrade(User $user, Tile $tile): bool
    {
        return $tile->game->users()->where('users.id', $user->id)->exists();
    }
}
```

Las Policies se registran en `AppServiceProvider::boot()` con `Gate::policy(...)` y se invocan automáticamente al usar `$this->authorize('explore', $tile)` desde un Controller.

---

### 4.4. Resources

Los Resources viven en `app/Http/Resources/` y se encargan de **serializar modelos Eloquent a JSON**. Permiten controlar exactamente qué campos se exponen al cliente y cómo se formatean.

**Ejemplo real** — `TileResource.php`:

```php
class TileResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'           => $this->id,
            'coord_x'      => $this->coord_x,
            'coord_y'      => $this->coord_y,
            'tile_type_id' => $this->tile_type_id,
            'explored'     => (bool) $this->explored,
            'type'         => $this->whenLoaded('type', fn () => $this->type ? [
                'id'        => $this->type->id,
                'name'      => $this->type->name,
                'base_type' => $this->type->base_type,
                'level'     => $this->type->level,
            ] : null),
        ];
    }
}
```

`whenLoaded('type', ...)` evita queries N+1: si la relación `type` no se ha cargado previamente con `->load('type')`, simplemente no se incluye en la respuesta.

---

### 4.5. Excepciones personalizadas y Handler global

Las excepciones del dominio viven en `app/Exceptions/` y heredan de una clase base `DomainException`. Cada una corresponde a una violación específica de las reglas del juego y trae su propio código HTTP.

**Ejemplo real** — `TileAlreadyExploredException.php`:

```php
namespace App\Exceptions;

class TileAlreadyExploredException extends DomainException
{
    public function __construct()
    {
        parent::__construct('La casilla ya ha sido explorada.', 422);
    }
}
```

#### Catálogo de excepciones del dominio

| Excepción                          | HTTP | Mensaje                                                                  |
| ---------------------------------- | ---- | ------------------------------------------------------------------------ |
| `UserNotInGameException`           | 403  | "El usuario no pertenece a esta partida."                                |
| `ActionLimitExceededException`     | 403  | "No quedan acciones disponibles en esta jornada."                        |
| `TileAlreadyExploredException`     | 422  | "La casilla ya ha sido explorada."                                        |
| `TileNotExploredException`         | 422  | "La casilla aún no ha sido explorada."                                    |
| `TileNotAdjacentException`         | 422  | "Solo puedes explorar casillas adyacentes a tu territorio."              |
| `InsufficientMaterialsException`   | 400  | "Materiales insuficientes para realizar esta acción."                    |
| `TechnologyRequiredException`      | 400  | "Se requiere la tecnología «X» para evolucionar esta casilla."           |
| `VoteValidationException`          | 422  | (mensaje según el caso concreto)                                          |

#### Handler global

Estas excepciones nunca se atrapan en los Services ni en los Controllers — se dejan **propagar libremente**. El **Handler global de Laravel**, configurado en `bootstrap/app.php`, las captura y las traduce al formato uniforme `{success, data, error}`.

```php
->withExceptions(function (Exceptions $exceptions) {
    $exceptions->render(function (ValidationException $e, $request) {
        return response()->json([
            'success' => false,
            'data'    => null,
            'error'   => $e->errors(),
        ], 422);
    });

    $exceptions->render(function (DomainException $e, $request) {
        return response()->json([
            'success' => false,
            'data'    => null,
            'error'   => $e->getMessage(),
        ], $e->getCode() ?: 500);
    });

    $exceptions->render(function (AuthenticationException $e, $request) {
        return response()->json([
            'success' => false,
            'data'    => null,
            'error'   => 'No autenticado.',
        ], 401);
    });

    // ... AccessDeniedHttpException → 403, NotFoundHttpException → 404
})
```

**Beneficio**: el código de los Services queda limpio (`throw new TileAlreadyExploredException`) y la conversión a HTTP/JSON ocurre en un único lugar. Si se añade una nueva excepción del dominio, no hace falta tocar nada en el Handler — basta con extender `DomainException` con su código HTTP.

---

### 4.6. Events y Listeners

Los **eventos del dominio** se disparan desde los Services cuando ocurre algo relevante. Los **listeners** reaccionan a esos eventos de forma desacoplada.

**Ejemplo real** — `TileExplored.php`:

```php
namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;

class TileExplored
{
    use Dispatchable;

    public function __construct(
        public readonly Tile $tile,
        public readonly string $userId,
    ) {}
}
```

**Cómo se dispara:**

```php
TileExplored::dispatch($tile, $dto->userId);
```

**Listener** — `NotifyPlayersListener.php`:

```php
class NotifyPlayersListener implements ShouldQueue
{
    public function handle(object $event): void
    {
        // Notificar al resto de jugadores...
    }
}
```

Implementar `ShouldQueue` hace que el listener se ejecute en una **cola** (asíncrono), sin bloquear la respuesta HTTP al usuario.

#### Catálogo de eventos del dominio

| Evento              | Quién lo dispara                                                  | Listener registrado                  |
| ------------------- | ----------------------------------------------------------------- | ------------------------------------ |
| `TileExplored`      | `ActionService::explore()` cuando una casilla se explora           | (sin listener específico, log only)  |
| `TileUpgraded`      | `ActionService::upgrade()` cuando una casilla sube de nivel        | (sin listener específico, log only)  |
| `VoteCast`          | `VoteService::process()` cuando un jugador emite un voto           | `CheckQuorumOnVoteCast`              |
| `MaterialsProduced` | `CloseRoundService::process()` tras producir materiales            | (sin listener específico, log only)  |
| `InventionBuilt`    | `CloseRoundService::resolveInventionWinner()` al construir invento | (sin listener específico, log only)  |
| `RoundClosed`       | `CloseRoundService::process()` tras cerrar la jornada              | (sin listener específico, log only)  |
| `GameFinished`      | `CloseRoundService::resolveInventionWinner()` al construir invento final | (sin listener específico, log only) |

#### Comportamiento AFK durante el voto

`VoteService::vote()` tiene un efecto secundario importante: en cuanto cualquier jugador vota, **todos los jugadores de la partida pasan a `is_afk = false`**. La razón es que un voto demuestra actividad real en la sesión, lo que invalida cualquier marcado AFK previo. El AFK se re-evalúa únicamente al cerrar la jornada (`markAfkPlayers`) mirando quién tiene `actions_spent = 0` en esa jornada.

#### Listener clave: `CheckQuorumOnVoteCast`

Cuando un jugador vota, este listener delega en `RoundProgressService::markDoneIfReady()`. Este servicio comprueba si ese jugador ya ha **gastado 2 acciones Y votado por una tecnología Y votado por un invento**. Si se cumplen las tres condiciones, lo marca como "terminado" en la jornada. Cuando *todos* los jugadores están marcados como terminados, dispara automáticamente `CloseRoundJob::dispatchSync()` para cerrar la jornada sin esperar a que nadie pulse "Finalizar Turno".

Esta es una pieza esencial del bucle de juego: combina un evento del dominio (`VoteCast`) con la lógica de progreso individual (`RoundProgressService`) para automatizar el avance de jornada en cuanto todos han completado sus acciones.

Adicionalmente, `ExpireRoundJob` actúa como temporizador de seguridad: si se despacha cuando expira el tiempo de una jornada, marca como terminados a todos los jugadores que aún no lo estén y dispara el cierre si procede. Esto garantiza que la partida no se quede bloqueada si algún jugador es inactivo.

---

### 4.7. ResponseBuilder centralizado

Toda respuesta JSON de la API sigue un mismo formato gracias al `ResponseBuilder`:

```json
{
  "success": true,
  "data": { ... },
  "error": null
}
```

**Ejemplo real** — `app/Support/ResponseBuilder.php`:

```php
class ResponseBuilder
{
    public function success(mixed $data, int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => $data,
            'error'   => null,
        ], $code);
    }

    public function error(string $message, int $code = 500): JsonResponse
    {
        return response()->json([
            'success' => false,
            'data'    => null,
            'error'   => $message,
        ], $code);
    }
}
```

Tener un único punto de salida garantiza que el frontend siempre puede confiar en la estructura `{success, data, error}` sin importar el endpoint.

---

### 4.8. Middleware global

El middleware `ForceJson` se aplica globalmente para garantizar que todas las respuestas y request errors devuelvan JSON (en lugar de HTML por defecto), lo que es esencial para una API.

---

### 4.9. Documentación interactiva con Swagger / OpenAPI

La API se autodocumenta mediante **OpenAPI 3.0** usando el paquete `darkaonline/l5-swagger`. Cada endpoint lleva anotaciones PHPDoc tipo `@OA\Post`, `@OA\Get`, etc. que describen parámetros, body, respuestas y códigos HTTP. A partir de esas anotaciones se genera automáticamente la especificación JSON y una UI interactiva donde se pueden probar los endpoints directamente.

**Anotaciones globales** — `app/OpenApi.php`:

```php
namespace App;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="Bressolium API",
 *     description="API REST del videojuego de estrategia colaborativa Bressolium...",
 *     @OA\Contact(name="Equipo Bressolium")
 * )
 *
 * @OA\Server(
 *     url="http://localhost/api/v1",
 *     description="Servidor de desarrollo local"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="Sanctum-Token"
 * )
 *
 * @OA\Tag(name="Auth",  description="Registro, login y logout de usuarios")
 * @OA\Tag(name="Game",  description="Creación, unión y consulta de partidas")
 * @OA\Tag(name="Tile",  description="Acciones sobre casillas (explorar, mejorar)")
 * ...
 */
class OpenApi {}
```

**Anotación de un endpoint** — extracto de `TileController::explore()`:

```php
/**
 * @OA\Post(
 *     path="/tiles/{id}/explore",
 *     summary="Explora una casilla",
 *     description="Marca una casilla como explorada por el usuario...",
 *     tags={"Tile"},
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(
 *         name="id", in="path", required=true,
 *         description="UUID de la casilla a explorar",
 *         @OA\Schema(type="string", format="uuid")
 *     ),
 *     @OA\Response(response=200, description="Casilla explorada correctamente"),
 *     @OA\Response(response=403, description="El usuario no pertenece a esta partida"),
 *     @OA\Response(response=422, description="Casilla ya explorada / no adyacente / sin acciones")
 * )
 */
public function explore(ExploreActionRequest $request, string $id): JsonResponse
{
    // ...
}
```

**Generación de la documentación:**

```bash
php artisan l5-swagger:generate
```

Esto produce `storage/api-docs/api-docs.json` con la especificación OpenAPI 3.0 completa.

**Acceso:**
- **UI interactiva** (Swagger UI): `http://localhost/api/documentation`
- **Especificación JSON**: `http://localhost/docs`

Desde la UI se pueden ejecutar peticiones contra el servidor pulsando *Try it out*. Para endpoints protegidos basta con pegar el token Sanctum en el botón *Authorize* y queda autenticado en toda la sesión.

**Configuración del analyser** — `config/l5-swagger.php`:

```php
'scanOptions' => [
    'analyser' => new \OpenApi\Analysers\ReflectionAnalyser([
        new \OpenApi\Analysers\AttributeAnnotationFactory(),
        new \OpenApi\Analysers\DocBlockAnnotationFactory(),
    ]),
    // ...
],
```

Esta configuración permite mezclar anotaciones PHPDoc (`/** @OA\... */`) con atributos PHP 8 (`#[OA\...]`).

---

### 4.10. Sistema de Jobs y cierre de jornada

El cierre de una jornada es la operación más compleja del backend: hay que resolver los votos, activar la tecnología/invento ganador, producir materiales según el tablero, marcar jugadores AFK y crear la siguiente jornada. Para encapsular toda esa lógica se usa un **Job de Laravel** que delega en un Service especializado.

**Ejemplo real** — `app/Jobs/CloseRoundJob.php`:

```php
namespace App\Jobs;

class CloseRoundJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private readonly string $gameId) {}

    public function handle(CloseRoundService $service): void
    {
        $service->process($this->gameId);
    }
}
```

El Job es **muy fino**: solo recibe el `gameId` y delega en `CloseRoundService::process()`. Esto permite invocarlo desde un Controller (`dispatchSync()`, ejecución síncrona) o desde un listener (`dispatch()`, ejecución en cola).

#### Flujo de `CloseRoundService::process()`

0. **Guard anti-doble-procesamiento** — si `round.ended_at` ya está relleno (p.ej. por el temporizador y el botón simultáneamente), se retorna sin hacer nada.
1. **Marcar la jornada como terminada** (`markRoundEnded`) — se escribe `ended_at = now()` antes de resolver nada, para que cualquier invocación paralela que llegue entre medio sea bloqueada por el guard del punto 0.
2. **Resolver tecnología ganadora** — la más votada se activa en `game_technology` con `is_active = true`. Se registra en el resultado de la jornada (`markRoundTechResult`).
3. **Resolver invento ganador** — el más votado se intenta construir:
   - Comprueba **prerrequisitos** (otras tecnologías o inventos previos).
   - Comprueba **recursos disponibles** (`game_material.quantity` ≥ `invention_costs.quantity`).
   - Si todo OK: descuenta materiales, registra el invento en `game_invention`, dispara `InventionBuilt`.
4. **¿Es el invento final?**
   - Sí → dispara `GameFinished`, marca la partida como `FINISHED` y termina.
5. **Producción de materiales** — para cada casilla explorada, se suman los materiales que ese tipo de casilla produce. Dispara `MaterialsProduced`.
6. **Detección de AFK** — todo jugador con `actions_spent = 0` se marca como `is_afk = true` para la siguiente jornada.
7. **Crear nueva jornada** con `number = anterior + 1`.
8. **Inicializar jugadores** — todos los `users` se enlazan a la nueva jornada con `actions_spent = 0` y `finished_at = null`.
9. **Programar temporizador** — se despacha `ExpireRoundJob` con un retraso de 2 horas. Si la jornada no se cierra antes de ese tiempo, el Job la cerrará automáticamente.
10. **Invalidar caché** — se invalida la caché de `sync` de todos los jugadores y el tablero, para que el siguiente poll reciba datos frescos.
11. **Disparar `RoundClosed`** para que cualquier listener pueda reaccionar.

#### Tres rutas de disparo hacia `CloseRoundJob`

La jornada puede cerrarse por tres vías distintas, pero las tres convergen en el mismo `CloseRoundJob → CloseRoundService::process()`:

```
1) Manual — jugador pulsa "Finalizar Turno":
   RoundController::close()
     → marca al jugador como finished_at = now()
     → si TODOS los jugadores tienen finished_at → CloseRoundJob::dispatchSync($gameId)
     → si no → responde "Esperando al resto de jugadores"

2) Automático — jugador completa 2 acciones y vota en ambas categorías:
   VoteCast → CheckQuorumOnVoteCast → RoundProgressService::markDoneIfReady()
     → verifica: actions_spent ≥ 2 && voted_tech && voted_invention
     → si se cumple → marca al jugador como finished_at
     → si TODOS los jugadores tienen finished_at → CloseRoundJob::dispatchSync($gameId)

3) Temporizador — expira el tiempo de la jornada (2 h desde su creación):
   ExpireRoundJob::handle()
     → marca como finished_at a todos los jugadores que aún no lo tengan
     → si TODOS los jugadores tienen finished_at → CloseRoundJob::dispatch($gameId)  ← asíncrono
```

El uso de `dispatchSync` en las rutas 1 y 2 hace que el cliente reciba la respuesta HTTP una vez completado todo el proceso, simplificando la sincronización del frontend. El `dispatch` asíncrono del temporizador es seguro porque no hay ninguna petición HTTP esperando esa respuesta.

El guard de `CloseRoundService` garantiza que, aunque las tres vías disparen `CloseRoundJob` simultáneamente (raza de condición), solo la primera ejecución que encuentre `ended_at = null` hará trabajo real; las demás retornan inmediatamente.

> **Nota**: `ExpireRoundJob` se programa en dos momentos distintos:
> 1. Al **crear una partida** (`GameService::createGame()`), para la jornada 1.
> 2. Al **cerrar cada jornada** (`CloseRoundService::process()`), para la jornada siguiente.
> En ambos casos el retraso es de 2 horas desde el momento de creación de la jornada.

---

### 4.11. Estrategia de caché

El backend usa la caché de Laravel (driver por defecto) a través de un **wrapper centralizado** llamado `CacheService`. Esto evita que cada Service conozca las claves o TTLs y permite invalidar selectivamente desde un único punto.

**Ejemplo real** — `app/Services/CacheService.php`:

```php
class CacheService
{
    public function __construct(private Repository $cache) {}

    public function rememberBoard(string $gameId, Closure $callback): mixed
    {
        return $this->cache->remember("board:{$gameId}", 300, $callback);
    }

    public function rememberSync(string $gameId, string $userId, Closure $callback): mixed
    {
        return $this->cache->remember("sync:{$gameId}:{$userId}", 30, $callback);
    }

    public function invalidateBoard(string $gameId): void
    {
        $this->cache->forget("board:{$gameId}");
    }

    public function invalidateSync(string $gameId, string $userId): void
    {
        $this->cache->forget("sync:{$gameId}:{$userId}");
    }
}
```

#### Claves y TTLs

| Clave                    | TTL    | Qué cachea                                     | Invalidación                              |
| ------------------------ | ------ | ---------------------------------------------- | ----------------------------------------- |
| `board:{gameId}`         | 300 s  | Tablero completo de la partida (225 casillas)  | Manual tras `explore`/`upgrade`           |
| `sync:{gameId}:{userId}` | 30 s   | Estado del juego para un jugador concreto      | Manual tras `explore`, `upgrade` (solo el jugador que actúa) y al cerrar jornada (todos los jugadores) |

#### Cómo se usa

`BoardService` envuelve la consulta del tablero:

```php
public function getBoardForUser(string $gameId, string $userId): Collection
{
    return $this->cacheService->rememberBoard($gameId, function () use ($gameId) {
        return $this->boardRepository->getTilesForGame($gameId);
    });
}
```

`SyncService` cachea por usuario porque cada jugador ve el tablero con sus propios datos (su `actions_spent`, su `has_voted`, etc.):

```php
return $this->cacheService->rememberSync($game->id, $userId, function () use ($game, $userId) {
    return new SyncResponseDTO(/* ... */);
});
```

#### Cuándo se invalida

`ActionService::explore()` y `ActionService::upgrade()` invalidan **explícitamente** el tablero al final de cada acción exitosa:

```php
$this->cacheService->invalidateBoard($tile->game_id);
```

La caché `sync` también se invalida manualmente en dos situaciones:
- **Tras explorar o mejorar una casilla**: solo se invalida la entrada del jugador que realizó la acción (`invalidateSync(gameId, userId)`), para que su siguiente poll reciba sus acciones actualizadas inmediatamente.
- **Al cerrar una jornada**: `CloseRoundService` invalida la entrada `sync` de **todos** los jugadores, porque el estado cambia globalmente (nueva jornada, materiales producidos, tecnología/invento activado).

Para el resto de casos (acciones de otros jugadores, votos de otros), el TTL de 30 s es suficiente.

#### Por qué dos TTLs distintos

- **Board (300 s)**: cambia poco (solo cuando alguien explora/mejora una casilla, y eso lo invalidamos manualmente).
- **Sync (30 s)**: cambia con frecuencia (acciones, votos, jornadas), pero un retraso de 30 s es aceptable y reduce drásticamente la carga de queries.

---

### 4.12. Autenticación con Sanctum

El proyecto usa **Laravel Sanctum** para autenticación basada en tokens. El flujo end-to-end es:

```
   Cliente                                Servidor
      │                                       │
      │  POST /register {name, email, pwd}    │
      ├──────────────────────────────────────►│ AuthService::register()
      │                                       │ - Hash::make(password)
      │                                       │ - User::create()
      │                                       │ - $user->createToken()
      │     200 {user, token: "1|abc..."}     │
      │◄──────────────────────────────────────┤
      │                                       │
      │  localStorage.setItem('auth_token')   │
      │                                       │
      │  GET /game/my                         │
      │  Authorization: Bearer 1|abc...       │
      ├──────────────────────────────────────►│ middleware: auth:sanctum
      │                                       │ - busca token en personal_access_tokens
      │                                       │ - hidrata $request->user()
      │     200 {data: [...]}                 │
      │◄──────────────────────────────────────┤
      │                                       │
      │  (cualquier 401 →                     │
      │   localStorage.removeItem('token'))   │
```

**Lado servidor** — `AuthService.php`:

```php
public function register(array $data): array
{
    $user = User::create([
        'name'     => $data['name'],
        'email'    => $data['email'],
        'password' => Hash::make($data['password']),
    ]);

    $token = $user->createToken('api-token')->plainTextToken;

    return ['user' => $user, 'token' => $token];
}

public function login(string $email, string $password): array
{
    $user = User::where('email', $email)->first();

    if (! $user || ! Hash::check($password, $user->password)) {
        throw new Exception('Invalid credentials');
    }

    $token = $user->createToken('api-token')->plainTextToken;

    return ['user' => $user, 'token' => $token];
}
```

**Validación de entrada** — `RegisterRequest.php`:

```php
public function rules(): array
{
    return [
        'name'     => 'required|string|max:255',
        'email'    => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:8',
    ];
}
```

**Lado cliente** — `httpClient.js` (interceptor de axios, ya visto en §6.4):

```javascript
httpClient.interceptors.request.use((config) => {
    const token = localStorage.getItem('auth_token');
    if (token) {
        config.headers['Authorization'] = `Bearer ${token}`;
    }
    return config;
});

httpClient.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 401) {
            localStorage.removeItem('auth_token');
        }
        return Promise.reject(error);
    }
);
```

Este patrón hace que el frontend **nunca** tenga que pasar el token explícitamente: una vez guardado en `localStorage` tras el login, se inyecta automáticamente en cada petición.

---

### 4.13. UUIDs como claves primarias

Toda la base de datos usa **UUIDs** (cadenas de 36 caracteres) en lugar de los típicos `bigIncrements`. El proyecto incluye **15 modelos** con el trait `HasUuids`:

> `User`, `Game`, `Round`, `TileType`, `Tile`, `Material`, `Technology`, `Invention`, `Vote`, `InventionCost`, `TechnologyUnlock`, `TechnologyPrerequisite`, `InventionBonus`, `TechnologyBonus`, `InventionPrerequisite`, `InventionUnlock`.

```php
class Tile extends Model
{
    use HasFactory, HasUuids;
    // ...
}
```

#### Por qué UUIDs

1. **Generables en cliente sin colisión**. Un UUID v4 puede generarse en el frontend o en otro servicio sin necesidad de consultar la base de datos. Esto facilita el escenario futuro en el que el cliente cree entidades de forma optimista.
2. **No expone información del sistema**. Con `id=42` se puede inferir cuántas partidas hay; con `01HFG...` no se puede.
3. **Permite distribuir la base de datos**. Si en el futuro se hace sharding, los UUIDs no colisionan entre nodos.
4. **Coherencia con el dominio**. Una partida y una casilla son entidades de dominio que viven independientemente — no son "la fila 42 de una tabla".

El coste es ~16 bytes extra por columna y un orden de inserción no monótono (que en MySQL 8 ya está optimizado con UUIDs ordenados). Para un proyecto educativo del tamaño de Bressolium el overhead es despreciable.

---

### 4.14. Seguridad

La seguridad del backend se aborda en cuatro frentes:

#### 1. Autenticación (Sanctum)
Ya descrita en §4.12. Tokens Bearer transmitidos por header `Authorization`. No hay sesiones de estado para el flujo SPA.

#### 2. Autorización (Policies)
Las acciones que afectan a entidades concretas pasan por una Policy:

| Policy                       | Método      | Regla                                         |
| ---------------------------- | ----------- | --------------------------------------------- |
| `GamePolicy::view()`         | GET board   | El usuario debe pertenecer a la partida       |
| `TilePolicy::explore()`      | POST tile   | El usuario debe pertenecer a la partida       |
| `TilePolicy::upgrade()`      | POST tile   | El usuario debe pertenecer a la partida       |

Se invocan desde el Controller con `$this->authorize('explore', $tile)`. Si la regla falla, Laravel lanza `AccessDeniedHttpException` (403) que el Handler global (§4.5) convierte a JSON.

#### 3. Validación de entrada (FormRequests)
Toda entrada del cliente pasa por un FormRequest que valida tipos, presencia, longitudes, unicidad, etc. Si falla, Laravel lanza `ValidationException` (422) **antes** de invocar al Controller. Ver §4.2.

#### 4. Rate limiting (throttling)
El bloque completo de la API tiene `throttle:60,1` (60 peticiones por minuto y por IP/usuario):

```php
Route::prefix('v1')->middleware('throttle:60,1')->group(function () { ... })
```

Esto previene abuso por scripts o bots. El cliente recibe `429 Too Many Requests` con un header `Retry-After`.

#### Middleware globales
En `bootstrap/app.php` se registran dos middlewares globales para el grupo `api`:

```php
$middleware->statefulApi();
$middleware->appendToGroup('api', [
    ForceJsonMiddleware::class,         // garantiza Accept: application/json
    RequestLoggingMiddleware::class,    // loguea method/path/user_id/status/duration
]);
```

`ForceJsonMiddleware` evita que un cliente sin headers correctos reciba HTML de error de Laravel; `RequestLoggingMiddleware` deja un rastro auditable de cada petición.

---

### 4.15. Observabilidad

El proyecto está instrumentado con **Laravel Telescope** y un endpoint propio de métricas.

#### Laravel Telescope

Telescope captura, en una base de datos separada, todo lo que pasa dentro de la aplicación. Se accede en `http://localhost/telescope`.

**Watchers activos** (configurados en `config/telescope.php`):

| Watcher              | Qué captura                                                         |
| -------------------- | ------------------------------------------------------------------- |
| `RequestWatcher`     | Cada HTTP request con headers, body, response, duración              |
| `QueryWatcher`       | SQL ejecutado (marca como *slow* si > 100 ms)                        |
| `ExceptionWatcher`   | Toda excepción con stack trace                                       |
| `JobWatcher`         | Jobs encolados, ejecutados o fallidos                                |
| `EventWatcher`       | Eventos del dominio disparados (`TileExplored`, `VoteCast`…)         |
| `ModelWatcher`       | Eventos de Eloquent (`creating`, `updating`, `deleting`)             |
| `LogWatcher`         | Logs de nivel ≥ `error`                                              |
| `GateWatcher`        | Resoluciones de Policies                                             |
| `CacheWatcher`       | Hits, misses y writes a la caché                                     |
| `CommandWatcher`     | Comandos Artisan ejecutados                                          |
| `ScheduleWatcher`    | Tareas programadas                                                   |

En **producción** Telescope se filtra para capturar solo lo relevante (excepciones reportables, requests fallidas, jobs fallidos), evitando que se llene de datos.

El acceso al panel está protegido por un Gate definido en `TelescopeServiceProvider`:

```php
Gate::define('viewTelescope', function (User $user) {
    return in_array($user->email, [/* whitelist de emails admin */]);
});
```

#### Endpoint `/api/v1/stats`

Es público (sin autenticación) y devuelve métricas agregadas en tiempo real, agrupadas en `system` y `game`:

```json
{
  "success": true,
  "data": {
    "system": {
      "uptime": 12345,
      "database": "ok",
      "requests_per_minute": 42,
      "errors_per_minute": 0,
      "latency_p95": 87.3
    },
    "game": {
      "total_games": 18,
      "waiting_games": 4,
      "active_games": 12,
      "finished_games": 2,
      "total_players": 35,
      "total_rounds": 84,
      "players": [...]
    }
  },
  "error": null
}
```

`requests_per_minute`, `errors_per_minute` y `latency_p95` se calculan agregando los datos de Telescope (`EntryModel::where('type', 'request')`). El frontend tiene una página `MonitoringPage` que consume este endpoint y lo renderiza con gráficas de `recharts`.

#### Resiliencia

El controlador de stats está diseñado para **no caer** aunque la base de datos esté inaccesible: cada bloque (`getDatabaseStatus`, `getGameStats`, etc.) está envuelto en try/catch y devuelve un fallback seguro. Esto permite que el endpoint sirva como *health check* externo.

---

## 5. Arquitectura del Frontend

### 5.1. Organización por features

El frontend se organiza siguiendo el patrón **feature-based**: cada funcionalidad de negocio tiene su propia carpeta dentro de `src/features/` que contiene **todo lo relacionado con ella**: componentes, hooks, slices, lógica.

```
src/
├── pages/                  # Vistas de nivel superior (lazy-loaded)
│   ├── LoginPage.jsx
│   ├── DashboardPage.jsx
│   ├── GameBoardPage.jsx
│   └── MonitoringPage.jsx
│
├── features/               # Funcionalidades de negocio
│   ├── auth/
│   │   ├── authSlice.js
│   │   ├── useAuth.js
│   │   ├── Login.jsx
│   │   └── Register.jsx
│   ├── game/
│   │   ├── gameSlice.js
│   │   ├── useVoting.js
│   │   ├── useTechTree.js
│   │   ├── VotingPanel.jsx
│   │   └── TechTreeModal.jsx
│   ├── board/
│   │   ├── boardSlice.js
│   │   ├── useBoard.js
│   │   └── BoardGrid.jsx
│   ├── inventory/
│   │   ├── inventorySlice.js
│   │   └── InventoryPanel.jsx
│   └── dashboard/
│       └── Dashboard.jsx
│
├── components/             # Componentes UI reutilizables (sin lógica)
│   ├── ui/
│   │   ├── Button.jsx
│   │   ├── Card.jsx
│   │   └── Modal.jsx
│   └── layout/
│       └── TopBar.jsx
│
├── services/               # Llamadas a la API
│   ├── authService.js
│   ├── gameService.js
│   └── bressoliumApi.js    # API de RTK Query
│
├── lib/                    # Utilidades transversales
│   └── httpClient.js       # Axios configurado
│
├── contexts/               # React Contexts
│   └── ToastContext.jsx
│
├── routes/                 # Configuración de routing
│   ├── AppRoutes.jsx
│   └── ProtectedRoute.jsx
│
└── store.js                # Configuración de Redux
```

**Ventaja del feature-based:** Cuando hay que tocar la votación, todo lo relevante está en `features/game/` — slice, hook y componentes. No hay que saltar entre carpetas (componentes, reducers, services) para entender una funcionalidad.

---

### 5.2. Pages, Features y Componentes UI

Hay tres tipos de componentes y nunca se confunden entre sí:

| Tipo                  | Carpeta            | Responsabilidad                                                     | Tiene lógica de negocio | Tiene estado Redux |
| --------------------- | ------------------ | ------------------------------------------------------------------- | ----------------------- | ------------------ |
| **Page**              | `src/pages/`       | Compone una pantalla completa a partir de features                  | No                      | No                 |
| **Feature component** | `src/features/.../`| Implementa una funcionalidad de negocio concreta                    | Sí                      | Sí                 |
| **UI component**      | `src/components/ui/`| Pieza visual reutilizable (botón, card, modal). Solo recibe props. | No                      | No                 |

**Ejemplo de UI component** — `Button.jsx`:

```jsx
const VARIANT_CLASSES = {
    primary:   'bg-bgreen hover:bg-[#3b7864] text-white',
    danger:    'bg-bred hover:bg-[#b84633] text-white',
    secondary: 'bg-bbrown hover:bg-[#6e5b44] text-white',
};

function Button({ children, variant = 'primary', disabled = false, onClick, type = 'button', className = '', style }) {
    return (
        <button
            type={type}
            disabled={disabled}
            onClick={onClick}
            style={style}
            className={`w-full flex justify-center py-4 px-6 text-base font-bold transition-colors
                disabled:opacity-50 disabled:cursor-not-allowed
                ${VARIANT_CLASSES[variant] ?? VARIANT_CLASSES.primary}
                ${className}`}
        >
            {children}
        </button>
    );
}
```

Este Button **no sabe nada del juego**: solo recibe props y se renderiza. Por eso es reutilizable en login, dashboard, votación, etc.

---

### 5.3. Routing y lazy loading

Cada página se carga **bajo demanda** mediante `React.lazy()` + `<Suspense>`. Esto reduce el bundle inicial: el código de `MonitoringPage` no se descarga hasta que el usuario entra en `/monitoring`.

**Ejemplo real** — `src/routes/AppRoutes.jsx`:

```jsx
import React, { lazy, Suspense } from 'react';
import { Routes, Route, Navigate, Outlet } from 'react-router-dom';
import ProtectedRoute from './ProtectedRoute';
import TopBar from '../components/layout/TopBar';

const LoginPage      = lazy(() => import('../pages/LoginPage'));
const RegisterPage   = lazy(() => import('../pages/RegisterPage'));
const DashboardPage  = lazy(() => import('../pages/DashboardPage'));
const GameBoardPage  = lazy(() => import('../pages/GameBoardPage'));
const MonitoringPage = lazy(() => import('../pages/MonitoringPage'));

function AppRoutes() {
    return (
        <Suspense fallback={<div style={{ padding: '2rem', textAlign: 'center' }}>Cargando…</div>}>
            <Routes>
                <Route path="/" element={<Navigate to="/login" replace />} />
                <Route path="/login"    element={<LoginPage />} />
                <Route path="/register" element={<RegisterPage />} />

                <Route element={<ProtectedRoute />}>
                    <Route element={<ProtectedLayout />}>
                        <Route path="/dashboard"  element={<DashboardPage />} />
                        <Route path="/board"      element={<GameBoardPage />} />
                        <Route path="/monitoring" element={<MonitoringPage />} />
                    </Route>
                </Route>
            </Routes>
        </Suspense>
    );
}
```

**`<ProtectedRoute>`** comprueba que el usuario está autenticado (token válido en `localStorage`). Si no, redirige a `/login`. Esto evita que cualquier ruta privada se renderice sin autenticación.

---

## 6. Gestión del estado en Frontend

Bressolium combina **dos sistemas de estado** porque cada uno resuelve un problema distinto:

| Sistema           | Para qué sirve                                                |
| ----------------- | ------------------------------------------------------------- |
| **Redux Toolkit** (slices) | Estado **propio del cliente**: UI, sesión, selección, etc.    |
| **RTK Query**     | Estado **del servidor cacheado**: tablero, sync, votos, etc.  |

La regla general:

> Si el dato vive **en la base de datos** y necesita cache automática y sincronización en tiempo real, va en RTK Query.  
> Si el dato vive **solo en el navegador** (usuario logueado, modal abierto, casilla seleccionada), va en un slice de Redux.

**Excepción histórica — `gameSlice`**: el Dashboard se construyó antes de adoptar RTK Query y usa `createAsyncThunk` con `gameService` (fetch directo). Los datos de lobby (`availableGames`, `myGames`) son datos del servidor gestionados en Redux, no en RTK Query. Esta excepción se mantiene porque el Dashboard solo necesita refrescar datos en momentos concretos (al cargar la página, al unirse/abandonar), no polling continuo.

---

### 6.1. Redux Toolkit (slices) — estado de UI

Los slices viven en `src/features/<feature>/<feature>Slice.js` y se ensamblan en `src/store.js`.

**Ejemplo real** — `store.js`:

```javascript
import { configureStore } from '@reduxjs/toolkit';
import authReducer       from './features/auth/authSlice';
import gameReducer       from './features/game/gameSlice';
import boardReducer      from './features/board/boardSlice';
import inventoryReducer  from './features/inventory/inventorySlice';
import { bressoliumApi } from './services/bressoliumApi';

export const store = configureStore({
    reducer: {
        auth:      authReducer,
        game:      gameReducer,
        board:     boardReducer,
        inventory: inventoryReducer,
        [bressoliumApi.reducerPath]: bressoliumApi.reducer,
    },
    middleware: (getDefault) => getDefault().concat(bressoliumApi.middleware),
});
```

**Slice típico** — `authSlice.js` (resumido):

```javascript
const initialState = {
    status: authService.getToken() ? 'LOGGED_IN' : 'IDLE',
    user:   null,
    error:  null,
};

const authSlice = createSlice({
    name: 'auth',
    initialState,
    reducers: {
        logout: (state) => {
            state.status = 'IDLE';
            state.user   = null;
            state.error  = null;
            authService.logout();
        },
        clearError: (state) => { state.error = null; },
    },
    extraReducers: (builder) => {
        builder
            .addCase(loginThunk.pending,   (state) => { state.status = 'LOADING'; })
            .addCase(loginThunk.fulfilled, (state, action) => {
                state.status = 'LOGGED_IN';
                state.user   = action.payload.user;
            })
            .addCase(loginThunk.rejected,  (state, action) => {
                state.status = 'ERROR';
                state.error  = action.payload;
            });
    },
});

export const { logout, clearError } = authSlice.actions;
export default authSlice.reducer;
```

El estado `status` puede ser `'IDLE' | 'LOADING' | 'LOGGED_IN' | 'ERROR'`. Las páginas leen este estado para saber qué renderizar (formulario, spinner, dashboard, mensaje de error).

#### Resumen de slices y su rol

| Slice           | Qué contiene                                                        | Patrón              |
| --------------- | ------------------------------------------------------------------- | ------------------- |
| `authSlice`     | `status`, `user`, `error` de sesión                                 | UI + thunks de auth |
| `gameSlice`     | `availableGames`, `myGames`, `currentGame` (partida activa), `error`| Thunks + `gameService` (excepción histórica, no RTK Query) |
| `boardSlice`    | `pendingTileId` — qué casilla está esperando respuesta (spinner)    | UI pura, sin fetch  |
| `inventorySlice`| Legacy: tenía `materials`/`inventions`; ahora `useInventory` lee directo de RTK Query. El slice queda en el store pero sus acciones ya no se despachan. | Legado              |

**`currentGame` en `gameSlice`** merece atención especial: al navegar al tablero, el Dashboard despacha `setCurrentGame(game)` que escribe la partida en `localStorage`. Al volver a cargar la página, `gameSlice` la recupera de `localStorage` en el `initialState`, permitiendo que el usuario continúe sin tener que seleccionar la partida de nuevo. Al abandonar una partida, `leaveGameThunk` limpia también `localStorage`.

---

### 6.2. RTK Query — caché de servidor

RTK Query genera **automáticamente** hooks tipo `useGetBoardQuery`, `useExploreTileMutation`, etc. a partir de una definición declarativa de los endpoints. Estos hooks gestionan:

- Caché por argumentos (cada `gameId` se cachea por separado).
- Loading / error states.
- Polling (refetch periódico).
- Invalidación de tags al ejecutar mutaciones.

**Ejemplo real** — `src/services/bressoliumApi.js`:

```javascript
import { createApi } from '@reduxjs/toolkit/query/react';
import httpClient from '../lib/httpClient';

const axiosBaseQuery = () => async ({ url, method = 'GET', data }) => {
    try {
        const result = await httpClient({ url, method, data });
        return { data: result.data };
    } catch (err) {
        return {
            error: {
                status: err.response?.status,
                data:   err.response?.data ?? err.message,
            },
        };
    }
};

export const bressoliumApi = createApi({
    reducerPath: 'bressoliumApi',
    baseQuery:   axiosBaseQuery(),
    tagTypes:    ['Board', 'Sync'],
    endpoints: (builder) => ({

        getBoard: builder.query({
            query: (gameId) => ({ url: `/board/${gameId}` }),
            providesTags: ['Board'],
            // Normaliza tanto la respuesta real { success, data: [] } como la forma de tests { tiles: [] }
            transformResponse: (response) => {
                if (Array.isArray(response?.tiles)) return response;
                return { tiles: response.data ?? response };
            },
        }),

        exploreTile: builder.mutation({
            query: (tileId) => ({ url: `/tiles/${tileId}/explore`, method: 'POST' }),
            invalidatesTags: ['Board', 'Sync'],
        }),

        upgradeTile: builder.mutation({
            query: (tileId) => ({ url: `/tiles/${tileId}/upgrade`, method: 'POST' }),
            invalidatesTags: ['Board', 'Sync'],
        }),

        getSync: builder.query({
            query: (gameId) => ({ url: `/game/${gameId}/sync` }),
            providesTags: ['Sync'],
            // Desenvuelve el wrapper { success, data: {...} } de la API
            transformResponse: (response) => response.data ?? response,
        }),

        vote: builder.mutation({
            query: ({ gameId, ...body }) => ({
                url:    `/game/${gameId}/vote`,
                method: 'POST',
                data:   body,
            }),
            // Sin invalidatesTags: el feedback visual del voto se gestiona con estado local
            // en useVoting para evitar un refetch completo tras cada voto parcial.
        }),

        closeRound: builder.mutation({
            query: (gameId) => ({
                url:    `/game/${gameId}/close-round`,
                method: 'POST',
            }),
            invalidatesTags: ['Sync'],
        }),
    }),
});

export const {
    useGetBoardQuery,
    useExploreTileMutation,
    useUpgradeTileMutation,
    useGetSyncQuery,
    useVoteMutation,
    useCloseRoundMutation,
} = bressoliumApi;
```

**Cómo funcionan los `tagTypes`:**

- `getBoard` y `getSync` declaran que **proveen** los tags `Board` y `Sync`. Sus respuestas se cachean asociadas a esos tags.
- `exploreTile` y `upgradeTile` invalidan `['Board', 'Sync']`: el tablero y el estado del jugador se recargan automáticamente tras cada acción.
- `closeRound` invalida `['Sync']`: al cerrarse la jornada, todos los consumidores de `/sync` reciben datos frescos.
- `vote` **no invalida ningún tag**: el feedback visual del voto se gestiona con estado local en `useVoting` (ver §6.3). Esto evita un refetch completo tras cada voto parcial y permite que el hook detecte el cierre de jornada con polling acelerado.

Es decir, después de explorar una casilla, el tablero se refresca solo. El caso del voto es más sutil: el hook mantiene en local state qué categorías se han votado y solo dispara polling intensivo cuando el jugador pulsa "Finalizar Turno".

---

### 6.3. Hooks customizados por feature

Para no exponer directamente los hooks de RTK Query a los componentes, se construye una capa intermedia: **hooks por feature** que encapsulan la lógica específica.

**Ejemplo real** — `src/features/game/useVoting.js`:

```javascript
import { useState, useEffect } from 'react';
import { bressoliumApi } from '../../services/bressoliumApi';

export function useVoting(gameId) {
    // Estado local por categoría de voto para feedback inmediato sin esperar refetch
    const [votedTechRound, setVotedTechRound] = useState(null);
    const [votedInvRound,  setVotedInvRound]  = useState(null);
    const [votedName,      setVotedName]      = useState(null);
    const [finishedRound,  setFinishedRound]  = useState(null);

    const { data, isLoading, refetch } = bressoliumApi.useGetSyncQuery(gameId, {
        skip:                      !gameId,
        pollingInterval:           30000,
        refetchOnMountOrArgChange: true,
        refetchOnFocus:            true,
    });
    const [voteMutation]                                 = bressoliumApi.useVoteMutation();
    const [closeRoundMutation, { isLoading: isClosing }] = bressoliumApi.useCloseRoundMutation();

    const currentRound    = data?.current_round ?? null;
    const lastRoundResult = data?.last_round_result ?? null;
    const gameStatus      = data?.game_status ?? null;
    const playersCount    = data?.players_count ?? 1;

    // Resetear flags locales cuando llega una nueva jornada del servidor
    useEffect(() => {
        if (currentRound?.number > (votedTechRound ?? 0)) setVotedTechRound(null);
        if (currentRound?.number > (votedInvRound  ?? 0)) setVotedInvRound(null);
        if (currentRound?.number > (finishedRound  ?? 0)) setFinishedRound(null);
    }, [currentRound?.number]);

    // Combinar estado del servidor con estado local (optimistic UI)
    const hasVotedTech = (data?.has_voted_tech ?? false) || votedTechRound === currentRound?.number;
    const hasVotedInv  = (data?.has_voted_inv  ?? false) || votedInvRound  === currentRound?.number;
    const hasVoted     = hasVotedTech || hasVotedInv;
    const hasFinished  = (data?.has_finished   ?? false) || finishedRound  === currentRound?.number;

    // Polling acelerado (1 s) mientras esperamos que el servidor cierre la jornada
    const isWaiting = finishedRound !== null;
    useEffect(() => {
        if (!isWaiting || !gameId) return;
        refetch();
        const id = setInterval(refetch, 1000);
        return () => clearInterval(id);
    }, [isWaiting, gameId, refetch]);

    const technologies = (data?.progress?.technologies ?? []).map((t) => ({
        id:      t.id,
        name:    t.name,
        canVote: !t.is_active && (t.missing ?? []).length === 0,
        missing: t.missing ?? [],
    }));

    const inventions = (data?.progress?.inventions ?? []).map((i) => ({
        id:       i.id,
        name:     i.name,
        quantity: i.quantity,
        canVote:  (i.missing ?? []).length === 0,
        missing:  i.missing ?? [],
        costs:    i.costs ?? [],
    }));

    const userActions = data?.user_actions?.actions_spent ?? 0;

    async function vote(voteData, name = null) {
        const result = await voteMutation({ gameId, ...voteData });
        if (!result.error) {
            if (voteData.technology_id) {
                setVotedTechRound(currentRound?.number ?? null);
                setVotedName(name);
            }
            if (voteData.invention_id) {
                setVotedInvRound(currentRound?.number ?? null);
            }
        }
        return result;
    }

    async function closeRound() {
        const result = await closeRoundMutation(gameId);
        if (!result.error) {
            setFinishedRound(currentRound?.number ?? null);
            refetch();
        }
        return result;
    }

    return {
        technologies, inventions, userActions, currentRound, lastRoundResult,
        gameStatus, playersCount, isLoading, isClosing,
        hasVoted, hasVotedTech, hasVotedInv, hasFinished, votedName,
        vote, closeRound,
    };
}
```

**Puntos clave del hook:**

- **Estado local optimista**: el hook guarda en local state qué categorías ha votado el jugador en qué jornada (`votedTechRound`, `votedInvRound`). Esto actualiza la UI inmediatamente sin esperar el siguiente poll de 30 s.
- **Polling acelerado**: cuando el jugador pulsa "Finalizar Turno" (`finishedRound` se establece), el hook activa un `setInterval` de 1 s para detectar cuanto antes que el servidor ha cerrado la jornada y actualizar la UI (nueva jornada, resultados).
- **Reset automático**: al llegar un `currentRound.number` mayor del servidor, los flags locales se limpian para la nueva jornada.
- **Sin `abstain()`**: el backend exige votar al menos en una categoría; la abstención no está implementada.

**El componente que lo consume queda muy limpio:**

```jsx
function VotingPanel({ gameId }) {
    const {
        technologies, inventions, currentRound,
        hasVotedTech, hasVotedInv, hasFinished,
        vote, closeRound, isClosing,
    } = useVoting(gameId);

    return (
        <div>
            {technologies.map(tech => (
                <VoteItem
                    key={tech.id}
                    name={tech.name}
                    canVote={!hasVotedTech && tech.canVote}
                    onClick={() => vote({ technology_id: tech.id }, tech.name)}
                />
            ))}
            {inventions.map(inv => (
                <VoteItem
                    key={inv.id}
                    name={inv.name}
                    canVote={!hasVotedInv && inv.canVote}
                    onClick={() => vote({ invention_id: inv.id })}
                />
            ))}
            <Button onClick={closeRound} disabled={isClosing || hasFinished}>
                {hasFinished ? 'Esperando jugadores...' : 'Finalizar Turno'}
            </Button>
        </div>
    );
}
```

`VotingPanel` no sabe que detrás hay polling a 30 s que se acelera a 1 s, ni estado local optimista. Solo sabe si puede votar tech, si puede votar invento, y si ya ha terminado su turno.

**`useInventory`** sigue el mismo patrón pero más sencillo: consume el mismo `useGetSyncQuery` (RTK Query deduplica la petición) y extrae los datos del inventario. Filtra tecnologías para mostrar **solo las ya activas** (el panel de inventario muestra lo que se *tiene*, no lo que se puede votar):

```javascript
export function useInventory(gameId) {
    const { data, isLoading } = bressoliumApi.useGetSyncQuery(gameId, {
        skip:                      !gameId,
        pollingInterval:           30000,
        refetchOnMountOrArgChange: true,
        refetchOnFocus:            true,
    });
    const materials    = data?.inventory                                          ?? [];
    const inventions   = data?.progress?.inventions                               ?? [];
    const technologies = (data?.progress?.technologies ?? []).filter(t => t.is_active);
    return { materials, inventions, technologies, isLoading };
}
```

---

### 6.4. Cliente HTTP centralizado

Toda llamada HTTP del frontend pasa por un único cliente `httpClient.js`, que se encarga de:

- Añadir el token Bearer en cada request.
- Limpiar el token y redirigir si recibimos un 401.
- Configurar la URL base.

**Ejemplo real** — `src/lib/httpClient.js`:

```javascript
import axios from 'axios';

const httpClient = axios.create({
    baseURL: import.meta.env.VITE_API_URL ?? 'http://localhost/api/v1',
});

httpClient.defaults.headers.common['Accept']       = 'application/json';
httpClient.defaults.headers.common['Content-Type'] = 'application/json';

httpClient.interceptors.request.use((config) => {
    const token = localStorage.getItem('auth_token');
    if (token) {
        config.headers['Authorization'] = `Bearer ${token}`;
    }
    return config;
});

httpClient.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 401) {
            localStorage.removeItem('auth_token');
        }
        return Promise.reject(error);
    }
);

export default httpClient;
```

Si en el futuro hay que cambiar la URL base, refrescar tokens, añadir un header de telemetría, etc., **se hace en un único sitio** y afecta a todas las llamadas.

---

### 6.5. Sincronización con polling

Bressolium es un juego **multijugador colaborativo**, así que cuando un jugador explora una casilla o vota, el resto deben verlo. La forma más simple de sincronizar a todos es que cada cliente **pregunte cada cierto tiempo** al servidor por el estado actualizado. Esto se llama *polling*.

#### Cómo se implementa

RTK Query soporta polling de forma nativa con la opción `pollingInterval`. Los hooks de feature lo activan al consumir queries:

```javascript
// useVoting.js
const { data, isLoading } = bressoliumApi.useGetSyncQuery(gameId, {
    skip:                      !gameId,
    pollingInterval:           30000,   // pregunta cada 30 s
    refetchOnMountOrArgChange: true,    // recarga al montar el hook
    refetchOnFocus:            true,    // recarga al volver a la pestaña
});
```

#### Endpoints con polling

| Hook                   | Endpoint                | Intervalo  |
| ---------------------- | ----------------------- | ---------- |
| `useVoting`            | `GET /game/{id}/sync`   | 30 s       |
| `useInventory`         | `GET /game/{id}/sync`   | 30 s       |

Como los dos hooks consumen el mismo endpoint, RTK Query **deduplica la petición**: el navegador solo hace UNA llamada cada 30 s, no dos.

#### Por qué polling y no WebSockets

Para el alcance del proyecto (turnos relativamente lentos, ≤ 5 jugadores por partida), el polling cada 30 s es **suficiente**:

- ✅ **Simple de implementar y depurar**: una request HTTP es trivial; un canal WebSocket requiere reverse proxy, autenticación específica, manejo de reconexión, etc.
- ✅ **Sin estado en servidor**: el backend no mantiene conexiones abiertas, lo que simplifica el escalado horizontal.
- ✅ **Compatible con la caché de servidor (§4.11)**: el TTL de `sync` (30 s) coincide con el `pollingInterval`, así que el coste de DB por jugador es ~1 query cada 30 s.
- ❌ **Latencia**: hasta 30 s para que un jugador vea el voto de otro. Aceptable en este contexto (las acciones se planean por jornadas, no en tiempo real).

Si el juego evolucionara hacia un modo de tiempo real (chat, eventos en directo, ticking de jornada visible), se sustituiría el polling por **Laravel Reverb** (WebSockets nativos de Laravel) o **Pusher**, sin tocar el resto de la arquitectura.

#### Sincronización inmediata tras una mutación

El polling cubre el caso *colaborativo* (que yo vea lo que han hecho otros). Para el caso *propio* (que yo vea inmediatamente lo que acabo de hacer) usamos **invalidación de tags de RTK Query** (§6.2):

```javascript
exploreTile: builder.mutation({
    query: (tileId) => ({ url: `/tiles/${tileId}/explore`, method: 'POST' }),
    invalidatesTags: ['Board', 'Sync'],
}),
```

Tras explorar una casilla, RTK Query invalida los tags `Board` y `Sync` y vuelve a hacer fetch automáticamente. El usuario ve el cambio en cuestión de milisegundos, sin esperar al siguiente ciclo de polling.

---

### 6.6. Manejo de errores hacia el usuario

Las acciones del juego pueden fallar por reglas del dominio (casilla no adyacente, materiales insuficientes, etc.). El backend devuelve estos errores como `{success: false, error: "..."}` con un código HTTP apropiado. El frontend los muestra al usuario mediante un sistema de **toasts** centralizado.

#### `ToastContext`

Provee un contexto React con un único `show(message, type)` y una pila de toasts que se autoeliminan tras 4 segundos.

```jsx
// src/contexts/ToastContext.jsx
const AUTO_DISMISS_MS = 4000;
const noopToast = { toasts: [], show: () => {}, dismiss: () => {} };

export function useToast() {
    return useContext(ToastContext) ?? noopToast;
}
```

> Nota técnica: `useToast()` devuelve un *noop* si no hay `ToastProvider` montado. Esto evita errores de renderizado en componentes de prueba o cuando se renderiza el componente fuera del árbol de la aplicación.

#### Cómo se usa desde un hook de feature

```jsx
// useBoard.js
const { show } = useToast();

async function exploreTile(tileId) {
    const result = await exploreTileMutation(tileId);

    if (result.error) {
        const message = result.error.data?.error
            ?? result.error.data?.message
            ?? 'No se puede explorar esta casilla.';
        show(message, 'error');
        return;
    }

    show('Casilla explorada', 'success');
}
```

#### Tipos de toast

| Tipo       | Uso                                                            |
| ---------- | -------------------------------------------------------------- |
| `'info'`   | Información neutra ("Esperando al resto de jugadores")        |
| `'success'`| Confirmación de acción ("Voto registrado", "Tecnología activada") |
| `'error'`  | Errores del backend que el usuario debe corregir               |

El estilo (color, icono) se determina por el `type`. La pila se renderiza en posición fija en la esquina inferior-derecha con `z-index: 9999` para flotar sobre el resto de la UI.

#### Por qué un sistema centralizado

Antes de centralizar, cada componente que hacía mutaciones tenía que manejar manualmente el error: `if (error) alert(error.message)`. El problema es que las `alert()` son intrusivas y bloquean la UI, y duplicar la lógica en cada componente es frágil. Con el `ToastContext`:

- Hay un único punto donde se decide cómo se muestran los errores.
- Los componentes solo llaman `show(msg, 'error')`.
- Los toasts son **no bloqueantes**: el usuario puede seguir interactuando.

---

## 7. Convenciones de nomenclatura

### Backend

| Elemento                        | Convención                            | Ejemplo                          |
| ------------------------------- | ------------------------------------- | -------------------------------- |
| Controller                      | PascalCase + `Controller` suffix      | `TileController`                 |
| Service                         | PascalCase + `Service` suffix         | `ActionService`                  |
| Repository (interfaz)           | PascalCase + `RepositoryInterface`    | `TileRepositoryInterface`        |
| Repository (implementación)     | PascalCase + `Repository` suffix      | `TileRepository`                 |
| DTO                             | PascalCase + `DTO` suffix             | `ExploreActionDTO`               |
| FormRequest                     | PascalCase + `Request` suffix         | `ExploreActionRequest`           |
| Resource                        | PascalCase + `Resource` suffix        | `TileResource`                   |
| Policy                          | PascalCase + `Policy` suffix          | `TilePolicy`                     |
| Exception                       | PascalCase + `Exception` suffix       | `TileAlreadyExploredException`   |
| Event                           | PascalCase, verbo en pasado           | `TileExplored`, `VoteCast`       |
| Listener                        | PascalCase, verbo en presente         | `NotifyPlayersListener`          |
| Tabla DB                        | snake_case en plural                  | `tiles`, `games`, `tile_types`   |
| Columna DB                      | snake_case                            | `coord_x`, `explored_by_player_id` |
| Ruta API                        | kebab-case en URL, snake_case en JSON | `/game/{id}/close-round`         |

### Frontend

| Elemento                        | Convención                            | Ejemplo                          |
| ------------------------------- | ------------------------------------- | -------------------------------- |
| Componente                      | PascalCase                            | `VotingPanel`, `BoardGrid`       |
| Hook custom                     | camelCase + prefijo `use`             | `useVoting`, `useTechTree`       |
| Slice                           | camelCase + sufijo `Slice`            | `authSlice`, `gameSlice`         |
| Acción de slice                 | camelCase, verbo                      | `logout`, `clearError`           |
| Service                         | camelCase + sufijo `Service`          | `authService`, `gameService`     |
| Carpeta de feature              | camelCase                             | `auth/`, `game/`, `inventory/`   |
| Variable de RTK Query           | `use<Verbo><Recurso><Query/Mutation>` | `useGetBoardQuery`, `useVoteMutation` |

---

## 8. Testing

El proyecto sigue una estrategia de testing en **dos niveles**:

### Backend (Pest)
- **Tests unitarios** (`tests/Unit/`): testean Services con repositorios *mockeados* (Mockery). No tocan la base de datos.
- **Tests de feature** (`tests/Feature/`): testean endpoints HTTP completos contra una base de datos de testing (vía `RefreshDatabase`).

**Ejemplo de test unitario:**

```php
test('explore: lanza TileAlreadyExploredException si la casilla ya está explorada', function () {
    $tile = mockTileObj('game-1', explored: true);

    $repo = Mockery::mock(TileRepositoryInterface::class);
    $repo->shouldReceive('find')->andReturn($tile);
    $repo->shouldReceive('isUserInGame')->andReturn(true);
    $repo->shouldReceive('getCurrentRound')->andReturn(Mockery::mock(Round::class));
    $repo->shouldReceive('getActionsSpent')->andReturn(0);

    $dto = new ExploreActionDTO(tileId: 'tile-1', userId: 'user-1');

    expect(fn () => makeAction($repo)->explore($dto))
        ->toThrow(TileAlreadyExploredException::class);
});
```

### Frontend (Vitest + React Testing Library)
- Tests de componentes con `render()` + `screen`, mockeando los servicios HTTP y las APIs de RTK Query con `vi.mock`.

### Cifras actuales
- **Backend**: 510 tests pasando, 0 fallos.
- **Frontend**: 312 tests pasando, 0 fallos.

---

## 9. Despliegue y CI/CD

### Entorno local
- **Laravel Sail** levanta MySQL + PHP 8.4 + Redis en contenedores Docker (`docker compose up -d`).
- **Vite dev server** sirve el frontend en `localhost:5173` con hot-reload.

### Pipeline de CI (GitHub Actions)
En cada push se ejecuta automáticamente:
1. Instalación de dependencias backend (`composer install`) y frontend (`npm ci`).
2. Ejecución de la suite de tests backend (Pest) en un MySQL de test.
3. Ejecución de la suite de tests frontend (Vitest).
4. Linting básico.

Si algún paso falla, el PR no puede mergearse a `main`.

### Producción (despliegue futuro)
- Backend: imagen Docker servida tras Nginx, con Laravel Octane opcional.
- Frontend: build estático (`npm run build`) servido desde un CDN o un Nginx.
- Base de datos: instancia gestionada de MySQL.

---

## 10. Justificación de decisiones técnicas

### ¿Por qué Controller → Service → Repository en lugar de un fat controller?

En proyectos pequeños es tentador meter toda la lógica en el Controller. Lo descartamos por tres razones:

1. **Testabilidad**. Un fat controller obliga a hacer tests HTTP completos para cubrir cualquier rama de lógica. Con Services aislados, podemos testear toda la lógica con mocks, sin levantar la aplicación.
2. **Reutilización**. La lógica de "explorar una casilla" se invoca desde el endpoint HTTP, pero también podría ejecutarse desde un comando Artisan, un Job en cola o un test de feature. Con un Service, una sola implementación sirve para todo.
3. **Claridad de las capas**. Cuando alguien nuevo entra al proyecto, sabe exactamente dónde buscar: si es un problema de validación de input, en `Requests/`; si es lógica de negocio, en `Services/`; si es una query, en `Repositories/`.

### ¿Por qué interfaces de repositorio en lugar de usar Eloquent directamente?

Trabajar con `Tile::find($id)` directamente desde el Service nos acoplaría a Eloquent. Las interfaces nos permiten:

1. **Testear sin base de datos**. En los tests unitarios mockeamos `TileRepositoryInterface` y devolvemos los datos que queramos, sin levantar MySQL.
2. **Sustituir la implementación**. Si en el futuro ciertos datos vinieran de una API externa o de Redis, basta con escribir una nueva implementación de la interfaz.
3. **Documentación contractual**. La interfaz es la documentación canónica de qué operaciones de datos existen para esa entidad. No hay que adivinar leyendo queries dispersas.

### ¿Por qué Redux + RTK Query y no solo Redux?

Antes de adoptar RTK Query, los datos del servidor se traían con `createAsyncThunk` y se almacenaban en slices. Esto generaba mucho código repetitivo: estados `loading/data/error` para cada fetch, lógica manual para invalidar al hacer una mutación, polling implementado a mano…

RTK Query elimina todo eso:
- La caché por argumentos viene de serie.
- Los tags + `invalidatesTags` resuelven la sincronización automática tras una mutación.
- El polling se activa pasando `pollingInterval`.

Mantenemos slices solo para **estado de UI** (autenticación, modales, selecciones). El servidor lo gestiona RTK Query.

### ¿Por qué organizar por features y no por tipo (components/, hooks/, slices/)?

La organización por tipo (todos los componentes en `components/`, todos los hooks en `hooks/`, etc.) escala mal: cada cambio de feature obliga a tocar muchas carpetas. Con la organización por feature, **una funcionalidad concreta vive entera en una sola carpeta**, lo que facilita encontrar, entender y mover código.

### ¿Por qué un cliente HTTP centralizado y no usar fetch/axios directamente?

Tener un único punto de entrada para llamadas HTTP nos permite:
- Inyectar el token Bearer de forma transparente.
- Manejar respuestas 401 globalmente (logout automático).
- Cambiar la URL base con una sola variable de entorno.
- Añadir headers de telemetría, retry policies o middleware de logging en un solo sitio.

---

## Anexo: Estructura de carpetas completa

### Backend
```
backend/BressoliumProject/
├── app/
│   ├── Console/
│   ├── DTOs/
│   ├── Events/
│   ├── Exceptions/
│   ├── Http/
│   │   ├── Controllers/Api/
│   │   ├── Middleware/
│   │   ├── Requests/
│   │   └── Resources/
│   ├── Jobs/
│   ├── Listeners/
│   ├── Models/
│   ├── Policies/
│   ├── Providers/
│   ├── Repositories/
│   │   ├── Contracts/
│   │   └── Eloquent/
│   ├── Services/
│   └── Support/
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
├── routes/
│   ├── api.php
│   ├── web.php
│   └── console.php
└── tests/
    ├── Feature/
    └── Unit/
```

### Frontend
```
frontend/bressolium-front/src/
├── assets/
├── components/
│   ├── layout/
│   └── ui/
├── contexts/
├── features/
│   ├── auth/
│   ├── board/
│   ├── dashboard/
│   ├── game/
│   └── inventory/
├── lib/
├── pages/
├── routes/
├── services/
├── App.jsx
├── main.jsx
└── store.js
```

---

*Documento elaborado como parte de la Tarea T35 — Documentación de Arquitectura.*
