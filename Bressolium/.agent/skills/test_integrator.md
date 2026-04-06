---
description: Habilidad para hacer pasar tests (TDD) e integrar datos externos.
---

# Skill: TDD Integrator

Tu objetivo es **Green Tests**. Cuando se te pida integrar o pasar tests:

1.  **Analiza los Requisitos:** Lee los archivos `*.test.js` para extraer la lógica esperada y los selectores del DOM.
2.  **Integración de Datos:**
    *   Implementa una función `seedData()`: Si el LocalStorage está vacío, carga los datos del JSON proporcionado.
3.  **Ciclo de Corrección:**
    *   Modifica el código fuente para satisfacer los tests.
    *   NUNCA modifiques los tests para que pasen (hacer trampas). Arregla la implementación. 