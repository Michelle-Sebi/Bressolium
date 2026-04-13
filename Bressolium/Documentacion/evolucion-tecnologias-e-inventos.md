# Evolución de Tecnologías e Inventos

## Sistema de Progresión: Cómo Funciona

### Conceptos Fundamentales

**Tecnologías** son investigaciones colectivas del equipo que:
- Desbloquean nuevas evoluciones de casillas (especialmente niveles 4 y 5)
- Desbloquean nuevos inventos
- Aplican bonificadores de producción global
- Se alcanzan votando en el Panel de Votación colectivo

**Inventos** son construcciones concretas que:
- Cumplen un propósito específico (herramienta, transporte, etc.)
- Requieren consumir recursos del inventario global
- Pueden desbloquear nuevas tecnologías
- Pueden desbloquear nuevos inventos

### Dos tipos de requisito — distinción crítica

| Tipo | Qué significa | Se consume al construir? | Ejemplo |
|---|---|---|---|
| **Prerequisito** | Necesitas haberlo construido/investigado antes | ❌ No | `trampa` requiere tener `cuchillo` ya construido |
| **Coste de recursos** | Materiales que se descuentan del inventario | ✅ Sí | `reloj` consume `cobre×10` del inventario |

**Regla fundamental: los costes de construcción son SIEMPRE recursos producidos por casillas** (raw nv1–4 o avanzados nv5). Los inventos como `acero`, `vidrio` y `papel` son **únicamente prerequisitos** — necesitas haberlos construido antes, pero no se consumen ni aparecen como coste.

> **Por qué**: `acero`, `vidrio` y `papel` son outputs de inventos, no recursos de casilla. Solo los recursos que las casillas producen directamente pueden ser costes de construcción.

### Relaciones de Dependencia

Una tecnología o invento se puede construir/investigar cuando:
1. **Prerequisitos de inventos cumplidos**: Se han construido todos los inventos prerequisitos (no se consumen)
2. **Prerequisitos de tecnología**: Se han investigado todas las tecnologías prerequisitas (no se consumen)
3. **Recursos disponibles**: El inventario global tiene suficientes materiales (se consumen al construir)

### Bonificadores

Ciertas tecnologías e inventos ofrecen mejoras permanentes:

| Tipo | Bonificador | Aplicación |
|---|---|---|
| **Producción de Casilla** | +X% recursos producidos | Solo en tipo de casilla específica (ej: +30% Río) |
| **Producción Global** | +X% todos los recursos | Aplicado a todas las casillas sin distinción |
| **Velocidad** | +X% velocidad de producción | Reduce tiempo de espera entre jornadas |
| **Reducción de Coste** | -X% coste inventos | Reduce recursos necesarios en inventos futuros |
| **Mitigación de Eventos** | -X% impacto eventos adversos | Reduce daño de sequías, invasiones, etc. |

---

## Árbol de Tecnologías

Las tecnologías forman cadenas de dependencia. A continuación se listan todas las tecnologías:

| ID Tech | Nombre | Prerequisitos (Inventos) | Prerequisitos (Techs) | Desbloquea | Bonificador |
|---|---|---|---|---|---|
| `herr-piedra` | Herramientas de Piedra | — | — | Invento: Lanza, Hacha, Rueda, Carro | +0% |
| `control-fuego` | Control del Fuego | — | — | Invento: Cerámica; Tech: Fermentación | +20% Bosque |
| `ganaderia` | Ganadería | Trampa, Refugios, Arcos | — | Nv4 Bosque, Nv4 Prado | +0% |
| `ceramica-alfareria` | Cerámica y Alfarería | Cerámica (invento) | Control del Fuego | Invento: Vidrio; Nv4 Cantera | +0% |
| `tejido` | Tejido | Cuerda, Tela | — | Nv5 Prado | +20% Prado |
| `agricultura` | Agricultura | Cuerda, Hacha | — | Invento: Barco, Molino, Acueducto, Arado; Nv4 Río | +0% |
| `fermentacion` | Fermentación | — | Cerámica y Alfarería | Tech: Conservación de Alimentos | +10% todos |
| `metalurgia` | Metalurgia y Aleaciones | Hacha, Molino | — | Invento: Acero, Moneda, Brújula, Reloj; Nv3 Mina, Nv4 Mina | +0% |
| `conservacion` | Conservación de Alimentos | Acueducto | Fermentación | Nv5 Prado | -25% pérdida eventos |
| `quimica` | Química | Vidrio, Acero | — | Invento: Papel, Microscopio, Penicilina, Bombilla; Nv5 Bosque, Nv5 Río | +0% |
| `escritura` | Escritura | Papel, Moneda | — | Invento: Imprenta | +0% |
| `fotografia` | Fotografía | Microscopio, Papel | Imprenta | Invento: Telescopio | +15% Silicio |
| `electricidad` | Electricidad | Batería, Bombilla | — | Invento: Láser, Fibra Óptica | +0% |
| `computacion` | Computación | — | Electricidad, Reloj | Tech: Comunicaciones, GPS | +20% Silicio |
| `comunicaciones` | Comunicaciones Inalámbricas | Teléfono Móvil, Fibra Óptica | Computación | Tech: Internet | +0% |
| `gps` | GPS | — | Computación | — | +0% |
| `internet` | Internet | — | Comunicaciones | Tech: IA | +0% |
| `ia` | Inteligencia Artificial | — | Internet, Computación | Tech: Robótica, Sistemas Autónomos | +15% todos |
| `energias-renovables` | Energías Renovables | — | Electricidad, Acero | Invento: Avión | +30% todas |
| `robotica` | Robótica | — | IA, Acero | — | +0% |
| `nanotecnologia` | Nanotecnología | Láser | Microscopio | Tech: Edición Genética | +25% Carbono |
| `edicion-genetica` | Edición Genética | Microscopio | Nanotecnología | Tech: Biotecnología | +20% Granja |
| `biotecnologia` | Biotecnología | Penicilina | Edición Genética | Tech: Terraformación | -30% eventos |
| `sistemas-autonomos` | Sistemas Autónomos | — | Robótica, IA | Tech: Terraformación | -15% coste acciones |
| `tecnologia-espacial` | Tecnología Espacial | Telescopio, Estación Espacial | Satélite, GPS | Invento: Nave de Asentamiento | +0% |
| `terraformacion` | Terraformación | — | Tecnología Espacial, Biotecnología, Sistemas Autónomos | Invento: Nave de Asentamiento Interestelar | +0% |

---

## Árbol de Inventos

Los inventos son construcciones concretas que consumen recursos específicos. Se organizan por edad tecnológica:

### Edad de Piedra (Herramientas Primitivas)

| ID Invento | Nombre | Tech Requerida | Prerequisito Invento | Costes (se consumen) | Desbloquea | Bonificador |
|---|---|---|---|---|---|---|
| `cuchillo` | Cuchillo | — | — | obsidiana×8, roble×3 | Invento: Trampa | — |
| `cuerda` | Cuerda | — | — | lino×8, canamo×5 | Invento: Barco | — |
| `lanza` | Lanza | Herramientas de Piedra | — | silex×8, roble×5 | Invento: Arcos | — |
| `hacha` | Hacha | Herramientas de Piedra | — | silex×10, roble×8 | Nv5 Cantera | +25% Bosque |
| `arcos` | Arcos | Herramientas de Piedra | `lanza` | roble×8 | Tech: Ganadería | — |
| `trampa` | Trampa | — | `cuchillo` | roble×5 | Tech: Ganadería | — |
| `refugios` | Refugios | — | — | roble×15, cana-comun×10 | Tech: Ganadería | -10% sequías |
| `rueda` | Rueda | Herramientas de Piedra | — | roble×12, silex×8 | Invento: Carro | — |
| `carro` | Carro | Herramientas de Piedra | `rueda` | roble×15, pieles×8 | Invento: Molino | — |

### Edad Media (Textiles y Agricultura)

| ID Invento | Nombre | Tech Requerida | Prerequisito Invento | Costes (se consumen) | Desbloquea | Bonificador |
|---|---|---|---|---|---|---|
| `tela` | Tela | Ganadería | — | lino×8, yute×6, lana×8 | Tech: Tejido | +15% Prado |
| `ceramica-inv` | Cerámica | Control del Fuego | — | granito×10, agua×5 | Tech: Cerámica y Alfarería | — |
| `barco` | Barco | Agricultura | `cuerda` | roble×20, pino×15, canamo×8 | Invento: Brújula | — |
| `molino` | Molino | Agricultura | `carro` | granito×15, roble×10 | — | +20% Granja |
| `acueducto` | Acueducto | Agricultura | — | granito×20, agua×10 | Tech: Calefacción y Refrigeración | +30% Río |
| `arado` | Arado | Agricultura | — | roble×10, hierro×5 | Nv5 Prado | — |

### Edad de Bronce (Metalurgia)

| ID Invento | Nombre | Tech Requerida | Prerequisito Invento | Costes (se consumen) | Desbloquea | Bonificador |
|---|---|---|---|---|---|---|
| `acero` | Acero | Metalurgia y Aleaciones | — | hierro×20, carbon-natural×15, grafito×5 | Tech: Escritura, Química | — |
| `moneda` | Moneda | Metalurgia y Aleaciones | — | cobre×15, oro×5 | Tech: Escritura | — |
| `brujula` | Brújula | Metalurgia y Aleaciones | `barco` | hierro×15, cobre×8, carbon-natural×10 | Nv5 Mina | — |
| `reloj` | Reloj | Metalurgia y Aleaciones | `acero` | cobre×10 | Tech: Computación | +10% velocidad |

### Edad Moderna (Vidrio y Química)

| ID Invento | Nombre | Tech Requerida | Prerequisito Invento | Costes (se consumen) | Desbloquea | Bonificador |
|---|---|---|---|---|---|---|
| `vidrio` | Vidrio | Cerámica y Alfarería | — | arena-de-silice×15, arena-de-cuarzo×10 | Tech: Química | — |
| `papel` | Papel | Química | — | cana-comun×15, agua×10 | Tech: Escritura | — |
| `imprenta` | Imprenta | Escritura | `papel`, `acero` | — | Tech: Fotografía | — |
| `microscopio` | Microscopio | Química | `vidrio`, `acero` | arena-de-silice×10, hierro×8 | Tech: Fotografía, Nanotecnología, Edición Genética | — |
| `penicilina` | Penicilina | Química | — | agua×10, tierras-fertiles×15, latex×8 | Tech: Biotecnología | -50% epidemias |
| `bombilla` | Bombilla | Química | `vidrio` | cobre×10, carbon-natural×8 | Tech: Electricidad | +20% todas |

### Era Industrial (Electricidad y Máquinas)

| ID Invento | Nombre | Tech Requerida | Prerequisito Invento | Costes (se consumen) | Desbloquea | Bonificador |
|---|---|---|---|---|---|---|
| `bateria` | Batería | Química | — | cobre×15, estano×10, resinas-inflamables×10 | Tech: Electricidad | — |
| `laser` | Láser | Electricidad | — | silicio×15, cristales-nat×10 | Tech: Nanotecnología | — |
| `fibra-optica` | Fibra Óptica | Electricidad | `vidrio` | silicio×20, mat-aisl-nat×10 | Tech: Internet | — |

### Era de Información (Computación y Comunicaciones)

| ID Invento | Nombre | Tech Requerida | Prerequisito Invento | Costes (se consumen) | Desbloquea | Bonificador |
|---|---|---|---|---|---|---|
| `telefono-movil` | Teléfono Móvil | Computación | — | silicio×20, mat-mag-nat×10, min-semi×10 | Tech: Comunicaciones Inalámbricas | — |
| `telescopio` | Telescopio | Fotografía | `vidrio`, `acero` | arena-de-cuarzo×15, hierro×8 | Tech: Tecnología Espacial | — |

### Era Moderna Tardía (Transporte Avanzado)

| ID Invento | Nombre | Tech Requerida | Prerequisito Invento | Costes (se consumen) | Desbloquea | Bonificador |
|---|---|---|---|---|---|---|
| `avion` | Avión | Energías Renovables | `cuerda`, `acero` | carbon-natural×20, hierro×15 | — | Exploración libre |
| `satelite` | Satélite | Energías Renovables + GPS | `acero` | silicio×15, gases-naturales×10 | Tech: Tecnología Espacial | — |
| `estacion-espacial` | Estación Espacial | Energías Renovables | `acero` | silicio×20, hidrogeno×15 | Tech: Tecnología Espacial | — |

### Era Espacial (Victoria)

| ID Invento | Nombre | Tech Requerida | Prerequisito Invento | Costes (se consumen) | Desbloquea | Bonificador |
|---|---|---|---|---|---|---|
| `nave-asentamiento` | Nave de Asentamiento Interestelar | Terraformación | `estacion-espacial`, `acero`, `vidrio` | silicio×400, hidrogeno×600, agua×300, mat-aisl-nat×200 | — | **FIN DEL JUEGO** |

---

## Camino a la Victoria: Cadena Crítica

Para ganar el juego (construir Nave de Asentamiento Interestelar) se debe seguir esta cadena crítica mínima:

### Fase 1: Fundamentos (Edad de Piedra)
1. **Herramientas de Piedra** (tech base, sin requisitos)
   - Invento: Hacha → +25% Bosque, desbloquea Cantera de Sílice (Nv5)
   - Invento: Lanza → desbloquea Arcos
   - Invento: Rueda → desbloquea Carro

2. **Control del Fuego** (tech base, sin requisitos)
   - Invento: Cerámica → desbloquea Cerámica y Alfarería

3. **Ganadería** (requiere inventos: Trampa + Refugios)
   - Cuchillo → Trampa (prerequisito) → Tech Ganadería
   - Desbloquea: Nv4 Bosque, Nv4 Prado

### Fase 2: Agricultura e Industria Temprana (Edad Media)
4. **Agricultura** (requiere inventos: Cuerda + Hacha)
   - Invento: Cuerda → desbloquea Barco
   - Invento: Rueda → Carro → desbloquea Molino (prerequisito)
   - Desbloquea: Barco, Molino, Acueducto, Arado, Nv4 Río

5. **Tejido** (requiere inventos: Cuerda + Tela)
   - Invento: Tela (requiere Ganadería) → desbloquea Tech Tejido
   - Desbloquea: Nv5 Prado (junto con Agricultura, Conservación de Alimentos y Arado)

6. **Conservación de Alimentos** (requiere: Acueducto + Tech Fermentación)
   - Invento: Salazón → usa peces (Río Nv4)
   - Desbloquea: Nv5 Prado (junto con Agricultura, Tejido y Arado)

7. **Cerámica y Alfarería** (requiere: Control del Fuego + Cerámica invento)
   - Desbloquea: Vidrio, Nv4 Cantera

8. **Metalurgia y Aleaciones** (requiere inventos: Hacha + Molino)
   - Invento: Barco → desbloquea Brújula (prerequisito)
   - Invento: Acero → desbloquea Escritura, Química
   - Invento: Reloj → desbloquea Tech Computación

### Fase 3: Ciencia Moderna (Era Moderna)
9. **Química** (requiere inventos: Vidrio + Acero)
   - Invento: Papel → desbloquea Escritura
   - Invento: Bombilla → +20% todas
   - Desbloquea: Microscopio, Penicilina, Nv5 Bosque, Nv5 Río

10. **Escritura** (requiere inventos: Papel + Moneda)
    - Invento: Imprenta → desbloquea Tech Fotografía

11. **Fotografía** (requiere inventos: Microscopio + Papel; prereq tech: Imprenta)
    - Desbloquea: Telescopio → Tecnología Espacial

### Fase 4: Era Industrial (Electricidad)
12. **Electricidad** (requiere inventos: Batería + Bombilla)
    - Batería requiere **Química** + recursos: cobre×15, estaño×10, resinas-inflamables×10
    - Invento: Láser → desbloquea Nanotecnología, Impresión 3D
    - Invento: Fibra Óptica → desbloquea Internet

13. **Computación** (requiere techs: Electricidad + Reloj)
    - Desbloquea: Teléfono Móvil → Tech Comunicaciones → GPS

### Fase 5: Era de Información y Sistemas Avanzados
14. **Internet** (requiere: Fibra Óptica + Tech Computación)
    - Desbloquea: Inteligencia Artificial

15. **Inteligencia Artificial** (requiere techs: Internet + Computación)
    - Desbloquea: Robótica, Sistemas Autónomos
    - Bonificador: +15% todos los recursos

16. **Energías Renovables** (requiere techs: Electricidad + Acero)
    - Invento: Avión → exploración libre (cualquier casilla)
    - Bonificador: +30% todas las casillas

### Fase 6: Nanotecnología y Biotecnología
17. **Nanotecnología** (requiere inventos: Láser + Microscopio)
    - Desbloquea: Edición Genética

18. **Edición Genética** (requiere: Microscopio + Tech Nanotecnología)
    - Desbloquea: Biotecnología

19. **Biotecnología** (requiere: Penicilina + Tech Edición Genética)
    - Desbloquea: Terraformación
    - Bonificador: -30% impacto eventos

20. **Robótica** (requiere techs: IA + Acero)
    - Desbloquea: Sistemas Autónomos

21. **Sistemas Autónomos** (requiere techs: Robótica + IA)
    - Desbloquea: Terraformación

### Fase 7: Era Espacial (Victoria)
22. **Tecnología Espacial** (requiere inventos: Satélite + Estación Espacial)
    - Satélite y Estación Espacial requieren **Energías Renovables**
    - Desbloquea: Terraformación

23. **Terraformación** (requiere techs: Tecnología Espacial + Biotecnología + Sistemas Autónomos)
    - **FINAL**: Invento: Nave de Asentamiento Interestelar

---

## Tabla de Costes por Invento

Referencia rápida. Columnas separadas para prerequisitos (no se consumen) y costes reales (se descuentan del inventario).

| Invento | Tech Requerida | Prerequisitos (no se consumen) | Costes de recursos (se consumen) | Desbloquea |
|---|---|---|---|---|
| Cuchillo | — | — | obsidiana×8, roble×3 | Invento: Trampa |
| Cuerda | — | — | lino×8, canamo×5 | Invento: Barco |
| Lanza | Herramientas de Piedra | — | silex×8, roble×5 | Invento: Arcos |
| Hacha | Herramientas de Piedra | — | silex×10, roble×8 | Nv5 Cantera |
| Arcos | Herramientas de Piedra | `lanza` | roble×8 | Tech: Ganadería |
| Trampa | — | `cuchillo` | roble×5 | Tech: Ganadería |
| Refugios | — | — | roble×15, cana-comun×10 | Tech: Ganadería |
| Rueda | Herramientas de Piedra | — | roble×12, silex×8 | Invento: Carro |
| Carro | Herramientas de Piedra | `rueda` | roble×15, pieles×8 | Invento: Molino |
| Tela | Ganadería | — | lino×8, yute×6, lana×8 | Tech: Tejido |
| Cerámica | Control del Fuego | — | granito×10, agua×5 | Tech: Cerámica y Alfarería |
| Barco | Agricultura | `cuerda` | roble×20, pino×15, canamo×8 | Invento: Brújula |
| Molino | Agricultura | `carro` | granito×15, roble×10 | — |
| Acueducto | Agricultura | — | granito×20, agua×10 | Tech: Calefacción y Refrigeración |
| Arado | Agricultura | — | roble×10, hierro×5 | Nv5 Prado |
| Vidrio | Cerámica y Alfarería | — | arena-de-silice×15, arena-de-cuarzo×10 | Tech: Química |
| Acero | Metalurgia y Aleaciones | — | hierro×20, carbon-natural×15, grafito×5 | Tech: Escritura, Química |
| Moneda | Metalurgia y Aleaciones | — | cobre×15, oro×5 | Tech: Escritura |
| Brújula | Metalurgia y Aleaciones | `barco` | hierro×15, cobre×8, carbon-natural×10 | Nv5 Mina |
| Reloj | Metalurgia y Aleaciones | `acero` | cobre×10 | Tech: Computación |
| Papel | Química | — | cana-comun×15, agua×10 | Tech: Escritura |
| Imprenta | Escritura | `papel`, `acero` | — | Tech: Fotografía |
| Microscopio | Química | `vidrio`, `acero` | arena-de-silice×10, hierro×8 | Tech: Fotografía, Nanotecnología, Edición Genética |
| Penicilina | Química | — | agua×10, tierras-fertiles×15, latex×8 | Tech: Biotecnología |
| Bombilla | Química | `vidrio` | cobre×10, carbon-natural×8 | Tech: Electricidad |
| Batería | Química | — | cobre×15, estano×10, resinas-inflamables×10 | Tech: Electricidad |
| Láser | Electricidad | — | silicio×15, cristales-nat×10 | Tech: Nanotecnología |
| Fibra Óptica | Electricidad | `vidrio` | silicio×20, mat-aisl-nat×10 | Tech: Comunicaciones |
| Teléfono Móvil | Computación | — | silicio×20, mat-mag-nat×10, min-semi×10 | Tech: Comunicaciones Inalámbricas |
| Telescopio | Fotografía | `vidrio`, `acero` | arena-de-cuarzo×15, hierro×8 | Tech: Tecnología Espacial |
| Avión | Energías Renovables | `cuerda`, `acero` | carbon-natural×20, hierro×15 | Exploración libre |
| Satélite | Energías Renovables + GPS | `acero` | silicio×15, gases-naturales×10 | Tech: Tecnología Espacial |
| Estación Espacial | Energías Renovables | `acero` | silicio×20, hidrogeno×15 | Tech: Tecnología Espacial |
| **Nave de Asentamiento** | **Terraformación** | **`estacion-espacial`, `acero`, `vidrio`** | **silicio×400, hidrogeno×600, agua×300, mat-aisl-nat×200** | **FIN DEL JUEGO** |

---

## Notas de Implementación

### Identificadores de Tecnologías (tech_id)

Usar formato slug en minúsculas con guiones:
- `herr-piedra`, `control-fuego`, `ganaderia`, `ceramica-alfareria`, `tejido`
- `agricultura`, `fermentacion`, `metalurgia`, `conservacion`
- `quimica`, `escritura`, `fotografia`, `electricidad`, `computacion`
- `comunicaciones`, `gps`, `internet`, `ia`, `energias-renovables`
- `robotica`, `nanotecnologia`
- `edicion-genetica`, `biotecnologia`, `sistemas-autonomos`, `tecnologia-espacial`, `terraformacion`

### Identificadores de Inventos (invention_id)

Usar formato slug en minúsculas con guiones:
- Edad de Piedra: `cuchillo`, `cuerda`, `lanza`, `hacha`, `arcos`, `trampa`, `refugios`, `rueda`, `carro`
- Edad Media: `tela`, `ceramica`, `barco`, `molino`, `acueducto`, `arado`
- Edad de Bronce: `acero`, `moneda`, `brujula`, `reloj`
- Edad Moderna: `vidrio`, `papel`, `imprenta`, `microscopio`, `penicilina`, `bombilla`
- Era Industrial: `bateria`, `laser`, `fibra-optica`
- Era de Información: `telefono-movil`, `telescopio`
- Era Moderna Tardía: `avion`, `satelite`, `estacion-espacial`
- Era Espacial: `nave-asentamiento`

### Tipos de Prerequisitos

Hay **tres** tipos de prerequisito distintos, ninguno se consume:

| Tipo | Tabla | Descripción |
|---|---|---|
| `invention` | `invention_prerequisites` | Necesitas haber construido ese invento antes |
| `technology` | `technology_prerequisites` | Necesitas haber investigado esa tech antes |

> **Importante**: `acero`, `vidrio` y `papel` son **solo prerequisitos** — necesitas haberlos construido antes, pero NO se consumen ni aparecen en `invention_costs`. Los costes son siempre recursos de casilla.

```sql
-- Prerequisitos de tecnologías (no se consumen)
CREATE TABLE technology_prerequisites (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  technology_id VARCHAR(50) NOT NULL,
  prereq_type ENUM('invention', 'technology') NOT NULL,
  prereq_id VARCHAR(50) NOT NULL,
  UNIQUE(technology_id, prereq_type, prereq_id)
);

-- Prerequisitos de inventos (no se consumen)
CREATE TABLE invention_prerequisites (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  invention_id VARCHAR(50) NOT NULL,
  prereq_type ENUM('invention', 'technology') NOT NULL,
  prereq_id VARCHAR(50) NOT NULL,
  UNIQUE(invention_id, prereq_type, prereq_id)
);

-- Ejemplos
INSERT INTO invention_prerequisites VALUES
  -- trampa requiere haber construido cuchillo (no consume cuchillo)
  (NULL, 'trampa',    'invention',  'cuchillo'),
  -- arcos requiere haber construido lanza (no consume lanza)
  (NULL, 'arcos',     'invention',  'lanza'),
  -- barco requiere haber construido cuerda (no consume cuerda)
  (NULL, 'barco',     'invention',  'cuerda'),
  -- molino requiere haber construido carro (no consume carro)
  (NULL, 'molino',    'invention',  'carro'),
  -- brujula requiere haber construido barco (no consume barco)
  (NULL, 'brujula',   'invention',  'barco'),
  -- reloj requiere haber construido acero (acero no se consume, solo es prereq)
  (NULL, 'reloj',     'invention',  'acero'),
  -- imprenta requiere haber construido papel y acero
  (NULL, 'imprenta',  'invention',  'papel'),
  (NULL, 'imprenta',  'invention',  'acero'),
  -- microscopio requiere haber construido vidrio y acero
  (NULL, 'microscopio', 'invention', 'vidrio'),
  (NULL, 'microscopio', 'invention', 'acero'),
  -- telescopio requiere haber construido vidrio y acero
  (NULL, 'telescopio', 'invention', 'vidrio'),
  (NULL, 'telescopio', 'invention', 'acero'),
  -- bombilla requiere haber construido vidrio
  (NULL, 'bombilla',  'invention',  'vidrio'),
  -- fibra-optica requiere haber construido vidrio
  (NULL, 'fibra-optica', 'invention', 'vidrio'),
  -- avion requiere haber construido cuerda y acero
  (NULL, 'avion',     'invention',  'cuerda'),
  (NULL, 'avion',     'invention',  'acero'),
  -- satelite requiere haber construido acero
  (NULL, 'satelite',  'invention',  'acero'),
  -- estacion-espacial requiere haber construido acero
  (NULL, 'estacion-espacial', 'invention', 'acero'),
  -- nave requiere estacion-espacial, acero y vidrio construidos
  (NULL, 'nave-asentamiento', 'invention', 'estacion-espacial'),
  (NULL, 'nave-asentamiento', 'invention', 'acero'),
  (NULL, 'nave-asentamiento', 'invention', 'vidrio');
```

### Costes de Inventos (Recursos que se consumen)

Los costes son materiales que se **descuentan del inventario** al construir el invento. **Solo pueden ser recursos producidos por casillas** (raw nv1–4 o avanzados nv5). Los inventos como `acero`, `vidrio` y `papel` nunca son costes — solo pueden ser prerequisitos.

```sql
CREATE TABLE invention_costs (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  invention_id VARCHAR(50) NOT NULL,
  resource_id VARCHAR(50) NOT NULL,  -- siempre un resource de tile_level_resources, nunca un invention_id
  quantity INT NOT NULL,
  UNIQUE(invention_id, resource_id)
);

-- Ejemplos
INSERT INTO invention_costs VALUES
  -- trampa: solo consume roble (cuchillo es prerequisito, no coste)
  (NULL, 'trampa',    'roble',    5),
  -- arcos: solo consume roble (cuerda es prerequisito, no coste)
  (NULL, 'arcos',     'roble',    8),
  -- reloj: solo cobre (acero es prerequisito, no coste)
  (NULL, 'reloj',     'cobre',    10),
  -- microscopio: arena-de-silice y hierro (vidrio y acero son prereqs)
  (NULL, 'microscopio', 'arena-de-silice', 10),
  (NULL, 'microscopio', 'hierro',    8),
  -- nave de asentamiento (acero y vidrio son prereqs, no costes)
  (NULL, 'nave-asentamiento', 'silicio',   400),
  (NULL, 'nave-asentamiento', 'hidrogeno', 600),
  (NULL, 'nave-asentamiento', 'cobre',     80),
  (NULL, 'nave-asentamiento', 'agua',      300);
```

### Desbloqueos (Techs e Inventos)

Almacenar qué se desbloquea cuando se construye/investiga:

```sql
CREATE TABLE technology_unlocks (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  technology_id VARCHAR(50),
  unlock_type ENUM('technology', 'invention', 'tile_level'),  -- qué tipo se desbloquea
  unlock_id VARCHAR(50),  -- id de lo que se desbloquea
  UNIQUE(technology_id, unlock_type, unlock_id)
);

CREATE TABLE invention_unlocks (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  invention_id VARCHAR(50),
  unlock_type ENUM('technology', 'invention', 'tile_level'),  -- qué tipo se desbloquea
  unlock_id VARCHAR(50),  -- id de lo que se desbloquea
  UNIQUE(invention_id, unlock_type, unlock_id)
);
```

### Bonificadores

Almacenar como tipos enumerados + valor:

```sql
CREATE TABLE technology_bonuses (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  technology_id VARCHAR(50),
  bonus_type ENUM(
    'production_global',      -- +X% todos
    'production_tile',        -- +X% tipo casilla
    'production_speed',       -- +X% velocidad
    'invention_cost_reduction', -- -X% costes
    'event_mitigation'        -- -X% impacto eventos
  ),
  bonus_value INT,            -- +20, -15, etc.
  bonus_target VARCHAR(50) NULLABLE,  -- tipo casilla si es production_tile, ej: 'rio'
  UNIQUE(technology_id, bonus_type, bonus_target)
);
```

### Validación de Construcción

En el código de aplicación, antes de permitir votación de invento/tech:

```pseudocode
function canBuildInvention(invention, team):
  // 1. Verificar prerequisitos de inventos (no se consumen, solo se comprueba que existen)
  for each prereq in invention.prerequisites where prereq.type == 'invention':
    if !team.hasBuiltInvention(prereq.id):
      return false, "Falta construir primero: " + prereq.id

  // 2. Verificar prerequisitos de tecnología (no se consumen)
  for each prereq in invention.prerequisites where prereq.type == 'technology':
    if !team.hasResearchedTech(prereq.id):
      return false, "Falta investigar primero: " + prereq.id

  // 3. Verificar recursos del inventario (SÍ se consumen)
  for each cost in invention.costs:
    if team.inventory[cost.resource_id] < cost.quantity:
      return false, "Recursos insuficientes: " + cost.resource_id + " (faltan " + (cost.quantity - team.inventory[cost.resource_id]) + ")"

  return true

function executeInventionBuild(invention, team):
  // 1. Restar recursos
  for each resource_cost in invention.costs:
    team.inventory[resource_cost.resource] -= resource_cost.quantity
  
  // 2. Registrar invento construido
  team.addInvention(invention)
  
  // 3. Desbloquear consecuencias
  for each unlock in invention.unlocks:
    if unlock.type == 'technology':
      team.addAvailableTechnology(unlock.id)
    else if unlock.type == 'invention':
      team.addAvailableInvention(unlock.id)
    else if unlock.type == 'tile_level':
      // Permitir que casillas de ese tipo suban a nivel 5
      team.unlockTileLevel(unlock.id)
  
  // 4. Aplicar bonificadores permanentes
  for each bonus in invention.bonuses:
    team.addBonus(bonus)
```

### Punto de Revisión: Victoria

Antes de cada fin de Jornada, verificar si se ha alcanzado la victoria:

```pseudocode
function checkVictory(team):
  return team.hasInvention('nave-asentamiento') && 
         team.hasTechnology('terraformacion')
```

---
