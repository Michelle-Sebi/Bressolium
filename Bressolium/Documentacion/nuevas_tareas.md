# Product Backlog MVP - Bressolium (Revisado)

Revisión de `raw_tareas.md` que incorpora:
- Estado actualizado de tareas completadas (T1–T9)
- Adaptación de tareas pendientes (T10, T12, T13, T15, T16, T19) a los nuevos requisitos arquitectónicos y de mecánica
- Nuevas tareas arquitectónicas (T25–T37) exigidas por la guía del módulo

Las tareas T1–T9 y T11, T17, T18, T23, T24 se mantienen sin modificación respecto a `raw_tareas.md`.

---

## 👤 Épica 1: Gestión de Usuarios y Equipos

### Tarea 1 [TERMINADA]
- **Título**: `[Feat] Migrations and Base Models (Relational V4)`
- **Estimación**: S
- **Área**: [BASE DE DATOS]
- **Asignado a**: Bárbara
- **Descripción**: Crear las migraciones y modelos base con **UUID como PK**: users, games, rounds, votes, etc.

### Tarea 2 [TERMINADA]
- **Título**: `[Feat] API Authentication Setup with Sanctum`
- **Estimación**: M
- **Área**: [BACKEND]
- **Asignado a**: Michelle
- **Descripción**: Configuración de Sanctum y endpoints `/api/register` y `/api/login`. Estándar `{success, data, error}`.

### Tarea 3 [TERMINADA]
- **Título**: `[Feat] Frontend Structure, Auth Routing and Redux`
- **Estimación**: L
- **Área**: [FRONTEND]
- **Asignado a**: Bárbara
- **Descripción**: Inicializar Vite, React Router, Redux Toolkit y vistas de Auth con diseño Brutalista.

### Tarea 4 [TERMINADA]
- **Título**: `[Feat] CRUD Endpoints for Teams and 1st Round Creation`
- **Estimación**: M
- **Área**: [BACKEND]
- **Asignado a**: Michelle
- **Descripción**: Endpoints para crear equipo y generación automática de la primera ronda.

### Tarea 20 [TERMINADA]
- **Título**: `[Refactor] Mover lógica de Auth y Teams a Servicios y Repositorios`
- **Estimación**: S
- **Área**: [BACKEND]
- **Asignado a**: Bárbara
- **Descripción**: Extraer la lógica escrita en los controladores creados en las Tareas 2 y 4 para adaptarla al patrón Controller -> Service -> Repository. Los tests no deberían romperse.

### Tarea 5 [TERMINADA]
- **Título**: `[Feat] Game Lobby & Team Manager UI`
- **Estimación**: M
- **Área**: [FRONTEND]
- **Asignado a**: Bárbara
- **HUs**: 1.2, 1.3, 1.4, 1.5, 1.7
- **Descripción**: Portal principal con diseño de bloques sólidos. Sección para buscar (lista), unirse aleatorio o crear equipo (modal civilización). Listado lateral de partidas activas del usuario.

### Tarea 17 [TERMINADA]
- **Título**: `[Feat] Global TopBar & Session Navigation`
- **Estimación**: S
- **Área**: [FRONTEND]
- **Asignado a**: Michelle
- **HUs**: 1.8
- **Descripción**: Barra superior persistente con nombre de usuario, logout, nombre del equipo actual y selector rápido de partidas (Quick Switcher).

---

## 🗺️ Épica 2: El Tablero y la Exploración

### Tarea 6 [TERMINADA]
- **Título**: `[Feat] Tile Migrations and Base Dictionary`
- **Estimación**: S
- **Área**: [BASE DE DATOS]
- **Asignado a**: Michelle
- **Descripción**: Migraciones para tiles, tile_types y producción de materiales inicial.

### Tarea 21 [TERMINADA]
- **Título**: `[Refactor] DB Migration V5a: Tile Schema Correction`
- **Estimación**: M
- **Área**: [BASE DE DATOS]
- **Asignado a**: Michelle
- **Bloqueado por**: Tarea 6
- **Descripción**: Corregir el schema de casillas para alinearlo con el diseño de 5 niveles. Añadir columna `base_type` (enum: bosque, cantera, rio, prado, mina, pueblo) a `tile_types`: es la clave para identificar la familia del terreno con independencia del nivel o del nombre visible. El campo `name` en `tile_types` almacena el nombre de presentación, que en nivel 5 es el nombre especializado (ej: el registro bosque-lv5 tiene `base_type=bosque` y `name="Pozo de Goma y Resina"`). Añadir columnas `tech_required` e `invention_required` a `material_tile_type`. Añadir `explored_by_player_id` y `explored_at` a `tiles`. Añadir `tier` y `group` a `materials`. Añadir el tipo `pueblo` al catálogo. Los tests existentes no deben romperse (cambios aditivos).

### Tarea 7 [TERMINADA]
- **Título**: `[Feat] Board Generator and API Controller`
- **Estimación**: L
- **Área**: [BACKEND]
- **Asignado a**: Bárbara
- **Bloqueado por**: Tarea 21
- **Descripción**: Generación algorítmica de la matriz de tablero (15x15) aleatoria al crear equipo. Endpoint `GET /api/board`. (Implementar usando arquitectura Controller -> Service -> Repository).

### Tarea 8 [TERMINADA]
- **Título**: `[Feat] Individual Actions API (Explore / Upgrade)`
- **Estimación**: L
- **Área**: [BACKEND]
- **Asignado a**: Bárbara
- **Bloqueado por**: Tarea 7
- **Descripción**: Endpoints POST para realizar jugadas. Validación de acciones diarias en `round_user` y costes de materiales en `game_material`. (Implementar usando arquitectura Controller -> Service -> Repository).

### Tarea 9 [TERMINADA]
- **Título**: `[Feat] Board Grid Component and Frontend Visualization`
- **Estimación**: XL
- **Área**: [FRONTEND]
- **Asignado a**: Michelle
- **Bloqueado por**: Tarea 7, Tarea 23
- **HUs**: 2.1, 2.2, 2.6
- **Descripción**: Componente central de mapa (CSS Grid). Renderizado de casillas descubiertas y gestión de "niebla de guerra". Conexión con API de acciones.

### Tarea 18 [TERMINADA]
- **Título**: `[Feat] Material Inventory Side-Panel (SidePanel Izquierdo)`
- **Estimación**: S
- **Área**: [FRONTEND]
- **Asignado a**: Michelle
- **HUs**: 2.4, 2.7
- **Descripción**: Panel lateral de inventario. Iconos de materiales con Badges de cantidad. Estados activo/inactivo (opacidad) según descubrimiento.

### Tarea 50
- **Título**: `[Feat] Inventory Panel: Inventions Section`
- **Estimación**: S
- **Área**: [FRONTEND]
- **Asignado a**: Michelle
- **Bloqueado por**: Tarea 10, Tarea 48
- **HUs**: 2.4, 2.7
- **Descripción**: Extender el `inventorySlice` para añadir `inventions: InventoryInvention[]` (id, name, quantity, icon). Modificar `InventoryPanel.jsx` para dividir el panel en dos zonas claramente separadas: **"Recursos"** (existente, T18) y **"Inventos"** (nueva). Reutilizar la lógica de active/inactive según `quantity > 0`. Actualizar `Epica2_Front.test.jsx` con tests de la nueva sección. La hidratación se realiza desde el sync (T10).

---

## 🗳️ Épica 3: Mecánicas de Turno y Votos

### Tarea 10 ⚙️
- **Título**: `[Feat] Relational Sync and Polling`
- **Estimación**: M
- **Área**: [BACKEND] / [FRONTEND]
- **Asignado a**: Bárbara
- **Bloqueado por**: Tarea 8, Tarea 25, Tarea 26, Tarea 27, Tarea 28, Tarea 48
- **Descripción**: Endpoint `GET /api/game/sync` para hidratar el estado global de RTK (recursos, **inventos construidos con sus cantidades**, progreso tecnológico, rounds). Polling cada ~30s. Implementar siguiendo la arquitectura completa: Form Request, DTO, Service, Repository con interfaz, API Resource. La parte frontend del polling debe consumir la API a través del cliente HTTP centralizado (Tarea 30).

### Tarea 11
- **Título**: `[Feat] Progress Voting API (Relational)`
- **Estimación**: M
- **Área**: [BACKEND]
- **Asignado a**: Bárbara
- **Bloqueado por**: Tarea 10
- **Descripción**: Endpoint para insertar en `votes`. Acepta votos tanto a **tecnologías como a inventos** (la tabla `votes` ya soporta ambos vía `technology_id` o `invention_id` nullable). Validación de si el usuario ya votó y si el item ya está completado/investigado. Para inventos, considerar la cantidad acumulada del equipo, no solo presencia. (Implementar usando arquitectura Controller -> Service -> Repository).

### Tarea 12 ⚙️
- **Título**: `[Feat] Action & Decision Control Panel (SidePanel Derecho)`
- **Estimación**: L
- **Área**: [FRONTEND]
- **Asignado a**: Bárbara
- **Bloqueado por**: Tarea 11, Tarea 30
- **HUs**: 3.8
- **Descripción**: Panel de control de jornada con **dos zonas de votación claramente separadas**: una para **Tecnologías** y otra para **Inventos**. Cada zona muestra su propia lista de votables según stock y prerrequisitos cumplidos. Incluir contador visual de acciones, timer de fase y botón de finalizar turno. Los ítems con recursos suficientes se muestran activos; los ítems alcanzables en pocos pasos (faltan recursos o un prerrequisito previo) en gris con indicación de qué falta. Consumir la API a través del cliente HTTP centralizado (Tarea 30).

### Tarea 13 ⚙️
- **Título**: `[Feat] Schedule / Cron Round Close and Round Jump`
- **Estimación**: XL
- **Área**: [BACKEND]
- **Asignado a**: Michelle
- **Bloqueado por**: Tarea 11, Tarea 48
- **Descripción**: Job de Laravel para procesar el salto de turno: resuelve ganador de votos (de tecnología y de invento), aplica costes/recompensas, **incrementa la cantidad del invento construido en el inventario del equipo**, suma producción de materiales según las casillas explotadas por el equipo, y crea nueva Round. La validación de prerrequisitos compara **cantidades** acumuladas, no solo presencia. Resetear `actions_spent` en `round_user` para todos los jugadores de la partida al cerrar la jornada.

---

## 🌳 Épica 4: Tecnología y Meta

### Tarea 14 [TERMINADA]
- **Título**: `[Feat] Migrations and Relations for the Tech Process`
- **Estimación**: M
- **Área**: [BASE DE DATOS]
- **Asignado a**: Bárbara
- **Descripción**: Tablas de árbol tecnológico (technologies, inventions, recipes) y tablas de progreso por partida.

### Tarea 22 [TERMINADA]
- **Título**: `[Refactor] DB Migration V5b: Tech Tree Normalization`
- **Estimación**: M
- **Área**: [BASE DE DATOS]
- **Asignado a**: Bárbara
- **Bloqueado por**: Tarea 14
- **Descripción**: Normalizar el schema de tecnologías e inventos separando prerequisitos de costes. Crear `invention_prerequisites(invention_id, prereq_type ENUM[invention|technology], prereq_id)` y `technology_prerequisites(technology_id, prereq_type, prereq_id)`. Refactorizar `recipes` hacia `invention_costs(invention_id, resource_id, quantity)` donde los costes son siempre recursos de casilla, nunca invention_ids. Crear `technology_bonuses(technology_id, bonus_type, bonus_value, bonus_target)` e `invention_bonuses`. Crear `technology_unlocks` e `invention_unlocks` con `unlock_type ENUM[technology|invention|tile_level]`. Los tests existentes no deben romperse (cambios aditivos).

### Tarea 23 [TERMINADA]
- **Título**: `[Feat] Catalog Seeders: Complete Game Data`
- **Estimación**: L
- **Área**: [BASE DE DATOS]
- **Asignado a**: Michelle
- **Bloqueado por**: Tarea 21, Tarea 22
- **Descripción**: Poblar el catálogo completo del juego con seeders. Incluye: `ResourcesSeeder` (44 recursos con tier y group), `TileLevelResourcesSeeder` (5 tipos × 5 niveles con cantidades y requisitos de tech e invento), `TechnologiesSeeder` (31 tecnologías con prerequisitos, desbloqueos y bonificadores), `InventionsSeeder` (34 inventos con prerequisitos, costes, bonificadores y desbloqueos). Desbloquea la implementación verificable de las Tareas 7, 8, 11 y 13.

### Tarea 24 [TERMINADA]
- **Título**: `[Docs] Update ER Diagram to V5`
- **Estimación**: S
- **Área**: [DOCUMENTACIÓN]
- **Asignado a**: Michelle
- **Bloqueado por**: Tarea 21, Tarea 22
- **Descripción**: Actualizar el diagrama ER_v4.html a V5 reflejando todos los cambios de schema introducidos en las Tareas 21 y 22: nuevas tablas `invention_prerequisites`, `technology_prerequisites`, `invention_costs`, `technology_bonuses`, `invention_bonuses`, `technology_unlocks`, `invention_unlocks`, columna `base_type` en `tile_types`, y atributos `tier`, `group` en `materials`.

### Tarea 48
- **Título**: `[Refactor] DB Migration V6: Quantities in Inventions & Prerequisites`
- **Estimación**: M
- **Área**: [BASE DE DATOS]
- **Asignado a**: Bárbara
- **Bloqueado por**: Tarea 22
- **Descripción**: Migración aditiva V6: añadir columna `quantity int` a `invention_prerequisites` y `technology_prerequisites` (cuántos del invento/tech previo se requieren). Convertir el pivot many-to-many `game ↔ invention` en una tabla `game_inventions(id, game_id, invention_id, quantity)` análoga a `game_material`. Los tests existentes no deben romperse (cambios aditivos). Actualizar las relaciones de los modelos Eloquent.

### Tarea 49
- **Título**: `[Docs] Update ER Diagram to V6 + Evolución Tecnológica`
- **Estimación**: S
- **Área**: [DOCUMENTACIÓN]
- **Asignado a**: Michelle
- **Bloqueado por**: Tarea 48
- **Descripción**: Actualizar el diagrama ER (actualmente V5) a V6 reflejando: columna `quantity` en `invention_prerequisites` y `technology_prerequisites`, y la nueva tabla `game_inventions`. Actualizar `casillas.md` y `evolucion-tecnologias-e-inventos.md` con las cantidades requeridas en cada prerrequisito. Verificar que la referencia desde `global_rules.md` apunta a la versión correcta del diagrama.

### Tarea 38
- **Título**: `[Feat] Actualización de Seeders (Nuevos Items + Quantities)`
- **Estimación**: S
- **Área**: [BASE DE DATOS]
- **Asignado a**: Michelle
- **Bloqueado por**: Tarea 23, Tarea 48
- **Descripción**: Modificar `ResourcesSeeder`, `TechnologiesSeeder` e `InventionsSeeder` para reflejar (1) la eliminación de Caolinita/Peces y la adición de los nuevos materiales/tecnologías según el documento `casillas.md` y `evolucion-tecnologias-e-inventos.md` actualizado (44 recursos y 31 tecnologías), y (2) los nuevos campos de **cantidad** en prerrequisitos de inventos y tecnologías introducidos por T48.

### Tarea 19 ⚙️
- **Título**: `[Feat] Technology Tree & Progress Archive`
- **Estimación**: M
- **Área**: [FRONTEND]
- **Asignado a**: Michelle
- **Bloqueado por**: Tarea 23, Tarea 30, Tarea 48
- **HUs**: 4.1
- **Descripción**: Modal del árbol tecnológico del equipo. Mostrar investigaciones completadas, disponibles (recursos y prerrequisitos suficientes) y bloqueadas (indicando qué falta y en qué cantidad). Representar los múltiples caminos posibles hacia la tecnología final para que el equipo pueda planificar su estrategia. **El modal se abre al hacer click sobre la casilla central de tipo `pueblo` del tablero (ver Tarea 51).** Consumir la API a través del cliente HTTP centralizado (Tarea 30).

### Tarea 51
- **Título**: `[Feat] Pueblo Tile: Center Placement + Tech Tree Access`
- **Estimación**: M
- **Área**: [BACKEND] / [FRONTEND]
- **Asignado a**: Michelle
- **Bloqueado por**: Tarea 19, Tarea 44
- **Descripción**:
  - **Backend:** modificar `BoardGeneratorService` para garantizar que la casilla central `(7, 7)` del 15×15 sea siempre de `base_type=pueblo`. La casilla pueblo no puede explorarse ni mejorarse mediante acciones individuales.
  - **Frontend:** en `BoardGrid` (T9), detectar el click sobre la casilla pueblo y abrir el modal del árbol tecnológico (T19) en lugar de disparar `exploreTileThunk` o `upgradeTileThunk`. Aplicar estilo visual diferenciado siguiendo la guía brutalista. Actualizar tests de `Epica2_Front.test.jsx` para cubrir la apertura del modal desde la casilla central.

### Tarea 15 ⚙️
- **Título**: `[Feat] End of Game (Terraforming)`
- **Estimación**: S
- **Área**: [BACKEND]
- **Asignado a**: Michelle
- **Bloqueado por**: Tarea 13
- **Descripción**: Lógica para finalizar la partida al completar la tecnología final ("La Nave"). Cambio de estado a `FINISHED`. Notificar a todos los jugadores de la partida del resultado final.

### Tarea 16 ⚙️
- **Título**: `[Feat] Abandonment Management (Inactive Players Backend)`
- **Estimación**: S
- **Área**: [BACKEND]
- **Asignado a**: Michelle
- **Bloqueado por**: Tarea 13
- **Descripción**: Modificar resolución de turno para ignorar a jugadores offline (flag `is_afk`) sin bloquear el avance del equipo. El flag `is_afk` debe activarse automáticamente si un jugador no realiza ninguna acción durante una jornada completa.

---

## 🏛️ Épica 5: Calidad Arquitectónica

> Tareas derivadas de los requisitos de la guía del módulo (s4-proyecto.md). Deben completarse antes de implementar T10 en adelante para que el código nuevo nazca con los patrones correctos.

### Tarea 25
- **Título**: `[Refactor] Contracts, Interfaces y Service Providers`
- **Estimación**: M
- **Área**: [BACKEND]
- **Asignado a**: Bárbara
- **Descripción**: Crear `/Repositories/Contracts` con una interfaz por cada repositorio existente (Game, User, Round, Board, Tile). Mover las implementaciones actuales a `/Repositories/Eloquent`. Crear un `RepositoryServiceProvider` que registre los bindings interfaz→implementación en el IoC Container de Laravel. Actualizar todos los servicios que inyectan repositorios para que dependan de la interfaz, no de la clase concreta.

### Tarea 26
- **Título**: `[Refactor] Form Requests, Policies y Namespace de Controladores API`
- **Estimación**: M
- **Área**: [BACKEND]
- **Asignado a**: Michelle
- **Descripción**: Crear clases en `/Http/Requests` para validación de entrada en los endpoints existentes (register, login, create game, join game, explore, upgrade). Crear `/Http/Policies` para las reglas de autorización sobre recursos (acceso a partida, acciones sobre casillas). Mover todos los controladores a `/Http/Controllers/Api/` para separar espacio de nombres correctamente. Refactorizar controladores para delegar validación a Form Requests y autorización a Policies en lugar de hacerlo manualmente en servicios.

### Tarea 27
- **Título**: `[Refactor] DTOs y API Resources`
- **Estimación**: M
- **Área**: [BACKEND]
- **Asignado a**: Bárbara
- **Descripción**: Crear `/DTOs` con clases de transferencia de datos para los flujos principales (CreateGameDTO, JoinGameDTO, ExploreActionDTO, UpgradeActionDTO, SyncResponseDTO, VoteDTO). Crear `/Http/Resources` con API Resources para transformar modelos Eloquent antes de devolverlos al cliente (GameResource, TileResource, MaterialResource, RoundResource). Sustituir los retornos directos de modelos por sus Resources correspondientes en todos los controladores existentes.

### Tarea 28
- **Título**: `[Refactor] Excepciones Personalizadas y Handler Global`
- **Estimación**: S
- **Área**: [BACKEND]
- **Asignado a**: Michelle
- **Descripción**: Crear `/Exceptions` con clases de excepción de dominio (InsufficientMaterialsException, ActionLimitExceededException, TileAlreadyExploredException, TileNotExploredException, UserNotInGameException). Configurar el handler global en `bootstrap/app.php` para interceptar estas excepciones y convertirlas automáticamente en respuestas JSON con el código HTTP correcto. Refactorizar ActionService y demás servicios para lanzar excepciones en lugar de devolver arrays con status.

### Tarea 29
- **Título**: `[Feat] Tests Unitarios de Backend`
- **Estimación**: L
- **Área**: [BACKEND]
- **Asignado a**: Bárbara
- **Bloqueado por**: Tarea 25, Tarea 26, Tarea 27, Tarea 28
- **Descripción**: Crear tests en `/tests/Unit` para los servicios y repositorios de forma aislada, mockeando dependencias. Cubrir como mínimo: GameService, ActionService, y los repositorios principales. Los tests unitarios deben verificar lógica de negocio sin tocar la base de datos.

### Tarea 30
- **Título**: `[Feat] Cliente HTTP Centralizado con Interceptores`
- **Estimación**: S
- **Área**: [FRONTEND]
- **Asignado a**: Michelle
- **Descripción**: Crear en `/src/lib/` una instancia de Axios configurada como cliente HTTP centralizado. Incluir interceptores para: añadir automáticamente el token de autenticación en cada petición, manejar globalmente errores 401 (redirigir a login) y 500. Restructurar las llamadas API existentes para que pasen por este cliente. Mover las definiciones de llamadas API a las carpetas `/features/[nombre]/api/` de cada feature en lugar de `/services` global.

### Tarea 31
- **Título**: `[Refactor] Hooks por Feature`
- **Estimación**: M
- **Área**: [FRONTEND]
- **Asignado a**: Bárbara
- **Bloqueado por**: Tarea 30
- **Descripción**: Extraer la lógica de interacción con la API de los componentes existentes a hooks personalizados dentro de cada feature: `useAuth` (auth), `useGames` (game), `useBoard` (board), `useInventory` (inventory). Los componentes deben quedar como presentacionales puros que reciben datos y callbacks por props o desde el hook.

### Tarea 32
- **Título**: `[Feat] Tests de Frontend`
- **Estimación**: L
- **Área**: [FRONTEND]
- **Asignado a**: Michelle
- **Bloqueado por**: Tarea 30, Tarea 31
- **Descripción**: Configurar Vitest y React Testing Library. Escribir tests unitarios para los hooks principales (useAuth, useBoard, useInventory) y tests de componentes para las vistas críticas (Login, Register, Dashboard, BoardGrid). Mockear el cliente HTTP centralizado para que los tests no dependan de red.

### Tarea 33
- **Título**: `[Feat] CI/CD Pipeline`
- **Estimación**: M
- **Área**: [DEVOPS]
- **Asignado a**: Bárbara
- **Descripción**: Configurar GitHub Actions con un workflow que se ejecute en cada PR y push a `main`: instalación de dependencias de backend y frontend, ejecución de linters, ejecución de todos los tests de backend (Feature + Unit), build del frontend. El pipeline debe fallar si algún test falla o el build no compila.

### Tarea 34
- **Título**: `[Feat] Tests E2E`
- **Estimación**: L
- **Área**: [TESTING]
- **Asignado a**: Michelle
- **Bloqueado por**: Tarea 33
- **Descripción**: Configurar Playwright. Escribir pruebas de flujo completo que simulen el recorrido real del usuario a través del navegador: registro → login → crear partida → ver tablero → explorar casilla → ver inventario actualizado. Los tests E2E deben ejecutarse contra el entorno de desarrollo con Docker levantado.

### Tarea 35
- **Título**: `[Docs] Documentación de Arquitectura`
- **Estimación**: S
- **Área**: [DOCUMENTACIÓN]
- **Asignado a**: Bárbara
- **Descripción**: Redactar un documento de arquitectura que explique las decisiones técnicas del proyecto: justificación del patrón Controller→Service→Repository, uso de Contracts e IoC Container, estructura de features en el frontend, gestión del estado con Redux, y convenciones de nomenclatura. Debe poder usarse como referencia durante la presentación del proyecto.

### Tarea 36
- **Título**: `[Feat] Rate Limiting y Versionado de API`
- **Estimación**: XS
- **Área**: [BACKEND]
- **Asignado a**: Michelle
- **Descripción**: Configurar throttle middleware en las rutas API (por ejemplo 60 peticiones/minuto por usuario autenticado). Añadir prefijo `/api/v1/` a todas las rutas para implementar versionado. Actualizar el frontend y los tests para que apunten a la nueva URL base.

### Tarea 37
- **Título**: `[Feat] Cache Service`
- **Estimación**: S
- **Área**: [BACKEND]
- **Asignado a**: Bárbara
- **Bloqueado por**: Tarea 25
- **Descripción**: Crear un servicio de caché que centralice la lógica de guardar, recuperar e invalidar datos temporales. Aplicarlo inicialmente al endpoint de sync (Tarea 10) y al tablero (Tarea 7): cachear el estado del tablero por partida e invalidar la caché al ejecutar una acción de explorar o upgrade.

### Tarea 39
- **Título**: `[Feat] Eventos y Listeners de Dominio`
- **Estimación**: M
- **Área**: [BACKEND]
- **Asignado a**: Michelle
- **Descripción**: Crear `/app/Events` con eventos de dominio (`TileExplored`, `TileUpgraded`, `RoundClosed`, `MaterialsProduced`, `GameFinished`, `VoteCast`, `InventionBuilt`) y `/app/Listeners` desacoplados (notificación a jugadores, auditoría, log estructurado). Refactorizar `ActionService` y los jobs de T13 y T15 para que emitan eventos en lugar de ejecutar todo inline. Encolar los listeners costosos.

### Tarea 40
- **Título**: `[Refactor] Response Builder Centralizado`
- **Estimación**: S
- **Área**: [BACKEND]
- **Asignado a**: Bárbara
- **Descripción**: Extraer la lógica de respuestas estandarizadas a `app/Support/ResponseBuilder.php` con métodos `success(data, code)`, `error(message, code)`, `paginated(query)`. Refactorizar todos los controladores existentes (T2, T4, T7, T8) para que pasen por este builder en lugar de devolver arrays directamente desde `BaseController`.

### Tarea 41
- **Título**: `[Feat] Middleware Global (Force JSON + Request Logging)`
- **Estimación**: XS
- **Área**: [BACKEND]
- **Asignado a**: Michelle
- **Descripción**: Crear middleware global que fuerce `Accept: application/json` en peticiones API y registre cada petición (método, ruta, usuario, tiempo de respuesta, status) en log estructurado. Registrar en `bootstrap/app.php`. Distinguir explícitamente del middleware de ruta (Sanctum, throttle).

### Tarea 42
- **Título**: `[Feat] RTK Query / Server State Cache`
- **Estimación**: M
- **Área**: [FRONTEND]
- **Asignado a**: Bárbara
- **Bloqueado por**: Tarea 30
- **Descripción**: Añadir **RTK Query** al store Redux existente. Migrar progresivamente las llamadas API (board, inventory, sync, votes) para que usen `createApi` con caché automática y revalidación. Los `slices` quedan reservados para estado puramente de UI/cliente. Justifica documentalmente que cumple "librería de gestión de estado de servidor" exigida por la guía del módulo.

### Tarea 43
- **Título**: `[Refactor] Pages + Lazy Loading + Routes Centralizado`
- **Estimación**: M
- **Área**: [FRONTEND]
- **Asignado a**: Bárbara
- **Descripción**: Crear `/src/pages/` y extraer las vistas (`Login`, `Register`, `Dashboard`, `GameBoard`) desde `features/` como Pages independientes. Aplicar `React.lazy()` + `Suspense` para carga diferida. Crear `/src/routes/` extrayendo la configuración del router de `App.jsx`, con HOC `ProtectedRoute` para rutas autenticadas.

### Tarea 44
- **Título**: `[Feat] Contexts + UI Components Reutilizables`
- **Estimación**: S
- **Área**: [FRONTEND]
- **Asignado a**: Bárbara
- **Descripción**: Crear `/src/contexts/` con providers de baja frecuencia (`ThemeContext`, `ToastContext` para notificaciones globales). Crear `/src/components/ui/` con primitivos brutalistas reutilizables (`Button`, `Input`, `Modal`, `Toast`, `Badge`, `IconTile`) según `guia_estilos/`. Refactorizar componentes pendientes (T12, T19, T50, T51) para usarlos.

### Tarea 45
- **Título**: `[Feat] Despliegue Producción (HTTPS + CORS)`
- **Estimación**: M
- **Área**: [DEVOPS]
- **Asignado a**: Bárbara
- **Bloqueado por**: Tarea 33
- **Descripción**: Configurar despliegue del backend en contenedor Docker (php-fpm + nginx) y del frontend como estáticos. Asegurar **HTTPS** en producción (certbot o equivalente). Configurar **CORS** estricto en Laravel para que solo el dominio del frontend acceda a la API. Variables de entorno separadas dev/prod (`.env.production`).

### Tarea 46
- **Título**: `[Feat] Monitoreo y Métricas`
- **Estimación**: S
- **Área**: [DEVOPS]
- **Asignado a**: Michelle
- **Descripción**: Integrar **Sentry** (o equivalente) para captura de errores en backend y frontend. Configurar logs estructurados en Laravel con canales separados. Endpoint `/api/v1/health` con métricas básicas (uptime, conexiones BD, peticiones/min, errores/min, latencia p95). Dashboard mínimo (Grafana o el panel free de Sentry).

### Tarea 47
- **Título**: `[Feat] Accesibilidad`
- **Estimación**: S
- **Área**: [FRONTEND]
- **Asignado a**: Bárbara
- **Bloqueado por**: Tarea 34
- **Descripción**: Auditar la app con axe-core o Lighthouse. Asegurar contraste mínimo AA, alts en iconos de materiales y casillas, navegación por teclado en `BoardGrid` y modales del lobby/tech tree, atributos ARIA en componentes interactivos. Añadir tests de a11y dentro de Playwright.

---

## 📋 Resumen de distribución

> ⚙️ Tareas con este símbolo tienen modificaciones respecto a `raw_tareas.md`.

### Bárbara
| Tarea | Título | Talla | Estado |
|---|---|---|---|
| T1 | Migrations and Base Models | S | ✅ Terminada |
| T3 | Frontend Structure, Auth Routing and Redux | L | ✅ Terminada |
| T20 | Refactor Auth y Teams a Services/Repos | S | ✅ Terminada |
| T5 | Game Lobby & Team Manager UI | M | ✅ Terminada |
| T14 | Migrations Tech Process | M | ✅ Terminada |
| T22 | DB V5b Tech Tree Normalization | M | ✅ Terminada |
| T7 | Board Generator and API Controller | L | ✅ Terminada |
| T8 | Individual Actions API | L | ✅ Terminada |
| T10 ⚙️ | Relational Sync and Polling | M | Pendiente |
| T11 | Progress Voting API | M | Pendiente |
| T12 ⚙️ | Action & Decision Control Panel | L | Pendiente |
| T25 | Contracts, Interfaces y Service Providers | M | Pendiente |
| T27 | DTOs y API Resources | M | Pendiente |
| T29 | Tests Unitarios Backend | L | Pendiente |
| T31 | Hooks por Feature | M | Pendiente |
| T33 | CI/CD Pipeline | M | Pendiente |
| T35 | Docs Arquitectura | S | Pendiente |
| T37 | Cache Service | S | Pendiente |
| T40 | Response Builder Centralizado | S | Pendiente |
| T42 | RTK Query / Server State Cache | M | Pendiente |
| T43 | Pages + Lazy Loading + Routes Centralizado | M | Pendiente |
| T44 | Contexts + UI Components Reutilizables | S | Pendiente |
| T45 | Despliegue Producción (HTTPS + CORS) | M | Pendiente |
| T47 | Accesibilidad | S | Pendiente |
| T48 | DB Migration V6: Quantities | M | Pendiente |

### Michelle
| Tarea | Título | Talla | Estado |
|---|---|---|---|
| T2 | API Authentication Sanctum | M | ✅ Terminada |
| T4 | CRUD Endpoints Teams | M | ✅ Terminada |
| T6 | Tile Migrations and Base Dictionary | S | ✅ Terminada |
| T21 | DB V5a Tile Schema Correction | M | ✅ Terminada |
| T17 | Global TopBar & Session Navigation | S | ✅ Terminada |
| T9 | Board Grid Component and Frontend Visualization | XL | ✅ Terminada |
| T18 | Material Inventory Side-Panel | S | ✅ Terminada |
| T13 ⚙️ | Schedule / Cron Round Close | XL | Pendiente |
| T15 ⚙️ | End of Game (Terraforming) | S | Pendiente |
| T16 ⚙️ | Abandonment Management | S | Pendiente |
| T19 ⚙️ | Technology Tree & Progress Archive | M | Pendiente |
| T23 | Catalog Seeders | L | Pendiente |
| T24 | Update ER Diagram to V5 | S | Pendiente |
| T26 | Form Requests, Policies y Namespace | M | Pendiente |
| T28 | Excepciones Personalizadas y Handler | S | Pendiente |
| T30 | Cliente HTTP Centralizado | S | Pendiente |
| T32 | Tests de Frontend | L | Pendiente |
| T34 | Tests E2E | L | Pendiente |
| T36 | Rate Limiting y Versionado API | XS | Pendiente |
| T39 | Eventos y Listeners de Dominio | M | Pendiente |
| T41 | Middleware Global (Force JSON + Logging) | XS | Pendiente |
| T46 | Monitoreo y Métricas | S | Pendiente |
| T49 | Docs ER V6 + Evolución Tecnológica | S | Pendiente |
| T50 | Inventory Panel: Inventions Section | S | Pendiente |
| T51 | Pueblo Tile + Tech Tree Access | M | Pendiente |
