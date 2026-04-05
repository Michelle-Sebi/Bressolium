---
name: "Reglas Globales Bressolium"
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
- **Componentes:** Utiliza siempre *Functional Components* y *Hooks*. No uses *Class Components*.
- **Gestión de Estado:** Utiliza **Redux Toolkit (RTK)** a través de `createSlice` para el manejo global del estado. Evita usar código boilerplate verboso de Redux clásico.
- **Validación de Datos:** Al no usar TypeScript, debes emplear anotaciones **JSDoc** o **PropTypes** de manera rutinaria para tipar y documentar las respuestas esperadas desde la API en los componentes principales.
- **Estilos:** Utiliza exclusivamente clases utilitarias de *Tailwind CSS*. No crees archivos `.css` globales o modulares externos a menos que sea estrictamente necesario (como inicializar Tailwind).
- **Consumo de API:** Todas las llamadas al backend se deben centralizar en métodos dentro del directorio `/frontend/src/services/` utilizando `fetch` o Axios, leyendo siempre la url base de una variable de entorno (`VITE_API_URL`).
- **Testing Frontend:** En caso de que se solicite escribir pruebas, utiliza siempre **Vitest** combinado con **React Testing Library**.

## 3. Pautas de Código - Backend (Laravel)
- **API REST & Seguridad:** Todos los endpoints consumidos por el cliente React deben estar protegidos y autenticados oficialmente usando **Laravel Sanctum**. No implementes JWT de librerías de terceros.
- **Formato de Respuesta:** Todos los controladores deben devolver respuestas estandarizadas en JSON, preferiblemente siguiendo este formato estricto: `{ "success": boolean, "data": object | null, "error": string | null }`.
- **Base de Datos:** El proyecto utiliza MySQL. Toda la persistencia temporal de la "jornada" debe interactuar con el campo de tipo JSON (`estado_jornada`) de la tabla `PARTIDA`.
- **Concurrencia:** En funciones críticas (por ejemplo, el cron de resolución de turno o el sistema de votos), utiliza *Database Locks* o *Jobs / Queues* de Laravel para evitar colisiones condicionales (Race Conditions).
- **Eloquent:** Utiliza la convención de modelos, migraciones y fábricas (factories) nativas de Laravel. 
- **Testing Backend:** En caso de que se te solicite escribir pruebas, prioriza usar **Pest** (ideal para Laravel 12) o alternativamente PHPUnit.

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

## 6. Contexto de Proyecto (MVP)
Para este MVP de desarrollo rápido, ten siempre presente que:
- Los eventos adversos y los múltiples servidores competitivos **no se implementan**. 
- El sistema de tecnologías e inventos cuesta puramente materiales referenciados a través de "recetas". Se eliminaron los "Puntos de Investigación (PI)".
- Para optimizar la red/Base de Datos, mantén las respuestas asíncronas desde Laravel.

## 7. Interacción con el Equipo
- **Nunca asumas:** Para generar o modificar el código de cualquier tarea, debes **detenerte y preguntar al desarrollador** siempre que tengas una duda de lógica de negocio o de implementación. Es preferible pausas cortas para aclarar requisitos que reescribir código erróneo.
