#### **1. Requisitos Funcionales (RF)**

**Épica 1: Gestión de Usuarios y Equipos**

| Nombre del requisito | Definición | Prioridad |
| --- | --- | --- |
| RF1.1 | El sistema debe permitir el registro de usuarios mediante email y contraseña cifrada. | Must |
| RF1.2 | El sistema debe validar que el nombre del equipo sea único en el servidor. | Must |
| RF1.3 | El sistema debe permitir el almacenamiento de la relación “Jugador–Equipo” de 1 a N (un jugador en varios equipos). | Should |
| RF1.4 | [DESCARTADO] El sistema debe permitir elegir civilización. | Won't |

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
| RF3.2 | Sincronización del Inventario Común: actualización casi en tiempo real (mediante Long Polling u otra técnica asíncrona) de los recursos en el front. | Must |
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
| RF4.2 | Trigger de Eventos: probabilidad de disparar un evento adverso al inicio de cada jornada que reste un porcentaje de recursos. | Could |
| RF4.3 | Modificador “Avión”: al activarse, el sistema debe eliminar la validación de adyacencia en la función de exploración. | Could |

**2. Requisitos No Funcionales (RNF)**

| Nombre del requisito | Definición | Prioridad |
| --- | --- | --- |
|  |  |  |
| RNF-E1 (Escalabilidad) | El servidor debe soportar al menos 100 equipos simultáneos (hasta 500 jugadores conectados). | Could |
| RNF-S1 (Seguridad) | Todas las comunicaciones entre cliente y servidor deben estar bajo protocolo HTTPS/WSS. | Must |
| RNF-D1 (Disponibilidad) | El estado de la partida debe guardarse en base de datos cada vez que finalice una acción para evitar pérdida de progreso por desconexión. | Must |
| RNF-U1 (Usabilidad) | La interfaz debe ser intuitiva, permitiendo acceder a la ventana de tecnologías (Pueblo) con un máximo de 2 clics. | Must |

