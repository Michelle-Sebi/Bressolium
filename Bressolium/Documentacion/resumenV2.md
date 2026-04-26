# DOCUMENTO DE DISEÑO: BRESSOLIUM

**Bressolium** es un juego estratégico cooperativo en el que varios jugadores (entre uno y cinco) controlan juntos el desarrollo de una civilización en un tablero de mosaicos. Los jugadores exploran casillas adyacentes, evolucionan su producción de recursos y votan colectivamente sobre qué tecnologías inventar e investigar. El objetivo final es alcanzar la tecnología de **Terraformación** y construir la **Nave de Asentamiento Interestelar** para ganar la partida.

---

## 1. Inicio del Juego e Interfaz

### Acceso y Equipos
Al iniciarse el juego, el jugador hace login y crea un usuario. Debe elegir un equipo al que unirse o crear uno propio (1-5 miembros). Un jugador puede pertenecer a varios equipos. El equipo comparte un **inventario global** de recursos representados por iconos; estos estarán activos si hay casillas produciéndolos e inactivos (en gris) si aún no se generan.

### El Tablero y el Mapa
El tablero es una matriz cuadrada de 15x15. Cada casilla genera un tipo de material asignado aleatoriamente al principio. 

* **Casillas iniciales:** Hay 5 tipos (Bosque, Cantera, Río, Prado y Mina) que generan recursos base.
* **El Pueblo:** En el centro del mapa hay una casilla especial. Al hacer clic en ella, se abre la ventana de **Tecnologías e Inventos**, donde se gestionan las investigaciones únicas mediante votación.
* **Posicionamiento:** Cada jugador comienza la partida en una casilla asignada de forma equidistante al resto para expandirse colindantemente. Los recursos generados por la casilla inicial pasan al inventario general.

---

## 2. Ciclo de Juego: La Jornada

El juego se desarrolla de forma cíclica en unidades llamadas **“Jornadas”**. Una jornada dura un máximo de **2 horas** desde que el primer jugador la inicia. Si todos terminan antes, la jornada avanza inmediatamente; si alguien no actúa, sus acciones se pierden.

### Fase 1: Inicio de Jornada
Se generan los recursos correspondientes a las casillas descubiertas y evolucionadas. También se generan **Puntos de Investigación** basados en el número/nivel de casillas y tecnologías ya obtenidas, desbloqueando nuevas posibilidades de invención.

### Fase 2: Turnos Individuales (Simultáneos)
Cada jugador dispone de **dos acciones** por turno. Puede elegir cualquier combinación de:
* **Explorar:** Descubrir una casilla adyacente (arriba, abajo, izquierda o derecha). Al descubrirla, el recurso se suma al almacén común. (El invento **Avión** permitirá explorar casillas no adyacentes).
* **Evolucionar:** Mejorar una casilla ya explorada para aumentar la producción o generar recursos nuevos.
    * **Niveles:** El nivel N produce N tipos de recursos. El paso de Nv1 a Nv2 es gratuito, pero niveles superiores requieren tecnologías o inventos específicos.

### Fase 3: Votación Colectiva
Tras realizar las dos acciones, el jugador accede al panel de votación, dividido en dos zonas: **tecnologías** e **inventos**. El equipo decide en qué invertir los materiales del inventario (tecnologías o inventos como la rueda, el arado o un acueducto).
* Se muestran las opciones posibles según los recursos actuales.
* Los inventos pueden construirse **múltiples veces**: cada partida lleva un contador por invento. Algunos prerequisitos de inventos avanzados exigen una cantidad mínima (p.ej. la Nave de Asentamiento requiere 2× Acero y 2× Vidrio).
* Existe la opción de **"No gastar"** para ahorrar para la siguiente jornada.
* En caso de empate, el sistema elige uno al azar entre los empatados.

### Fase 4: Turno del Equipo (Resolución)
Se ejecutan los resultados de las votaciones por orden de apoyo mientras los recursos lo permitan, consumiéndose los materiales invertidos. Al investigar o crear, se desbloquean nuevas opciones de evolución en las casillas o nuevas ramas en el árbol tecnológico.

---

## 3. Elementos del Juego y Progresión

### Árbol Tecnológico e Inventos
Los inventos construidos desbloquean nuevas tecnologías dentro de un árbol (agricultura, transporte, ingeniería, etc.). Algunos aplican bonificadores permanentes:
* **Acueducto:** +30% producción en Río.
* **Hacha:** +25% producción en Bosque.
* **Energías Renovables:** +30% producción global.

### Recursos (42 tipos)
Están divididos en capas de progresión:
* **Capa Base/Intermedia:** Roble, Hierro, Carbón, Lana, etc.
* **Capa Avanzada (Nv 5):** Silicio, Hidrógeno, Oro, Látex, etc., generados por casillas especializadas (ej. Pozo de Goma, Extractor de Gases).

### Eventos Adversos
Suceden eventos aleatorios (sequías, invasiones, epidemias) que consumen recursos. Ciertas tecnologías (como la Penicilina o Refugios) mitigan estos impactos. No implementado en MVP

---

## 4. Fin del Juego y Ranking

* **Puntuación:** Cada exploración, evolución e investigación otorga puntos al equipo.
* **Modo Competitivo:** Los equipos compiten en un ranking global por ser los primeros en alcanzar la terraformación.
* **Reinicio:** Cuando los 3 primeros equipos logran la meta (Nave de Asentamiento Interestelar), la competición se "reinicia" y el resto de equipos pierden.
* **Abandono:** Si un jugador deja la partida, sus casillas siguen generando recursos base pero no pueden ser evolucionadas.

---

