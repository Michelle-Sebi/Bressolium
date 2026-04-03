# Prompt para Generar las Tareas del Proyecto (Bressolium MVP)

**Copia y pega el siguiente texto en tu asistente o utilízalo como instrucción directiva:**

---

"Actúa como un Tech Lead o Senior Developer experto en metodologías ágiles (Scrum) y diseño de software. Tenemos un proyecto de juego estratégico cooperativo llamado 'Bressolium' planificado para ser desarrollado como Producto Mínimo Viable (MVP). 

El proyecto cuenta con la siguiente base documental que debes tener en cuenta:
1. **Requisitos Funcionales (Épicas)**: Gestión de Usuarios, Tablero/Exploración, Turnos/Cooperación, y Tecnología/Victoria. *Nota: Se descartan métricas de escalabilidad global, multiservidor y eventos adversos (fuera del scope del MVP).*
2. **Modelo de Base de Datos**: Un esquema híbrido relacional con PostgreSQL. Incluye tablas de Usuarios, Partida (que almacena un JSON para el estado volátil del turno), Casillas, Tecnologías, Inventos, Materiales y Recetas intermedias.
3. **Acuerdos de Equipo (Working Agreements)**:
   - Repositorio colaborativo en GitHub usando Trunk-Based Development sobre la rama `main`.
   - Nomenclatura de ramas: `feat/<desc>`, `fix/<desc>`, `docs/<desc>`.
   - Nomenclatura de commits convencionales y cierres de issues en la PR (`Closes #<numero>`).
   - Política de Pull Requests: 1 aprobación necesaria para hacer merge.
   - Todo debe cumplir una Definition of Ready (DoR) para empezar y una Definition of Done (DoD) para cerrarse.

**Tu objetivo es:**
Toma estos requisitos y genera el **Product Backlog completo inicial** dividiendo el trabajo en tareas granulares y accionables (historias de usuario / tareas técnicas).

**Requisitos estrictos para la generación de las tareas:**
1. **Categorización Técnica**: Cada tarea debe estar claramente etiquetada si pertenece a [FRONTEND], [BACKEND] o [BASE DE DATOS]. Muchas historias de usuario se tendrán que dividir en subtareas técnicas.
2. **Asignación Equilibrada**: El equipo consta de dos desarrolladores (Dev A y Dev B). Debes proponer una asignación de tareas equitativa (mismo peso de esfuerzo global).
3. **Desarrollo Full-Stack**: Es imprescindible que tanto el 'Dev A' como el 'Dev B' tengan asignadas tareas de Frontend, Backend y Base de Datos (nadie es puramente Front o puramente Back).
4. **Formato de la Tarea**: Presenta cada tarea con la siguiente estructura inspirada en issues de GitHub:
   - **Título**: `[Tipo] Descripción breve` (Ej. `[Feat] Endpoint para votación de tecnologías`)
   - **Estimación (Story Points)**: 1, 2, 3, 5, u 8.
   - **Área**: Front / Back / DB
   - **Asignado a**: Dev A / Dev B
   - **Descripción**: Qué hay que hacer a nivel técnico.
   - **Scripts / Pasos de Git requeridos**: Ramas a crear y convenciones a seguir por el Dev al subir esto.
   - **Criterios de Aceptación (DoD)**: Qué tiene que funcionar para dar el código por bueno.

Crea el listado completo para abarcar la Épica 1 y Épica 2 (El core del juego inicial: Login, Tablero y Base de datos). Organízalos en forma de lista clara para que podamos pasarlo a GitHub Projects de inmediato."
