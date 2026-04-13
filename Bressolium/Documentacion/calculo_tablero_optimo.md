# Distribución de Casillas — Diseño del Tablero

## Parámetros de Diseño

| Parámetro | Valor |
|---|---|
| **Tamaño del tablero** | 15×15 = 225 casillas |
| **Casillas explorables** | 224 (+ 1 El Pueblo en el centro) |
| **Jugadores por equipo** | 1–5 |
| **Jornadas objetivo** | 40–60 |
| **El Pueblo** | Posición fija (7,7) — centro exacto |

---

## Justificación del Tamaño 15×15

- **Impar**: El centro cae en (7,7), posición exacta sin ambigüedad. Con un tablero par (16×16) el centro no existe como casilla única.
- **Escala universal**: Un solo tablero funciona para todos los modos (1–5 jugadores) sin redimensionar.
- **Ritmo de exploración**: Con 2 acciones por jugador/jornada y ~50% dedicadas a explorar, cada configuración de jugadores explora un porcentaje distinto del tablero, generando experiencias de juego diferentes pero igualmente válidas.

| Jugadores | Acciones explorar/jornada | Casillas exploradas en 50j | % tablero |
|---|---|---|---|
| 1 | ~1 | ~50 | 22% — tablero misterioso, mucho por descubrir |
| 2 | ~2 | ~100 | 44% — expansión progresiva bien ritmada |
| 3 | ~3 | ~150 | 67% — exploración casi completa al final |
| 5 | ~5 | ~225 | ~100% — tablero completo, clímax épico |

> **Nota**: El 50% explorar/evolucionar es aproximado. Al inicio se explora más; en jornadas tardías se evoluciona más. La adyacencia limita la exploración simultánea — 5 jugadores no pueden explorar 5 casillas en paralelo si parten de posiciones dispersas.

---

## Posiciones Iniciales de Jugadores

Los jugadores comienzan equidistantes entre sí y del centro (El Pueblo en 7,7).

```
Coordenadas: (col, fila), índice 0-14

1 jugador:   (7,1)
             — norte, máxima distancia al centro

2 jugadores: (3,3) y (11,11)
             — diagonales opuestas

3 jugadores: (7,1), (1,13), (13,13)
             — triángulo equilátero aproximado

4 jugadores: (2,2), (12,2), (2,12), (12,12)
             — cuatro esquinas simétricas

5 jugadores: (2,2), (12,2), (2,12), (12,12), (7,1)
             — cuatro esquinas + norte
```

Cada jugador comienza con su casilla inicial ya **explorada y en nivel 1**, generando recursos desde la primera jornada.

---

## Distribución de Tipos de Casilla

### Propuesta (224 casillas explorables)

| Tipo | Cantidad | % | Color sugerido |
|---|---|---|---|
| **Mina** | 60 | 27% | Marrón oscuro `#92400e` |
| **Río** | 55 | 24% | Azul `#0284c7` |
| **Cantera** | 50 | 22% | Gris `#78716c` |
| **Bosque** | 35 | 16% | Verde oscuro `#15803d` |
| **Prado** | 24 | 11% | Verde claro `#65a30d` |
| **El Pueblo** | 1 | — | Dorado `#fbbf24` |
| **Total** | **225** | 100% | |

### Justificación por tipo

**Mina (27% — 60 casillas)**
Es el tipo más abundante porque sus recursos aparecen en casi todos los inventos de la ruta crítica: `hierro` en Acero y Arado, `cobre` en Moneda/Reloj/Bombilla/Batería/Brújula, `grafito` en Acero, `estano` en Batería. En nivel 5 (requiere Metalurgia + Brújula construida) produce `oro` para la Moneda y `mat-mag-nat` para la Brújula y el Teléfono Móvil. Sin Minas suficientes el juego se atasca en la era industrial.

**Río (24% — 55 casillas)**
`agua` requiere ~1.070 unidades solo en la ruta crítica (Cerámica, Papel, Penicilina, Acueducto, Nave). `hidrogeno` requiere ~650 unidades (Estación Espacial×50 + Nave×600). El cuello de botella real es llegar a Nv5 para obtener `hidrogeno` y `gases-naturales`. Segunda más abundante.

**Cantera (22% — 50 casillas)**
`silex` desbloquea Herramientas de Piedra (tech fundamental). `granito` para Molino y Acueducto. `obsidiana` para el Cuchillo (primer invento, desbloquea Trampa → Ganadería). `arena-de-silice` y `arena-de-cuarzo` (nivel 5) son imprescindibles para el Vidrio, que desbloquea toda la cadena Química → Electricidad → Computación. Sin Cantera, el juego no avanza más allá de la Edad de Piedra.

**Bosque (16% — 35 casillas)**
`roble` aparece en casi todos los inventos primitivos (Cuchillo, Trampa, Refugios, Lanza, Hacha, Arcos, Rueda, Carro, Barco, Molino, Arado). `carbon-natural` es necesario para el Acero. `pieles` para el Carro. En nivel 5 produce `latex` para Penicilina y `resinas-inflamables` para Batería. Menos crítico en late-game pero fundamental en early. Con 35 casillas hay suficiente para cubrir la demanda temprana sin saturar el mapa.

**Prado (11% — 24 casillas)**
Los recursos textiles (`lino`, `canamo`, `lana`, `yute`) son necesarios para Cuerda, Tela y Barco, pero en cantidades moderadas. El Nv5 (Granja Organizada) requiere Agricultura + Tejido + Conservación de Alimentos + Arado y regenera `tierras-fertiles` para la Penicilina. Es el tipo menos demandado en la ruta principal, por eso es el menos abundante.

---

## Principio de Distribución Espacial

### No distribución aleatoria pura

Las casillas **no deben distribuirse de forma completamente aleatoria**. Un mapa con distribución aleatoria pura puede generar clusters accidentales injugables (ej: todos los Ríos en una esquina) o zonas áridas sin recursos críticos cerca de algún jugador.

### Distribución por zonas (clustering controlado)

Dividir el tablero en zonas y asignar densidades por tipo:

```
Tablero 15×15 dividido en 4 cuadrantes (+ centro):

┌─────────┬─────────┐
│  NO     │  NE     │
│ Bosque  │ Cantera │
│ +Prado  │ +Río    │
├────┬────┴────┬────┤
│    │ PUEBLO  │    │
│    │  (7,7)  │    │
├────┴────┬────┴────┤
│  SO     │  SE     │
│  Río    │  Mina   │
│ +Mina   │ +Cantera│
└─────────┴─────────┘
```

**Regla**: Cada cuadrante tiene una densidad mayor de 1-2 tipos pero incluye al menos 1 casilla de cada tipo. Esto garantiza que:
- Ningún jugador empiece rodeado únicamente de un tipo de casilla
- Existan zonas estratégicamente valiosas que incentiven la expansión en distintas direcciones
- El juego tenga "territorios" con identidad propia

### Garantías mínimas de adyacencia al inicio

Al generar el mapa, verificar que cada posición inicial de jugador tenga en sus 8 casillas adyacentes al menos:
- 1 casilla de tipo **Mina** o **Cantera** (recursos de piedra/metal)
- 1 casilla de tipo **Bosque** o **Prado** (recursos orgánicos)
- 1 casilla de tipo **Río** (agua)

Si no se cumple, regenerar el mapa (o ajustar las adyacencias).

---

## Validación Demanda/Oferta

### Recursos críticos de la ruta ganadora

| Recurso | Demanda total (ruta crítica) | Fuente principal |
|---|---|---|
| `agua` | ~1.070u | Río nv1–4 |
| `hidrogeno` | ~650u | Río nv5 (Extractor de Gases) |
| `hierro` | ~300u | Mina nv2–4 |
| `cobre` | ~250u | Mina nv1–4 |
| `roble` | ~200u | Bosque nv1–4 |
| `silicio` | ~640u | Cantera nv5 (Cantera de Sílice) |
| `cana-comun` | ~85u | Río nv2–4 |
| `carbon-natural` | ~85u | Bosque nv3–4 |

### Producción estimada (3 jugadores, 50 jornadas, nivel medio 2.5)

| Recurso | Casillas activas | Producción estimada | Ratio oferta/demanda |
|---|---|---|---|
| `agua` | ~35 Ríos | ~3.300u | **3× ✓** |
| `hidrogeno` | Extractor nv5 × jornadas | ~2.400u | **3.7× ✓** |
| `hierro` | ~40 Minas | ~2.500u | **8× ✓** |
| `cobre` | ~40 Minas | ~2.000u | **8× ✓** |
| `roble` | ~22 Bosques | ~1.800u | **9× ✓** |
| `silicio` | Cantera nv5 × jornadas | ~1.200u | **1.9× ✓** |

La producción supera la demanda con margen suficiente para absorber:
- Eventos adversos (sequías, invasiones: reducen producción un ciclo)
- Bifurcaciones de ruta (construir inventos no críticos)
- Inventos repetidos o intermedios necesarios como ingredientes

> **El cuello de botella real no son los recursos básicos — es llegar a nivel 5 en los Ríos** para producir `hidrogeno` y `gases-naturales`, y llegar a nivel 5 en las Canteras para producir `silicio`. El diseño debe incentivar evolucionar Ríos y Canteras temprano.

---

## Notas de Implementación

### Generación del mapa (backend Laravel)

```php
// Parámetros del generador
const BOARD_SIZE = 15;
const PUEBLO_POS = [7, 7];

const TILE_DISTRIBUTION = [
    'mina'    => 60,
    'rio'     => 55,
    'cantera' => 50,
    'bosque'  => 35,
    'prado'   => 24,
];

// Algoritmo sugerido: distribución por zonas + shuffle controlado
// 1. Dividir tablero en 4 cuadrantes
// 2. Asignar densidades por tipo a cada cuadrante (ver tabla arriba)
// 3. Dentro de cada cuadrante, distribuir aleatoriamente
// 4. Aplicar verificación de adyacencia en posiciones iniciales
// 5. Si falla verificación, regenerar solo el cuadrante afectado (max 3 intentos)
```

### Schema de base de datos

```sql
-- Tipos de casilla (catálogo fijo)
CREATE TABLE tile_types (
    id VARCHAR(20) PRIMARY KEY,  -- 'bosque', 'cantera', 'rio', 'prado', 'mina', 'pueblo'
    name VARCHAR(100),
    color VARCHAR(10),
    description TEXT
);

-- Instancias de casilla en una partida concreta
CREATE TABLE tile_instances (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    game_id BIGINT NOT NULL,
    tile_type VARCHAR(20) NOT NULL,
    position_x TINYINT NOT NULL,   -- 0-14
    position_y TINYINT NOT NULL,   -- 0-14
    level TINYINT DEFAULT 1,       -- 1-5
    explored BOOLEAN DEFAULT FALSE,
    explored_by_player_id BIGINT NULLABLE,
    explored_at TIMESTAMP NULLABLE,
    UNIQUE KEY uq_position (game_id, position_x, position_y),
    FOREIGN KEY (game_id) REFERENCES games(id),
    FOREIGN KEY (tile_type) REFERENCES tile_types(id)
);
```

### Enum de tipos de casilla (Laravel)

```php
enum TileType: string
{
    case Bosque   = 'bosque';
    case Cantera  = 'cantera';
    case Rio      = 'rio';
    case Prado    = 'prado';
    case Mina     = 'mina';
    case Pueblo   = 'pueblo';

    public function distribution(): int
    {
        return match($this) {
            self::Mina    => 60,
            self::Rio     => 55,
            self::Cantera => 50,
            self::Bosque  => 35,
            self::Prado   => 24,
            self::Pueblo  => 1,
        };
    }
}
```

### Validación de adyacencia al generar mapa

```php
// Verificar que una posición inicial tiene al menos un vecino de cada grupo crítico
function validateStartPosition(array $board, int $x, int $y): bool
{
    $neighbors = getAdjacentTiles($board, $x, $y); // 4 u 8 vecinos
    $types = array_column($neighbors, 'tile_type');

    $hasMineralResource = !empty(array_intersect($types, ['mina', 'cantera']));
    $hasOrganicResource = !empty(array_intersect($types, ['bosque', 'prado']));
    $hasWater           = in_array('rio', $types);

    return $hasMineralResource && $hasOrganicResource && $hasWater;
}
```

### Coordenadas de inicio por número de jugadores

```php
const STARTING_POSITIONS = [
    1 => [[7, 1]],
    2 => [[3, 3], [11, 11]],
    3 => [[7, 1], [1, 13], [13, 13]],
    4 => [[2, 2], [12, 2], [2, 12], [12, 12]],
    5 => [[2, 2], [12, 2], [2, 12], [12, 12], [7, 1]],
];
```

---
