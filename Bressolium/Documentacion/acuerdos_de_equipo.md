# Working Agreements

> Documento vivo. Cualquier cambio debe acordarse en equipo y actualizarse aquí.
> Última actualización: 14/03/2026

---

## Comunicación

- Herramienta principal: Meet, Telegram
- Idioma del código y documentación: inglés para código, español para documentación
- Cómo se escalan bloqueos: Comentar en la issue + mencionar en el daily

---

## Flujo de trabajo en GitHub

### Ramas

- Rama principal: `main`
- Convención de nombres:
  - `feat/<descripcion>` — nueva funcionalidad
  - `fix/<descripcion>` — corrección de bug
  - `docs/<descripcion>` — documentación

### Pull Requests

- Aprobaciones necesarias para mergear: 1 (al ser un equipo de dos, solo hace falta la aprobación de la otra persona)
- Estrategia de merge: Merge commit
- Quién puede mergear: cualquier miembro con aprobaciones suficientes
- Las PRs deben referenciar su issue: `Closes #<número>`

---

## GitHub Project (Kanban)

### Columnas y su significado

| Columna | Significado |
|---|---|
| `Backlog` | Tareas definidas y listas para ser priorizadas |
| `Ready` | Priorizadas, con DoR cumplida, listas para empezar |
| `In Progress` | En desarrollo activo |
| `In Review` | PR abierta, esperando revisión |
| `Done` | Criterios de aceptación cumplidos, mergeada y desplegada |


### Issues

- Campos obligatorios al crear una issue: título claro, etiqueta, asignado
- Formato del título: `[Tipo] Descripción breve` — ej. `[Feat] Login con Google`
- Quién mueve las tarjetas: la persona asignada a la tarea

---

## Calidad

### Definition of Ready (DoR)
Una tarea está lista para empezar cuando:
-  Tiene descripción clara y criterios de aceptación
-  Está estimada
-  No tiene dependencias bloqueantes sin resolver

### Definition of Done (DoD)
Una tarea está terminada cuando:
-  El código está en la rama correcta y la PR mergeada
-  Ha sido revisada y aprobada
-  La documentación está actualizada si aplica

---

## Reuniones

Se realizarán las siguientes ceremonias:

| Ceremonia | Duración máx. | Frecuencia |
|---|---|---|
| Daily | 15 min | Todos los miércoles |
| Planning | 1h | Inicio de sprint |
| Review | 1h | Fin de sprint |

- Todas las ceremonias se realizarán en formato videollamada.
- El Planning y Review estarán sujetos a posibles modificaciones por circunstancias externas
- Aviso por ausencia: notificar en el canal antes de la reunión

### Daily
Hora fija: miércoles 21h
Las tres preguntas clásicas: qué hice, qué haré, qué me bloquea

### Sprint Planning
Duración máxima: 1h
Qué se necesita tener preparado antes de entrar: backlog refinado, DoR cumplida

### Sprint Review
Se presenta lo que se ha completado al equipo

### Retrospectiva
Inmediatamente después de la review o en el mismo día

### Refinamiento del backlog
A mitad del sprint para preparar el siguiente
Se hará después de la Daily
---

## Otros acuerdos

<!-- Espacio libre para acuerdos específicos del equipo -->
