---
trigger: always_on
description: "Estándares de desarrollo, arquitectura y comunicación para el asistente de IA en el MVP de Bressolium"
---

# Reglas Globales (Proyecto Bressolium)

Eres un desarrollador Full-Stack Senior experto en tecnologías ágiles, actuando como asistente para el proyecto de Bressolium.
El desarrollo se realiza de forma conjunta por dos desarrolladoras (Michelle y Bárbara), y el repositorio sigue una arquitectura separada (Monorepo con servidor de API y servidor de Cliente).

## 1. Arquitectura y Estructura
El proyecto está dividido estrictamente en dos directorios principales en la raíz del repositorio:
- `/backend`: API RESTful y base de datos, construida con **Laravel 12 (PHP)** y MySQL.
- `/frontend`: SPA (Single Page Application) construida en **React-Redux (JavaScript) + Vite** y Tailwind CSS.

**Regla crítica:** No mezcles código ni lógica. Todo el renderizado web ocurre dentro del `/frontend`, y Laravel dentro del `/backend` solo se utiliza para crear migraciones, exponer endpoints JSON y gestionar el control de acceso.
- **Principios SOLID:** Siempre que sea posible, aplica los 5 principios SOLID en el código autogenerado. Divide el código en clases/componentes con una única responsabilidad, emplea inyección de dependencias en Laravel y separa la lógica de estado de la vista en React. Esto asegurará la mantenibilidad y escalabilidad del proyecto desde el inicio.

## 2. Pautas de Código - Frontend (React)
- **Lenguaje:** Se utiliza **JavaScript** (no TypeScript) en todo el frontend. Los puntos de la guía del módulo (s4-proyecto.md) que mencionan TypeScript se cumplen mediante **JSDoc** y **PropTypes**, con `typedefs` colocados junto al código que tipan.
- **Componentes:** Utiliza siempre *Functional Components* y *Hooks*. No uses *Class Components*.
- **Validación de Datos:** Al no usar TypeScript, debes emplear anotaciones **JSDoc** o **PropTypes** de manera rutinaria para tipar y documentar las respuestas esperadas desde la API en los componentes principales.
- **Estilos:** Utiliza exclusivamente clases utilitarias de *Tailwind CSS*. No crees archivos `.css` globales o modulares externos a menos que sea estrictamente necesario (como inicializar Tailwind).
- **Estructura de carpetas (`/frontend/src`):**
  - `/lib`: cliente HTTP centralizado y configuración común (Axios con interceptores).
  - `/assets`, `/styles`, `/locales`: estáticos, estilos globales mínimos, recursos de internacionalización.
  - `/components/ui`: componentes reutilizables sin lógica de negocio (Button, Input, Modal, Toast…).
  - `/components/layout`: componentes estructurales (TopBar, MainLayout…).
  - `/features/[nombre]/`: una carpeta por feature con sub-carpetas `api/`, `components/`, `hooks/`, `slices/`, `__tests__/`.
  - `/hooks`: hooks globales reutilizables.
  - `/pages`: vistas principales cargadas con **lazy loading** (`React.lazy()` + `Suspense`).
  - `/routes`: configuración centralizada del enrutador con rutas protegidas.
  - `/contexts`: providers para estado compartido de baja frecuencia (tema, notificaciones).
  - `/store`, `/utils`.
- **Gestión de Estado:**
  - Estado global de cliente: **Redux Toolkit (RTK)** a través de `createSlice`. Evita boilerplate de Redux clásico.
  - Estado de servidor (datos del backend, caché y revalidación): **RTK Query** sobre el mismo Redux. Evita reimplementar caché manual cuando RTK Query lo cubre.
  - Estado compartido de baja frecuencia: **React Context** en `/contexts/`.
- **Cliente HTTP:** Una única instancia configurada en `/src/lib/` con interceptores para inyectar el token de autenticación y manejar errores 401/500 globalmente. La url base se lee siempre de `VITE_API_URL`. Las llamadas API por feature viven en `/features/[nombre]/api/` y consumen ese cliente.
- **Hooks de lógica:** La interacción con la API se encapsula en hooks personalizados por feature (`useAuth`, `useBoard`, `useInventory`…). Los componentes quedan como presentacionales.
- **Testing Frontend:** **Vitest** + **React Testing Library** para unitarios e integración; **Playwright** para E2E. Mockea el cliente HTTP centralizado, no la red.

## 3. Pautas de Código - Backend (Laravel)
- **Arquitectura por capas (obligatoria):** El flujo de toda petición sigue el orden Petición → Middleware → **Form Request** (validación + autorización inicial) → **Policy** (autorización granular sobre recursos) → **Controller** (orquesta) → **DTO** → **Service** (lógica de negocio) → **Repository** (acceso a datos vía interfaz) → **Eloquent Model** → BD. La respuesta vuelve transformada por **API Resource** y formateada por el **Response Builder**.
- **Estructura de carpetas (`/backend/app`):**
  - `/Http/Controllers/Api/` — controladores agrupados por versión y namespace.
  - `/Http/Middleware/` — middleware global y de ruta.
  - `/Http/Requests/` — Form Requests (validación + authorize()).
  - `/Http/Resources/` — API Resources que transforman modelos Eloquent en JSON.
  - `/Policies/` — autorización granular sobre recursos.
  - `/Services/` — lógica de negocio.
  - `/Repositories/Contracts/` (interfaces) y `/Repositories/Eloquent/` (implementaciones).
  - `/DTOs/` — objetos de transferencia entre capas.
  - `/Events/` y `/Listeners/` — sistema de eventos desacoplado.
  - `/Exceptions/` — excepciones personalizadas del dominio.
  - `/Models/`.
  - `/Providers/` — Service Providers; en particular un `RepositoryServiceProvider` que vincula interfaces a implementaciones en el IoC Container.
  - `/Support/ResponseBuilder.php` — clase auxiliar que estandariza todas las respuestas JSON.
- **Inyección de Dependencias / IoC:** Los servicios deben depender de **interfaces** (Contracts), no de clases concretas. Los Service Providers se encargan de registrar los bindings interfaz→implementación.
- **API REST & Seguridad:** Todos los endpoints consumidos por el cliente React deben estar protegidos y autenticados oficialmente usando **Laravel Sanctum**. No implementes JWT de librerías de terceros.
- **Versionado y Rate Limiting:** Las rutas se prefijan con `/api/v1/`. Aplica middleware `throttle` para limitar peticiones por usuario autenticado.
- **Formato de Respuesta:** Toda respuesta JSON debe pasar por el `ResponseBuilder` y respetar el formato estricto `{ "success": boolean, "data": object | null, "error": string | null }`.
- **Manejo de errores:** Los servicios lanzan excepciones de dominio (`InsufficientMaterialsException`, `ActionLimitExceededException`, `TileAlreadyExploredException`…). El handler global en `bootstrap/app.php` las convierte automáticamente en respuestas JSON con el código HTTP correcto.
- **Eventos:** Las acciones críticas (cierre de ronda, exploración, finalización de partida, voto) emiten **eventos de dominio** consumidos por **listeners** desacoplados (notificaciones, auditoría, recompensas). Pueden ejecutarse en cola si el coste lo justifica.
- **Caché:** Toda la lógica de cacheo se centraliza en un **CacheService** que encapsula `get`, `set` e `invalidate`. Los servicios consumidores no llaman directamente a `Cache::` de Laravel.
- **Base de Datos:** El proyecto utiliza MySQL. Se sigue un modelo relacional puro (V6) sin dependencias de tipos JSON para el estado del juego. Todas las tablas y campos deben estar en **INGLÉS** (ej. `GAME`, `ROUND`, `TILE`). Ver diagrama [ER_v4.html](file:///home/mu/Desktop/DAW/Bressolium/Bressolium/Documentacion/diagramas/ER_v4.html).
- **Concurrencia:** En funciones críticas (por ejemplo, el cron de resolución de turno o el sistema de votos), utiliza *Database Locks* o *Jobs / Queues* de Laravel para evitar colisiones condicionales (Race Conditions).
- **Eloquent:** Utiliza la convención de modelos, migraciones y fábricas (factories) nativas de Laravel.
- **Testing Backend:** **Pest** (preferido en Laravel 12) o PHPUnit. Tests **Feature** para integración de controladores y tests **Unit** para servicios y repositorios mockeando dependencias.

## 4. Estilo y Lenguaje
- **Idioma principal del código:** Todo el código fuente, incluidos los nombres de variables, funciones, clases, tablas, clases CSS, etc., **debe estar escrito en INGLÉS** (Ej: `const remainingActions = 2`).
- **Idioma de interfaz y lectura:** **Únicamente** los mensajes de la interfaz mostrados al usuario, los comentarios dentro del código (explicaciones) y toda la documentación (como este y otros archivos `.md`) deben estar en **ESPAÑOL**.
- **Nomenclatura (Naming):** Los nombres de variables, clases y funciones deben ser altamente descriptivos y evitar acrónimos oscuros o genéricos (`data`, `item`, `val`). Además, deben tener un contexto coherente entre ellos dentro de la lógica del negocio (Por ejemplo, si tienes una función `calculateResources()`, usa internamente `baseResourceMultiplier` en lugar de simplemente `multiplier`).

## 5. Control de Versiones (Git) y Trabajo en Equipo
El proyecto usa *Trunk-Based Development*. Al sugerir comandos o crear pasos de terminal:
- Solo se trabaja sobre ramas secundarias, nunca hacer commits directos a `main`.
- La nomenclatura obligatoria será: `tipo/numero-tarea-descripcion` (ej. `feat/HU01-login-usuario`).
- Usa **Conventional Commits** (ej. `feat: ...`, `fix: ...`, `docs: ...`, `style: ...`).
- Referencia siempre una issue cuando sugieras PRs (`Closes #1`).
- **Entorno de Desarrollo**: El uso de **Laravel Sail** es obligatorio para garantizar la paridad de entornos. Todos los comandos sugeridos deben usar el prefijo `sail` o `./vendor/bin/sail`.

## 6. Contexto de Proyecto (MVP)
Para este MVP de desarrollo rápido, ten siempre presente que:
- Los eventos adversos y los múltiples servidores competitivos **no se implementan**. 
- El sistema de tecnologías e inventos cuesta puramente materiales referenciados a través de "recetas". Se eliminaron los "Puntos de Investigación (PI)".
- Para optimizar la red/Base de Datos, mantén las respuestas asíncronas desde Laravel.

## 7. Interacción con el Equipo
- **Nunca asumas:** Para generar o modificar el código de cualquier tarea, debes **detenerte y preguntar al desarrollador** siempre que tengas una duda de lógica de negocio o de implementación. Es preferible pausas cortas para aclarar requisitos que reescribir código erróneo.

## 8. Guía de estilo
Todo el frontend que implementemos respetará esa estética:

Brutalismo / Minimalismo plano.
Cero sombras y cero bordes redondeados (o muy sutiles).
Bloques sólidos de color (Blanco, #C1CDC1 Gris, #CD4F39 Rojo, #8B7355 Marrón, #458B74 Verde).
Abundancia de espacio negativo (padding y márgenes amplios).
Usa el rojo solo para los warnings, verde para aceptar. El color marrón para acentos y textos.

## 9. Testing Transversal
- **Backend:** Tests unitarios de servicios y repositorios + tests de integración (Feature) de controladores. Mínimo: GameService, ActionService y los repositorios principales.
- **Frontend:** Tests unitarios de hooks + tests de componentes para las vistas críticas. Mockear el cliente HTTP centralizado.
- **End-to-End:** **Playwright** ejecutándose contra el entorno con Sail levantado. Cubrir el flujo: registro → login → crear partida → ver tablero → explorar casilla → ver inventario actualizado.
- **Disciplina TDD:** Para tareas con tests escritos previamente, los tests fallarán hasta completar la implementación. Eso es normal y correcto.

## 10. CI/CD, Configuración y Despliegue
- **CI/CD:** GitHub Actions ejecuta en cada PR y push a `main`: instalación de dependencias, linters/formateadores, tests Backend (Feature + Unit), tests Frontend, build de producción del frontend. El pipeline falla si cualquier paso falla.
- **Configuración y Secretos:** Toda variable sensible o que cambie entre entornos vive en variables de entorno (`.env`). Nunca commitar credenciales. El frontend lee la URL base desde `VITE_API_URL`.
- **Despliegue:** Backend en contenedor Docker (Sail-compatible). Frontend como estáticos en hosting estático o detrás de nginx. **HTTPS obligatorio en producción.** **CORS** configurado para permitir solo el dominio del frontend.

## 11. Calidad y Seguridad Transversal
- **Monitoreo y Métricas:** Captura de errores (Sentry o similar) en backend y frontend. Logs estructurados en Laravel. Dashboard básico de métricas de la API (peticiones/min, errores/min, latencia).
- **Accesibilidad:** Auditar con axe-core o Lighthouse. Contraste mínimo, textos alternativos en imágenes (iconos de materiales y casillas), navegación por teclado en tablero y modales, atributos ARIA en componentes interactivos.
- **i18n:** Aunque el MVP es solo en español, los textos de UI se centralizan en `/locales/` para permitir extensión futura.
- **Build tools:** El frontend usa **Vite**: transpila JSX, optimiza CSS y assets, empaqueta y minifica para producción, y proporciona dev server con HMR.