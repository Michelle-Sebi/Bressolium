import { defineConfig, devices } from '@playwright/test';

/**
 * Configuración de Playwright para tests E2E de Bressolium (T34).
 *
 * Requisito previo: entorno de desarrollo con Docker Sail levantado.
 *   - Backend API: http://localhost/api/v1
 *   - Frontend Vite dev server: http://localhost:5173
 *
 * Para ejecutar:
 *   npx playwright test
 * Para ver informe:
 *   npx playwright show-report
 */
export default defineConfig({
    testDir: './e2e',

    /** Tiempo máximo por test completo */
    timeout: 60_000,

    /** Reintentos en CI para tests inestables de red */
    retries: process.env.CI ? 1 : 0,

    /** Workers paralelos deshabilitados — la BD es compartida */
    workers: 1,

    reporter: [['list'], ['html', { open: 'never' }]],

    use: {
        baseURL: 'http://localhost:5174',

        /** Captura automática de traza en el primer reintento */
        trace: 'on-first-retry',

        /** Sin headless en local para poder ver la ejecución; headless en CI */
        headless: true,
    },

    projects: [
        {
            name: 'chromium',
            use: { ...devices['Desktop Chrome'] },
        },
    ],
});
