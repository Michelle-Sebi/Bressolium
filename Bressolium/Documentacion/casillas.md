# Casillas — Progresión de Recursos y Evolución

## Reglas Fundamentales de Diseño

### Regla Principal: Nivel N = N Tipos de Recursos

Cada casilla sigue una progresión estricta donde el nivel determina la cantidad de tipos de recursos distintos producidos:

| Nivel | Tipos de Recursos | Cantidad por Recurso | Total Anual |
|---|---|---|---|
| **1** | 1 tipo | 5 unidades | 5 |
| **2** | 2 tipos | 8 unidades cada | 16 |
| **3** | 3 tipos | 8 unidades cada | 24 |
| **4** | 4 tipos | 9 unidades cada | 36 |
| **5** | 2-5 tipos especializados | 4-10 unidades cada | variable |

### Requisitos de Evolución

| Transición | Coste en Recursos | Tecnología Requerida | Invento Requerido |
|---|---|---|---|
| **Nv1 → Nv2** | Gratis | Ninguno | Ninguno |
| **Nv2 → Nv3** | Gratis | Depende de casilla | Ninguno |
| **Nv3 → Nv4** | Gratis | Depende de casilla | Ninguno |
| **Nv4 → Nv5** | Gratis | Específica | Específico (excepto Mina) |

---

## Tipos de Casillas Base

### 1. Bosque

**Concepto**: Bosque primario. Evoluciona hacia un Pozo especializado de Goma y Resina que extrae materiales de árboles maduros.

| Nivel | Recursos Producidos | Cantidad | Tecnología Requerida | Invento Requerido |
|---|---|---|---|---|
| **1** | `roble` | ×5 | — | — |
| **2** | `roble`, `pino` | ×8 cada | — | — |
| **3** | `roble`, `pino`, `carbon-natural` | ×8 cada | — | — |
| **4** | `roble`, `pino`, `carbon-natural`, `pieles` | ×9 cada | **Ganadería** | — |
| **5** | `latex`, `resinas-inflamables` | ×8 cada; `mat-aisl-nat` | ×4 | **Química** | — |
| | **Pozo de Goma y Resina** | — | — | — |

**Bonificadores disponibles**:
- Control del Fuego: +20% producción
- Hacha: +25% producción

**Notas de diseño**:
- Nivel 4 desbloquea `pieles` (requisito: Ganadería), usado en invento Carro
- Nivel 5 especializado en materiales orgánicos avanzados:
  - `latex`: Penicilina
  - `resinas-inflamables`: Batería
  - `mat-aisl-nat`: Fibra Óptica
- `carbon-natural` (Nv3) se usa en Acero y Bombilla

---

### 2. Cantera

**Concepto**: Cantera de piedra. Evoluciona hacia una Cantera de Sílice especializada que extrae arenas finas mediante técnicas avanzadas.

| Nivel | Recursos Producidos | Cantidad | Tecnología Requerida | Invento Requerido |
|---|---|---|---|---|
| **1** | `silex` | ×5 | — | — |
| **2** | `silex`, `granito` | ×8 cada | — | — |
| **3** | `silex`, `granito`, `obsidiana` | ×8 cada | — | — |
| **4** | `silex`, `granito`, `obsidiana` | ×9 cada | **Cerámica y Alfarería** | — |
| **5** | `arena-de-silice`, `arena-de-cuarzo`, `cristales-nat`, `min-semi` | ×8 cada; `silicio` | ×10 | **Herramientas de Piedra** + **Hacha** | — |
| | **Cantera de Sílice** | — | — | — |

**Bonificadores disponibles**:
- Hacha: +25% producción (Nv5 Cantera desbloqueada por Hacha)

**Notas de diseño**:
- Nivel 3 añade `obsidiana` (usado en invento Cuchillo)
- Nivel 4 requiere Cerámica y Alfarería; 3 tipos de roca (caolinita eliminada — sin uso)
- Nivel 5 especializado: arenas finas y minerales tecnológicos
  - `arena-de-silice`: ingrediente en Vidrio y Microscopio
  - `arena-de-cuarzo`: óptica (Telescopio, Láser)
  - `cristales-nat`: Láser
  - `silicio`: Láser, Fibra Óptica, Teléfono Móvil, Satélite, Estación Espacial, Nave de Asentamiento Interestelar
  - `min-semi`: Teléfono Móvil

---

### 3. Río

**Concepto**: Fuente de agua. Evoluciona hacia un Extractor de Gases que separa hidrógeno, oxígeno y otros gases del agua mediante técnicas químicas.

| Nivel | Recursos Producidos | Cantidad | Tecnología Requerida | Invento Requerido |
|---|---|---|---|---|
| **1** | `agua` | ×5 | — | — |
| **2** | `agua`, `cana-comun` | ×8 cada | — | — |
| **3** | `agua`, `cana-comun`, `tierras-fertiles` | ×8 cada | — | — |
| **4** | `agua`, `cana-comun`, `tierras-fertiles` | ×9 cada | **Agricultura** | — |
| **5** | `hidrogeno` | ×10; `gases-naturales` | ×8 | **Química** | — |
| | **Extractor de Gases** | — | — | — |

**Bonificadores disponibles**:
- Acueducto: +30% producción
- Energías Renovables: +30% producción (global)

**Notas de diseño**:
- Nivel 2 añade `cana-comun` (usado en Refugios y Papel)
- Nivel 3 añade `tierras-fertiles` (usado en Penicilina)
- Nivel 4 requiere Agricultura
- Nivel 5 especializado (solo Química, sin invento requisito):
  - `hidrogeno`: Estación Espacial, Nave de Asentamiento Interestelar
  - `gases-naturales`: Satélite, Nave de Asentamiento Interestelar

---

### 4. Prado

**Concepto**: Pradera de cultivos. Evoluciona hacia una Granja Organizada con técnicas agrícolas intensivas que produce cultivos especializados.

| Nivel | Recursos Producidos | Cantidad | Tecnología Requerida | Invento Requerido |
|---|---|---|---|---|
| **1** | `lino` | ×5 | — | — |
| **2** | `lino`, `yute` | ×8 cada | — | — |
| **3** | `lino`, `yute`, `canamo` | ×8 cada | — | — |
| **4** | `lino`, `yute`, `canamo`, `lana` | ×9 cada | **Ganadería** | — |
| **5** | `tierras-fertiles` | ×8 | **Agricultura** + **Tejido** + **Conservación de Alimentos** | **Arado** |
| | **Granja Organizada** | — | — | — |

**Bonificadores disponibles**:
- Tejido: +20% producción
- Molino: +20% producción
- Edición Genética: +20% producción
- Energías Renovables: +30% producción (global)

**Notas de diseño**:
- Nivel 2-3: Fibras naturales (lino, yute, cáñamo para textiles y cuerdas)
- Nivel 4 requiere Ganadería; añade `lana` (×12, usado en Tela)
- Nivel 5 regenera `tierras-fertiles` (usado en Penicilina); requiere haber dominado textiles (Tejido) y conservación (Conservación de Alimentos) además de Agricultura y Arado; algodón y aceites eliminados por falta de uso

---

### 5. Mina

**Concepto**: Mina de roca dura con minerales. Evoluciona hacia una Mina de Minerales especializada que extrae minerales raros y preciosos en profundidad.

| Nivel | Recursos Producidos | Cantidad | Tecnología Requerida | Invento Requerido |
|---|---|---|---|---|
| **1** | `cobre` | ×5 | — | — |
| **2** | `cobre`, `hierro` | ×8 cada | **Herramientas de Piedra** | — |
| **3** | `cobre`, `hierro`, `estano` | ×10 cada | **Metalurgia y Aleaciones** | — |
| **4** | `cobre`, `hierro`, `estano`, `grafito` | ×12 cada | **Metalurgia y Aleaciones** | — |
| **5** | `oro`, `mat-mag-nat` | ×8 cada | **Metalurgia y Aleaciones** | **Brújula** |
| | **Mina de Minerales** | — | — | — |

**Bonificadores disponibles**:
- Energías Renovables: +30% producción (global)
- Computación Cuántica: +40% producción

**Notas de diseño**:
- Nivel 1: Base de bronce y aleaciones
- Nivel 2 requiere Herramientas de Piedra; añade `hierro`
- Nivel 3-4 requieren Metalurgia y Aleaciones:
  - Nivel 3: `estano` (Batería)
  - Nivel 4: `grafito` (Acero)
- Nivel 5 especializado (plata, neodimio, uranio eliminados — sin uso en inventos):
  - `oro`: Moneda
  - `mat-mag-nat`: Brújula, Teléfono Móvil

---

## Tabla Comparativa: Todos los Niveles

### Bosque

| Nivel | Recursos | Req. Tech | Req. Invento | Producción Total |
|---|---|---|---|---|
| 1 | roble×5 | — | — | 5 |
| 2 | roble×8 + pino×8 | — | — | 16 |
| 3 | roble×8 + pino×8 + carbon-natural×8 | — | — | 24 |
| 4 | roble×9 + pino×9 + carbon-natural×9 + pieles×9 | Ganadería | — | 36 |
| 5 | latex×8 + resinas-inflamables×8 + mat-aisl-nat×4 | Química | — | 20 |

### Cantera

| Nivel | Recursos | Req. Tech | Req. Invento | Producción Total |
|---|---|---|---|---|
| 1 | silex×5 | — | — | 5 |
| 2 | silex×8 + granito×8 | — | — | 16 |
| 3 | silex×8 + granito×8 + obsidiana×8 | — | — | 24 |
| 4 | silex×9 + granito×9 + obsidiana×9 | Cerámica y Alfarería | — | 27 |
| 5 | arena-de-silice×8 + arena-de-cuarzo×8 + cristales-nat×8 + silicio×10 + min-semi×8 | Herramientas de Piedra | Hacha | 42 |

### Río

| Nivel | Recursos | Req. Tech | Req. Invento | Producción Total |
|---|---|---|---|---|
| 1 | agua×5 | — | — | 5 |
| 2 | agua×8 + cana-comun×8 | — | — | 16 |
| 3 | agua×8 + cana-comun×8 + tierras-fertiles×8 | — | — | 24 |
| 4 | agua×9 + cana-comun×9 + tierras-fertiles×9 | Agricultura | — | 27 |
| 5 | hidrogeno×10 + gases-naturales×8 | Química | — | 18 |

### Prado

| Nivel | Recursos | Req. Tech | Req. Invento | Producción Total |
|---|---|---|---|---|
| 1 | lino×5 | — | — | 5 |
| 2 | lino×8 + yute×8 | — | — | 16 |
| 3 | lino×8 + yute×8 + canamo×8 | — | — | 24 |
| 4 | lino×9 + yute×9 + canamo×9 + lana×9 | Ganadería | — | 36 |
| 5 | tierras-fertiles×8 | Agricultura + Tejido + Conservación de Alimentos | Arado | 8 |

### Mina

| Nivel | Recursos | Req. Tech | Req. Invento | Producción Total |
|---|---|---|---|---|
| 1 | cobre×5 | — | — | 5 |
| 2 | cobre×8 + hierro×8 | Herramientas de Piedra | — | 16 |
| 3 | cobre×10 + hierro×10 + estano×10 | Metalurgia y Aleaciones | — | 30 |
| 4 | cobre×12 + hierro×12 + estano×12 + grafito×12 | Metalurgia y Aleaciones | — | 48 |
| 5 | oro×8 + mat-mag-nat×8 | Metalurgia y Aleaciones | Brújula | 16 |

---

## Especialización Nivel 5

Cada casilla tipo tiene una especialización única en Nivel 5, produciendo recursos avanzados:

| Casilla Tipo | Especialización (Nv5) | Requisito | Recursos (×8 cada) | Uso Principal |
|---|---|---|---|---|
| **Bosque** | Pozo de Goma y Resina | Química | Látex, Resinas Inflamables, Mat. Aislantes | Orgánicos avanzados: Penicilina, Batería, Fibra Óptica |
| **Cantera** | Cantera de Sílice | Herramientas de Piedra + Hacha | Arena Sílice, Arena Cuarzo, Cristales Nat., Silicio, Min. Semi. | Vidrio, óptica, semiconductores, tecnología espacial |
| **Río** | Extractor de Gases | Química | Hidrógeno, Gases Naturales | Satélite, Estación Espacial, Nave de Asentamiento Interestelar |
| **Prado** | Granja Organizada | Agricultura + Tejido + Conservación de Alimentos + Arado | Tierras Fértiles | Regenera recurso para Penicilina |
| **Mina** | Mina de Minerales | Metalurgia y Aleaciones + Brújula | Oro, Mat. Magnéticos | Moneda, Brújula, Teléfono Móvil |

---

## Mapeo de Recursos: Producción y Uso

### Validación: Cada Recurso Tiene Al Menos Un Uso

Todos los 42 recursos en el juego son usados en al menos un invento o tecnología. A continuación se listan por categoría:

#### Capa Base (Recursos Nivel 1-2)

| Recurso | Producido en | Usado en Inventos |
|---|---|---|
| `roble` | Bosque Nv1-4 | Cuchillo, Trampa, Refugios, Barco, Lanza, Arcos, Rueda, Carro, Molino, Arado |
| `pino` | Bosque Nv2-4 | Barco |
| `silex` | Cantera Nv1-4 | Lanza, Hacha, Arcos, Rueda |
| `granito` | Cantera Nv2-4 | Molino, Acueducto |
| `agua` | Río Nv1-4 | Cerámica, Penicilina, Acueducto, Papel |
| `cana-comun` | Río Nv2-4 | Refugios, Papel |
| `lino` | Prado Nv1-4 | Cuerda, Tela |
| `yute` | Prado Nv2-4 | Tela |
| `cobre` | Mina Nv1-4 | Moneda, Brújula, Reloj, Bombilla |
| `hierro` | Mina Nv2-4 | Acero, Arado |

#### Capa Intermedia (Recursos Nivel 3-4)

| Recurso | Producido en | Usado en Inventos |
|---|---|---|
| `carbon-natural` | Bosque Nv3-4 | Acero |
| `pieles` | Bosque Nv4 | Carro |
| `obsidiana` | Cantera Nv3-4 | Cuchillo |
| `tierras-fertiles` | Río Nv3-4, Prado Nv5 | Penicilina |
| `canamo` | Prado Nv3-4 | Cuerda, Barco |
| `lana` | Prado Nv4 | Tela |
| `estano` | Mina Nv3-4 | Batería |
| `grafito` | Mina Nv4 | Acero |

#### Capa Avanzada (Recursos Nivel 5 de Casillas Especializadas)

| Recurso | Producido en | Usado en Inventos |
|---|---|---|
| `latex` | Bosque Nv5 | Penicilina |
| `resinas-inflamables` | Bosque Nv5 | Batería |
| `mat-aisl-nat` | Bosque Nv5 | Fibra Óptica |
| `arena-de-silice` | Cantera Nv5 | Vidrio, Microscopio |
| `arena-de-cuarzo` | Cantera Nv5 | Telescopio |
| `cristales-nat` | Cantera Nv5 | Láser |
| `silicio` | Cantera Nv5 | Láser, Fibra Óptica, Teléfono Móvil, Satélite, Estación Espacial, Nave de Asentamiento Interestelar |
| `min-semi` | Cantera Nv5 | Teléfono Móvil |
| `hidrogeno` | Río Nv5 | Estación Espacial, Nave de Asentamiento Interestelar |
| `gases-naturales` | Río Nv5 | Satélite, Nave de Asentamiento Interestelar |
| `tierras-fertiles` | Río Nv3-4, Prado Nv5 | Penicilina |
| `oro` | Mina Nv5 | Moneda |
| `mat-mag-nat` | Mina Nv5 | Brújula, Teléfono Móvil |

---

## Recursos Eliminados (Refactorización Histórica)

Durante el diseño del juego se eliminaron recursos para optimizar la progresión y eliminar recursos huérfanos:

| Recurso Eliminado | Ubicación Original | Razón de Eliminación | Solución de Reemplazo |
|---|---|---|---|
| `cedro` | Bosque Nv2-3 | Huérfano sin invento principal | Reemplazado por `canamo` en Barco |
| `totora` | Río Nv2 | Recurso muy específico, sin uso claro | Eliminado; `cana-comun` más versátil |
| `carrizo` | Río Nv4 | Recurso muy específico, sin uso claro | Eliminado; `cana-comun` ya cubierto |
| `tendones` | Prado Nv4 | Huérfano sin invento claro | Movido a sub-producto de Ganadería |
| `sal` | Mina Nv4 | Huérfano sin invento asociado | Eliminado |
| `plomo` | Mina Nv5 | Recurso sin uso definitivo en cadena tech | Eliminado; no hay invento asociado |
| `lantano` / `cerio` | Mina Nv5 | Sin uso en inventos | Eliminados |
| `cuarzo` (independiente) | Cantera Nv3 | Absorbido en recurso compuesto | Transformado a `arena-de-cuarzo` (Nv5 Cantera) |
| `goma-arabiga` | Bosque Nv5 | Sin invento que la consuma | Eliminada; reemplazada por `mat-aisl-nat` |
| `oxigeno` / `nitrogeno` | Río Nv5 | Sin invento que los consuma | Eliminados; Río Nv5 solo produce hidrógeno y gases-naturales |
| `algodon` / `aceites-vegetales` | Prado Nv5 | Sin invento que los consuma | Eliminados; Prado Nv5 solo produce tierras-fertiles |
| `plata` / `neodimio` / `uranio` | Mina Nv5 | Sin invento que los consuma | Eliminados; Mina Nv5 produce oro y mat-mag-nat |
| `caolinita` | Cantera Nv4 | Circular: dependía de Cerámica que requería caolinita | Eliminada; ceramica-inv usa granito+agua |
| `fosforo` | Tech Nanotecnología | Generado mágicamente por tech (sin casilla) | Eliminado; bombilla cambia a cobre+carbon-natural |
| `min-semi` / `cristales-nat` / `mat-mag-nat` / `mat-aisl-nat` | Techs Electricidad/Computación/Fotografía | Generados mágicamente por techs | Reasignados a casillas: mat-aisl-nat→Bosque Nv5, cristales-nat+silicio+min-semi→Cantera Nv5, mat-mag-nat→Mina Nv5 |

---

## Notas de Implementación

### Enumeración de Tipos de Casilla (tile_type)

```
BOSQUE = 'bosque'
CANTERA = 'cantera'
RIO = 'rio'
PRADO = 'prado'
MINA = 'mina'
PUEBLO = 'pueblo'  # casilla especial (sin evolución)
```

### Identificadores de Recursos (resource_id)

**Capa Base**:
- `roble`, `pino`, `silex`, `granito`, `agua`, `cana-comun`, `lino`, `yute`, `cobre`, `hierro`

**Capa Intermedia**:
- `carbon-natural`, `pieles`, `obsidiana`, `tierras-fertiles`, `canamo`, `lana`, `estano`, `grafito`

**Capa Avanzada (Nv5)**:
- `latex`, `resinas-inflamables`, `mat-aisl-nat` (Bosque)
- `arena-de-silice`, `arena-de-cuarzo`, `cristales-nat`, `silicio`, `min-semi` (Cantera)
- `hidrogeno`, `gases-naturales` (Río)
- `tierras-fertiles` extra (Prado)
- `oro`, `mat-mag-nat` (Mina)

**Capa Ultra Avanzada**: Esta capa ha sido eliminada. Los recursos que antes se generaban mágicamente por tecnologías (`silicio`, `cristales-nat`, `mat-mag-nat`, `mat-aisl-nat`, `min-semi`) han sido reasignados a casillas Nv5. `fosforo` fue eliminado por no tener invento que lo consuma.

### Esquema de Base de Datos (hints)

**Importante: Distinción entre Prerequisitos y Costes**

El juego utiliza dos tipos de requisitos para inventos:

1. **`invention_prerequisites`**: Inventos que deben estar construidos/investigados antes de poder construir otro invento.
   - NO se consumen del inventario
   - Ejemplo: Para construir `trampa` necesitas tener `cuchillo` ya construido, pero el cuchillo no se gasta

2. **`invention_costs`**: Recursos que se consumen del inventario cuando construyes un invento.
   - Siempre referencian `resource_id` (nunca `invention_id`)
   - Ejemplo: `reloj` consume `acero×12` del inventario

3. **`tile_level_resources`**: Recursos que produce una casilla en cada nivel.
   - Son recursos crudos/base generados por la casilla
   - Los mismos recursos pueden ser tanto producidos por casillas como consumidos por inventos

**Tabla: `tile_types`**
```sql
CREATE TABLE tile_types (
  id VARCHAR(50) PRIMARY KEY,  -- 'bosque', 'cantera', 'rio', 'prado', 'mina'
  name VARCHAR(255),
  description TEXT
);
```

**Tabla: `tile_instances`**
```sql
CREATE TABLE tile_instances (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  tile_type VARCHAR(50) FOREIGN KEY,
  game_id BIGINT FOREIGN KEY,
  player_id BIGINT FOREIGN KEY (propietario),
  level INT DEFAULT 1,  -- 1-5
  position_x INT,
  position_y INT,
  explored BOOLEAN DEFAULT FALSE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

**Tabla: `tile_level_resources`**
```sql
-- Define qué recursos produce cada nivel de cada tipo de casilla (recursos crudos/base)
CREATE TABLE tile_level_resources (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  tile_type VARCHAR(50),
  level INT,
  resource_id VARCHAR(50) FOREIGN KEY,
  quantity INT,
  tech_required VARCHAR(50) NULLABLE FOREIGN KEY,
  invention_required VARCHAR(50) NULLABLE FOREIGN KEY,
  UNIQUE(tile_type, level, resource_id)
);
```

**Tabla: `resources`**
```sql
CREATE TABLE resources (
  id VARCHAR(50) PRIMARY KEY,
  name VARCHAR(255),
  tier INT,  -- 1: base, 2: intermedia, 3: avanzada, 4: ultra-avanzada
  description TEXT
);
```

### Validación en Aplicación

Implementar validación en cada evolución:

1. **Verificar nivel actual**: `TileInstance.level < 5`
2. **Verificar tecnología**: Si requisito tech, verificar que existe en `Team.technologies`
3. **Verificar invento**: Si requisito invento, verificar que existe en `Team.inventions`
4. **Generar nuevos recursos**: Cuando se evoluciona, registrar cambios en `TileInstance` y generar `TileProductionQueue` para próxima jornada

### Cálculo de Producción de Casilla

Este cálculo determina qué recursos crudos produce una casilla cada jornada (solo recursos base de `tile_level_resources`, no costes de inventos).

```pseudocode
function calculateTileProduction(tileInstance, team):
  tier = getTileLevel(tileInstance)
  resources = getTileLevelResources(tileInstance.tileType, tier)
  
  for each resource in resources:
    -- Verificar requisitos PREVIOS de tecnología e invento
    -- Estos son requisitos para DESBLOQUEAR la producción de este recurso
    -- (no son costes consumidos, solo condiciones)
    if resource.techRequired && !team.hasTech(resource.techRequired):
      skip  -- No producir este recurso si falta tecnología
    if resource.inventionRequired && !team.hasInvention(resource.inventionRequired):
      skip  -- No producir este recurso si falta invento
    
    -- Calcular producción de este recurso
    baseQuantity = resource.quantity
    bonusMultiplier = calculateBonuses(team, tileInstance.tileType)
    
    production = baseQuantity * bonusMultiplier
    addToTeamInventory(team, resource.id, production)
```

**Nota**: Este cálculo NO maneja `invention_costs` (costes de construcción de inventos).
Los costes se aplican en el sistema de construcción de inventos de forma independiente:
- Verificar `invention_prerequisites` (inventos ya construidos, no se consumen)
- Restar `invention_costs` (recursos consumidos del inventario)

---
