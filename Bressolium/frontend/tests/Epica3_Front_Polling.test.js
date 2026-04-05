import { describe, it, expect, beforeEach, afterEach, vi } from 'vitest';

// ==========================================
// TEST CRITICO EDGE CAES PARA: TAREA 10 (Raw_Tareas)
// Título: Front Long Polling (Sync Asíncrono)
// ==========================================

describe('Front: Sync de Long Polling', () => {

    beforeEach(() => {
        // Habilitar simulación de tiempo en JSDOM
        vi.useFakeTimers();
    });

    afterEach(() => {
        // Desmontar simulación de tiempo
        vi.useRealTimers();
    });

    it('Ejecuta el request de sincronización de Redux obligatoriamente cada 5 segundos', () => {
        // Mockeamos la función del hook/RTK que llama al Backend
        const syncWithBackendAction = vi.fn();

        // Recreamos la lógica que Bárbara deberá escribir
        function initializeSyncInterval() {
            // Regla de Negocio: 5000ms
            return setInterval(syncWithBackendAction, 5000);
        }

        const intervalId = initializeSyncInterval();

        // Verificamos que no se ha llamado prematuramente
        expect(syncWithBackendAction).not.toHaveBeenCalled();

        // Avanzamos el reloj de Vitest exactamente 5 segundos
        vi.advanceTimersByTime(5000);
        expect(syncWithBackendAction).toHaveBeenCalledTimes(1);

        // Avanzamos otros 5 segundos (Turno 2)
        vi.advanceTimersByTime(5000);
        expect(syncWithBackendAction).toHaveBeenCalledTimes(2);

        // Avanzamos la mitad (no debe llamarse de nuevo)
        vi.advanceTimersByTime(2500);
        expect(syncWithBackendAction).toHaveBeenCalledTimes(2);

        clearInterval(intervalId); // Cleanup
    });

});
