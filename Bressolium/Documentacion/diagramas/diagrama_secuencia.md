# Diagrama de Secuencia - Ciclo de Jornada (MVP)

Basado en el documento `resumen.md` y adaptado a la arquitectura tecnológica elegida (React + Laravel 12 + MySQL), este diagrama modela el flujo exacto de las operaciones que suceden en un ciclo de juego o "Jornada".

Se ha tenido en cuenta la regla de usar **Long Polling** para la sincronización casi en tiempo real y el uso intensivo de la columna JSON `estado_jornada` para no sobrecargar de registros temporales la BD.

```mermaid
sequenceDiagram
    autonumber
    actor Jugador as SPA Cliente (React)
    participant API as API Backend (Laravel 12)
    participant Cron as Tarea Programada (Jobs)
    participant DB as Base de Datos (MySQL)

    note over Jugador, DB: 1. FASE DE ACCIONES INDIVIDUALES (Máx. 2)
    
    Jugador->>API: POST /api/casillas/{id}/explorar
    API->>DB: Lee `estado_jornada` (¿Tiene acciones restantes > 0?)
    DB-->>API: OK (acciones: 2)
    API->>API: Valida adyacencia X/Y o modifier activo (ej. Avión)
    API->>DB: Transaction: Marca casilla explorada + Actualiza JSON (acciones: 1)
    DB-->>API: Transaction Commit
    API-->>Jugador: 200 OK - Estado de la Casilla Actualizado

    Jugador->>API: POST /api/casillas/{id}/evolucionar
    API->>DB: Lee inventario común, valida recetas y Update JSON (acciones: 0)
    DB-->>API: Transaction Commit
    API-->>Jugador: 200 OK - Nivel de Casilla amentado

    note over Jugador, DB: 2. FASE DE VOTACIÓN (Individual)
    
    Jugador->>API: GET /api/progreso/disponibles (Pueblo)
    API->>DB: Cruza Tecnologías pre-requisitos vs Inventario común
    DB-->>API: Lista generada en base a Recetas costeables
    API-->>Jugador: 200 OK - Listado para Votar
    
    Jugador->>API: POST /api/partida/votar (progreso_id)
    API->>DB: DB Lock: Update JSON (suma 1 voto y marca jugador_ha_votado = true)
    
    alt Todos los jugadores han emitido su voto
        API->>DB: Comprueba si (votos_emitidos == jugadores_activos)
        API->>API: Despacha Job Inmediato: Cierre de Jornada
        API-->>Jugador: 200 OK - Procesando Cierre...
    else Faltan compañeros por votar
        DB-->>API: Guarda estado de forma segura (RNF-D1)
        API-->>Jugador: 200 OK - Esperando resto del equipo...
    end

    note over API, DB: 3. LÍMITE DE TIEMPO (Cierre Forzado 120min)
    Cron->>API: Schedule Laravel: CheckJornadasCaducadas() (Cada minuto)
    API->>DB: Busca Partidas donde (now() - inicio_jornada >= 120min)
    DB-->>API: Partidas Afectadas
    API->>API: Despacha Colas para procesarlas
    
    rect rgb(230, 240, 255)
    note right of API: 4. EJECUCIÓN SÍNCRONA DEL TURNO COMÚN (Gatillada por Votos completados o Límite de Cron)
    API->>DB: INICIA TRANSACTION
    API->>DB: Módulo Votos: Extrae `estado_jornada.votos`
    API->>API: Empate detectado? -> Array::random()
    API->>DB: Veredicto: Insert progresión en lista completada
    API->>DB: Cobro Receta: Resta materiales del inventario base
    API->>DB: Generación: Recorre todas las casillas del equipo y suma recursos al almacén
    API->>DB: Reset: Vacía el JSON `estado_jornada` devolviendo las 2 acciones al equipo y reiniciando marca temporal
    DB-->>API: COMMIT TRANSACTION OK
    end

    note over Jugador, DB: 5. ACTUALIZACIÓN CASI-REAL DE INTERFAZ (Long Polling)
    Jugador->>API: GET /api/partida/sync (Polleo manual cada 5 segundos)
    API->>DB: Consulta últimos cambios de inventario y estado
    DB-->>API: Nuevo Inventario engrosado y Votos vacíos
    API-->>Jugador: 200 OK - React actualiza variables y re-renderiza dashboard
```
