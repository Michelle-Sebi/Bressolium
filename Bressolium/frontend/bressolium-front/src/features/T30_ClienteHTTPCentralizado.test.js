import { describe, it, expect, beforeEach, afterEach, vi } from 'vitest';
import { existsSync } from 'fs';
import { resolve, dirname } from 'path';
import { fileURLToPath } from 'url';

const __dirname = dirname(fileURLToPath(import.meta.url));
const srcDir = resolve(__dirname, '..');

// ==========================================
// TEST PARA: TAREA 30
// Título: [Feat] Cliente HTTP Centralizado con Interceptores
// Área: FRONTEND
// ==========================================

// Carga dinámica ignorando el análisis estático de Vite para módulos que aún no existen
async function loadModule(relPath) {
    const abs = resolve(srcDir, relPath);
    if (!existsSync(abs)) throw new Error(`Módulo no encontrado: src/${relPath}`);
    return import(/* @vite-ignore */ abs);
}

// ─── 1. Estructura: archivos existen en src/lib/ y features/*/api/ ───────────

describe('T30: estructura de archivos', () => {
    it('existe src/lib/httpClient.js', () => {
        expect(existsSync(resolve(srcDir, 'lib/httpClient.js'))).toBe(true);
    });

    it('existe src/features/auth/api/authApi.js', () => {
        expect(existsSync(resolve(srcDir, 'features/auth/api/authApi.js'))).toBe(true);
    });

    it('existe src/features/game/api/gameApi.js', () => {
        expect(existsSync(resolve(srcDir, 'features/game/api/gameApi.js'))).toBe(true);
    });

    it('existe src/features/board/api/boardApi.js', () => {
        expect(existsSync(resolve(srcDir, 'features/board/api/boardApi.js'))).toBe(true);
    });
});

// ─── 2. httpClient: instancia Axios con baseURL y cabeceras por defecto ────────

describe('T30: httpClient exporta una instancia Axios configurada', () => {
    it('el cliente tiene baseURL apuntando a /api/v1', async () => {
        const { default: client } = await loadModule('lib/httpClient.js');
        const baseURL = client.defaults?.baseURL ?? '';
        expect(baseURL).toMatch(/api\/v1/);
    });

    it('el cliente tiene cabecera Accept: application/json por defecto', async () => {
        const { default: client } = await loadModule('lib/httpClient.js');
        const accept =
            client.defaults?.headers?.common?.Accept ??
            client.defaults?.headers?.Accept ??
            '';
        expect(accept).toBe('application/json');
    });

    it('el cliente tiene interceptores de request registrados', async () => {
        const { default: client } = await loadModule('lib/httpClient.js');
        const handlers = client.interceptors.request.handlers ?? [];
        expect(handlers.length).toBeGreaterThan(0);
    });

    it('el cliente tiene interceptores de response registrados', async () => {
        const { default: client } = await loadModule('lib/httpClient.js');
        const handlers = client.interceptors.response.handlers ?? [];
        expect(handlers.length).toBeGreaterThan(0);
    });
});

// ─── 3. Interceptor de request: inyecta token automáticamente ─────────────────

describe('T30: interceptor de request añade Authorization header', () => {
    beforeEach(() => {
        localStorage.setItem('auth_token', 'token-de-prueba');
    });

    afterEach(() => {
        localStorage.clear();
        vi.restoreAllMocks();
    });

    it('añade Authorization: Bearer <token> cuando hay token en localStorage', async () => {
        const { default: client } = await loadModule('lib/httpClient.js');
        const handler = (client.interceptors.request.handlers ?? []).find(h => h !== null);
        expect(handler).toBeDefined();

        const config = { headers: {} };
        const fulfilled = handler.fulfilled ?? handler;
        const result = fulfilled(config);
        const resolved = result instanceof Promise ? await result : result;

        expect(resolved.headers['Authorization']).toBe('Bearer token-de-prueba');
    });

    it('no añade Authorization cuando no hay token en localStorage', async () => {
        localStorage.removeItem('auth_token');
        const { default: client } = await loadModule('lib/httpClient.js');
        const handler = (client.interceptors.request.handlers ?? []).find(h => h !== null);
        expect(handler).toBeDefined();

        const config = { headers: {} };
        const fulfilled = handler.fulfilled ?? handler;
        const result = fulfilled(config);
        const resolved = result instanceof Promise ? await result : result;

        expect(resolved.headers['Authorization']).toBeUndefined();
    });
});

// ─── 4. Interceptor de response: maneja 401 ───────────────────────────────────

describe('T30: interceptor de response maneja 401', () => {
    afterEach(() => {
        localStorage.clear();
        vi.restoreAllMocks();
    });

    it('un error 401 elimina el token del localStorage', async () => {
        localStorage.setItem('auth_token', 'token-caducado');
        const { default: client } = await loadModule('lib/httpClient.js');
        const handler = (client.interceptors.response.handlers ?? []).find(h => h !== null);
        expect(handler).toBeDefined();

        const error401 = { response: { status: 401 } };
        try {
            const rejected = handler.rejected ?? handler;
            await rejected(error401);
        } catch (_) {
            // se espera que relance
        }

        expect(localStorage.getItem('auth_token')).toBeNull();
    });

    it('un error 401 relanza el error (no lo silencia)', async () => {
        const { default: client } = await loadModule('lib/httpClient.js');
        const handler = (client.interceptors.response.handlers ?? []).find(h => h !== null);

        const error401 = { response: { status: 401 } };
        const rejected = handler.rejected ?? handler;

        await expect(rejected(error401)).rejects.toBeDefined();
    });
});

// ─── 5. Interceptor de response: maneja 500 ───────────────────────────────────

describe('T30: interceptor de response maneja 500', () => {
    it('un error 500 relanza el error (no lo silencia)', async () => {
        const { default: client } = await loadModule('lib/httpClient.js');
        const handler = (client.interceptors.response.handlers ?? []).find(h => h !== null);

        const error500 = { response: { status: 500 } };
        const rejected = handler.rejected ?? handler;

        await expect(rejected(error500)).rejects.toBeDefined();
    });
});

// ─── 6. Los módulos de feature/api exportan las funciones esperadas ────────────

describe('T30: módulos de feature/api exportan funciones', () => {
    it('authApi exporta función login', async () => {
        const mod = await loadModule('features/auth/api/authApi.js');
        expect(typeof mod.login).toBe('function');
    });

    it('authApi exporta función register', async () => {
        const mod = await loadModule('features/auth/api/authApi.js');
        expect(typeof mod.register).toBe('function');
    });

    it('gameApi exporta función para crear partida', async () => {
        const mod = await loadModule('features/game/api/gameApi.js');
        const createFn = mod.createGame ?? mod.create;
        expect(typeof createFn).toBe('function');
    });

    it('gameApi exporta función para unirse a partida', async () => {
        const mod = await loadModule('features/game/api/gameApi.js');
        const joinFn = mod.joinRandom ?? mod.joinByName ?? mod.join;
        expect(typeof joinFn).toBe('function');
    });

    it('boardApi exporta función getBoard', async () => {
        const mod = await loadModule('features/board/api/boardApi.js');
        expect(typeof mod.getBoard).toBe('function');
    });

    it('boardApi exporta función exploreTile', async () => {
        const mod = await loadModule('features/board/api/boardApi.js');
        expect(typeof mod.exploreTile).toBe('function');
    });

    it('boardApi exporta función upgradeTile', async () => {
        const mod = await loadModule('features/board/api/boardApi.js');
        expect(typeof mod.upgradeTile).toBe('function');
    });
});
