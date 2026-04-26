---
description: Revisa los tests de una tarea del backlog contra su Definition of Done y los completa para que cubran realmente todas las funcionalidades.
args:
  - name: tarea
    description: Número de tarea (ej. 27, T27)
---

# Skill: Test Reviewer (DoD Coverage)

Tu objetivo es garantizar que **los tests cubren toda la Definition of Done** de la tarea indicada y prueban realmente las funcionalidades del código, no solo su existencia.

## Pasos

1. **Leer la tarea**: abre `Documentacion/nuevas_tareas.md` y localiza la tarea por número. Extrae:
   - Descripción completa
   - Estimación / área / asignación
   - Dependencias
   - Cada punto explícito del DoD

2. **Localizar los tests**: busca el archivo `backend/BressoliumProject/tests/Feature/T{N}_*.php` o en el directorio `frontend/tests` los tests correspondientes a la tarea:
   - Si no existe, créalo desde cero.
   - Si existe, léelo y haz inventario de qué cubre actualmente.

3. **Comparar tests vs DoD**: para cada punto del DoD, verifica que existe al menos un test que lo prueba. Marca:
   - ✅ Cubierto y prueba la funcionalidad real
   - ⚠️ Cubierto solo superficialmente (ej. comprueba que la clase existe pero no su comportamiento)
   - ❌ No cubierto

4. **Completar los tests**: añade o modifica los tests necesarios para que toda la columna pase a ✅.
   - Usa el estilo del proyecto: Pest PHP, `uses(RefreshDatabase::class)` cuando toque BD, `expect()`, `actingAs()`, `postJson()`/`getJson()`.
   - Cubre casos felices Y casos de error/borde explícitos en el DoD.
   - No elimines tests existentes válidos.
   - Si la tarea es arquitectónica (refactor sin endpoints), usa Reflection / IoC container / `interface_exists` / `class_exists` para verificar la estructura.

## Reglas

- NUNCA modifiques el código fuente en esta skill — solo tests. La implementación se hace después con `test_integrator`.
- NO inventes puntos del DoD que no estén en la descripción de la tarea.
- Si el DoD es ambiguo, pregunta antes de añadir tests.
- Al terminar, reporta una tabla con: punto del DoD → test que lo cubre → estado.
