# Product Backlog MVP - Bressolium

A continuación se desglosan las tareas técnicas correspondientes a las Historias de Usuario formadas en `historias_mvp.md`.

---

## 👤 Épica 1: Gestión de Usuarios y Equipos

### Tarea 1
- **Título**: `[Feat] Migrations and Base Models (Relational V3)`
- **Estimación**: S
- **Área**: [BASE DE DATOS]
- **Asignado a**: Bárbara
- **Bloqueado por**: Ninguna
- **Descripción**: Crear las migraciones y modelos base con **UUID como PK**:
  1. `users` (Laravel default + UUID).
  2. `games` (id, name, status ENUM).
  3. `rounds` (id, game_id, number, start_date).
  4. `round_user` (pivot: round_id, user_id, actions_spent).
  5. `votes` (id, round_id, user_id, technology_id).
- **Scripts / Git**: Rama `feat/T1-base-migrations`. 
- **Criterios de Aceptación (DoD)**: `php artisan migrate` crea las 5 tablas correctamente. No existe campo JSON ni puntos.

### Tarea 2
- **Título**: `[Feat] API Authentication Setup with Sanctum`
- **Estimación**: M
- **Área**: [BACKEND]
- **Asignado a**: Michelle
- **Bloqueado por**: Tarea 1
- **Descripción**: Instalar y configurar Laravel Sanctum. Crear endpoints `/api/register` y `/api/login` devolviendo tokens. Todas las respuestas JSON formadas bajo el estándar: `{success, data, error}`.
- **Scripts / Git**: Rama `feat/T2-auth-sanctum`. Test en Pest para login.
- **Criterios de Aceptación (DoD)**: El POST a `/login` con datos correctos devuelve HTTP 200 y el token válido.

### Tarea 3
- **Título**: `[Feat] Frontend Structure, Auth Routing and Redux`
- **Estimación**: L
- **Área**: [FRONTEND]
- **Asignado a**: Bárbara
- **Bloqueado por**: Tarea 2
- **Descripción**: Inicializar proyecto con Vite + React Router, Redux Toolkit Slices (para user auth) e instalar las vistas (Tailwind) para el Registro y Login (HU 1.1). Aplicar JSDoc en el slice.
- **Scripts / Git**: Rama `feat/T3-front-auth`. Testing con Vitest/RTL.
- **Criterios de Aceptación (DoD)**: El usuario puede rellenar el formulario de Login, y a través de un servicio `authService.js`, hacer login salvando el token en cliente.

### Tarea 4
- **Título**: `[Feat] CRUD Endpoints for Teams and 1st Round Creation`
- **Estimación**: M
- **Área**: [BACKEND]
- **Asignado a**: Bárbara
- **Bloqueado por**: Tarea 1 y Tarea 2
- **Descripción**: (HUs 1.2, 1.3, 1.4, 1.5). Endpoint para crear equipo. Al crear un `game`, se debe insertar automáticamente el primer registro en la tabla `rounds` (number: 1) y en `round_user` para los miembros iniciales.
- **Scripts / Git**: Rama `feat/T4-crud-teams`.
- **Criterios de Aceptación (DoD)**: Al crear game, existe un registro en `rounds` vinculado. No usa JSON.

### Tarea 5
- **Título**: `[Feat] Multi-Team Dashboard (Selector) Frontend`
- **Estimación**: S
- **Área**: [FRONTEND]
- **Asignado a**: Michelle
- **Bloqueado por**: Tarea 3 y Tarea 4
- **Descripción**: Vista una vez autenticado (HU 1.6). Pantalla que muestra botones con los `games` activos del usuario, y botones para Unirse o Crear. Al elegir uno, debe impactar el estado en RTK y redirigir al Board.
- **Scripts / Git**: Rama `feat/T5-front-dashboard`.
- **Criterios de Aceptación (DoD)**: Lista dinámica renderizada mapeando el JSON devuelto por el Backend.

---

## 🗺️ Épica 2: El Tablero y la Exploración

### Tarea 6
- **Título**: `[Feat] Tile Migrations and Base Dictionary`
- **Estimación**: S
- **Área**: [BASE DE DATOS]
- **Asignado a**: Michelle
- **Bloqueado por**: Tarea 1
- **Descripción**: Migración para tabla `tiles` siguiendo el diagrama ER: `id` (UUID), `game_id`, `coord_x`, `coord_y`, `tile_type_id` (FK), `level`, `explored` (boolean), y `assigned_player` (FK).
- **Scripts / Git**: Rama `feat/T6-tile-migration`.
- **Criterios de Aceptación (DoD)**: Los nombres de columnas deben ser idénticos al ER (`coord_x`, `explored`). Seeder inyecta tipos base.

### Tarea 7
- **Título**: `[Feat] Board Generator and API Controller`
- **Estimación**: L
- **Área**: [BACKEND]
- **Asignado a**: Bárbara
- **Bloqueado por**: Tarea 6
- **Descripción**: (HU 2.1 y 2.6) Al crear equipo nuevo, se debe despachar la generación de una matriz (ej. 10x10) de `tiles` aleatorizados. Endpoint `GET /api/board` que devuelve únicamente el array.
- **Scripts / Git**: Rama `feat/T7-back-board`.
- **Criterios de Aceptación (DoD)**: Respuesta del Endpoint trae los `tiles` con coordenadas.

### Tarea 8
- **Título**: `[Feat] Individual Actions API (Explore / Upgrade)`
- **Estimación**: L
- **Área**: [BACKEND]
- **Asignado a**: Michelle
- **Bloqueado por**: Tarea 7
- **Descripción**: (HUs 2.2 y 2.3) Endpoints POST de acciones. Deben chequear obligatoriamente en `round_user` si el usuario tiene `actions_spent < 2`. Bloqueo de BD transaccional. Aplicar coste de recetas verificando el inventario actual del game si evoluciona.
- **Scripts / Git**: Rama `feat/T8-back-actions`.
- **Criterios de Aceptación (DoD)**: Falla HTTP 403 si `actions_spent` son 2. Actualiza `explored` (Tile) y suma 1 a `actions_spent`.

### Tarea 9
- **Título**: `[Feat] Board Grid Component and Frontend Visualization`
- **Estimación**: XL
- **Área**: [FRONTEND]
- **Asignado a**: Bárbara
- **Bloqueado por**: Tarea 7
- **Descripción**: Renderización visual (CSS Grid) del mapa. Gestión gráfica de "oscurecido" para tiles (HU 2.4). Envío de llamadas Axios a los endpoints Explore y Upgrade (HU 2.2).
- **Scripts / Git**: Rama `feat/T9-front-grid`.
- **Criterios de Aceptación (DoD)**: Botones integrados. Al explorar, RESTa 1 acción local de RTK y visualmente desvela la ficha.

---

## 🗳️ Épica 3: Mecánicas de Turno y Votos

### Tarea 10
- **Título**: `[Feat] Relational Sync and Polling`
- **Estimación**: M
- **Área**: [BACKEND] / [FRONTEND]
- **Asignado a**: Michelle
- **Bloqueado por**: Tarea 8 y Tarea 9
- **Descripción**: Endpoints `GET /api/game/sync` (Back) que lee de las tablas `rounds` y `round_user`. El Front (RTK Query) debe consumirlo cada ~30 segundos guardando la data en Redux (HU 3.2).
- **Scripts / Git**: Rama `feat/T10-sync-round`.
- **Criterios de Aceptación (DoD)**: El hook hidrata el Redux con el estado real de la BD sin usar campos JSON.

### Tarea 11
- **Título**: `[Feat] Progress Voting API (Relational)`
- **Estimación**: M
- **Área**: [BACKEND]
- **Asignado a**: Bárbara
- **Bloqueado por**: Tarea 10 y Tarea 14
- **Descripción**: (HU 3.3). Endpoint que inserta nuevo registro en tabla `votes`. Y comprueba si (count(votes) == total_players) para disparar el evento de ejecución.
- **Scripts / Git**: Rama `feat/T11-back-vote`.
- **Criterios de Aceptación (DoD)**: Almacena el voto de forma segura. Falla si el usuario ya votó en ese round.

### Tarea 12
- **Título**: `[Feat] Interactive Voting UI (The People)`
- **Estimación**: L
- **Área**: [FRONTEND]
- **Asignado a**: Michelle
- **Bloqueado por**: Tarea 11
- **Descripción**: Modal central al pulsar Tile N0 (HU 2.5). Enumera recetas a desbloquear. Render de candados (Prerrequisitos), consumo del endpoint vote.
- **Scripts / Git**: Rama `feat/T12-front-voting`.
- **Criterios de Aceptación (DoD)**: Botón votar ocultará la vista y dejará un mensaje de espera (Gherkin test).

### Tarea 13
- **Título**: `[Feat] Schedule / Cron Round Close and Round Jump`
- **Estimación**: XL
- **Área**: [BACKEND]
- **Asignado a**: Bárbara
- **Bloqueado por**: Tarea 11
- **Descripción**: (HUs 3.4, 3.5, 3.6). Job de Laravel. Lee tabla `votes`, decide ganador, resta materiales, añade recursos al game y CRÍTICO: Crea una NUEVA fila en `rounds` incrementando el `number`, reseteando los registros en `round_user` para que todos vuelvan a tener 2 acciones.
- **Scripts / Git**: Rama `feat/T13-cron-close`. Test con Pest vital.
- **Criterios de Aceptación (DoD)**: Al cerrar turno, el comando `round:close` deja la tabla `rounds` con un nuevo número correlativo.

---

## 🌳 Épica 4: Tecnología y Meta

### Tarea 14
- **Título**: `[Feat] Migrations and Relations for the Tech Process`
- **Estimación**: M
- **Área**: [BASE DE DATOS]
- **Asignado a**: Michelle
- **Bloqueado por**: Ninguna
- **Descripción**: (HU 4.1). Tablas `technologies`, `inventions`, `materials`, `recipes` (Pivot Polymorphic o asociativa simple) y pre-seed tecnológico.
- **Scripts / Git**: Rama `feat/T14-db-technologies`.
- **Criterios de Aceptación (DoD)**: El seed genera las dependencias correctamente ("Wheel" -> "Cart").

### Tarea 15
- **Título**: `[Feat] End of Game (Terraforming)`
- **Estimación**: S
- **Área**: [BACKEND]
- **Asignado a**: Bárbara
- **Bloqueado por**: Tarea 13
- **Descripción**: Al resolver turno, si la tecnología ganadora es la Nave, cambiar `game.status` a `FINISHED` (HU 4.3).
- **Scripts / Git**: Rama `feat/T15-back-victory`.
- **Criterios de Aceptación (DoD)**: Test Pest comprueba que el game cambia de status al ganar.

### Tarea 16
- **Título**: `[Feat] Abandonment Management (Inactive Players Backend)`
- **Estimación**: S
- **Área**: [BACKEND]
- **Asignado a**: Michelle
- **Bloqueado por**: Tarea 13
- **Descripción**: (HU 4.5). Modificar el Job de la Tarea 13 para ignorar en la condición "All voted" a los usuarios que llevan inactivos N rounds (guardar flag `is_afk`). Mantener la suma de sus tiles activada.
- **Scripts / Git**: Rama `feat/T16-abandonments`.
- **Criterios de Aceptación (DoD)**: El game avanza turno instantáneamente si el único jugador vivo votó y el otro está marcado inactivo.
