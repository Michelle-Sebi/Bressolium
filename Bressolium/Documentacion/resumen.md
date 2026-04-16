# RESUMEN

Se trata de un juego estratégico cooperativo en el que varios jugadores (entre uno y cinco) controlan juntos el desarrollo de una civilización.
La interfaz principal es un panel compartido donde se ven los recursos globales (como materiales, inventos y tecnologías). 

El tablero es fijo y está compuesto por casillas. Se compone de 225 casillas (matriz de 15×15). Cada casilla genera un tipo de material asignado de forma aleatoria al principio de la partida. Cada jugador comienza la partida en una casilla asignada.

El juego se desarrolla de forma cíclica en unidades llamadas “Jornadas”. Cada jornada comprende todos los turnos individuales más un turno del equipo, en el que se ejecutan los resultados de las votaciones individuales, consumiéndose los recursos invertidos en la ejecución de las votaciones. Al inicio de la jornada siguiente, se generan los recursos correspondientes a las casillas descubiertas y evolucionadas así como las investigaciones tecnológicas y las creaciones de inventos.

En cada turno individual el jugador tiene dos tipos de acciones posibles: explorar o evolucionar casillas. Puede hacer dos acciones por turno, es decir, explorar dos casillas, evolucionar dos casillas o explorar una casilla y evoluciona otra.  Explorar implica descubrir que recursos produce una casilla adyacente a las ya descubiertas. Evolucionar casilla conlleva mejorar la producción, aumentándola o generando recursos nuevos. Después de las dos acciones se pasa a la votación.

En cada jornada, después de todos los turnos individuales, el equipo tiene que tomar una decisión conjunta mediante votación. Tiene que elegir si investigan (tecnologías) o crean (inventos) con los materiales que tienen en el inventario. Al investigar o crear se desbloquean nuevas opciones de investigación/creación o de generación de materiales en las casillas ya exploradas. La creación del invento Avión permitirá explorar casillas que no sean adyacentes. 

Al terminar el turno, todas estas acciones se combinan y los recursos se suman al almacén común, es decir existe un inventario común del equipo.

Con estos recursos acumulados, los jugadores deciden mediante votación qué inventos o tecnologías crear (como la rueda, el arado o un acueducto). Los inventos construidos desbloquean nuevas tecnologías dentro de un árbol tecnológico (por ejemplo, agricultura, transporte o ingeniería).

La meta es alcanzar la tecnología final, como “Viaje Espacial” y alcanzar la terraformación de otro planeta.

Sin embargo, a lo largo de la partida aparecen eventos adversos aleatorios —sequías, invasiones, etc.— que consumen recursos y obligan a los jugadores a organizarse bien y cooperar.

**Inicio del juego**

Al iniciarse el juego el jugador hace login y se crea un usuario. Tiene que elegir un equipo al que unirse o crear uno propio. Para elegir puede buscar el nombre de un equipo que quiera de un listado o que le asignen uno de forma aleatoria. Un jugador puede pertenecer a varios equipos. 

Al iniciar la partida, la interfaz muestra una ventana central con un mapa con las casillas de los recursos en forma de matriz de cuadrados.

A la derecha del mapa se muestra el inventario general con los recursos comunes del equipo. En este inventario hay iconos representado los distintos recursos. Los recursos estarán activos si hay casillas que los están produciendo e inactivos (icono gris, sin nombre) si todavía no se producen. Se muestra la cantidad de recursos acumulados que hay en cada momento.

A la izquierda del mapa se muestra una ventana donde los jugadores votan en qué invertir los recursos disponibles durante el turno común para crear tecnologías o inventos en ese turno. También podría existir una ventana de chat para conversación. 

Cuando la partida comienza, cada jugador es colocado en una casilla del mapa separado del resto de jugadores de forma equidistante y de ahí puede ir expandiendose a las casillas colindantes. Los recursos generado por la casilla inicial pasan inmediatamente al inventario general. En el centro del mapa hay una casilla especial que es el Pueblo. Haciendo click en esta casilla se abrirá la ventana de [Tecnologías e Inventos](https://www.notion.so/Evoluci-n-de-tecnolog-as-e-inventos-324acdd692d780c6ab65cd91f11ebe46?pvs=21). En esta ventana se mostrará un inventario de Tecnologías e Inventos, elementos únicos que se crean mediante votación pagando los recursos correspondientes y que desbloquan otras Tecnologías e Inventos que permiten a su vez nuevas evoluciones de las casillas y la creación de otros inventos.

Hay 5 tipos de casillas iniciales (Bosque, Cantera, Rio, Prado y Mina) que generan recursos base. Como ya se ha mencionado, cada jugador puede realizar dos acciones por turno, consistentes en explorar o evolucionar. Una vez explorada una casilla se puede usar una acción para subirla de nivel y que aumente la producción de recursos y aumentar el tipo de recursos que produce. Los turnos de los jugadores se ejecutan de forma simultánea. Cuando todos los jugadores han completado su turno ( 2 acciones + votación ) se pasa al turno común, donde se ejecutan los resultados de la votación.

**Ciclo de juego:**

Primer turno: el jugador inicia en su casilla dentro del mapa de su equipo. Puede explorar las casillas que hay alrededor (arriba, abajo, izquierda o derecha). El jugador puede hacer dos acciones por turno: explorar y/o evolucionar casillas.

Una vez efectuadas sus dos acciones (y no antes) se pasa a la fase de votación, donde aparece en una pantalla las  [Tecnologías e Inventos](https://www.notion.so/Evoluci-n-de-tecnolog-as-e-inventos-324acdd692d780c6ab65cd91f11ebe46?pvs=21) que se pueden hacer con los recursos existentes. Cada jugador vota su propuesta y al final de la jornada se construyen/investigan los más votados por orden mientras la cantidad de recursos lo permita . Si hay empate se hace uno aleatorio entre los empatados. Hay una opción que se puede marcar si no se quieren gastar recursos en esta ronda para ahorrar para la siguiente.

Este proceso se realiza por cada jugador, de forma simultánea . Cada jornada dura un máximo de 2 horas desde que el primer jugador la inicia con su primera acción. Si un jugador no vota o no realiza acciones pasado ese tiempo, su voto y sus acciones se pierden y se pasa al turno común y a la siguiente jornada. Si todos los jugadores votan y acaban su turno individual antes de que ese tiempo se agote el turno se completa y se pasa la siguiente jornada.

Cuando empieza la siguiente jornada se añaden los recursos generados al inventario común. Al inicio de la Jornada también se generarán Puntos de Investigación en función de diversos parámetros: número y nivel de casillas exploradas y tecnologías e inventos ya investigados. Estos Puntos de Investigación pueden desbloquear nuevas posibilidades de evolución tecnológica o invención.

**Fin del Juego y casos excepcionales:**

Cada casilla explorada, evolución, Tecnología e Investigación proporcionan un número de puntos al equipo. Hay un ranking donde se ven otros equipos que compiten en el mismo servidor por llegar los primeros a la terraformación y ganar la partida. 

Opciones competitivas: Modo simultáneo entre varios equipos. Cuando los 3 primeros equipos han alcanzado la terraformación la competición se “reinicia” y las partidas se resetean, de manera que el resto de equipos pierden. 

Si un jugador abandona la partida las casillas se quedan en su estado actual y generan cada Jornada los recursos correspondientes, pero no se pueden evolucionar.

Aunque la primera versión solo tendrá una opción estética se contempla que más adelante se pueda elegir el tipo de civilización que se quiera desarrollar: clásica, steampunk, kawaii.

Exploras y evolucionas casillas

Investigas tecnologías 

Creas inventos

[Entidad-relación Canva](https://www.canva.com/design/DAHEkAP6rsY/1ZQigMMa_cdW2YXSRuv5gQ/view?utm_content=DAHEkAP6rsY&utm_campaign=designshare&utm_medium=link2&utm_source=uniquelinks&utlId=h19b2618674)

[WIREFRAME](https://www.notion.so/WIREFRAME-31aacdd692d780e99f4ae244fb8f8eb2?pvs=21)

[Casillas](https://www.notion.so/Casillas-324acdd692d7808bb55cfcf65d15d748?pvs=21)

[Evolución de tecnologías e inventos](https://www.notion.so/Evoluci-n-de-tecnolog-as-e-inventos-324acdd692d780c6ab65cd91f11ebe46?pvs=21)

[Épicas e Historias de usuario](https://www.notion.so/picas-e-Historias-de-usuario-325acdd692d780b289ced3927dfa6dee?pvs=21)

[Definición de requisitos:](https://www.notion.so/Definici-n-de-requisitos-32aacdd692d7806eba58cbef5d7554ec?pvs=21)