# Product Backlog MVP - Bressolium

A continuación se desglosan las tareas técnicas correspondientes a las Historias de Usuario formadas en `historias_mvp.md`.

---

## 👤 Épica 1: Gestión de Usuarios y Equipos

### Tarea 1
- **Título**: `[Feat] Migraciones y Modelos base (Relacional V3)`
- **Estimación**: S
- **Área**: [BASE DE DATOS]
- **Asignado a**: Bárbara
- **Bloqueado por**: Ninguna
- **Descripción**: Crear las migraciones y modelos base con **UUID como PK**:
  1. `users` (Laravel default + UUID).
  2. `partidas` (id, nombre, estado ENUM).
  3. `jornadas` (id, partida_id, numero, fecha_inicio).
  4. `jornada_user` (pivot: jornada_id, user_id, acciones_gastadas).
  5. `votos` (id, jornada_id, user_id, tecnologia_id).
- **Scripts / Git**: Rama `feat/T1-migraciones-base`. 
- **Criterios de Aceptación (DoD)**: `php artisan migrate` crea las 5 tablas correctamente. No existe campo JSON ni puntos.

### Tarea 2
- **Título**: `[Feat] Setup de autenticación API con Sanctum`
- **Estimación**: M
- **Área**: [BACKEND]
- **Asignado a**: Michelle
- **Bloqueado por**: Tarea 1
- **Descripción**: Instalar y configurar Laravel Sanctum. Crear endpoints `/api/register` y `/api/login` devolviendo tokens. Todas las respuestas JSON formadas bajo el estándar: `{success, data, error}`.
- **Scripts / Git**: Rama `feat/T2-auth-sanctum`. Test en Pest para login.
- **Criterios de Aceptación (DoD)**: El POST a `/login` con datos correctos devuelve HTTP 200 y el token válido.

### Tarea 3
- **Título**: `[Feat] Estructura Frontend, Routing de Auth y Redux`
- **Estimación**: L
- **Área**: [FRONTEND]
- **Asignado a**: Bárbara
- **Bloqueado por**: Tarea 2
- **Descripción**: Inicializar proyecto con Vite + React Router, Redux Toolkit Slices (para user auth) e instalar las vistas (Tailwind) para el Registro y Login (HU 1.1). Aplicar JSDoc en el slice.
- **Scripts / Git**: Rama `feat/T3-front-auth`. Testing con Vitest/RTL.
- **Criterios de Aceptación (DoD)**: El usuario puede rellenar el formulario de Login, y a través de un servicio `authService.js`, hacer login salvando el token en cliente.

### Tarea 4
- **Título**: `[Feat] Endpoints CRUD para Equipos y Creación de 1ª Jornada`
- **Estimación**: M
- **Área**: [BACKEND]
- **Asignado a**: Bárbara
- **Bloqueado por**: Tarea 1 y Tarea 2
- **Descripción**: (HUs 1.2, 1.3, 1.4, 1.5). Endpoint para crear equipo. Al crear una partida, se debe insertar automáticamente el primer registro en la tabla `jornadas` (numero: 1) y en `jornada_user` para los miembros iniciales.
- **Scripts / Git**: Rama `feat/T4-crud-equipos`.
- **Criterios de Aceptación (DoD)**: Al crear partida, existe un registro en `jornadas` vinculado. No usa JSON.

### Tarea 5
- **Título**: `[Feat] Dashboard Multiequipo (Selector) Frontend`
- **Estimación**: S
- **Área**: [FRONTEND]
- **Asignado a**: Michelle
- **Bloqueado por**: Tarea 3 y Tarea 4
- **Descripción**: Vista una vez autenticado (HU 1.6). Pantalla que muestra botones con las partidas activas del usuario, y botones para Unirse o Crear. Al elegir una, debe impactar el estado en RTK y redirigir al Tablero.
- **Scripts / Git**: Rama `feat/T5-front-dashboard`.
- **Criterios de Aceptación (DoD)**: Lista dinámica renderizada mapeando el JSON devuelto por el Backend.

---

## 🗺️ Épica 2: El Tablero y la Exploración

### Tarea 6
- **Título**: `[Feat] Migraciones de Casillas y Diccionario Base`
- **Estimación**: S
- **Área**: [BASE DE DATOS]
- **Asignado a**: Michelle
- **Bloqueado por**: Tarea 1
- **Descripción**: Migración para tabla `casillas` siguiendo el diagrama ER: `id` (UUID), `partida_id`, `coord_x`, `coord_y`, `tipo_casilla_id` (FK), `nivel`, `explorada` (boolean), y `jugador_asignado` (FK).
- **Scripts / Git**: Rama `feat/T6-migracion-casillas`.
- **Criterios de Aceptación (DoD)**: Los nombres de columnas deben ser idénticos al ER (`coord_x`, `explorada`). Seeder inyecta tipos base.

### Tarea 7
- **Título**: `[Feat] Generador y Controlador de Tablero API`
- **Estimación**: L
- **Área**: [BACKEND]
- **Asignado a**: Bárbara
- **Bloqueado por**: Tarea 6
- **Descripción**: (HU 2.1 y 2.6) Al crear equipo nuevo, se debe despachar la generación de una matriz (ej. 10x10) de casillas aleatorizadas. Endpoint `GET /api/tablero` que devuelve únicamente el array.
- **Scripts / Git**: Rama `feat/T7-back-tablero`.
- **Criterios de Aceptación (DoD)**: Respuesta del Endpoint trae las casillas con cordenadas.

### Tarea 8
- **Título**: `[Feat] Acciones Individuales API (Explorar / Mejorar)`
- **Estimación**: L
- **Área**: [BACKEND]
- **Asignado a**: Michelle
- **Bloqueado por**: Tarea 7
- **Descripción**: (HUs 2.2 y 2.3) Endpoints POST de acciones. Deben chequear obligatoriamente en `jornada_user` si el usuario tiene `acciones_gastadas < 2`. Bloqueo de BD transaccional. Aplicar coste de recetas verificando el inventario actual de la partida si evoluciona.
- **Scripts / Git**: Rama `feat/T8-back-acciones`.
- **Criterios de Aceptación (DoD)**: Falla HTTP 403 si las acciones gastadas son 2. Actualiza `explorada` (Casilla) y suma 1 a `acciones_gastadas`.
### Tarea 9
- **Título**: `[Feat] Componente Grid Tablero y Visualización Frontend`
- **Estimación**: XL
- **Área**: [FRONTEND]
- **Asignado a**: Bárbara
- **Bloqueado por**: Tarea 7
- **Descripción**: Renderización visual (CSS Grid) del mapa. Gestión gráfica de "oscurecido" para casillas (HU 2.4). Envío de llamadas Axios a los endpoints Explorar y Evolucionar (HU 2.2).
- **Scripts / Git**: Rama `feat/T9-front-grid`.
- **Criterios de Aceptación (DoD)**: Botones integrados. Al explorar, RESTa 1 acción local de RTK y visualmente desvela la ficha.

---

## 🗳️ Épica 3: Mecánicas de Turno y Votos

### Tarea 10
- **Título**: `[Feat] Sincronización Relacional y Polling`
- **Estimación**: M
- **Área**: [BACKEND] / [FRONTEND]
- **Asignado a**: Michelle
- **Bloqueado por**: Tarea 8 y Tarea 9
- **Descripción**: Endpoints `GET /api/partida/sync` (Back) que lee de las tablas `jornada` y `jornada_user`. El Front (RTK Query) debe consumirlo cada ~30 segundos guardando la data en Redux (HU 3.2). 
- **Scripts / Git**: Rama `feat/T10-sync-jornada`.
- **Criterios de Aceptación (DoD)**: El hook hidrata el Redux con el estado real de la BD sin usar campos JSON.
### Tarea 11
- **Título**: `[Feat] API Votaciones de Progreso (Relacional)`
- **Estimación**: M
- **Área**: [BACKEND]
- **Asignado a**: Bárbara
- **Bloqueado por**: Tarea 10 y Tarea 14
- **Descripción**: (HU 3.3). Endpoint que inserta nuevo registro en tabla `votos`. Y comprueba si (count(votos) == total_jugadores) para disparar el evento de ejecución.
- **Scripts / Git**: Rama `feat/T11-back-votar`.
- **Criterios de Aceptación (DoD)**: Almacena el voto de forma segura. Falla si el usuario ya votó en esa jornada.
### Tarea 12
- **Título**: `[Feat] UI Votación interactiva (El Pueblo)`
- **Estimación**: L
- **Área**: [FRONTEND]
- **Asignado a**: Michelle
- **Bloqueado por**: Tarea 11
- **Descripción**: Modal central al pulsar Casilla N0 (HU 2.5). Enumera recetas a desbloquear. Render de candados (Prerrequisitos), consumo del endpoint votar.
- **Scripts / Git**: Rama `feat/T12-front-votacion`.
- **Criterios de Aceptación (DoD)**: Botón votar ocultará la vista y dejará un mensaje de espera (Gherkin test).

### Tarea 13
- **Título**: `[Feat] Schedule / Cron Cierre de Turno y Salto de Jornada`
- **Estimación**: XL
- **Área**: [BACKEND]
- **Asignado a**: Bárbara
- **Bloqueado por**: Tarea 11
- **Descripción**: (HUs 3.4, 3.5, 3.6). Job de Laravel. Lee tabla `votos`, decide ganador, resta materiales, añade recursos a la partida y CRIMINAL: Crea una NUEVA fila en `jornadas` incrementando el `numero`, reseteando los registros en `jornada_user` para que todos vuelvan a tener 2 acciones.
- **Scripts / Git**: Rama `feat/T13-cron-cierre`. Test con Pest vital.
- **Criterios de Aceptación (DoD)**: Al cerrar turno, el comando `jornada:close` deja la tabla `jornadas` con un nuevo número correlativo.

---

## 🌳 Épica 4: Tecnología y Meta

### Tarea 14
- **Título**: `[Feat] Migraciones y Relaciones del Proceso Técnico`
- **Estimación**: M
- **Área**: [BASE DE DATOS]
- **Asignado a**: Michelle
- **Bloqueado por**: Ninguna
- **Descripción**: (HU 4.1). Tablas `tecnologias`, `inventos`, `materiales`, `recetas` (Pivote Polymorphic o asociativa simple) y pre-seed tecnológico.
- **Scripts / Git**: Rama `feat/T14-db-tecnologias`.
- **Criterios de Aceptación (DoD)**: El seed genera las dependencias correctamente ("Rueda" -> "Carro").

### Tarea 15
- **Título**: `[Feat] Fin de Juego (Terraformación)`
- **Estimación**: S
- **Área**: [BACKEND]
- **Asignado a**: Bárbara
- **Bloqueado por**: Tarea 13
- **Descripción**: Al resolver turno, si la tecnología ganadora es la Nave, cambiar `partida.estado` a `FINALIZADA` (HU 4.3).
- **Scripts / Git**: Rama `feat/T15-back-victoria`.
- **Criterios de Aceptación (DoD)**: Test Pest comprueba que la partida cambia de estado al ganar.
### Tarea 16
- **Título**: `[Feat] Gestión de Abandono (Jugadores Inactivos Backend)`
- **Estimación**: S
- **Área**: [BACKEND]
- **Asignado a**: Michelle
- **Bloqueado por**: Tarea 13
- **Descripción**: (HU 4.5). Modificar el Job de la Tarea 13 para ignorar en la condición "Han votado todos" a los usuarios que llevan inactivos N jornadas (guardar flag `is_afk`). Mantener la suma de sus casillas activada.
- **Scripts / Git**: Rama `feat/T16-abandonos`.
- **Criterios de Aceptación (DoD)**: Partida avanza turno instantáneamente si el único jugador vivo votó y el otro está marcado inactivo.
