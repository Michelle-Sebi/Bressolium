#### **1. Requisitos Funcionales (RF)**

**Épica 1: Gestión de Usuarios y Equipos**

| Nombre del requisito | Definición | Prioridad |
| --- | --- | --- |
| RF1.1 | El sistema debe permitir el registro de usuarios mediante email y contraseña cifrada. | Must |
| RF1.2 | El sistema debe validar que el nombre del equipo sea único en el servidor. | Must |
| RF1.3 | El sistema debe permitir el almacenamiento de la relación “Jugador–Equipo” de 1 a N (un jugador en varios equipos). | Should |
| RF1.4 | El sistema debe aplicar un “Skin” o set de activos visuales según la civilización elegida al crear el equipo. | Could |

**Épica 2: El Tablero y la Exploración**

| Nombre del requisito | Definición | Prioridad |
| --- | --- | --- |
| RF2.1 | Generación de matriz de $N \times M$ casillas con asignación aleatoria de tipo (Bosque, Cantera, Río, Prado, Mina). | Must |
| RF2.2 | Cálculo de posición equidistante para jugadores al inicio de la partida. | Must |
| RF2.3 | Lógica de adyacencia: el sistema solo permitirá explorar casillas en ejes X/Y (arriba, abajo, izquierda, derecha) respecto a las ya descubiertas. | Must |
| RF2.4 | Sistema de niveles de casilla: cada evolución aumenta el multiplicador de producción de recursos base. | Must |

**Épica 3: Mecánicas de Turno y Cooperación**

| Nombre del requisito | Definición | Prioridad |
| --- | --- | --- |
| RF3.1 | Contador de acciones: el sistema debe bloquear acciones adicionales tras consumir las 2 permitidas por jornada. | Must |
| RF3.2 | Sincronización del Inventario Común: actualización en tiempo real de los recursos para todos los miembros del equipo. | Must |
| RF3.3 | Motor de Votación: registrar votos individuales y ejecutar el cierre de jornada basado en la mayoría simple o azar en caso de empate. | Must |
| RF3.4 | Cron Job de Jornada: cierre automático de la jornada al cumplirse 120 minutos desde la primera acción registrada. | Must |
| RF3.5 | Chat en tiempo real: persistencia de mensajes durante la jornada actual. | Could |

**Épica 4: Tecnología y Meta**

| Nombre del requisito | Definición | Prioridad |
| --- | --- | --- |
| RF4.1 | Árbol de dependencias: las tecnologías deben tener requisitos previos (ej. “Rueda” antes que “Carro”). | Must |
| RF4.1.1 | El sistema debe contar con un diccionario de “Objetos de Progreso” (Tecnologías/Inventos). Cada objeto debe tener: ID, Nombre, Tipo (Tecnología o Invento), Coste_Recursos (diccionario de materiales), Requisitos_Previos (lista de IDs) y Efecto_Script (qué mejora aplica). | Must |
| RF4.1.2 | El sistema debe impedir que una Tecnología sea votable si sus requisitos previos no han sido completados al 100% en jornadas anteriores (ej.: no se puede votar “Navegación” si no se ha investigado “Carpintería”). | Must |
| RF4.1.3 | Los Inventos/Tecnologías deben poder ejecutar tres tipos de cambios: (1) Desbloqueo (ver nuevas Tecnologías), (2) Multiplicador (aumenta producción; ej. Arado = +20% Comida) y (3) Nueva Acción (habilita mecánicas; ej. Avión = exploración no adyacente). | Must |
| RF4.1.4 | Al interactuar con la casilla “Pueblo”, se debe desplegar un menú que filtre: Disponibles (recursos y requisitos OK), Bloqueados (falta recursos o requisitos; mostrar en gris/candado) y Completados (ya investigados). | Must |
| RF4.1.5 | Al inicio de cada jornada, el sistema debe calcular los Puntos de Investigación (PI): $PI = sum(text{Nivel de Casillas}) + text{Bonos de Tecnologías activas}$. Estos puntos se restan al “comprar” una tecnología, similar a los materiales. | Must |
| RF4.2 | Trigger de Eventos: probabilidad de disparar un evento adverso al inicio de cada jornada que reste un porcentaje de recursos. | Could |
| RF4.3 | Modificador “Avión”: al activarse, el sistema debe eliminar la validación de adyacencia en la función de exploración. | Should |

**2. Requisitos No Funcionales (RNF)**

| Nombre del requisito | Definición | Prioridad |
| --- | --- | --- |
|  |  |  |
| RNF-E1 (Escalabilidad) | El servidor debe soportar al menos 100 equipos simultáneos (hasta 500 jugadores conectados). | Should |
| RNF-S1 (Seguridad) | Todas las comunicaciones entre cliente y servidor deben estar bajo protocolo HTTPS/WSS. | Must |
| RNF-D1 (Disponibilidad) | El estado de la partida debe guardarse en base de datos cada vez que finalice una acción para evitar pérdida de progreso por desconexión. | Must |
| RNF-U1 (Usabilidad) | La interfaz debe ser intuitiva, permitiendo acceder a la ventana de tecnologías (Pueblo) con un máximo de 2 clics. | Must |

**3. Matriz de Priorización (Resumen)**

Resumen por prioridad:

- **Must (Crítico):** Requisitos imprescindibles para que el juego funcione (autenticación/equipos, mapa/exploración, turnos/jornadas, inventario compartido, votación/cierre, base tecnológica y NFR de seguridad, disponibilidad y usabilidad).
- **Should (Importante):** Funcionalidades que mejoran la experiencia y completan el sistema (multiequipos, modal del Pueblo, eventos).
- **Could (Deseable):** Extras de calidad/estética o comunicación (chat, skins).

| Prioridad | Nombre del requisito | Definición (resumen) |
| --- | --- | --- |
| Must | RF1.1 | Registro de usuarios por email + contraseña cifrada. |
| Must | RF1.2 | Nombre de equipo único en el servidor. |
| Should | RF1.3 | Relación Jugador–Equipo 1:N (un jugador en varios equipos). |
| Could | RF1.4 | Aplicar “skin”/activos visuales según civilización. |
| Must | RF2.1 | Generación de mapa $N \times M$ con tipos de casilla aleatorios. |
| Must | RF2.2 | Posición inicial equidistante de jugadores. |
| Must | RF2.3 | Exploración solo adyacente (X/Y) respecto a casillas descubiertas. |
| Must | RF2.4 | Niveles de casilla: evolucionar aumenta multiplicador de producción. |
| Must | RF3.1 | Límite de 2 acciones por jornada (bloqueo de acciones extra). |
| Must | RF3.2 | Inventario común sincronizado en tiempo real para el equipo. |
| Must | RF3.3 | Votación: registrar votos y cerrar jornada por mayoría o azar en empate. |
| Must | RF3.4 | Cierre automático de jornada a los 120 min desde la primera acción. |
| Could | RF3.5 | Chat en tiempo real con persistencia durante la jornada actual. |
| Must | RF4.1 | Árbol de dependencias: tecnologías con requisitos previos. |
| Must | RF4.1.1 | Diccionario de objetos (Tecnologías/Inventos) con atributos obligatorios. |
| Must | RF4.1.2 | Bloquear votación de tecnologías si prerrequisitos no están completados. |
| Must | RF4.1.3 | Recompensas: desbloqueo, multiplicador o nueva acción (p.ej. Avión). |
| Should | RF4.1.4 | Modal del Pueblo: disponibles/bloqueados/completados con filtros. |
| Must | RF4.1.5 | Cálculo de PI por jornada y gasto de PI para comprar tecnologías. |
| Should | RF4.2 | Eventos adversos aleatorios al inicio de jornada (resta de recursos). |
| Must | RF4.3 | Modificador “Avión”: exploración sin validación de adyacencia. |
| Must | RNF-P1 (Rendimiento) | Tiempo de respuesta de UI &lt; 200ms en acciones clave. |
| Should | RNF-E1 (Escalabilidad) | Soportar ≥ 100 equipos simultáneos (hasta 500 jugadores). |
| Must | RNF-S1 (Seguridad) | Comunicaciones bajo HTTPS/WSS. |
| Must | RNF-D1 (Disponibilidad) | Persistencia del estado en BD al finalizar cada acción. |
| Must | RNF-U1 (Usabilidad) | Acceso a tecnologías (Pueblo) en máximo 2 clics. |