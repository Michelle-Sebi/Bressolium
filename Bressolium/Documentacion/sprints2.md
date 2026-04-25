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

# 🏛️ Sprint 3: Arquitectura Completa y Fundamentos

(La guía del módulo exige capas que aún faltan. Este sprint las construye antes de seguir añadiendo features, para que T10 en adelante ya nazcan con los patrones correctos. Las tareas de este sprint no tienen bloqueantes pendientes y pueden hacerse en paralelo.)

**En paralelo — Backend (Bárbara):**
- Tarea 25: `[Refactor] Contracts, Interfaces y Service Providers` (Backend - Bárbara) [Talla: M]
- Tarea 27: `[Refactor] DTOs y API Resources` (Backend - Bárbara) [Talla: M]

**En paralelo — Backend + Frontend (Michelle):**
- Tarea 26: `[Refactor] Form Requests, Policies y Namespace Controladores API` (Backend - Michelle) [Talla: M]
- Tarea 28: `[Refactor] Excepciones Personalizadas y Handler Global` (Backend - Michelle) [Talla: S]
- Tarea 36: `[Feat] Rate Limiting y Versionado de API` (Backend - Michelle) [Talla: XS]
- Tarea 30: `[Feat] Cliente HTTP Centralizado con Interceptores` (Frontend - Michelle) [Talla: S]

**Sin bloqueantes pendientes — puede solaparse con lo anterior:**
- Tarea 38: `[Feat] Actualización de Seeders (Nuevos Items)` (BD - Michelle) [Talla: S]

> ⚠️ Tarea 38 adapta los seeders a los últimos cambios de diseño (eliminación de Caolinita/Peces). T23 ya dejó la estructura lista.

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

**Arranca cuando T25–T28 estén listas:**
- Tarea 10: `[Feat] Relational Sync and Polling` (Fullstack - Bárbara) [Talla: M]
  - *Bloqueado por: T8✅, T25, T26, T27, T28*

**Arranca cuando T10 esté lista:**
- Tarea 11: `[Feat] Progress Voting API` (Backend - Bárbara) [Talla: M]
  - *Bloqueado por: T10, T25, T26, T27, T28*
- Tarea 19: `[Feat] Technology Tree & Progress Archive` (Frontend - Michelle) [Talla: M]
  - *Bloqueado por: T23, T30*

**Arranca cuando T11 esté lista:**
- Tarea 12: `[Feat] Action & Decision Control Panel` (Frontend - Bárbara) [Talla: L]
  - *Bloqueado por: T11, T30*
- Tarea 13: `[Feat] Schedule / Cron Round Close and Round Jump` (Backend - Michelle) [Talla: XL]
  - *Bloqueado por: T11*

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

**Arranca cuando T33 esté lista:**
- Tarea 34: `[Feat] Tests E2E` (Testing - Michelle) [Talla: L]
  - *Bloqueado por: T33*

---

# 📊 Grafo de dependencias resumido

```
T21✅ ──┐
T22✅ ──┼──► T23✅
         └──► T24✅

T8✅ ────────────────────► T10 ──► T11 ──► T12
                            ▲              │
T25 ──┐                     │              └──► T13 ──► T15
T26 ──┤                     │                       └──► T16
T27 ──┼─────────────────────┘
T28 ──┘
  │
  └──► T29

T23✅ ──► T38 ──► T19
T30 ──┬──► T12
      ├──► T19
      └──► T31 ──► T32

T33 ──► T34
T25 ──► T37

T9✅, T18✅ (ya completadas — T30 y T31 adaptarán su código al cliente centralizado y al hook)
```

---

# 📋 Carga por sprint y desarrolladora

| Sprint | Bárbara | Michelle |
|---|---|---|
| S1 ✅ | T1(S), T14(M) | T2(M), T4(M), T6(S) |
| S2 ✅ | T3(L), T20(S), T5(M), T22(M), T7(L), T8(L) | T17(S), T21(M), T24(S), T9(XL), T18(S), T23(L) |
| S3 | T25(M), T27(M) | T26(M), T28(S), T36(XS), T30(S), T38(S) |
| S4 | T29(L), T31(M), T33(M), T10(M), T11(M), T12(L) | T19(M), T13(XL) |
| S5 | T37(S), T35(S) | T15(S), T16(S), T32(L), T34(L) |
