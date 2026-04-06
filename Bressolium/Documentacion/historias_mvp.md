# Historias de Usuario - Bressolium MVP (Modelo Gherkin)

A continuación se detallan las historias de usuario aprobadas para el MVP, redactadas utilizando la sintaxis BDD (Behavior-Driven Development) estilo Gherkin, donde cada funcionalidad se describe con `Dado`, `Cuando`, `Entonces`.

---

## 👤 Épica 1: Gestión de Usuarios y Equipos

### HU 1.1 - Registro y Login
**Característica:** Autenticación de jugador
  **Escenario:** El jugador accede a su cuenta para guardar el progreso.
    **Dado** que el jugador accede a la pantalla de acceso
    **Cuando** introduce sus credenciales válidas
    **Entonces** el sistema inicia su sesión de forma segura
    **Y** redirige al jugador a su menú principal de selección de equipos.

### HU 1.2 - Creación de Equipo
**Característica:** Gestión de equipo propio
  **Escenario:** Un jugador crea un equipo con nombre único.
    **Dado** que un jugador autenticado está en el menú de equipos
    **Cuando** elige crear un equipo e introduce un nombre que no existe en el servidor
    **Entonces** el sistema registra el nuevo equipo (Partida)
    **Y** le otorga acceso a la instancia generada.

### HU 1.3 - Unirse a Equipo (Búsqueda)
**Característica:** Búsqueda y unión a equipos
  **Escenario:** Un jugador se une a un equipo de amigos.
    **Dado** que un jugador autenticado está buscando partidas
    **Cuando** introduce el nombre exacto de un equipo existente y le da a unirse
    **Entonces** el sistema lo añade como miembro de ese equipo
    **Y** le muestra el estado actual del juego de ese equipo.

### HU 1.4 - Asignación Aleatoria
**Característica:** Matchmaking rápido
  **Escenario:** Un jugador solitario busca equipo rápidamente.
    **Dado** que un jugador autenticado selecciona "Unirse aleatoriamente"
    **Cuando** envía la petición
    **Entonces** el sistema busca un equipo con huecos libres (menos de 5 miembros)
    **Y** lo asigna de forma automática sin necesidad de más interacción.

### HU 1.5 - [DESCARTADO] Elección de civilización
**Característica:** Personalización temática
  **Nota:** No se implementará en el MVP para reducir complejidad técnica.

### HU 1.6 - Selector de Equipo Activo
**Característica:** Multisesión en diferentes equipos
  **Escenario:** Cambio de equipo en el dashboard.
    **Dado** que un jugador pertenece a varios equipos
    **Cuando** accede al menú principal
    **Entonces** el sistema le lista todos sus equipos
    **Y** al pulsar sobre uno, carga dinámicamente el estado y mapa de esa partida concreta.

---

## 🗺️ Épica 2: El Tablero y la Exploración

### HU 2.1 - Inicialización del Mapa
**Característica:** Configuración del entorno de juego
  **Escenario:** Generación de casillas iniciales.
    **Dado** que una nueva partida comienza
    **Cuando** se renderiza el tablero por primera vez
    **Entonces** el sistema genera una matriz de casillas ocultas con materiales aleatorios
    **Y** asigna a cada jugador una casilla inicial equidistante visible.

### HU 2.2 - Exploración de Casillas
**Característica:** Expansión del territorio
  **Escenario:** El jugador revela una casilla nueva.
    **Dado** que es el turno de un jugador y tiene acciones restantes
    **Cuando** pulsa "Explorar" sobre una casilla oculta, contigua en eje X/Y a una descubierta
    **Entonces** el sistema invierte una acción
    **Y** revela el tipo de casilla (bosque, mina, etc.) y los materiales que genera.

### HU 2.3 - Mejora de Casilla (Evolución)
**Característica:** Progresión de la producción de recursos
  **Escenario:** El jugador sube de nivel una casilla existente.
    **Dado** que es el turno del jugador y tiene recursos o tecnologías suficientes
    **Cuando** pulsa "Evolucionar" en una de las casillas ya descubiertas
    **Entonces** el sistema gasta los recursos correspondientes y una acción
    **Y** la casilla sube al siguiente nivel multiplicando su producción base.

### HU 2.4 - Visualización de Recursos
**Característica:** Interfaz del inventario 
  **Escenario:** Actualización del panel lateral.
    **Dado** que el jugador está visualizando el mapa
    **Cuando** observa su panel lateral
    **Entonces** el sistema listará todos los iconos de recursos
    **Y** se resaltarán a color los descubiertos, indicando la cantidad actual en el almacén común.

### HU 2.5 - Nodo Central (El Pueblo)
**Característica:** Acceso al menú de progresión tecnológica
  **Escenario:** Apertura del árbol de tecnología.
    **Dado** que el tablero principal está visible
    **Cuando** el jugador hace clic en la casilla especial "Pueblo" central
    **Entonces** se abrirá una ventana modal superpuesta
    **Y** mostrará el inventario completo de Tecnologías e Inventos filtradas por disponibilidad.

### HU 2.6 - Generación de Recursos Base
**Característica:** Creación del diccionario de terrenos
  **Escenario:** Asignación en partida nueva.
    **Dado** que el sistema genera una nueva matriz
    **Cuando** decide el tipo de casilla
    **Entonces** garantizará emplear equitativamente los 5 tipos base: Bosque, Cantera, Río, Prado y Mina para dar balance.

---

## 🗳️ Épica 3: Mecánicas de Turno y Cooperación

### HU 3.1 - Acciones de Turno
**Característica:** Limitador de jugadas individuales
  **Escenario:** Bloqueo tras consumir los movimientos.
    **Dado** que un jugador realiza acciones de "Explorar" o "Evolucionar"
    **Cuando** el contador de acciones consumidas llega a dos en la jornada actual
    **Entonces** el sistema bloquea cualquier interacción adicional en el mapa
    **Y** avisa al jugador que pase a la fase de votación.

### HU 3.2 - Inventario Común
**Característica:** Almacén unificado del equipo
  **Escenario:** Suma de recursos periódica asíncrona.
    **Dado** que la partida está transcurriendo
    **Cuando** ocurre la fase de cierre o carga visual asíncrona por parte del front
    **Entonces** el inventario común renderizado será el mismo para todos los miembros, reflejando el material recolectado y gastado sin desajustes.

### HU 3.3 - Sistema de Votación
**Característica:** Democracia para gastos de inventario
  **Escenario:** Emisión del voto por tecnología/invento.
    **Dado** que el jugador ha completado sus dos acciones de tablero
    **Cuando** envía su voto para crear una "Rueda" (Tecnología) en la interfaz
    **Entonces** el sistema registra la elección en el estado volátil de la jornada
    **Y** oculta las opciones de votación para él dejándole en espera del fin de jornada.

### HU 3.4 - Resolución de Empates
**Característica:** Solución de la votación
  **Escenario:** Dos tecnologías tienen los mismos votos al cierre de la jornada.
    **Dado** que el turno comun va a procesarse
    **Cuando** los votos recopilados detectan un empate a puntos de mayoría simple
    **Entonces** el sistema ejecuta una función aleatoria (Random) sobre las opciones empatadas
    **Y** selecciona una para ejecutar el descuento de materiales e investigación.

### HU 3.5 - Temporizador de Jornada
**Característica:** Límite máximo de turno vivo
  **Escenario:** Cierre forzoso por tiempo límite (120 min).
    **Dado** que una jornada acaba de comenzar
    **Cuando** transcurren 120 minutos desde la primera acción sin que todos hayan votado
    **Entonces** el Cron (o job asíncrono) asume los turnos restantes como abandonados
    **Y** ejecuta la jornada cerrándola obligatoriamente con los votos emitidos hasta el momento.

### HU 3.6 - Ejecución de la Jornada (Turno de Equipo)
**Característica:** Procesador de recompensas
  **Escenario:** Los inventos y recursos se calculan al final del día.
    **Dado** que la jornada recibe la señal de cierre (por tiempo o por voto de todos)
    **Cuando** el sistema procesa los resultados
    **Entonces** resta los materiales totales de la tecnología ganadora del inventario
    **Y** la marca como investigada para todo el equipo desbloqueando ramificaciones nuevas
    **Y** resetea los turnos individuales para la próxima jornada.

---

## 🌳 Épica 4: Tecnología y Meta

### HU 4.1 - Árbol Tecnológico
**Característica:** Reglas de dependencia (Prerrequisitos)
  **Escenario:** Intento de investigar algo sin la base necesaria.
    **Dado** que un equipo no ha investigado la "Rueda"
    **Cuando** mira la lista de tecnologías en El Pueblo
    **Entonces** la tecnología "Carro" debe estar deshabilitada y no podrá ser votada
    **Y** el sistema rechazará a nivel de API cualquier voto que intente saltarse el requisito.

### HU 4.3 - Condición de Victoria
**Característica:** Fin del estado de Partida
  **Escenario:** El equipo alcanza la Terraformación.
    **Dado** que la jornada finaliza
    **Cuando** la tecnología ganadora por votos y recursos es "Nave de asentamiento interestelar"
    **Entonces** el sistema marca el estado de la partida como "Finalizada/Ganada"
    **Y** paraliza futuros turnos publicando un mensaje de victoria en el ranking.



### HU 4.5 - Gestión de Abandono
**Característica:** Mitigación de jugadores inactivos
  **Escenario:** Un miembro del equipo deja de jugar en mitad del mes.
    **Dado** que se está ejecutando el cron de 120 minutos reiteradas veces
    **Cuando** se detecta de forma perenne a un jugador ausente
    **Entonces** sus casillas exploradas no se pierden y siguen sumando recursos base al pozo común
    **Y** el sistema no le cuenta (lo ignora) a la hora de verificar que "todos han votado" para acelerar las jornadas del equipo que sí juega activamente.
