# Planificación de Sprints v2 - Bressolium

Basado en `nuevas_tareas.md`. Se añade un Sprint 3 de arquitectura obligatoria y un Sprint 5 de calidad y cierre, resultado de incorporar los requisitos de la guía del módulo.

El criterio de ordenación dentro de cada sprint es: primero las tareas sin bloqueantes, luego las que dependen de ellas. Las tareas que pueden hacerse en paralelo entre las dos desarrolladoras se indican en el mismo bloque.

---

# ✅ Sprint 1: La Base y el Autenticado [COMPLETADO]

- Tarea 1: `[Feat] Migrations and Base Models (Relational V4)` (BD - Bárbara) [Talla: S] [TERMINADA]
- Tarea 6: `[Feat] Tile Migrations and Base Dictionary` (BD - Michelle) [Talla: S] [TERMINADA]
- Tarea 14: `[Feat] Migrations and Relations for the Tech Process` (BD - Bárbara) [Talla: M] [TERMINADA]
- Tarea 2: `[Feat] API Authentication Setup with Sanctum` (Backend - Michelle) [Talla: M] [TERMINADA]
- Tarea 4: `[Feat] CRUD Endpoints for Teams and 1st Round Creation` (Backend - Michelle) [Talla: M] [TERMINADA]

---

# ✅ Sprint 2: Schema V5, Cliente Web y Tablero API [COMPLETADO]

- Tarea 3: `[Feat] Frontend Structure, Auth Routing and Redux` (Frontend - Bárbara) [Talla: L] [TERMINADA]
- Tarea 17: `[Feat] Global TopBar & Session Navigation` (Frontend - Michelle) [Talla: S] [TERMINADA]
- Tarea 20: `[Refactor] Mover lógica de Auth y Teams a Servicios y Repos` (Backend - Bárbara) [Talla: S] [TERMINADA]
- Tarea 5: `[Feat] Game Lobby & Team Manager UI` (Frontend - Bárbara) [Talla: M] [TERMINADA]
- Tarea 21: `[Refactor] DB Migration V5a: Tile Schema Correction` (BD - Michelle) [Talla: M] [TERMINADA]
- Tarea 22: `[Refactor] DB Migration V5b: Tech Tree Normalization` (BD - Bárbara) [Talla: M] [TERMINADA]
- Tarea 24: `[Docs] Update ER Diagram to V5` (Documentación - Michelle) [Talla: S] [TERMINADA]
- Tarea 7: `[Feat] Board Generator and API Controller` (Backend - Bárbara) [Talla: L] [TERMINADA]
- Tarea 8: `[Feat] Individual Actions API (Explore / Upgrade)` (Backend - Bárbara) [Talla: L] [TERMINADA]
- Tarea 9: `[Feat] Board Grid Component and Frontend Visualization` (Frontend - Michelle) [Talla: XL] [TERMINADA]
- Tarea 18: `[Feat] Material Inventory Side-Panel` (Frontend - Michelle) [Talla: S] [TERMINADA]
- Tarea 23: `[Feat] Catalog Seeders: Complete Game Data` (BD - Michelle) [Talla: L] [TERMINADA]

---

# ✅ Sprint 3: Arquitectura Completa y Fundamentos [COMPLETADO]

(La guía del módulo exige capas que aún faltan. Este sprint las construye antes de seguir añadiendo features, para que T10 en adelante ya nazcan con los patrones correctos. Las tareas de este sprint no tienen bloqueantes pendientes y pueden hacerse en paralelo.)

**En paralelo — Backend (Bárbara):**
- Tarea 25: `[Refactor] Contracts, Interfaces y Service Providers` (Backend - Bárbara) [Talla: M] [TERMINADA]
- Tarea 27: `[Refactor] DTOs y API Resources` (Backend - Bárbara) [Talla: M] [TERMINADA]

**En paralelo — Backend + Frontend (Michelle):**
- Tarea 26: `[Refactor] Form Requests, Policies y Namespace Controladores API` (Backend - Michelle) [Talla: M] [TERMINADA]
- Tarea 28: `[Refactor] Excepciones Personalizadas y Handler Global` (Backend - Michelle) [Talla: S] [TERMINADA]
- Tarea 36: `[Feat] Rate Limiting y Versionado de API` (Backend - Michelle) [Talla: XS] [TERMINADA]
- Tarea 30: `[Feat] Cliente HTTP Centralizado con Interceptores` (Frontend - Michelle) [Talla: S] [TERMINADA]

**Cierre del sprint — paralelo (cualquiera de las dos):**
- Tarea 48: `[Refactor] DB Migration V6: Quantities in Inventions & Prerequisites` (BD - Bárbara) [Talla: M] [TERMINADA]
- Tarea 49: `[Docs] Update ER Diagram to V6 + Evolución Tecnológica` (Documentación - Michelle) [Talla: S]
  - *Bloqueado por: T48*
- Tarea 38: `[Feat] Actualización de Seeders (Nuevos Items + Quantities)` (BD - Michelle) [Talla: S] [TERMINADA]
- Tarea 40: `[Refactor] Response Builder Centralizado` (Backend - Bárbara) [Talla: S] [TERMINADA]
- Tarea 41: `[Feat] Middleware Global (Force JSON + Logging)` (Backend - Michelle) [Talla: XS] [TERMINADA]

> ⚠️ T48 introduce el schema V6 (cantidades en prerrequisitos e inventos). Bloquea T10, T13, T19 y T38, así que debe cerrarse antes de pasar a S4.

---

# 🗺️ Sprint 4: Gameplay Core — Tablero, Sync y Votaciones

(Con la arquitectura lista y el catálogo poblado, se construye la experiencia de juego completa. Las tareas de backend y frontend pueden avanzar en paralelo.)

**Arrancan en cuanto T25–T28 estén listas:**
- Tarea 29: `[Feat] Tests Unitarios de Backend` (Backend - Bárbara) [Talla: L]
  - *Bloqueado por: T25, T26, T27, T28*
- Tarea 31: `[Refactor] Hooks por Feature` (Frontend - Bárbara) [Talla: M]
  - *Bloqueado por: T30*
- Tarea 33: `[Feat] CI/CD Pipeline` (DevOps - Bárbara) [Talla: M]
  - *Sin bloqueantes técnicos; antes mejor que después*
- Tarea 42: `[Feat] RTK Query / Server State Cache` (Frontend - Bárbara) [Talla: M] [TERMINADA]
- Tarea 43: `[Refactor] Pages + Lazy Loading + Routes Centralizado` (Frontend - Bárbara) [Talla: M]
  - *Sin bloqueantes técnicos. Mejor antes de T19 y T50/T51 para que las nuevas vistas nazcan en `/pages`.*
- Tarea 44: `[Feat] Contexts + UI Components Reutilizables` (Frontend - Bárbara) [Talla: S] [TERMINADA]

**Arranca cuando T25–T28 y T42 estén listas:**
- Tarea 10: `[Feat] Relational Sync and Polling` (Fullstack - Bárbara) [Talla: M] [TERMINADA]

**Arranca cuando T10 esté lista:**
- Tarea 11: `[Feat] Progress Voting API` (Backend - Bárbara) [Talla: M] [TERMINADA]
- Tarea 19: `[Feat] Technology Tree & Progress Archive` (Frontend - Michelle) [Talla: M]
  - *Bloqueado por: T23✅, T30, T48*
- Tarea 50: `[Feat] Inventory Panel: Inventions Section` (Frontend - Michelle) [Talla: S] [TERMINADA]
- Tarea 52: `[Fix] Inventory Panel — Layout en Grid de 4 Columnas` (Frontend - Michelle) [Talla: XS] [TERMINADA]
  - *Bloqueado por: T50*
- Tarea 54: `[Fix] VotingPanel — Inventos construibles solo una vez` (Frontend - Michelle) [Talla: XS]
  - *Bloqueado por: T12, T48*
- Tarea 55: `[Fix] Inventory Panel — Nombres de materiales e iconos de inventos` (Frontend - Michelle) [Talla: XS] [En revisión]
  - *Bloqueado por: T52*

**Arranca cuando T11 esté lista:**
- Tarea 12: `[Feat] Action & Decision Control Panel` (Frontend - Bárbara) [Talla: L] [TERMINADA]
- Tarea 13: `[Feat] Schedule / Cron Round Close and Round Jump` (Backend - Michelle) [Talla: XL] [TERMINADA]
- Tarea 39: `[Feat] Eventos y Listeners de Dominio` (Backend - Michelle) [Talla: M]
  - *Sin bloqueantes técnicos. Idealmente en paralelo a T13 (Michelle) para que el cron emita eventos.*

**Arranca cuando T19 y T44 estén listas:**
- Tarea 51: `[Feat] Pueblo Tile: Center Placement + Tech Tree Access` (Fullstack - Michelle) [Talla: M]
  - *Bloqueado por: T19, T44*
- Tarea 53: `[Fix] Tech Tree Modal — Tecnologías no visibles al abrir desde casilla Pueblo` (Frontend - Michelle) [Talla: M]
  - *Bloqueado por: T19, T51*

> ⚠️ T13 es XL y es probable que se extienda al Sprint 5. Considerar dividir T13 en subtareas internas para poder hacer seguimiento.

---

# 🏁 Sprint 5: Calidad, Cierre y Despliegue

(Tests de calidad, cierre de mecánicas y documentación final. Se puede solapar con el período de colchón definido en `sprints.md`.)

**Arrancan cuando T13 esté lista:**
- Tarea 15: `[Feat] End of Game (Terraforming)` (Backend - Michelle) [Talla: S]
  - *Bloqueado por: T13*
- Tarea 16: `[Feat] Abandonment Management` (Backend - Michelle) [Talla: S]
  - *Bloqueado por: T13*

**Arrancan cuando T30 y T31 estén listas:**
- Tarea 32: `[Feat] Tests de Frontend` (Frontend - Michelle) [Talla: L]
  - *Bloqueado por: T30, T31*

**Sin bloqueantes pendientes, pueden hacerse en cualquier momento del sprint:**
- Tarea 37: `[Feat] Cache Service` (Backend - Bárbara) [Talla: S]
  - *Bloqueado por: T25*
- Tarea 35: `[Docs] Documentación de Arquitectura` (Documentación - Bárbara) [Talla: S]
- Tarea 46: `[Feat] Monitoreo y Métricas` (DevOps - Michelle) [Talla: S]

**Arranca cuando T33 esté lista:**
- Tarea 34: `[Feat] Tests E2E` (Testing - Michelle) [Talla: L]
  - *Bloqueado por: T33*
- Tarea 45: `[Feat] Despliegue Producción (HTTPS + CORS)` (DevOps - Bárbara) [Talla: M]
  - *Bloqueado por: T33*

**Arranca cuando T34 esté lista:**
- Tarea 47: `[Feat] Accesibilidad` (Frontend - Bárbara) [Talla: S]
  - *Bloqueado por: T34*

---

# 📊 Grafo de dependencias resumido

```
T21✅ ──┐
T22✅ ──┼──► T23✅
         └──► T24✅

T22✅ ──► T48 ──┬──► T38
                ├──► T10 ──► T11 ──► T12
                ├──► T13 ──► T15
                ├──► T13 ──► T16
                ├──► T19 ──► T51
                └──► T49

T8✅ ────────────────────► T10
T42 ─────────────────────► T10
T25 ──┐
T26 ──┤
T27 ──┼─────────────────► T10
T28 ──┘
  │
  └──► T29

T11 ──► T12
T11 ──► T13 ──► T39 (paralelo)
T19 + T44 ──► T51

T30 ──┬──► T12
      ├──► T19
      ├──► T31 ──► T32
      └──► T42

T10 + T48 ──► T50

T33 ──► T34 ──► T47
T33 ──► T45
T25 ──► T37

T9✅, T18✅ (ya completadas — T30 y T31 adaptarán su código al cliente centralizado y al hook; T50 y T51 amplían inventario y BoardGrid sin modificarlas)
```

---

# 📋 Carga por sprint y desarrolladora

| Sprint | Bárbara | Michelle |
|---|---|---|
| S1 ✅ | T1(S), T14(M) | T2(M), T4(M), T6(S) |
| S2 ✅ | T3(L), T20(S), T5(M), T22(M), T7(L), T8(L) | T17(S), T21(M), T24(S), T9(XL), T18(S), T23(L) |
| S3 | T25(M), T27(M), T40(S), T48(M) | T26(M), T28(S), T30(S), T36(XS), T38(S), T41(XS), T49(S) |
| S4 | T10(M), T11(M), T12(L), T29(L), T31(M), T33(M), T42(M), T43(M), T44(S) | T13(XL), T19(M), T39(M), T50(S), T51(M), T52(XS), T53(M), T54(XS), T55(XS) |
| S5 | T35(S), T37(S), T45(M), T47(S) | T15(S), T16(S), T32(L), T34(L), T46(S) |
