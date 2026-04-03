## Propuesta de estructura

**Duración estimada de partida:** 40–60 turnos
**Niveles por casilla:** 4 niveles
**Coste de evolución:** recursos + tecnología requerida

---

## Coste de evolución de casillas

La evolución no cuesta recursos del inventario común pero en niveles altos requiere tener una tecnología desbloqueada. 

| Nivel | Coste | Tecnología requerida |
| --- | --- | --- |
| 1 → 2 | Gratis | Ninguna |
| 2 → 3 | Gratis | Depende de la casilla |
| 3 → 4 | Gratis | Depende de la casilla |
| Básica → Avanzada | Gratis | Tecnología + Invento según casilla |

---

El flujo por casilla quedaría así:

Casilla básica nivel 1
↓ evolucionar (gratis)
Casilla básica nivel 2, 3, 4
↓ nivel 3+ o 4 (según casilla) + tecnología + invento
Casilla avanzada
↓ tecnología superior + invento
Casilla avanzada de nivel 2 (Planta de Silicio, Reactor, Laboratorio)
↓ Tecnología Espacial + Satélite
Puerto Espacial

## Progresión por tipo de casilla

---

### Bosque

| Nivel | Materiales producidos | Tecnología requerida |
| --- | --- | --- |
| 1 | `roble` ×5, `pino` ×5 | — |
| 2 | `roble` ×8, `pino` ×8, `cedro` ×5 | — |
| 3 | `roble` ×10, `pino` ×10, `cedro` ×10, `carbon-natural` ×5 | — |
| 4 | todos ×12, `huesos` ×5, `pieles` ×5 | Ganadería |

→ Casilla avanzada: Pozo de Goma y Resina (nivel 3+ + Química)

---

### Cantera

| Nivel | Materiales producidos | Tecnología requerida |
| --- | --- | --- |
| 1 | `silex`, `obsidiana` (×5) | — |
| 2 | `silex`, `obsidiana` (×8), `granito` (×5) | — |
| 3 | todos (×10), `cuarzo` (×5) | — |
| 4 | todos (×12), `caolinita` (×5) | Cerámica y alfarería |

**Casilla avanzada:**

- Nivel 3+ + Herramientas de piedra + Hacha → **Cantera de Sílice**

---

### Río

| Nivel | Materiales producidos | Tecnología requerida |
| --- | --- | --- |
| 1 | `agua`, `cana-comun` (×5) | — |
| 2 | `agua` (×8), `cana-comun` (×8), `totora` (×5) | — |
| 3 | todos (×10), `tierras-fertiles` (×5) | — |
| 4 | todos (×12), `carrizo` (×5), `peces` (×5) | Agricultura |

**Casilla avanzada disponible:**

- Nivel 3+ + Agricultura + Arado → **Granja Organizada**

---

### Prado

| Nivel | Materiales producidos | Tecnología requerida |
| --- | --- | --- |
| 1 | `lino`, `yute` (×5) | — |
| 2 | `lino`, `yute` (×8), `canamo` (×5) | — |
| 3 | todos (×10), `pieles` (×5) | — |
| 4 | todos (×12), `lana` (×5), `tendones` (×5) | Ganadería |

**Casilla avanzada disponible:**

- Nivel 4 + Ganadería + Arado → **Granja Organizada** *(comparte casilla avanzada con Río)*

---

### Veta de Roca Dura (Mina)

| Nivel | Materiales producidos | Tecnología requerida |
| --- | --- | --- |
| 1 | `cobre` (×5) | — |
| 2 | `cobre` (×8), `hierro` (×5) | Herramientas de piedra |
| 3 | `cobre`, `hierro` (×10), `estano` (×5) | Metalurgia y aleaciones |
| 4 | todos (×12), `grafito` (×5), `sal` (×5) | Metalurgia y aleaciones |

**Casilla avanzada disponible:**

- Nivel 3+ + Metalurgia y aleaciones → **Mina de Minerales**

---

## Casillas avanzadas  — requisitos y producción

| **Casilla Avanzada** | **Desbloqueo Requerido** | **Materiales Producidos** | **Justificación** |
| --- | --- | --- | --- |
| **Granja Organizada** | **Tecnología: Agricultura** e **Invento: Arado** | `algodon`, `aceites-vegetales`, `tierras-fertiles` (mejorado) | Permite la producción intensiva de cultivos específicos. |
| **Cantera de Silice** | **Tecnología: Herramientas de Piedra** e **Invento: Hacha** (mejorada) | `arena-de-silice`, `arena-de-cuarzo` | Necesaria para la producción de **vidrio** (invento) y posterior **silicio**. |
| **Mina de Minerales** | **Tecnología: Metalurgia y Aleaciones** | `plata`, `oro`, `plomo`, `uranio`, `neodimio`, `lantano`, `cerio` | Permite la extracción de metales preciosos y tierras raras para electrónica avanzada. |
| **Pozo de Goma y Resina** | **Tecnología: Química** | `ambar`, `goma-arabiga`, `latex`, `resinas-inflamables` | Materiales clave para química, aislantes y productos de caucho. |
| **Extractor de Gases** | **Tecnología: Química** e **Invento: Compresor** (intermedio) | `hidrogeno`, `oxigeno`, `nitrogeno`, `gases-naturales` | Elementos puros esenciales para cohetes y química avanzada. |

| **Casilla Avanzada** | **Desbloqueo Requerido** | **Materiales Producidos** | **Justificación** |
| --- | --- | --- | --- |
| **Planta de Silicio** | **Tecnología: Computación** e **Invento: Microscopio** | `silicio`, `minerales-semiconductores`, `cristales-naturales` | Purificación de silicio, la base de la computación y la electrónica. |
| **Reactor de Carbono** | **Tecnología: Nanotecnología** | `carbono`, `fosforo`, `azufre` | Generación de carbono puro y otros elementos necesarios para materiales compuestos. |
| **Laboratorio Magnético** | **Tecnología: Electricidad** e **Invento: Batería** | `materiales-magneticos-naturales`, `neodimio` (procesado), `materiales-aislantes-naturales` (procesados) | Concentración y procesamiento de materiales para electrónica y almacenamiento de energía. |
| **Puerto Espacial/Sitio de Lanzamiento** | **Tecnología: Tecnología Espacial** e **Invento: Satélite** | **No produce materiales.** | Es una casilla necesaria para la victoria. |

| Casilla avanzada | Viene de | Tecnología | Invento |
| --- | --- | --- | --- |
| **Granja Organizada** | Río nv3+ o Pastizal nv4 | Agricultura | Arado |
| **Cantera de Sílice** | Yacimiento nv3+ | Herramientas de piedra | Hacha |
| **Mina de Minerales** | Veta nv3+ | Metalurgia y aleaciones | — |
| **Pozo de Goma y Resina** | Bosque nv3+ | Química | — |
| **Extractor de Gases** | Cualquier nv4 | Química | Compresor |
| **Planta de Silicio** | Cantera de Sílice | Computación | Microscopio |
| **Reactor de Carbono** | Mina de Minerales | Nanotecnología | — |
| **Laboratorio Magnético** | Mina de Minerales | Electricidad | Batería |
| **Puerto Espacial** | — | Tecnología Espacial | Satélite |