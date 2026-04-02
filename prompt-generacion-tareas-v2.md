# Prompt — Generación de tareas técnicas del proyecto

Eres un senior developer con experiencia en gestión de proyectos ágiles. A continuación se te proporciona la documentación completa de un juego estratégico cooperativo llamado **Civilización Cooperativa**. Tu tarea es generar el listado completo de tareas técnicas necesarias para desarrollar el proyecto, clasificadas en tres categorías: **Frontend**, **Backend** y **Base de Datos**.

---

## Contexto del equipo

- 2 developers júnior trabajando en pareja
- Deben repartirse las tareas de forma equilibrada entre los dos en las tres categorías
- Cada tarea debe documentarse con su correspondiente script o código
- Todo el trabajo se gestiona en un repositorio Git con commits atómicos y descriptivos
- Las tareas deben seguir una dependencia lógica (no se puede hacer el front sin el back, ni el back sin la BD)

---

## Requisitos para cada tarea

- Identificador único (ej. BD-01, BACK-01, FRONT-01)
- Título descriptivo
- Descripción técnica breve de qué hay que hacer
- Dependencias con otras tareas
- Developer asignado (DEV-1 o DEV-2), respetando equilibrio entre categorías y entre developers
- Criterios de aceptación técnicos concretos y verificables

---

## Restricciones del proyecto (MVP)

- No incluir eventos adversos ni estilos alternativos de civilización
- El Pueblo no tiene tabla en BD, se gestiona solo en frontend
- No hay puntos de investigación — tecnologías e inventos se pagan con materiales del inventario común
- Explorar y evolucionar casillas es gratuito (sin coste de recursos)
- Los turnos de los jugadores son simultáneos con un máximo de 2 horas por jornada
- Stack tecnológico: Laravel, React-redux, MySQL

---

## Documentaciión del proyecto
consultar @Bressolium/resumen.md, 
@Bressolium/casillas.md, 
@Bressolium/definicion-de-requisitos.md,
 @Bressolium/epicas-e-historias-de-usuario.md, 
 @Bressolium/er_diagram_v3_final.html, 
 @Bressolium/evolucion-tecnologias-e-inventos.md, 
 @Bressolium/guia-de-entregables-del-proyecto.md  


## Instrucciones de output

Genera el listado completo de tareas ordenadas por dependencia lógica, agrupadas por categoría en este orden: **BD → BACK → FRONT**.

Para cada tarea usa este formato:

```
### [ID] Título de la tarea

- **Developer:** DEV-1 / DEV-2
- **Dependencias:** ID de tareas previas o "Ninguna"
- **Descripción:** Qué hay que implementar técnicamente
- **Criterios de aceptación:**
  - [ ] Criterio 1
  - [ ] Criterio 2
  - [ ] Criterio 3
```

Al final incluye una tabla resumen con el recuento de tareas por developer y categoría para verificar el equilibrio:

| Categoría | DEV-1 | DEV-2 | Total |
|---|---|---|---|
| BD | | | |
| BACK | | | |
| FRONT | | | |
| **Total** | | |
