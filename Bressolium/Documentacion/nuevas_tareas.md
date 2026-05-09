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
- **DoD**: `php artisan migrate` crea todas las tablas correctamente. `votes` permite nulos en `technology_id` e `invention_id`. `rounds` tiene `ended_at`. Todas las PKs son UUID. No se usa JSON como columna.

### Tarea 2 [TERMINADA]
- **Título**: `[Feat] API Authentication Setup with Sanctum`
- **Estimación**: M
- **Área**: [BACKEND]
- **Asignado a**: Michelle
- **Descripción**: Configuración de Sanctum y endpoints `/api/register` y `/api/login`. Estándar `{success, data, error}`.
- **DoD**: POST a `/login` con credenciales correctas devuelve HTTP 200 y un token válido. Un acceso no autorizado devuelve HTTP 401.

### Tarea 3 [TERMINADA]
- **Título**: `[Feat] Frontend Structure, Auth Routing and Redux`
- **Estimación**: L
- **Área**: [FRONTEND]
- **Asignado a**: Bárbara
- **Descripción**: Inicializar Vite, React Router, Redux Toolkit y vistas de Auth con diseño Brutalista.
- **DoD**: El usuario puede rellenar el formulario de login y, a través del cliente de autenticación, iniciar sesión guardando el token en el almacenamiento del cliente. Las rutas protegidas redirigen a login si no hay sesión activa.

### Tarea 4 [TERMINADA]
- **Título**: `[Feat] CRUD Endpoints for Teams and 1st Round Creation`
- **Estimación**: M
- **Área**: [BACKEND]
- **Asignado a**: Michelle
- **Descripción**: Endpoints para crear equipo y generación automática de la primera ronda.
- **DoD**: Al crear una partida existe un registro vinculado en `rounds` con `number=1`. El campo `game.status` comienza como `WAITING`. No se usa JSON como columna.

### Tarea 20 [TERMINADA]
- **Título**: `[Refactor] Mover lógica de Auth y Teams a Servicios y Repositorios`
- **Estimación**: S
- **Área**: [BACKEND]
- **Asignado a**: Bárbara
- **Descripción**: Extraer la lógica escrita en los controladores creados en las Tareas 2 y 4 para adaptarla al patrón Controller -> Service -> Repository. Los tests no deberían romperse.
- **DoD**: La lógica de negocio se ejecuta desde los Services, delegando a los Repositories. Los Controladores solo gestionan inputs/outputs. Los tests existentes siguen en verde.

### Tarea 5 [TERMINADA]
- **Título**: `[Feat] Game Lobby & Team Manager UI`
- **Estimación**: M
- **Área**: [FRONTEND]
- **Asignado a**: Bárbara
- **HUs**: 1.2, 1.3, 1.4, 1.5, 1.7
- **Descripción**: Portal principal con diseño de bloques sólidos. Sección para buscar (lista), unirse aleatorio o crear equipo (modal civilización). Listado lateral de partidas activas del usuario.
- **DoD**: La lista de partidas se renderiza dinámicamente mapeando el JSON devuelto por el backend. Se gestiona correctamente el estado "No hay partidas activas".

### Tarea 17 [TERMINADA]
- **Título**: `[Feat] Global TopBar & Session Navigation`
- **Estimación**: S
- **Área**: [FRONTEND]
- **Asignado a**: Michelle
- **HUs**: 1.8
- **Descripción**: Barra superior persistente con nombre de usuario, logout, nombre del equipo actual y selector rápido de partidas (Quick Switcher).
- **DoD**: La topbar muestra correctamente los datos del usuario y la partida actual. Permite cambiar rápidamente a otra partida actualizando el estado de Redux.

---

## 🗺️ Épica 2: El Tablero y la Exploración

### Tarea 6 [TERMINADA]
- **Título**: `[Feat] Tile Migrations and Base Dictionary`
- **Estimación**: S
- **Área**: [BASE DE DATOS]
- **Asignado a**: Michelle
- **Descripción**: Migraciones para tiles, tile_types y producción de materiales inicial.
- **DoD**: Los nombres de columna coinciden con el ER (`coord_x`, `explored`). El seeder inyecta los tipos (ej. "Forest L1", "Forest L2") y sus cantidades de producción. La tabla `tiles` NO tiene columna `level`.

### Tarea 21 [TERMINADA]
- **Título**: `[Refactor] DB Migration V5a: Tile Schema Correction`
- **Estimación**: M
- **Área**: [BASE DE DATOS]
- **Asignado a**: Michelle
- **Bloqueado por**: Tarea 6
- **Descripción**: Corregir el schema de casillas para alinearlo con el diseño de 5 niveles. Añadir columna `base_type` (enum: bosque, cantera, rio, prado, mina, pueblo) a `tile_types`: es la clave para identificar la familia del terreno con independencia del nivel o del nombre visible. El campo `name` en `tile_types` almacena el nombre de presentación, que en nivel 5 es el nombre especializado (ej: el registro bosque-lv5 tiene `base_type=bosque` y `name="Pozo de Goma y Resina"`). Añadir columnas `tech_required` e `invention_required` a `material_tile_type`. Añadir `explored_by_player_id` y `explored_at` a `tiles`. Añadir `tier` y `group` a `materials`. Añadir el tipo `pueblo` al catálogo. Los tests existentes no deben romperse (cambios aditivos).
- **DoD**: `php artisan migrate` aplica sin errores. Las columnas nuevas existen en las tablas correspondientes. Los tests existentes siguen en verde.

### Tarea 7 [TERMINADA]
- **Título**: `[Feat] Board Generator and API Controller`
- **Estimación**: L
- **Área**: [BACKEND]
- **Asignado a**: Bárbara
- **Bloqueado por**: Tarea 21
- **Descripción**: Generación algorítmica de la matriz de tablero (15x15) aleatoria al crear equipo. Endpoint `GET /api/board`. (Implementar usando arquitectura Controller -> Service -> Repository).
- **DoD**: El endpoint devuelve los `tiles` con `coord_x` y `coord_y`. Todos los tiles están inicializados para el `game_id` dado. El tablero es de 15x15 (225 tiles).

### Tarea 8 [TERMINADA]
- **Título**: `[Feat] Individual Actions API (Explore / Upgrade)`
- **Estimación**: L
- **Área**: [BACKEND]
- **Asignado a**: Bárbara
- **Bloqueado por**: Tarea 7
- **Descripción**: Endpoints POST para realizar jugadas. Validación de acciones diarias en `round_user` y costes de materiales en `game_material`. (Implementar usando arquitectura Controller -> Service -> Repository).
- **DoD**: La evolución actualiza el FK `tile_type_id` y resta materiales del inventario del equipo en `game_material`. No hay incremento de nivel hardcodeado.

### Tarea 9 [TERMINADA]
- **Título**: `[Feat] Board Grid Component and Frontend Visualization`
- **Estimación**: XL
- **Área**: [FRONTEND]
- **Asignado a**: Michelle
- **Bloqueado por**: Tarea 7, Tarea 23
- **HUs**: 2.1, 2.2, 2.6
- **Descripción**: Componente central de mapa (CSS Grid). Renderizado de casillas descubiertas y gestión de "niebla de guerra". Conexión con API de acciones.
- **DoD**: Los botones de explorar y mejorar están integrados. Explorar resta 1 acción del estado local RTK y revela visualmente la casilla. El layout es responsive.

### Tarea 18 [TERMINADA]
- **Título**: `[Feat] Material Inventory Side-Panel (SidePanel Izquierdo)`
- **Estimación**: S
- **Área**: [FRONTEND]
- **Asignado a**: Michelle
- **HUs**: 2.4, 2.7
- **Descripción**: Panel lateral de inventario. Iconos de materiales con Badges de cantidad. Estados activo/inactivo (opacidad) según descubrimiento.
- **DoD**: La lista se renderiza automáticamente y reacciona a los cambios de estado generados por el polling. Los tooltips están implementados para las propiedades de cada material.

### Tarea 50 [TERMINADA]
- **Título**: `[Feat] Inventory Panel: Inventions Section`
- **Estimación**: S
- **Área**: [FRONTEND]
- **Asignado a**: Michelle
- **Bloqueado por**: Tarea 10, Tarea 48
- **HUs**: 2.4, 2.7
- **Descripción**: Extender el `inventorySlice` para añadir `inventions: InventoryInvention[]` (id, name, quantity, icon). Modificar `InventoryPanel.jsx` para dividir el panel en dos zonas claramente separadas: **"Recursos"** (existente, T18) y **"Inventos"** (nueva). Reutilizar la lógica de active/inactive según `quantity > 0`. Actualizar `Epica2_Front.test.jsx` con tests de la nueva sección. La hidratación se realiza desde el sync (T10).
- **DoD**: El `InventoryPanel` muestra dos zonas diferenciadas: "Recursos" e "Inventos". Los inventos con `quantity > 0` aparecen activos y los demás inactivos. Los tests de `Epica2_Front.test.jsx` cubren ambas zonas.

### Tarea 52 [TERMINADA]
- **Título**: `[Fix] Inventory Panel — Layout en Grid de 4 Columnas`
- **Estimación**: XS
- **Área**: [FRONTEND]
- **Asignado a**: Michelle
- **Bloqueado por**: Tarea 50
- **HUs**: 2.4
- **Descripción**: El panel de inventario mostraba materiales e inventos en columna única, desaprovechando el espacio del panel lateral. Cambiar el layout de cada sección a un grid de 4 columnas para que los iconos se dispongan en rejilla compacta.
- **DoD**: Materiales e inventos se muestran en un grid de 4 columnas (`repeat(4, 1fr)`). El panel mantiene su scroll vertical. Los tests existentes de `InventoryPanel` siguen en verde.

---

## 🗳️ Épica 3: Mecánicas de Turno y Votos

### Tarea 10 [TERMINADA]
- **Título**: `[Feat] Relational Sync and Polling`
- **Estimación**: M
- **Área**: [BACKEND] / [FRONTEND]
- **Asignado a**: Bárbara
- **Bloqueado por**: Tarea 8, Tarea 25, Tarea 26, Tarea 27, Tarea 28, Tarea 48
- **Descripción**: Endpoint `GET /api/game/sync` para hidratar el estado global de RTK (recursos, **inventos construidos con sus cantidades**, progreso tecnológico, rounds). Polling cada ~30s. Implementar siguiendo la arquitectura completa: Form Request, DTO, Service, Repository con interfaz, API Resource. La parte frontend del polling debe consumir la API a través del cliente HTTP centralizado (Tarea 30).
- **DoD**: El endpoint devuelve inventario de recursos, inventos construidos con cantidades, progreso tecnológico y datos de la ronda activa. El frontend realiza polling cada ~30s actualizando el estado RTK. La arquitectura completa (Form Request, DTO, Service, Repository, API Resource) está implementada.

### Tarea 11 [TERMINADA]
- **Título**: `[Feat] Progress Voting API (Relational)`
- **Estimación**: M
- **Área**: [BACKEND]
- **Asignado a**: Bárbara
- **Bloqueado por**: Tarea 10
- **Descripción**: Endpoint para insertar en `votes`. Acepta votos tanto a **tecnologías como a inventos** (la tabla `votes` ya soporta ambos vía `technology_id` o `invention_id` nullable). Validación de si el usuario ya votó y si el item ya está completado/investigado. Para inventos, considerar la cantidad acumulada del equipo, no solo presencia. (Implementar usando arquitectura Controller -> Service -> Repository).
- **DoD**: El voto se almacena correctamente. Soporta `technology_id` O `invention_id`. Falla si el usuario ya votó en esa ronda.

### Tarea 12 [TERMINADA]
- **Título**: `[Feat] Action & Decision Control Panel (SidePanel Derecho)`
- **Estimación**: L
- **Área**: [FRONTEND]
- **Asignado a**: Bárbara
- **Bloqueado por**: Tarea 11, Tarea 30
- **HUs**: 3.8
- **Descripción**: Panel de control de jornada con **dos zonas de votación claramente separadas**: una para **Tecnologías** y otra para **Inventos**. Cada zona muestra su propia lista de votables según stock y prerrequisitos cumplidos. Incluir contador visual de acciones, timer de fase y botón de finalizar turno. Los ítems con recursos suficientes se muestran activos; los ítems alcanzables en pocos pasos (faltan recursos o un prerrequisito previo) en gris con indicación de qué falta. Consumir la API a través del cliente HTTP centralizado (Tarea 30).
- **DoD**: El panel muestra dos zonas separadas: tecnologías e inventos votables. Los ítems con recursos suficientes están activos; los que no cumplen requisitos están en gris con indicación de qué falta. Las llamadas API pasan por el cliente HTTP centralizado.

### Tarea 13 [TERMINADA]
- **Título**: `[Feat] Schedule / Cron Round Close and Round Jump`
- **Estimación**: XL
- **Área**: [BACKEND]
- **Asignado a**: Michelle
- **Bloqueado por**: Tarea 11, Tarea 48
- **Descripción**: Job de Laravel para procesar el salto de turno: resuelve ganador de votos (de tecnología y de invento), aplica costes/recompensas, **incrementa la cantidad del invento construido en el inventario del equipo**, suma producción de materiales según las casillas explotadas por el equipo, y crea nueva Round. La validación de prerrequisitos compara **cantidades** acumuladas, no solo presencia. Resetear `actions_spent` en `round_user` para todos los jugadores de la partida al cerrar la jornada.
- **DoD**: El job resuelve el ganador de votos (tecnología e invento), aplica costes, incrementa la cantidad del invento construido, suma la producción de casillas explotadas y crea una nueva `Round`. El campo `actions_spent` se resetea a 0 para todos los jugadores. Los tests cubren todos los escenarios (con y sin votos, con y sin prerrequisitos cumplidos).

---

## 🌳 Épica 4: Tecnología y Meta

### Tarea 14 [TERMINADA]
- **Título**: `[Feat] Migrations and Relations for the Tech Process`
- **Estimación**: M
- **Área**: [BASE DE DATOS]
- **Asignado a**: Bárbara
- **Descripción**: Tablas de árbol tecnológico (technologies, inventions, recipes) y tablas de progreso por partida.
- **DoD**: El seeder incluye dependencias auto-referenciales (Tech desbloquea Tech) y stock inicial en `game_material` (`is_active: false` para los no descubiertos). No se usa JSON como columna.

### Tarea 22 [TERMINADA]
- **Título**: `[Refactor] DB Migration V5b: Tech Tree Normalization`
- **Estimación**: M
- **Área**: [BASE DE DATOS]
- **Asignado a**: Bárbara
- **Bloqueado por**: Tarea 14
- **Descripción**: Normalizar el schema de tecnologías e inventos separando prerequisitos de costes. Crear `invention_prerequisites(invention_id, prereq_type ENUM[invention|technology], prereq_id)` y `technology_prerequisites(technology_id, prereq_type, prereq_id)`. Refactorizar `recipes` hacia `invention_costs(invention_id, resource_id, quantity)` donde los costes son siempre recursos de casilla, nunca invention_ids. Crear `technology_bonuses(technology_id, bonus_type, bonus_value, bonus_target)` e `invention_bonuses`. Crear `technology_unlocks` e `invention_unlocks` con `unlock_type ENUM[technology|invention|tile_level]`. Los tests existentes no deben romperse (cambios aditivos).
- **DoD**: Las nuevas tablas se crean mediante migraciones. Las relaciones Eloquent están definidas en los modelos. Los tests existentes siguen en verde.

### Tarea 23 [TERMINADA]
- **Título**: `[Feat] Catalog Seeders: Complete Game Data`
- **Estimación**: L
- **Área**: [BASE DE DATOS]
- **Asignado a**: Michelle
- **Bloqueado por**: Tarea 21, Tarea 22
- **Descripción**: Poblar el catálogo completo del juego con seeders. Incluye: `ResourcesSeeder` (44 recursos con tier y group), `TileLevelResourcesSeeder` (5 tipos × 5 niveles con cantidades y requisitos de tech e invento), `TechnologiesSeeder` (31 tecnologías con prerequisitos, desbloqueos y bonificadores), `InventionsSeeder` (34 inventos con prerequisitos, costes, bonificadores y desbloqueos). Desbloquea la implementación verificable de las Tareas 7, 8, 11 y 13.
- **DoD**: `php artisan db:seed` puebla todas las tablas del catálogo. Los datos coinciden con `casillas.md` y `evolucion-tecnologias-e-inventos.md`.

### Tarea 24 [TERMINADA]
- **Título**: `[Docs] Update ER Diagram to V5`
- **Estimación**: S
- **Área**: [DOCUMENTACIÓN]
- **Asignado a**: Michelle
- **Bloqueado por**: Tarea 21, Tarea 22
- **Descripción**: Actualizar el diagrama ER_v4.html a V5 reflejando todos los cambios de schema introducidos en las Tareas 21 y 22: nuevas tablas `invention_prerequisites`, `technology_prerequisites`, `invention_costs`, `technology_bonuses`, `invention_bonuses`, `technology_unlocks`, `invention_unlocks`, columna `base_type` en `tile_types`, y atributos `tier`, `group` en `materials`.
- **DoD**: El diagrama HTML refleja todas las tablas y relaciones de V5. El archivo se abre correctamente en el navegador sin errores de sintaxis Mermaid.

### Tarea 48 [TERMINADA]
- **Título**: `[Refactor] DB Migration V6: Quantities in Inventions & Prerequisites`
- **Estimación**: M
- **Área**: [BASE DE DATOS]
- **Asignado a**: Bárbara
- **Bloqueado por**: Tarea 22
- **Descripción**: Migración aditiva V6: añadir columna `quantity int` a `invention_prerequisites` y `technology_prerequisites` (cuántos del invento/tech previo se requieren). Convertir el pivot many-to-many `game ↔ invention` en una tabla `game_inventions(id, game_id, invention_id, quantity)` análoga a `game_material`. Los tests existentes no deben romperse (cambios aditivos). Actualizar las relaciones de los modelos Eloquent.
- **DoD**: `php artisan migrate` añade la columna `quantity` a `invention_prerequisites` y `technology_prerequisites`. La tabla `game_inventions` existe con columnas `game_id`, `invention_id` y `quantity`. Los modelos Eloquent reflejan las nuevas relaciones. Los tests existentes siguen en verde.

### Tarea 49
- **Título**: `[Docs] Update ER Diagram to V6 + Evolución Tecnológica`
- **Estimación**: S
- **Área**: [DOCUMENTACIÓN]
- **Asignado a**: Michelle
- **Bloqueado por**: Tarea 48
- **Descripción**: Actualizar el diagrama ER (actualmente V5) a V6 reflejando: columna `quantity` en `invention_prerequisites` y `technology_prerequisites`, y la nueva tabla `game_inventions`. Actualizar `casillas.md` y `evolucion-tecnologias-e-inventos.md` con las cantidades requeridas en cada prerrequisito. Verificar que la referencia desde `global_rules.md` apunta a la versión correcta del diagrama.
- **DoD**: El diagrama ER HTML refleja V6 incluyendo la columna `quantity` en prerrequisitos y la tabla `game_inventions`. Los documentos `casillas.md` y `evolucion-tecnologias-e-inventos.md` incluyen las cantidades requeridas. La referencia en `global_rules.md` apunta a la versión V6.

### Tarea 38 [TERMINADA]
- **Título**: `[Feat] Actualización de Seeders (Nuevos Items + Quantities)`
- **Estimación**: S
- **Área**: [BASE DE DATOS]
- **Asignado a**: Michelle
- **Bloqueado por**: Tarea 23, Tarea 48
- **Descripción**: Modificar `ResourcesSeeder`, `TechnologiesSeeder` e `InventionsSeeder` para reflejar (1) la eliminación de Caolinita/Peces y la adición de los nuevos materiales/tecnologías según el documento `casillas.md` y `evolucion-tecnologias-e-inventos.md` actualizado (44 recursos y 31 tecnologías), y (2) los nuevos campos de **cantidad** en prerrequisitos de inventos y tecnologías introducidos por T48.
- **DoD**: `php artisan db:seed` puebla el catálogo con los 44 recursos, 31 tecnologías y 34 inventos actualizados según `casillas.md` y `evolucion-tecnologias-e-inventos.md`, incluyendo las cantidades correctas en todos los prerrequisitos.

### Tarea 19 ⚙️
- **Título**: `[Feat] Technology Tree & Progress Archive`
- **Estimación**: M
- **Área**: [FRONTEND]
- **Asignado a**: Michelle
- **Bloqueado por**: Tarea 23, Tarea 30, Tarea 48
- **HUs**: 4.1
- **Descripción**: Modal del árbol tecnológico del equipo. Mostrar investigaciones completadas, disponibles (recursos y prerrequisitos suficientes) y bloqueadas (indicando qué falta y en qué cantidad). Representar los múltiples caminos posibles hacia la tecnología final para que el equipo pueda planificar su estrategia. **El modal se abre al hacer click sobre la casilla central de tipo `pueblo` del tablero (ver Tarea 51).** Consumir la API a través del cliente HTTP centralizado (Tarea 30).
- **DoD**: El modal muestra investigaciones completadas, disponibles (recursos y prerrequisitos suficientes) y bloqueadas con indicación de qué falta y en qué cantidad. Se abre al hacer clic sobre la casilla central `pueblo`. Los tests de `Epica2_Front.test.jsx` cubren las tres categorías de visualización.

### Tarea 51
- **Título**: `[Feat] Pueblo Tile: Center Placement + Tech Tree Access`
- **Estimación**: M
- **Área**: [BACKEND] / [FRONTEND]
- **Asignado a**: Michelle
- **Bloqueado por**: Tarea 19, Tarea 44
- **Descripción**:
  - **Backend:** modificar `BoardGeneratorService` para garantizar que la casilla central `(7, 7)` del 15×15 sea siempre de `base_type=pueblo`. La casilla pueblo no puede explorarse ni mejorarse mediante acciones individuales.
  - **Frontend:** en `BoardGrid` (T9), detectar el click sobre la casilla pueblo y abrir el modal del árbol tecnológico (T19) en lugar de disparar `exploreTileThunk` o `upgradeTileThunk`. Aplicar estilo visual diferenciado siguiendo la guía brutalista. Actualizar tests de `Epica2_Front.test.jsx` para cubrir la apertura del modal desde la casilla central.
- **DoD**: La casilla `(7, 7)` del tablero es siempre `base_type=pueblo`. Las acciones de explorar y mejorar sobre esa casilla lanzan una excepción 422. En el frontend, hacer clic sobre la casilla pueblo abre el modal del árbol tecnológico. Los tests de backend y frontend cubren todos los casos.

### Tarea 53 [TERMINADA]
- **Título**: `[Fix] Tech Tree Modal — Tecnologías no visibles al abrir desde casilla Pueblo`
- **Estimación**: M
- **Área**: [FRONTEND]
- **Asignado a**: Michelle
- **Bloqueado por**: Tarea 19, Tarea 51
- **HUs**: 4.1
- **Descripción**: Al hacer clic sobre la casilla pueblo `(7, 7)`, el modal del árbol tecnológico se abre pero no muestra ningún contenido: ni tecnologías investigadas, ni disponibles, ni bloqueadas. La causa es que el modal no recibe ni consume correctamente los datos de progreso tecnológico del sync. Implementar la hidratación del modal desde el estado RTK Query (sync) y renderizar las tres categorías con su estado correcto.
- **DoD**: El modal muestra tres secciones diferenciadas: (1) **Investigadas** — tecnologías con `is_active=true`, resaltadas; (2) **Disponibles** — prerrequisitos y recursos cumplidos, botón de voto activo; (3) **Bloqueadas** — en gris con indicación de qué falta y en qué cantidad. Los datos se leen del sync vía RTK Query. Los tests de `Epica2_Front.test.jsx` (T19) cubren las tres categorías de visualización.

### Tarea 54 [TERMINADA]
- **Título**: `[Fix] VotingPanel — Inventos construibles solo una vez`
- **Estimación**: XS
- **Área**: [FRONTEND]
- **Asignado a**: Michelle
- **Bloqueado por**: Tarea 12, Tarea 48
- **Rama git**: `fix/T54-voting-panel-multi-invento`
- **Descripción**: El `VotingPanel` usaba `canVote: i.quantity === 0`, impidiendo votar para construir un invento si ya se tenía al menos uno. El sistema de prerrequisitos soporta cantidades (`quantity > 1`) — la Nave de Asentamiento Interestelar necesita Acero ×2 y Vidrio ×2 — por lo que los inventos deben poder construirse múltiples veces. Cambiar la condición a `canVote: missing.length === 0` para que el botón esté activo siempre que los requisitos estén cubiertos, independientemente de cuántas unidades ya se tengan. Añadir badge `×N` en verde para mostrar la cantidad actual de cada invento construido.
- **DoD**: `canVote` para inventos se activa cuando `missing.length === 0`, sin importar la cantidad ya construida. El panel muestra `×N` en verde para inventos con más de una unidad. Los tests existentes de VotingPanel siguen en verde.

### Tarea 15 ⚙️
- **Título**: `[Feat] End of Game (Terraforming)`
- **Estimación**: S
- **Área**: [BACKEND]
- **Asignado a**: Michelle
- **Bloqueado por**: Tarea 13
- **Descripción**: Lógica para finalizar la partida al completar la tecnología final ("La Nave"). Cambio de estado a `FINISHED`. Notificar a todos los jugadores de la partida del resultado final.
- **DoD**: Al construir el invento con `is_final=true` en `CloseRoundService`, el campo `game.status` cambia a `FINISHED`, se dispara el evento `GameFinished` y no se crea una nueva ronda.

### Tarea 16 ⚙️
- **Título**: `[Feat] Abandonment Management (Inactive Players Backend)`
- **Estimación**: S
- **Área**: [BACKEND]
- **Asignado a**: Michelle
- **Bloqueado por**: Tarea 13
- **Descripción**: Modificar resolución de turno para ignorar a jugadores offline (flag `is_afk`) sin bloquear el avance del equipo. El flag `is_afk` debe activarse automáticamente si un jugador no realiza ninguna acción durante una jornada completa.
- **DoD**: Si un jugador no realiza ninguna acción durante una jornada completa, el flag `is_afk` en `game_user` se activa automáticamente al cerrar la ronda. La resolución del turno ignora a los jugadores con `is_afk=true` al calcular el quórum de votos.

---

## 🏛️ Épica 5: Calidad Arquitectónica

> Tareas derivadas de los requisitos de la guía del módulo (s4-proyecto.md). Deben completarse antes de implementar T10 en adelante para que el código nuevo nazca con los patrones correctos.

### Tarea 25 [TERMINADA]
- **Título**: `[Refactor] Contracts, Interfaces y Service Providers`
- **Estimación**: M
- **Área**: [BACKEND]
- **Asignado a**: Bárbara
- **Descripción**: Crear `/Repositories/Contracts` con una interfaz por cada repositorio existente (Game, User, Round, Board, Tile). Mover las implementaciones actuales a `/Repositories/Eloquent`. Crear un `RepositoryServiceProvider` que registre los bindings interfaz→implementación en el IoC Container de Laravel. Actualizar todos los servicios que inyectan repositorios para que dependan de la interfaz, no de la clase concreta.
- **DoD**: Todos los repositorios existentes tienen su interfaz en `/Repositories/Contracts`. Las implementaciones están en `/Repositories/Eloquent`. El `RepositoryServiceProvider` registra todos los bindings. Los servicios inyectan la interfaz, no la clase concreta. Los tests existentes siguen en verde.

### Tarea 26 [TERMINADA]
- **Título**: `[Refactor] Form Requests, Policies y Namespace de Controladores API`
- **Estimación**: M
- **Área**: [BACKEND]
- **Asignado a**: Michelle
- **Descripción**: Crear clases en `/Http/Requests` para validación de entrada en los endpoints existentes (register, login, create game, join game, explore, upgrade). Crear `/Http/Policies` para las reglas de autorización sobre recursos (acceso a partida, acciones sobre casillas). Mover todos los controladores a `/Http/Controllers/Api/` para separar espacio de nombres correctamente. Refactorizar controladores para delegar validación a Form Requests y autorización a Policies en lugar de hacerlo manualmente en servicios.
- **DoD**: Cada endpoint tiene su Form Request con validación declarativa. Las Policies controlan la autorización sobre recursos. Todos los controladores están en `/Http/Controllers/Api/`. Los tests existentes siguen en verde.

### Tarea 27 [TERMINADA]
- **Título**: `[Refactor] DTOs y API Resources`
- **Estimación**: M
- **Área**: [BACKEND]
- **Asignado a**: Bárbara
- **Descripción**: Crear `/DTOs` con clases de transferencia de datos para los flujos principales (CreateGameDTO, JoinGameDTO, ExploreActionDTO, UpgradeActionDTO, SyncResponseDTO, VoteDTO). Crear `/Http/Resources` con API Resources para transformar modelos Eloquent antes de devolverlos al cliente (GameResource, TileResource, MaterialResource, RoundResource). Sustituir los retornos directos de modelos por sus Resources correspondientes en todos los controladores existentes.
- **DoD**: Cada flujo principal tiene su DTO. Todos los controladores usan API Resources para transformar modelos antes de devolverlos. Los modelos Eloquent no se devuelven directamente al cliente. Los tests existentes siguen en verde.

### Tarea 28 [TERMINADA]
- **Título**: `[Refactor] Excepciones Personalizadas y Handler Global`
- **Estimación**: S
- **Área**: [BACKEND]
- **Asignado a**: Michelle
- **Descripción**: Crear `/Exceptions` con clases de excepción de dominio (InsufficientMaterialsException, ActionLimitExceededException, TileAlreadyExploredException, TileNotExploredException, UserNotInGameException). Configurar el handler global en `bootstrap/app.php` para interceptar estas excepciones y convertirlas automáticamente en respuestas JSON con el código HTTP correcto. Refactorizar ActionService y demás servicios para lanzar excepciones en lugar de devolver arrays con status.
- **DoD**: Las excepciones de dominio están en `/Exceptions` y el handler global las convierte en JSON con el código HTTP correcto. Los servicios lanzan excepciones en lugar de devolver arrays con status. Los tests existentes siguen en verde.

### Tarea 29
- **Título**: `[Feat] Tests Unitarios de Backend`
- **Estimación**: L
- **Área**: [BACKEND]
- **Asignado a**: Bárbara
- **Bloqueado por**: Tarea 25, Tarea 26, Tarea 27, Tarea 28
- **Descripción**: Crear tests en `/tests/Unit` para los servicios y repositorios de forma aislada, mockeando dependencias. Cubrir como mínimo: GameService, ActionService, y los repositorios principales. Los tests unitarios deben verificar lógica de negocio sin tocar la base de datos.
- **DoD**: Los tests en `/tests/Unit` cubren GameService, ActionService y los repositorios principales mockeando todas las dependencias. Todos los tests unitarios pasan sin tocar la base de datos.

### Tarea 30 [TERMINADA]
- **Título**: `[Feat] Cliente HTTP Centralizado con Interceptores`
- **Estimación**: S
- **Área**: [FRONTEND]
- **Asignado a**: Michelle
- **Descripción**: Crear en `/src/lib/` una instancia de Axios configurada como cliente HTTP centralizado. Incluir interceptores para: añadir automáticamente el token de autenticación en cada petición, manejar globalmente errores 401 (redirigir a login) y 500. Restructurar las llamadas API existentes para que pasen por este cliente. Mover las definiciones de llamadas API a las carpetas `/features/[nombre]/api/` de cada feature en lugar de `/services` global.
- **DoD**: El cliente Axios en `/src/lib/` añade automáticamente el token en cada petición y redirige a login en 401. Todas las llamadas API existentes pasan por este cliente. Las definiciones de API están organizadas en `/features/[nombre]/api/`.

### Tarea 31
- **Título**: `[Refactor] Hooks por Feature`
- **Estimación**: M
- **Área**: [FRONTEND]
- **Asignado a**: Bárbara
- **Bloqueado por**: Tarea 30
- **Descripción**: Extraer la lógica de interacción con la API de los componentes existentes a hooks personalizados dentro de cada feature: `useAuth` (auth), `useGames` (game), `useBoard` (board), `useInventory` (inventory). Los componentes deben quedar como presentacionales puros que reciben datos y callbacks por props o desde el hook.
- **DoD**: Los hooks `useAuth`, `useGames`, `useBoard` y `useInventory` encapsulan la lógica de interacción con la API. Los componentes son presentacionales puros que reciben datos y callbacks desde el hook. Los tests existentes siguen en verde.

### Tarea 32
- **Título**: `[Feat] Tests de Frontend`
- **Estimación**: L
- **Área**: [FRONTEND]
- **Asignado a**: Michelle
- **Bloqueado por**: Tarea 30, Tarea 31
- **Descripción**: Configurar Vitest y React Testing Library. Escribir tests unitarios para los hooks principales (useAuth, useBoard, useInventory) y tests de componentes para las vistas críticas (Login, Register, Dashboard, BoardGrid). Mockear el cliente HTTP centralizado para que los tests no dependan de red.
- **DoD**: Los tests unitarios cubren `useAuth`, `useBoard` y `useInventory`. Los tests de componentes cubren Login, Register, Dashboard y BoardGrid. El cliente HTTP está mockeado en todos los tests y ningún test depende de red. Todos los tests pasan.

### Tarea 33
- **Título**: `[Feat] CI/CD Pipeline`
- **Estimación**: M
- **Área**: [DEVOPS]
- **Asignado a**: Bárbara
- **Descripción**: Configurar GitHub Actions con un workflow que se ejecute en cada PR y push a `main`: instalación de dependencias de backend y frontend, ejecución de linters, ejecución de todos los tests de backend (Feature + Unit), build del frontend. El pipeline debe fallar si algún test falla o el build no compila.
- **DoD**: El workflow de GitHub Actions se ejecuta en cada PR y push a `main`. Instala dependencias, ejecuta linters, tests de backend y frontend, y construye el frontend. El pipeline falla si algún test falla o el build no compila.

### Tarea 34
- **Título**: `[Feat] Tests E2E`
- **Estimación**: L
- **Área**: [TESTING]
- **Asignado a**: Michelle
- **Bloqueado por**: Tarea 33
- **Descripción**: Configurar Playwright. Escribir pruebas de flujo completo que simulen el recorrido real del usuario a través del navegador: registro → login → crear partida → ver tablero → explorar casilla → ver inventario actualizado. Los tests E2E deben ejecutarse contra el entorno de desarrollo con Docker levantado.
- **DoD**: Los tests de Playwright simulan el flujo completo: registro → login → crear partida → ver tablero → explorar casilla → ver inventario actualizado. Los tests pasan contra el entorno de desarrollo con Docker levantado.

### Tarea 35
- **Título**: `[Docs] Documentación de Arquitectura`
- **Estimación**: S
- **Área**: [DOCUMENTACIÓN]
- **Asignado a**: Bárbara
- **Descripción**: Redactar un documento de arquitectura que explique las decisiones técnicas del proyecto: justificación del patrón Controller→Service→Repository, uso de Contracts e IoC Container, estructura de features en el frontend, gestión del estado con Redux, y convenciones de nomenclatura. Debe poder usarse como referencia durante la presentación del proyecto.
- **DoD**: El documento de arquitectura existe en `/Documentacion/` y explica el patrón Controller→Service→Repository, el uso de Contracts e IoC Container, la estructura de features en el frontend, la gestión de estado con Redux y RTK Query, y las convenciones de nomenclatura.

### Tarea 36 [TERMINADA]
- **Título**: `[Feat] Rate Limiting y Versionado de API`
- **Estimación**: XS
- **Área**: [BACKEND]
- **Asignado a**: Michelle
- **Descripción**: Configurar throttle middleware en las rutas API (por ejemplo 60 peticiones/minuto por usuario autenticado). Añadir prefijo `/api/v1/` a todas las rutas para implementar versionado. Actualizar el frontend y los tests para que apunten a la nueva URL base.
- **DoD**: Las rutas API tienen throttle a 60 peticiones/minuto por usuario autenticado. Todas las rutas tienen el prefijo `/api/v1/`. El frontend y los tests apuntan a la nueva URL base.

### Tarea 37
- **Título**: `[Feat] Cache Service`
- **Estimación**: S
- **Área**: [BACKEND]
- **Asignado a**: Bárbara
- **Bloqueado por**: Tarea 25
- **Descripción**: Crear un servicio de caché que centralice la lógica de guardar, recuperar e invalidar datos temporales. Aplicarlo inicialmente al endpoint de sync (Tarea 10) y al tablero (Tarea 7): cachear el estado del tablero por partida e invalidar la caché al ejecutar una acción de explorar o upgrade.
- **DoD**: El `CacheService` centraliza guardar, recuperar e invalidar datos temporales. El estado del tablero y el sync están cacheados por `game_id`. La caché se invalida automáticamente al ejecutar una acción de explorar o upgrade. Los tests existentes siguen en verde.

### Tarea 39
- **Título**: `[Feat] Eventos y Listeners de Dominio`
- **Estimación**: M
- **Área**: [BACKEND]
- **Asignado a**: Michelle
- **Descripción**: Crear `/app/Events` con eventos de dominio (`TileExplored`, `TileUpgraded`, `RoundClosed`, `MaterialsProduced`, `GameFinished`, `VoteCast`, `InventionBuilt`) y `/app/Listeners` desacoplados (notificación a jugadores, auditoría, log estructurado). Refactorizar `ActionService` y los jobs de T13 y T15 para que emitan eventos en lugar de ejecutar todo inline. Encolar los listeners costosos.
- **DoD**: Los eventos `TileExplored`, `TileUpgraded`, `RoundClosed`, `MaterialsProduced`, `GameFinished`, `VoteCast` e `InventionBuilt` se emiten desde los servicios correspondientes. Los listeners costosos implementan `ShouldQueue`. Los tests verifican que los eventos se disparan con `Event::assertDispatched()`.

### Tarea 40 [TERMINADA]
- **Título**: `[Refactor] Response Builder Centralizado`
- **Estimación**: S
- **Área**: [BACKEND]
- **Asignado a**: Bárbara
- **Descripción**: Extraer la lógica de respuestas estandarizadas a `app/Support/ResponseBuilder.php` con métodos `success(data, code)`, `error(message, code)`, `paginated(query)`. Refactorizar todos los controladores existentes (T2, T4, T7, T8) para que pasen por este builder en lugar de devolver arrays directamente desde `BaseController`.
- **DoD**: `ResponseBuilder::success()`, `error()` y `paginated()` son utilizados por todos los controladores. Los tests existentes siguen respondiendo con el formato `{success, data, error}`.

### Tarea 41 [TERMINADA]
- **Título**: `[Feat] Middleware Global (Force JSON + Request Logging)`
- **Estimación**: XS
- **Área**: [BACKEND]
- **Asignado a**: Michelle
- **Descripción**: Crear middleware global que fuerce `Accept: application/json` en peticiones API y registre cada petición (método, ruta, usuario, tiempo de respuesta, status) en log estructurado. Registrar en `bootstrap/app.php`. Distinguir explícitamente del middleware de ruta (Sanctum, throttle).
- **DoD**: Todas las peticiones API reciben `Content-Type: application/json`. Cada petición queda registrada en log estructurado con método, ruta, usuario, tiempo de respuesta y status. El middleware está registrado globalmente en `bootstrap/app.php`.

### Tarea 42 [TERMINADA]
- **Título**: `[Feat] RTK Query / Server State Cache`
- **Estimación**: M
- **Área**: [FRONTEND]
- **Asignado a**: Bárbara
- **Bloqueado por**: Tarea 30
- **Descripción**: Añadir **RTK Query** al store Redux existente. Migrar progresivamente las llamadas API (board, inventory, sync, votes) para que usen `createApi` con caché automática y revalidación. Los `slices` quedan reservados para estado puramente de UI/cliente. Justifica documentalmente que cumple "librería de gestión de estado de servidor" exigida por la guía del módulo.
- **DoD**: Las llamadas API de board, inventory, sync y votes usan `createApi` con caché automática y revalidación. Los slices de Redux quedan reservados para estado de UI. El store incluye una justificación documentada de que RTK Query cumple el requisito de "librería de gestión de estado de servidor".

### Tarea 43
- **Título**: `[Refactor] Pages + Lazy Loading + Routes Centralizado`
- **Estimación**: M
- **Área**: [FRONTEND]
- **Asignado a**: Bárbara
- **Descripción**: Crear `/src/pages/` y extraer las vistas (`Login`, `Register`, `Dashboard`, `GameBoard`) desde `features/` como Pages independientes. Aplicar `React.lazy()` + `Suspense` para carga diferida. Crear `/src/routes/` extrayendo la configuración del router de `App.jsx`, con HOC `ProtectedRoute` para rutas autenticadas.
- **DoD**: Las vistas `Login`, `Register`, `Dashboard` y `GameBoard` son Pages independientes en `/src/pages/` con carga diferida mediante `React.lazy()`. La configuración del router está centralizada en `/src/routes/` con un HOC `ProtectedRoute`. El build del frontend no falla.

### Tarea 44 [TERMINADA]
- **Título**: `[Feat] Contexts + UI Components Reutilizables`
- **Estimación**: S
- **Área**: [FRONTEND]
- **Asignado a**: Bárbara
- **Descripción**: Crear `/src/contexts/` con providers de baja frecuencia (`ThemeContext`, `ToastContext` para notificaciones globales). Crear `/src/components/ui/` con primitivos brutalistas reutilizables (`Button`, `Input`, `Modal`, `Toast`, `Badge`, `IconTile`) según `guia_estilos/`. Refactorizar componentes pendientes (T12, T19, T50, T51) para usarlos.
- **DoD**: `ThemeContext` y `ToastContext` están disponibles en la app. Los primitivos `Button`, `Input`, `Modal`, `Toast`, `Badge` e `IconTile` existen en `/src/components/ui/` siguiendo la guía de estilos brutalista. Los componentes de T12, T19, T50 y T51 los utilizan.

### Tarea 45
- **Título**: `[Feat] Despliegue Producción (HTTPS + CORS)`
- **Estimación**: M
- **Área**: [DEVOPS]
- **Asignado a**: Bárbara
- **Bloqueado por**: Tarea 33
- **Descripción**: Configurar despliegue del backend en contenedor Docker (php-fpm + nginx) y del frontend como estáticos. Asegurar **HTTPS** en producción (certbot o equivalente). Configurar **CORS** estricto en Laravel para que solo el dominio del frontend acceda a la API. Variables de entorno separadas dev/prod (`.env.production`).
- **DoD**: El backend despliega correctamente en contenedor Docker con php-fpm + nginx. El frontend sirve como estáticos. HTTPS está activo con certbot o equivalente. CORS en Laravel restringe el acceso al dominio del frontend. Las variables de entorno de producción están separadas en `.env.production`.

### Tarea 46 [TERMINADA]
- **Título**: `[Feat] Monitoreo y Métricas`
- **Estimación**: S
- **Área**: [DEVOPS]
- **Asignado a**: Michelle
- **Descripción**: Integrar **Sentry** (o equivalente) para captura de errores en backend y frontend. Configurar logs estructurados en Laravel con canales separados. Endpoint `/api/v1/health` con métricas básicas (uptime, conexiones BD, peticiones/min, errores/min, latencia p95). Dashboard mínimo (Grafana o el panel free de Sentry).
- **DoD**: Sentry (o equivalente) captura errores en backend y frontend. El endpoint `/api/v1/health` devuelve uptime, estado de la BD, peticiones/min, errores/min y latencia p95. Existe un dashboard básico accesible al equipo.

### Tarea 47
- **Título**: `[Feat] Accesibilidad`
- **Estimación**: S
- **Área**: [FRONTEND]
- **Asignado a**: Bárbara
- **Bloqueado por**: Tarea 34
- **Descripción**: Auditar la app con axe-core o Lighthouse. Asegurar contraste mínimo AA, alts en iconos de materiales y casillas, navegación por teclado en `BoardGrid` y modales del lobby/tech tree, atributos ARIA en componentes interactivos. Añadir tests de a11y dentro de Playwright.
- **DoD**: La app supera la auditoría de axe-core con contraste mínimo AA. Los iconos de materiales y casillas tienen `alt` o `aria-label`. La navegación por teclado funciona en `BoardGrid` y en los modales. Los tests de Playwright incluyen aserciones de accesibilidad.
y e
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
| T10 | Relational Sync and Polling | M | ✅ Terminada |
| T11 | Progress Voting API | M | ✅ Terminada |
| T12 | Action & Decision Control Panel | L | ✅ Terminada |
| T25 | Contracts, Interfaces y Service Providers | M | ✅ Terminada |
| T27 | DTOs y API Resources | M | ✅ Terminada |
| T29 | Tests Unitarios Backend | L | Pendiente |
| T31 | Hooks por Feature | M | Pendiente |
| T33 | CI/CD Pipeline | M | Pendiente |
| T35 | Docs Arquitectura | S | Pendiente |
| T37 | Cache Service | S | Pendiente |
| T40 | Response Builder Centralizado | S | ✅ Terminada |
| T42 | RTK Query / Server State Cache | M | ✅ Terminada |
| T43 | Pages + Lazy Loading + Routes Centralizado | M | Pendiente |
| T44 | Contexts + UI Components Reutilizables | S | ✅ Terminada |
| T45 | Despliegue Producción (HTTPS + CORS) | M | Pendiente |
| T47 | Accesibilidad | S | Pendiente |
| T48 | DB Migration V6: Quantities | M | ✅ Terminada |

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
| T13 | Schedule / Cron Round Close | XL | ✅ Terminada |
| T15 ⚙️ | End of Game (Terraforming) | S | Pendiente |
| T16 ⚙️ | Abandonment Management | S | Pendiente |
| T19 ⚙️ | Technology Tree & Progress Archive | M | Pendiente |
| T23 | Catalog Seeders | L | ✅ Terminada |
| T24 | Update ER Diagram to V5 | S | ✅ Terminada |
| T26 | Form Requests, Policies y Namespace | M | ✅ Terminada |
| T28 | Excepciones Personalizadas y Handler | S | ✅ Terminada |
| T30 | Cliente HTTP Centralizado | S | ✅ Terminada |
| T32 | Tests de Frontend | L | Pendiente |
| T34 | Tests E2E | L | Pendiente |
| T36 | Rate Limiting y Versionado API | XS | ✅ Terminada |
| T38 | Actualización de Seeders | S | ✅ Terminada |
| T39 | Eventos y Listeners de Dominio | M | Pendiente |
| T41 | Middleware Global (Force JSON + Logging) | XS | ✅ Terminada |
| T46 | Monitoreo y Métricas | S | ✅ Terminada |
| T49 | Docs ER V6 + Evolución Tecnológica | S | Pendiente |
| T50 | Inventory Panel: Inventions Section | S | ✅ Terminada |
| T51 | Pueblo Tile + Tech Tree Access | M | Pendiente |
| T52 | Inventory Panel — Grid 4 Columnas | XS | ✅ Terminada |
| T53 | Tech Tree Modal — Tecnologías no visibles | M | ✅ Terminada |
| T54 | VotingPanel — Inventos construibles solo una vez | XS | ✅ Terminada |
