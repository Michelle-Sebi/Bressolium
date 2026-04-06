# Prompt para Generar las Tareas del Proyecto (Bressolium MVP)

**Copia y pega el siguiente texto en tu asistente o utilízalo como instrucción directiva:**

---

"Actúa como un Tech Lead o Senior Developer experto en metodologías ágiles (Scrum) y diseño de software. Tenemos un proyecto de juego estratégico cooperativo llamado 'Bressolium' planificado para ser desarrollado como Producto Mínimo Viable (MVP). 

El proyecto cuenta con la siguiente base documental que debes tener en cuenta:
1. **Pila Tecnológica**: El backend está en **Laravel 12 (PHP)** implementando una API RESTful, mientras el frontend está en **React-Redux con JavaScript**.
2. **Requisitos Funcionales (Épicas)**: Gestión de Usuarios, Tablero/Exploración, Turnos/Cooperación, y Tecnología/Victoria. *Nota: Se descartan métricas de escalabilidad global, multiservidor y eventos adversos (fuera del scope del MVP).*
3. **Modelo de Base de Datos**: Un esquema relacional normalizado con MySQL. Incluye tablas de Usuarios, Partida, Jornadas (para el control de turnos), Votos, Casillas, Tecnologías, Inventos, Materiales y Recetas. **Prohibido el uso de JSON para estados volátiles; usar tablas relacionadas**. No implementar Cultura Base/Skins.
4. **Acuerdos de Equipo (Working Agreements)**:
   - Repositorio colaborativo en GitHub usando Trunk-Based Development sobre la rama `main`.
   - Nomenclatura de ramas: `feat/<desc>`, `fix/<desc>`, `docs/<desc>`.
   - Nomenclatura de commits convencionales y cierres de issues en la PR (`Closes #<numero>`).
   - Política de Pull Requests: 1 aprobación necesaria para hacer merge.
   - Todo debe cumplir una Definition of Ready (DoR) para empezar y una Definition of Done (DoD) para cerrarse.

**Tu objetivo es:**
Toma estos requisitos y genera el **Product Backlog completo inicial** dividiendo el trabajo en tareas granulares y accionables (historias de usuario / tareas técnicas). El archivo fuente de las historias de usuario es el archivo @historias_mvp.md

**Requisitos estrictos para la generación de las tareas:**
1. **Categorización Técnica**: Cada tarea debe estar claramente etiquetada si pertenece a [FRONTEND], [BACKEND] o [BASE DE DATOS]. Muchas historias de usuario se tendrán que dividir en subtareas técnicas.
2. **Asignación Equilibrada**: El equipo consta de dos desarrolladoras (Dev A (Michelle) y Dev B (Bárbara)). Debes proponer una asignación de tareas equitativa (mismo peso de esfuerzo global).
3. **Desarrollo Full-Stack**: Es imprescindible que tanto el 'Dev A' como el 'Dev B' tengan asignadas tareas de Frontend, Backend y Base de Datos (nadie es puramente Front o puramente Back).
4. **Formato de la Tarea**: Presenta cada tarea con la siguiente estructura inspirada en issues de GitHub:
   - **ID**: Número de la tarea. Asignalo de forma incremental.
   - **Título**: `[Tipo] Descripción breve` (Ej. `[Feat] Endpoint para votación de tecnologías`)
   - **Estimación (Tallas de camisetas)**: XS, S, M, L, XL.
   - **Área**: Front / Back / DB
   - **Asignado a**: Michelle / Bárbara
   - **Bloqueado por**: Ninguna | Tarea X (Indicar de qué tarea anterior depende obligatoriamente para poder empezar a codificarse).
   - **Descripción**: Qué hay que hacer a nivel técnico.
   - **Scripts / Pasos de Git requeridos**: Ramas a crear y convenciones a seguir por el Dev al subir esto.
   - **Criterios de Aceptación (DoD)**: Qué tiene que funcionar para dar el código por bueno.

Crea el listado completo para abarcar todas las historias de usuario definidas en el MVP(El core del juego inicial: Login, Tablero y Base de datos). Organízalos en forma de lista clara para que podamos pasarlo a GitHub Projects de inmediato, guardándolo en un archivo llamado raw_tareas.md.

**REGLA CRÍTICA PARA TI COMO ASISTENTE:** Para generar el código y detalle de cada una de las tareas, **debes detenerte y preguntar a las desarrolladoras siempre que tengas una duda**. Nunca asumas partes de la arquitectura o la lógica del negocio sin confirmarlo previamente."
