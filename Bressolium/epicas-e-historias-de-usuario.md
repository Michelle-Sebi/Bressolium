## Épica 1: Gestión de Usuarios y Equipos

**Objetivo:** Permitir el acceso y la organización social de los jugadores.

- **HU 1.1 - Registro y Login:** Como nuevo jugador, quiero crear un usuario y loguearme para que mi progreso en el servidor quede guardado. Talla M
- **HU 1.2 - Creación de Equipo:** Como jugador, quiero crear un equipo nuevo con un nombre único para liderar una civilización con mis amigos. Talla M
- **HU 1.3 - Unirse a Equipo (Búsqueda):** Como jugador, quiero buscar un equipo por su nombre en un listado para unirme a mis aliados. Talla S
- **HU 1.4 - Asignación Aleatoria:** Como jugador solitario, quiero que el sistema me asigne un equipo aleatorio para empezar a jugar de inmediato sin buscar. Talla S
- **HU 1.5 - Elección de civilización:**  Como jugador, quiero que mi equipo pueda elegir una 'Cultura Base' (ej. Estética Ciberpunk, Steampunk o Clásica) para que el mapa y los iconos reflejen una identidad visual única.
- **HU 1.6 - Selector de Equipo Activo:** Como jugador multiequipo, quiero poder cambiar entre las partidas de mis diferentes equipos desde el menú principal. (Talla: S)

## 🗺️ Épica 2: El Tablero y la Exploración

**Objetivo:** Gestionar el mapa, la generación de recursos y el movimiento.

- **HU 2.1 - Inicialización del Mapa:** Como sistema, debo generar un tablero de casillas con materiales aleatorios y posicionar a cada jugador en una casilla inicial para comenzar la partida. Talla L-XL
- **HU 2.2 - Exploración de Casillas:** Como jugador, quiero explorar una casilla adyacente pagando un coste de recursos para descubrir qué materiales produce. L-XL
- **HU 2.3 - Mejora de Casilla (Evolución):** Como jugador, quiero investigar/evolucionar mi casilla para aumentar su nivel de producción y generar materiales más avanzados. M
- **HU 2.4 - Visualización de Recursos:** Como jugador, quiero ver en el panel lateral mis recursos activos (producidos) e inactivos (por descubrir) para planificar mi estrategia. L
- **HU 2.5 - Nodo Central (El Pueblo):** Como jugador, quiero interactuar con la casilla central de "Pueblo" para abrir la interfaz de Tecnologías e Inventos. (Talla: M)
- **HU 2.6 - Generación de Recursos Base:** Como sistema, debo asignar a las casillas uno de los 5 tipos iniciales (Bosque, Cantera, Río, Prado, Mina) al iniciar la partida. (Talla: S)

## 🗳️ Épica 3: Mecánicas de Turno y Cooperación

**Objetivo:** Regular el flujo de juego y la toma de decisiones grupal.

- **HU 3.1 - Acciones de Turno:** Como jugador, quiero realizar hasta dos jugadas (explorar/evolucionar) por turno para contribuir al desarrollo del equipo. M
- **HU 3.2 - Inventario Común:** Como sistema, debo sumar todos los recursos generados al final del turno en un almacén global accesible para todo el equipo. M
- **HU 3.3 - Sistema de Votación:** Como jugador, quiero votar entre las tecnologías disponibles después de mis jugadas para decidir en qué gastar los recursos comunes. M
- **HU 3.4 - Resolución de Empates:** Como sistema, debo elegir un elemento al azar entre los más votados en caso de empate para no bloquear el progreso. S
- **HU 3.5 - Temporizador de Jornada:** Como sistema, debo cerrar el turno automáticamente a las 2 horas o cuando todos voten para mantener el ritmo del servidor. S
- **HU 3.6 - Ejecución de la Jornada (Turno de Equipo):** Como sistema, al finalizar el tiempo o los turnos, debo procesar las votaciones, restar recursos del almacén común y entregar las recompensas (inventos/tecnología). (Talla: L)
- **HU 3.7 - Chat de Equipo:** Como jugador, quiero un canal de chat en la interfaz para coordinar la estrategia de votación con mis compañeros. (Talla: M)
- **HU 3.8 - Generación de Puntos de Investigación:** Como sistema, al inicio de cada jornada, debo calcular y sumar los puntos de investigación basados en el nivel de las casillas y tecnologías actuales. (Talla: M)

## 🌳 Épica 4: Tecnología, Eventos y Meta

**Objetivo:** El sistema de progresión y los desafíos externos.

- **HU 4.1 - Árbol Tecnológico:** Como equipo, queremos desbloquear nuevas tecnologías (ej. Rueda → Agricultura) para acceder a mejoras avanzadas y acercarnos a la terraformación. M
- **HU 4.2 - Eventos Adversos:** Como sistema, debo disparar eventos aleatorios (sequías, invasiones) que consuman recursos para obligar a los jugadores a coordinarse. M
- **HU 4.3 - Condición de Victoria:** Como equipo, queremos completar la tecnología "Viaje Espacial" para ganar la partida y aparecer en el ranking. M
- **HU 4.4 - Desbloqueo de Exploración No Adyacente (El Avión):** Como equipo, al construir el "Avión", queremos que el sistema permita explorar cualquier casilla del mapa sin que esté pegada a una ya descubierta. (Talla: L)
- **HU 4.5 - Gestión de Abandono:** Como sistema, si un jugador deja de participar, debo mantener sus casillas produciendo recursos de forma automática y desactivarlo de la votación (no esperarle) para no perjudicar al equipo. (Talla: S)

## 🏆 Épica 5: Competitividad y Servidor

**Objetivo:** La persistencia y el ranking global.

- **HU 5.1 - Ranking de Servidor:** Como jugador, quiero ver un ranking en tiempo real con los puntos de otros equipos para medir nuestra competitividad. M
- **HU 5.2 - Ciclo de Vida del Servidor:** Como sistema, debo reiniciar el servidor una vez que los 3 primeros equipos logren la terraformación para comenzar un nuevo ciclo de juego.

---

###