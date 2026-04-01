# Prompt — Generación de tareas técnicas del proyecto

Eres un senior developer con experiencia en gestión de proyectos ágiles. A continuación se te proporciona la documentación completa de un juego estratégico cooperativo llamado **Civilización Cooperativa**. Tu tarea es generar el listado completo de tareas técnicas necesarias para desarrollar el proyecto, clasificadas en tres categorías: **Frontend**, **Backend** y **Base de Datos**.

---

## Contexto del equipo

- 2 developers júnior trabajando en pareja
- Deben repartirse las tareas de forma equilibrada entre los dos en las tres categorías
- Cada tarea debe documentarse con su correspondiente script o código
- Todo el trabajo se gestiona en un repositorio Git con commits atómicos y descriptivos
- Las tareas deben seguir una dependencia lógica (no se puede hacer el front sin el back, ni el back sin la BD)

---

## Requisitos para cada tarea

- Identificador único (ej. BD-01, BACK-01, FRONT-01)
- Título descriptivo
- Descripción técnica breve de qué hay que hacer
- Dependencias con otras tareas
- Developer asignado (DEV-1 o DEV-2), respetando equilibrio entre categorías y entre developers
- Criterios de aceptación técnicos concretos y verificables

---

## Restricciones del proyecto (MVP)

- No incluir eventos adversos ni estilos alternativos de civilización
- El Pueblo no tiene tabla en BD, se gestiona solo en frontend
- No hay puntos de investigación — tecnologías e inventos se pagan con materiales del inventario común
- Explorar y evolucionar casillas es gratuito (sin coste de recursos)
- Los turnos de los jugadores son simultáneos con un máximo de 2 horas por jornada
- Stack tecnológico a definir por el equipo (debe ser coherente con un proyecto web fullstack)

---

## Documentación del proyecto

### 1. Resumen del juego

Juego estratégico cooperativo de 1 a 5 jugadores. Los jugadores controlan juntos el desarrollo de una civilización en un tablero de casillas. Cada jornada, cada jugador realiza 2 acciones (explorar o evolucionar casillas) y luego vota qué tecnología o invento crear con los recursos del inventario común. El objetivo es alcanzar la terraformación construyendo la Nave de asentamiento interestelar. Hay ranking entre equipos en el mismo servidor.

---

### 2. Entidades principales (E-R)

| Entidad | Atributos |
|---|---|
| USER | id, nombre, email, password |
| PARTIDA | id, puntos, estado |
| CASILLA | id, coord_x, coord_y, explorada, jugador_asignado FK, tipo_casilla_id FK, partida_id FK |
| TIPO_CASILLA | id, nombre, nivel |
| MATERIAL | id, nombre |
| INVENTO | id, nombre, nivel |
| TECNOLOGIA | id, nombre, nivel, coste_pi |
| RECETA | id, tipo, invento_id FK, tecnologia_id FK |

**Relaciones clave:**

- USER N:M PARTIDA
- PARTIDA 1:N CASILLA
- CASILLA N:1 TIPO_CASILLA
- CASILLA N:1 USER
- TIPO_CASILLA N:M MATERIAL (atributo: cantidad)
- TIPO_CASILLA N:M TECNOLOGIA (requiere)
- TIPO_CASILLA N:M INVENTO (requiere)
- PARTIDA N:M MATERIAL (atributos: cantidad, esta_activo)
- RECETA consume MATERIAL (cantidad), TECNOLOGIA e INVENTO
- RECETA produce 1 INVENTO o 1 TECNOLOGIA
- INVENTO desbloquea INVENTO
- INVENTO desbloquea TECNOLOGIA
- TECNOLOGIA desbloquea TECNOLOGIA

---

### 3. Tipos de casillas básicas y evolución

5 tipos: Bosque, Cantera, Río, Prado, Mina. Cada una tiene 4 niveles. Evolucionar es gratuito pero algunos niveles requieren tener cierta tecnología desbloqueada. Cada combinación tipo+nivel es una fila en TIPO_CASILLA con sus materiales asociados.

---

### 4. Casillas avanzadas

9 casillas avanzadas que reemplazan a una básica cuando se cumplen los requisitos de tecnología e invento:

| Casilla avanzada | Viene de | Tecnología | Invento |
|---|---|---|---|
| Granja Organizada | Río nv3+ o Prado nv4 | Agricultura | Arado |
| Cantera de Sílice | Cantera nv3+ | Herramientas de piedra | Hacha |
| Mina de Minerales | Mina nv3+ | Metalurgia y aleaciones | — |
| Pozo de Goma y Resina | Bosque nv3+ | Química | — |
| Extractor de Gases | Cualquier nv4 | Química | Compresor |
| Planta de Silicio | Cantera de Sílice | Computación | Microscopio |
| Reactor de Carbono | Mina de Minerales | Nanotecnología | — |
| Laboratorio Magnético | Mina de Minerales | Electricidad | Batería |
| Puerto Espacial | Cualquier casilla libre | Tecnología Espacial | Satélite |

---

### 5. Árbol tecnológico

30 tecnologías desde Herramientas de piedra hasta Terraformación. Cada una tiene un coste en materiales, requiere ciertos inventos previos y produce mejoras: desbloqueos de inventos, bonificaciones de producción o reducción de costes.

---

### 6. Árbol de inventos

31 inventos desde Cuerda hasta Nave de asentamiento interestelar. Cada uno requiere una tecnología previa y materiales específicos del inventario común. La Nave de asentamiento interestelar es el objeto final que gana la partida.

**Materiales de la Nave de asentamiento interestelar:**
acero ×300, silicio ×500, hidrogeno ×500, vidrio ×150, cobre ×100, agua ×1000.

---

### 7. Interfaz

- Mapa central en forma de matriz de casillas
- Inventario común a la derecha (iconos activos/inactivos con cantidad)
- Panel de votación a la izquierda
- Chat opcional
- Login y registro de usuario
- Selección o creación de equipo/partida
- Ventana de Tecnologías e Inventos accesible desde el Pueblo (casilla central, solo frontend)
- Ranking de equipos visible

---

### 8. Ciclo de juego

- **Jornada** = turnos individuales simultáneos + turno común de votación
- Cada jugador realiza 2 acciones (explorar o evolucionar) + votación
- Tiempo máximo por jornada: 2 horas desde la primera acción
- Si no se actúa en 2h, se pierden las acciones y el voto
- Al inicio de cada jornada se generan los recursos de las casillas exploradas
- Votación: se construye/investiga lo más votado mientras haya recursos; en empate, aleatorio; opción de ahorrar sin gastar en esa jornada
- Los turnos individuales son simultáneos; cuando todos terminan (o expira el tiempo) se ejecuta el turno común
- Si un jugador abandona, sus casillas siguen generando recursos pero no se pueden evolucionar

---

### 9. Gestión del repositorio

- Rama `main` protegida — solo merge por PR con al menos 1 aprobación
- Ramas de feature por tarea: `feat/BD-01`, `feat/BACK-03`, etc.
- Commits con Conventional Commits: `feat:`, `fix:`, `chore:`, `docs:`
- Scripts de BD documentados y versionados en el repo bajo `/scripts/db/`
- README con instrucciones de instalación, configuración y arranque local

---

## Instrucciones de output

Genera el listado completo de tareas ordenadas por dependencia lógica, agrupadas por categoría en este orden: **BD → BACK → FRONT**.

Para cada tarea usa este formato:

```
### [ID] Título de la tarea

- **Developer:** DEV-1 / DEV-2
- **Dependencias:** ID de tareas previas o "Ninguna"
- **Descripción:** Qué hay que implementar técnicamente
- **Criterios de aceptación:**
  - [ ] Criterio 1
  - [ ] Criterio 2
  - [ ] Criterio 3
```

Al final incluye una tabla resumen con el recuento de tareas por developer y categoría para verificar el equilibrio:

| Categoría | DEV-1 | DEV-2 | Total |
|---|---|---|---|
| BD | | | |
| BACK | | | |
| FRONT | | | |
| **Total** | | | |
