// ==========================================
// TEST FOR: TASK 43 — Pages + Lazy Loading + Routes Centralizado
// Validates: /src/pages/ structure, /src/routes/ structure,
//            React.lazy usage, Suspense wrapper, ProtectedRoute behavior,
//            App.jsx delegation to centralized routes
// ==========================================

import React, { Suspense } from 'react';
import { render, screen, waitFor } from '@testing-library/react';
import { MemoryRouter, Routes, Route } from 'react-router-dom';
import { Provider } from 'react-redux';
import { configureStore } from '@reduxjs/toolkit';
import { describe, it, expect } from 'vitest';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

import authReducer      from './auth/authSlice';
import gameReducer      from './game/gameSlice';
import boardReducer     from './board/boardSlice';
import inventoryReducer from './inventory/inventorySlice';
import { bressoliumApi } from '../services/bressoliumApi';

// Ruta absoluta a /src (este test vive en src/features/)
const __dirname = path.dirname(fileURLToPath(import.meta.url));
const SRC = path.resolve(__dirname, '..');

// ── Helpers ───────────────────────────────────────────────────────────────────

function makeStore(authStatus = 'IDLE', user = null) {
    return configureStore({
        reducer: {
            auth:      authReducer,
            game:      gameReducer,
            board:     boardReducer,
            inventory: inventoryReducer,
            [bressoliumApi.reducerPath]: bressoliumApi.reducer,
        },
        middleware: (g) => g().concat(bressoliumApi.middleware),
        preloadedState: {
            auth: { status: authStatus, user, error: null },
        },
    });
}

/** Importa un módulo relativo a /src ignorando el análisis estático de Vite. */
async function dynamicImport(relativePath) {
    try {
        // @vite-ignore evita que Vite falle en transformación cuando el fichero no existe todavía
        return await import(/* @vite-ignore */ relativePath);
    } catch {
        throw new Error(`Módulo no encontrado: ${relativePath} — ¿T43 está implementada?`);
    }
}

// ─── 1. /src/pages/ — ficheros existentes ───────────────────────────────────

describe('pages/ — ficheros existentes', () => {
    it('existe src/pages/LoginPage.jsx', () => {
        expect(fs.existsSync(path.join(SRC, 'pages', 'LoginPage.jsx'))).toBe(true);
    });

    it('existe src/pages/RegisterPage.jsx', () => {
        expect(fs.existsSync(path.join(SRC, 'pages', 'RegisterPage.jsx'))).toBe(true);
    });

    it('existe src/pages/DashboardPage.jsx', () => {
        expect(fs.existsSync(path.join(SRC, 'pages', 'DashboardPage.jsx'))).toBe(true);
    });

    it('existe src/pages/GameBoardPage.jsx', () => {
        expect(fs.existsSync(path.join(SRC, 'pages', 'GameBoardPage.jsx'))).toBe(true);
    });
});

// ─── 2. /src/pages/ — exportan componentes ──────────────────────────────────

describe('pages/ — exportan componentes React por defecto', () => {
    it('LoginPage exporta un componente por defecto', async () => {
        const mod = await dynamicImport('../pages/LoginPage');
        expect(typeof mod.default).toBe('function');
    });

    it('RegisterPage exporta un componente por defecto', async () => {
        const mod = await dynamicImport('../pages/RegisterPage');
        expect(typeof mod.default).toBe('function');
    });

    it('DashboardPage exporta un componente por defecto', async () => {
        const mod = await dynamicImport('../pages/DashboardPage');
        expect(typeof mod.default).toBe('function');
    });

    it('GameBoardPage exporta un componente por defecto', async () => {
        const mod = await dynamicImport('../pages/GameBoardPage');
        expect(typeof mod.default).toBe('function');
    });
});

// ─── 3. /src/routes/ — ficheros existentes ──────────────────────────────────

describe('routes/ — ficheros existentes', () => {
    it('existe src/routes/AppRoutes.jsx', () => {
        expect(fs.existsSync(path.join(SRC, 'routes', 'AppRoutes.jsx'))).toBe(true);
    });

    it('existe src/routes/ProtectedRoute.jsx', () => {
        expect(fs.existsSync(path.join(SRC, 'routes', 'ProtectedRoute.jsx'))).toBe(true);
    });
});

// ─── 4. Lazy loading y Suspense ─────────────────────────────────────────────

describe('AppRoutes.jsx — React.lazy y Suspense', () => {
    it('usa React.lazy para carga diferida de páginas', () => {
        const src = fs.readFileSync(path.join(SRC, 'routes', 'AppRoutes.jsx'), 'utf-8');
        expect(src).toMatch(/React\.lazy|lazy\s*\(/);
    });

    it('incluye <Suspense> como wrapper de rutas lazy', () => {
        const src = fs.readFileSync(path.join(SRC, 'routes', 'AppRoutes.jsx'), 'utf-8');
        expect(src).toMatch(/Suspense/);
    });

    it('referencia las 4 páginas del directorio pages/', () => {
        const src = fs.readFileSync(path.join(SRC, 'routes', 'AppRoutes.jsx'), 'utf-8');
        expect(src).toMatch(/LoginPage/);
        expect(src).toMatch(/RegisterPage/);
        expect(src).toMatch(/DashboardPage/);
        expect(src).toMatch(/GameBoardPage/);
    });

    it('AppRoutes exporta un componente por defecto', async () => {
        const mod = await dynamicImport('../routes/AppRoutes');
        expect(typeof mod.default).toBe('function');
    });
});

// ─── 5. ProtectedRoute — estructura interna ─────────────────────────────────

describe('ProtectedRoute.jsx — estructura', () => {
    it('usa <Outlet> para renderizar las rutas hijas autenticadas', () => {
        const src = fs.readFileSync(path.join(SRC, 'routes', 'ProtectedRoute.jsx'), 'utf-8');
        expect(src).toMatch(/Outlet/);
    });

    it('usa <Navigate> para redirigir a /login cuando no hay sesión', () => {
        const src = fs.readFileSync(path.join(SRC, 'routes', 'ProtectedRoute.jsx'), 'utf-8');
        expect(src).toMatch(/Navigate/);
        expect(src).toMatch(/\/login/);
    });
});

// ─── 6. ProtectedRoute — comportamiento ─────────────────────────────────────

describe('ProtectedRoute — control de acceso', () => {
    it('redirige a /login si el usuario NO está autenticado (status IDLE)', async () => {
        const mod = await dynamicImport('../routes/ProtectedRoute');
        const ProtectedRoute = mod.default ?? mod.ProtectedRoute;
        const store = makeStore('IDLE');

        render(
            <Provider store={store}>
                <MemoryRouter initialEntries={['/dashboard']}>
                    <Routes>
                        <Route path="/login" element={<div data-testid="login-page">LOGIN</div>} />
                        <Route element={<ProtectedRoute />}>
                            <Route path="/dashboard" element={<div data-testid="protected-content">DASHBOARD</div>} />
                        </Route>
                    </Routes>
                </MemoryRouter>
            </Provider>
        );

        await waitFor(() => {
            expect(screen.getByTestId('login-page')).toBeInTheDocument();
        });
        expect(screen.queryByTestId('protected-content')).not.toBeInTheDocument();
    });

    it('renderiza el contenido protegido si el usuario SÍ está autenticado (status LOGGED_IN)', async () => {
        const mod = await dynamicImport('../routes/ProtectedRoute');
        const ProtectedRoute = mod.default ?? mod.ProtectedRoute;
        const store = makeStore('LOGGED_IN', { id: 'u1', name: 'Michelle' });

        render(
            <Provider store={store}>
                <MemoryRouter initialEntries={['/dashboard']}>
                    <Routes>
                        <Route path="/login" element={<div data-testid="login-page">LOGIN</div>} />
                        <Route element={<ProtectedRoute />}>
                            <Route path="/dashboard" element={<div data-testid="protected-content">DASHBOARD</div>} />
                        </Route>
                    </Routes>
                </MemoryRouter>
            </Provider>
        );

        await waitFor(() => {
            expect(screen.getByTestId('protected-content')).toBeInTheDocument();
        });
        expect(screen.queryByTestId('login-page')).not.toBeInTheDocument();
    });
});

// ─── 7. App.jsx — delega al módulo de rutas ─────────────────────────────────

describe('App.jsx — delega la configuración al módulo routes/', () => {
    it('importa la config de rutas desde /src/routes/', () => {
        const src = fs.readFileSync(path.join(SRC, 'App.jsx'), 'utf-8');
        expect(src).toMatch(/from ['"].*routes/);
    });

    it('no importa Login directamente desde features/', () => {
        const src = fs.readFileSync(path.join(SRC, 'App.jsx'), 'utf-8');
        expect(src).not.toMatch(/from ['"].*features\/auth\/Login/);
    });

    it('no importa Dashboard directamente desde features/', () => {
        const src = fs.readFileSync(path.join(SRC, 'App.jsx'), 'utf-8');
        expect(src).not.toMatch(/from ['"].*features\/dashboard\/Dashboard/);
    });

    it('no importa GameBoard directamente desde features/', () => {
        const src = fs.readFileSync(path.join(SRC, 'App.jsx'), 'utf-8');
        expect(src).not.toMatch(/from ['"].*features\/game\/GameBoard/);
    });
});
