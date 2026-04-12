# Product Backlog MVP - Bressolium

A continuación se desglosan las tareas técnicas correspondientes a las Historias de Usuario formadas en `epicas-e-historias-de-usuario.md`.

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

### Tarea 5
- **Título**: `[Feat] Game Lobby & Team Manager UI`
- **Estimación**: M
- **Área**: [FRONTEND]
- **Asignado a**: Bárbara
- **HUs**: 1.2, 1.3, 1.4, 1.5, 1.7
- **Descripción**: Portal principal con diseño de bloques sólidos. Sección para buscar (lista), unirse aleatorio o crear equipo (modal civilización). Listado lateral de partidas activas del usuario.

### Tarea 17
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

### Tarea 7
- **Título**: `[Feat] Board Generator and API Controller`
- **Estimación**: L
- **Área**: [BACKEND]
- **Asignado a**: Michelle
- **Bloqueado por**: Tarea 6
- **Descripción**: Generación algorítmica de la matriz de tablero (10x10) aleatoria al crear equipo. Endpoint `GET /api/board`.

### Tarea 8
- **Título**: `[Feat] Individual Actions API (Explore / Upgrade)`
- **Estimación**: L
- **Área**: [BACKEND]
- **Asignado a**: Michelle
- **Bloqueado por**: Tarea 7
- **Descripción**: Endpoints POST para realizar jugadas. Validación de acciones diarias en `round_user` y costes de materiales en `game_material`.

### Tarea 9
- **Título**: `[Feat] Board Grid Component and Frontend Visualization`
- **Estimación**: XL
- **Área**: [FRONTEND]
- **Asignado a**: Michelle
- **Bloqueado por**: Tarea 7
- **HUs**: 2.1, 2.2, 2.6
- **Descripción**: Componente central de mapa (CSS Grid). Renderizado de casillas descubiertas y gestión de "niebla de guerra". Conexión con API de acciones.

### Tarea 18
- **Título**: `[Feat] Material Inventory Side-Panel (SidePanel Izquierdo)`
- **Estimación**: S
- **Área**: [FRONTEND]
- **Asignado a**: Michelle
- **HUs**: 2.4, 2.7
- **Descripción**: Panel lateral de inventario. Iconos de materiales con Badges de cantidad. Estados activo/inactivo (opacidad) según descubrimiento.

---

## 🗳️ Épica 3: Mecánicas de Turno y Votos

### Tarea 10
- **Título**: `[Feat] Relational Sync and Polling`
- **Estimación**: M
- **Área**: [BACKEND] / [FRONTEND]
- **Asignado a**: Bárbara
- **Bloqueado por**: Tarea 8 y Tarea 9
- **Descripción**: Endpoint `GET /api/game/sync` para hidratar el estado global de RTK (recursos, progreso, rounds). Polling cada ~30s.

### Tarea 11
- **Título**: `[Feat] Progress Voting API (Relational)`
- **Estimación**: M
- **Área**: [BACKEND]
- **Asignado a**: Bárbara
- **Bloqueado por**: Tarea 10
- **Descripción**: Endpoint para insertar en `votes`. Validación de si el usuario ya votó o si el item ya está investigado.

### Tarea 12
- **Título**: `[Feat] Action & Decision Control Panel (SidePanel Derecho)`
- **Estimación**: L
- **Área**: [FRONTEND]
- **Asignado a**: Bárbara
- **Bloqueado por**: Tarea 11
- **HUs**: 3.8
- **Descripción**: Panel de control de jornada: Contador visual de acciones, lista de votables (Tech/Invento) según stock, timer de fase y botón de finalizar turno.

### Tarea 13
- **Título**: `[Feat] Schedule / Cron Round Close and Round Jump`
- **Estimación**: XL
- **Área**: [BACKEND]
- **Asignado a**: Bárbara
- **Bloqueado por**: Tarea 11
- **Descripción**: Job de Laravel para procesar el salto de turno: resuelve ganador de votos, aplica costes/recompensas, suma producción y crea nueva Round.

---

## 🌳 Épica 4: Tecnología y Meta

### Tarea 14 [TERMINADA]
- **Título**: `[Feat] Migrations and Relations for the Tech Process`
- **Estimación**: M
- **Área**: [BASE DE DATOS]
- **Asignado a**: Bárbara
- **Descripción**: Tablas de árbol tecnológico (technologies, inventions, recipes) y tablas de progreso por partida.

### Tarea 19
- **Título**: `[Feat] Technology Tree & Progress Archive`
- **Estimación**: M
- **Área**: [FRONTEND]
- **Asignado a**: Michelle
- **HUs**: 4.1
- **Descripción**: Visualización (Modal o sección) del historial de investigaciones del equipo y árbol de desbloqueos pendientes.

### Tarea 15
- **Título**: `[Feat] End of Game (Terraforming)`
- **Estimación**: S
- **Área**: [BACKEND]
- **Asignado a**: Michelle
- **Bloqueado por**: Tarea 13
- **Descripción**: Lógica para finalizar la partida al completar la tecnología final ("La Nave"). Cambio de estado a `FINISHED`.

### Tarea 16
- **Título**: `[Feat] Abandonment Management (Inactive Players Backend)`
- **Estimación**: S
- **Área**: [BACKEND]
- **Asignado a**: Michelle
- **Bloqueado por**: Tarea 13
- **Descripción**: Modificar resolución de turno para ignorar a jugadores offline (flag `is_afk`) sin bloquear el avance del equipo.
