# Diagrama de Secuencia - Ciclo de Jornada (MVP)

Basado en el documento `resumen.md` y adaptado a la arquitectura tecnológica elegida (React + Laravel 12 + MySQL), este diagrama modela el flujo exacto de las operaciones que suceden en un ciclo de juego o "Jornada".

Se ha tenido en cuenta la regla de usar **Long Polling** para la sincronización casi en tiempo real y la tabla **JORNADA_USER** para el seguimiento de acciones individuales.

```mermaid
sequenceDiagram
    autonumber
    actor Jugador as SPA Cliente (React)
    participant API as API Backend (Laravel 12)
    participant Cron as Tarea Programada (Jobs)
    participant DB as Base de Datos (MySQL)

    note over Jugador, DB: 1. FASE DE ACCIONES INDIVIDUALES (Máx. 2)
    
    Jugador->>API: POST /api/casillas/{id}/explorar
    API->>DB: Consulta `JORNADA_USER` (¿acciones_gastadas < 2?)
    DB-->>API: OK (acciones_gastadas: 0)
    API->>API: Valida adyacencia X/Y
    API->>DB: Transaction: Marca casilla explorada + Update `JORNADA_USER` (acciones: 1)
    DB-->>API: Transaction Commit
    API-->>Jugador: 200 OK - Estado de la Casilla Actualizado

    Jugador->>API: POST /api/casillas/{id}/evolucionar
    API->>DB: Lee inventario común, valida recetas y Update `JORNADA_USER` (acciones: 2)
    DB-->>API: Transaction Commit
    API-->>Jugador: 200 OK - Nivel de Casilla aumentado

    note over Jugador, DB: 2. FASE DE VOTACIÓN (Individual)
    
    Jugador->>API: GET /api/progreso/disponibles (Pueblo)
    API->>DB: Cruza Tecnologías pre-requisitos vs Inventario común
    DB-->>API: Lista generada en base a Recetas costeables
    API-->>Jugador: 200 OK - Listado para Votar
    
    Jugador->>API: POST /api/partida/votar (tecnologia_id)
    API->>DB: DB Insert: Nuevo registro en tabla `VOTOS`
    
    alt Todos los jugadores han emitido su voto
        API->>DB: Comprueba si (count(VOTOS) == jugadores_activos)
        API->>API: Despacha Job Inmediato: Cierre de Jornada
        API-->>Jugador: 200 OK - Procesando Cierre...
    else Faltan compañeros por votar
        DB-->>API: Registro persistido de forma segura (RNF-D1)
        API-->>Jugador: 200 OK - Esperando resto del equipo...
    end

    note over API, DB: 3. LÍMITE DE TIEMPO (Cierre Forzado 120min)
    Cron->>API: Schedule Laravel: CheckJornadasCaducadas() (Cada minuto)
    API->>DB: Busca Jornadas donde (now() - fecha_inicio >= 120min)
    DB-->>API: Jornadas Afectadas
    API->>API: Despacha Colas para procesarlas
    
    rect rgba(38, 39, 41, 1)
    note right of API: 4. EJECUCIÓN SÍNCRONA DEL TURNO COMÚN (Iniciada por Votos completados o Límite de Cron)
    API->>DB: INICIA TRANSACTION
    API->>DB: Módulo Votos: SELECT COUNT(*) FROM VOTOS GROUP BY tecnologia_id
    API->>API: Empate detectado? -> Array::random()
    API->>DB: Veredicto: Insert progresión en lista completada
    API->>DB: Cobro Receta: Resta materiales del inventario base
    API->>DB: Generación: Recorre todas las casillas del equipo y suma recursos al almacén
    API->>DB: Salto de Turno: Crea NUEVA fila en `JORNADA` y resetea `JORNADA_USER` (acciones: 0)
    DB-->>API: COMMIT TRANSACTION OK
    end

    note over Jugador, DB: 5. ACTUALIZACIÓN CASI-REAL DE INTERFAZ (Long Polling)
    Jugador->>API: GET /api/partida/sync (Polleo manual cada 5 segundos)
    API->>DB: Consulta tabla `JORNADA` y almacén de recursos
    DB-->>API: Nuevo Inventario engrosado y Jornada incrementada
    API-->>Jugador: 200 OK - React actualiza variables y re-renderiza dashboard
```
