# Documento de Diseño: Cálculo Óptimo del Tablero

## 1. El Dilema del Tamaño

El número de casillas óptimo para hacer el MVP de Bressolium divertido, desafiante y técnicamente viable debe calcularse mediante unas matemáticas sencillas basadas en el ritmo de juego (cuántas acciones podéis hacer y cuánto dura la partida).

**Datos base del MVP:**
1. Máximo 5 jugadores por equipo.
2. Cada jugador tiene 2 acciones por turno (explorar o evolucionar).
3. Todo el equipo junto puede hacer un máximo de 10 exploraciones al día.

**Conclusiones de tamaño:**
Generar un **tablero fijo de 10x10 (100 casillas)** o **12x12 (144 casillas) al inicio de la partida** es la decisión arquitectónica óptima.
- **Balance de juego:** Fomenta la recolección sin agobios. En 10 jornadas un equipo 100% activo habrá desvelado todo, manteniendo el *end-game* centrado en evolucionar y votar, no en seguir levantando casillas ciegamente.
- **Ventaja técnica:** Una cuadrícula de 10x10 en React mediante `CSS Grid` se renderiza perfectamente en el centro de cualquier navegador moderno (por ejemplo, 600x600px si las casillas fuesen de 60px) sin requerir funciones caóticas de desplazamiento, cámaras (*canvas*) o programación asimétrica en el front.

---

## 2. El Balance de Materiales (La "Bolsa de Recolección")

Asignar un exacto "20% a cada tipo de terreno" (Bosque, Cantera, Prado, Mina, Río) genera problemas graves en la etapa tardía (*late-game*), ya que los requerimientos de la "Nave Interestelar" (Condición de victoria) exigen cantidades masivas de algunos elementos y nulas de otros. Basado en el árbol tecnológico:

- **La Mina:** Es el sumidero (Hierro, Silicio, Cobre, Acero). Todo el árbol desde el mid-game hacia adelante depende agresivamente de los metales.
- **El Bosque:** Troncal en los albores de la civilización (Lanzas, Ruedas, Barcos).
- **El Río:** Crucial en el late-game. Genera el Hidrógeno y el Agua necesarios para la nave.
- **La Cantera:** Útil, pero moderada (Piedra, Cristal).
- **Los Prados:** Específicos para supervivencia temprana (Pieles, Cuerdas).

### Distribución Recomendada para 100 Casillas:
*   🪨 **Minas (30 casillas):** Garantiza que todos los jugadores tengan acceso al motor de la Revolución Industrial y no se frustren.
*   🌲 **Bosques (25 casillas):** Suficientes para la expansión inicial rápida.
*   💧 **Ríos (20 casillas):** Prepara al equipo para destilar el Hidrógeno final.
*   ⛰️ **Canteras (15 casillas):** Escasas intencionadamente para dotar al "Cristal" de valor estratégico grueso.
*   🌾 **Prados (10 casillas):** Prácticamente únicos; obliga a un jugador especializado del equipo a trabajar para proveer telas y alimento.

---

## 3. Implementación Sugerida en Laravel (Backend)

Al separar la base de datos de esta forma, en el endpoint o función en la que se genere la **Partida Nueva**, la distribución no se deja a un puro `rand()`, sino que se utiliza la técnica de barajado sobre un conjunto finito (*Pool*):

```php
// En el Servicio/Controlador que inicializa el mapa tras crear Partida

public function generarTablero($partida_id)
{
    // 1. Crear el Pool exacto de IDs de los Recursos (diccionario)
    $pool = array_merge(
        array_fill(0, 30, 'Mina'),    // Reemplaza 'Mina' por el resource_id de la BD
        array_fill(0, 25, 'Bosque'),
        array_fill(0, 20, 'Rio'),
        array_fill(0, 15, 'Cantera'),
        array_fill(0, 10, 'Prado')
    );

    // 2. Barajamos array aleatoriamente
    shuffle($pool);

    $casillasData = [];
    $index = 0;

    // 3. Generar la matriz 10x10 asociando la posición del array
    for ($y = 0; $y < 10; $y++) {
        for ($x = 0; $x < 10; $x++) {
            $tipoTerreno = $pool[$index];

            $casillasData[] = [
                'partida_id' => $partida_id,
                'x' => $x,
                'y' => $y,
                'recurso_id' => $tipoTerreno,
                'nivel' => 1,
                'descubierta' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $index++;
        }
    }

    // 4. Inserción masiva única en BD (Mucho más óptimo que 100 inserts separados)
    Casilla::insert($casillasData);
}
```

Esta lógica previene crear tableros injustos y minimiza la carga en el servidor, construyendo un mapa estático perfecto para que React lo solicite e hidrate la interfaz.
