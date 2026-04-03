# Recomendaciones de Arquitectura y Base de Datos

En base a los requisitos del MVP de Bressolium y las decisiones tomadas respecto a mantener la jugabilidad lo más eficiente posible, se han establecido las siguientes recomendaciones de arquitectura y diseño:

## 1. Estado Temporizado de Jornada y Turnos (Opción B)
Para cumplir con el **RNF-D1** (guardar el estado tras cada acción para evitar pérdida de datos) sin sobrecargar de escrituras la base de datos relacional con múltiples tablas:
- **Modelo Híbrido:** El backend carga la partida en un objeto en memoria para dar respuesta ultrarrápida a las validaciones de los jugadores (ej. ¿le quedan acciones?).
- **Persistencia en JSON:** Se añade una columna `estado_jornada` (tipo JSON o TEXT) en la tabla `PARTIDA`. Cada vez que sucede una acción, el estado en memoria se serializa y guarda en la base de datos de manera asíncrona.
- **Limpieza de fin de turno:** Al cerrarse la jornada a las 2 horas, los datos permanentes de la partida se asientan (se cobran materiales de las recetas, se añaden descubrimientos) y el campo JSON `estado_jornada` se resetea para el turno siguiente.

```json
// Ejemplo de contenido en la columna estado_jornada
{
  "inicio_jornada": "2023-11-20T10:00:00",
  "votos": {"user_1": "Invento_4", "user_2": "Tecnologia_2"},
  "acciones_restantes": {"user_1": 0, "user_2": 2}
}
```

## 2. Simplificación del Árbol de Progreso (MVP)
Dado que los Puntos de Investigación (PI) se han eliminado, la investigación de **Tecnologías** y la creación de **Inventos** cuestan recursos (materiales) siguiendo exactamente el mismo sistema a través de la tabla asociativa de Recetas.
- **Acción a tomar en DB:** Eliminar `coste_pi` de la tabla `TECNOLOGIA`. La tabla asociativa de atributos de relación resolverá los costes.

## 3. Atributos en Entidades Asociativas
En sistemas relacionales, las relaciones Muchos-a-Muchos que contienen atributos propios deben definirse explícitamente en el diseño como Entidades Asociativas o tablas intermedias.
- **Acción a tomar en DB:** La tabla `RECETA` debe incluir explícitamente `material_id` y `cantidad` para documentar la relación como tabla física, además de apuntar físicamente a `invento_id` o `tecnologia_id`.

## 4. Elementos Fuera de Alcance del MVP
Puesto que en esta versión no se implementará competencia persistente multiservidor ni la generación de Eventos Adversos, el diseño de la base de datos enfocado en la instancia del equipo `PARTIDA` resulta adecuado y contenido, sin sobrecargar con entidades inncesarias de escalabilidad para los eventos.
