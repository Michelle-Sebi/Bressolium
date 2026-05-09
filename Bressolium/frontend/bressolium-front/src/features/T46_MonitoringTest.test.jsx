// ==========================================
// TEST FOR: TASK 46 — [Feat] Monitoreo y Métricas
// Validates:
//   - MonitoringPage existe en /src/pages/ y está en AppRoutes
//   - MonitoringPage muestra las métricas del endpoint /api/v1/stats
//   - ErrorBoundary captura errores de componentes React
// HTTP mockeado — sin dependencia de red
// ==========================================

import React from 'react';
import { render, screen, waitFor } from '@testing-library/react';
import { Provider } from 'react-redux';
import { MemoryRouter } from 'react-router-dom';
import { configureStore } from '@reduxjs/toolkit';
import { vi, describe, it, expect, beforeEach, afterEach } from 'vitest';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

import authReducer      from './auth/authSlice';
import gameReducer      from './game/gameSlice';
import boardReducer     from './board/boardSlice';
import inventoryReducer from './inventory/inventorySlice';
import { bressoliumApi } from '../services/bressoliumApi';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const SRC       = path.resolve(__dirname, '..');

// ─── Mock del cliente HTTP centralizado ──────────────────────────────────────

const mockGet = vi.hoisted(() => vi.fn());

vi.mock('../lib/httpClient', () => ({
    default: { get: mockGet },
}));

// ─── Store factory ────────────────────────────────────────────────────────────

function makeStore() {
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
            auth: { status: 'LOGGED_IN', user: { id: 'u1', name: 'Admin' }, error: null },
        },
    });
}

// ─── Respuesta de stats de ejemplo ───────────────────────────────────────────

const MOCK_STATS = {
    success: true,
    data: {
        system: {
            uptime:               3600,
            database:             'ok',
            requests_per_minute:  42,
            errors_per_minute:    1,
            latency_p95:          120.5,
        },
        game: {
            total_games:    5,
            waiting_games:  3,
            active_games:   1,
            finished_games: 1,
            total_players:  10,
            total_rounds:   25,
            players: [
                { name: 'alice', games_count: 3 },
                { name: 'bob',   games_count: 2 },
            ],
        },
    },
    error: null,
};

beforeEach(() => {
    mockGet.mockResolvedValue({ data: MOCK_STATS });
});

afterEach(() => {
    vi.clearAllMocks();
});

// ═══════════════════════════════════════════════════════════════════════════════
// SECCIÓN 1 — Estructura de archivos
// ═══════════════════════════════════════════════════════════════════════════════

describe('T46 — estructura de archivos', () => {
    it('existe src/pages/MonitoringPage.jsx', () => {
        expect(fs.existsSync(path.join(SRC, 'pages', 'MonitoringPage.jsx'))).toBe(true);
    });

    it('existe src/components/ui/ErrorBoundary.jsx', () => {
        expect(fs.existsSync(path.join(SRC, 'components', 'ui', 'ErrorBoundary.jsx'))).toBe(true);
    });

    it('AppRoutes registra la ruta /monitoring', () => {
        const src = fs.readFileSync(path.join(SRC, 'routes', 'AppRoutes.jsx'), 'utf8');
        expect(src).toMatch(/\/monitoring/);
    });

    it('AppRoutes importa MonitoringPage', () => {
        const src = fs.readFileSync(path.join(SRC, 'routes', 'AppRoutes.jsx'), 'utf8');
        expect(src).toMatch(/MonitoringPage/);
    });
});

// ═══════════════════════════════════════════════════════════════════════════════
// SECCIÓN 2 — MonitoringPage: renderizado y datos
// ═══════════════════════════════════════════════════════════════════════════════

describe('MonitoringPage — renderizado', () => {
    async function importPage() {
        const mod = await import(/* @vite-ignore */ path.join(SRC, 'pages', 'MonitoringPage.jsx'));
        return mod.default ?? mod.MonitoringPage;
    }

    it('muestra estado de carga mientras el fetch está pendiente', async () => {
        mockGet.mockReturnValueOnce(new Promise(() => {}));

        const MonitoringPage = await importPage();
        render(
            React.createElement(Provider, { store: makeStore() },
                React.createElement(MemoryRouter, null,
                    React.createElement(MonitoringPage)))
        );

        const loading = screen.queryByTestId('monitoring-loading')
            ?? screen.queryByText(/cargando/i)
            ?? screen.queryByRole('status');
        expect(loading).not.toBeNull();
    });

    it('muestra el uptime cuando la API responde', async () => {
        const MonitoringPage = await importPage();
        render(
            React.createElement(Provider, { store: makeStore() },
                React.createElement(MemoryRouter, null,
                    React.createElement(MonitoringPage)))
        );

        await waitFor(() => {
            expect(screen.getByTestId('metric-uptime')).toBeTruthy();
        });
    });

    it('muestra el estado de la BD cuando la API responde', async () => {
        const MonitoringPage = await importPage();
        render(
            React.createElement(Provider, { store: makeStore() },
                React.createElement(MemoryRouter, null,
                    React.createElement(MonitoringPage)))
        );

        await waitFor(() => {
            expect(screen.getByTestId('metric-database')).toBeTruthy();
            expect(screen.getByTestId('metric-database').textContent).toMatch(/ok/i);
        });
    });

    it('muestra peticiones por minuto cuando la API responde', async () => {
        const MonitoringPage = await importPage();
        render(
            React.createElement(Provider, { store: makeStore() },
                React.createElement(MemoryRouter, null,
                    React.createElement(MonitoringPage)))
        );

        await waitFor(() => {
            expect(screen.getByTestId('metric-requests')).toBeTruthy();
            expect(screen.getByTestId('metric-requests').textContent).toMatch(/42/);
        });
    });

    it('muestra errores por minuto cuando la API responde', async () => {
        const MonitoringPage = await importPage();
        render(
            React.createElement(Provider, { store: makeStore() },
                React.createElement(MemoryRouter, null,
                    React.createElement(MonitoringPage)))
        );

        await waitFor(() => {
            expect(screen.getByTestId('metric-errors')).toBeTruthy();
            expect(screen.getByTestId('metric-errors').textContent).toMatch(/1/);
        });
    });

    it('muestra la latencia p95 cuando la API responde', async () => {
        const MonitoringPage = await importPage();
        render(
            React.createElement(Provider, { store: makeStore() },
                React.createElement(MemoryRouter, null,
                    React.createElement(MonitoringPage)))
        );

        await waitFor(() => {
            expect(screen.getByTestId('metric-latency')).toBeTruthy();
            expect(screen.getByTestId('metric-latency').textContent).toMatch(/120/);
        });
    });

    it('muestra métricas de juego cuando la API responde', async () => {
        const MonitoringPage = await importPage();
        render(
            React.createElement(Provider, { store: makeStore() },
                React.createElement(MemoryRouter, null,
                    React.createElement(MonitoringPage)))
        );

        await waitFor(() => {
            expect(screen.getByTestId('metric-total-games').textContent).toMatch(/5/);
            expect(screen.getByTestId('metric-total-players').textContent).toMatch(/10/);
            expect(screen.getByTestId('metric-total-rounds').textContent).toMatch(/25/);
        });
    });

    it('muestra un mensaje de error cuando el endpoint /stats falla', async () => {
        mockGet.mockRejectedValueOnce(new Error('Network Error'));

        const MonitoringPage = await importPage();
        render(
            React.createElement(Provider, { store: makeStore() },
                React.createElement(MemoryRouter, null,
                    React.createElement(MonitoringPage)))
        );

        await waitFor(() => {
            const errorEl = screen.queryByTestId('monitoring-error')
                ?? screen.queryByRole('alert');
            expect(errorEl).not.toBeNull();
        });
    });

    it('llama al endpoint /stats al montar el componente', async () => {
        const MonitoringPage = await importPage();
        render(
            React.createElement(Provider, { store: makeStore() },
                React.createElement(MemoryRouter, null,
                    React.createElement(MonitoringPage)))
        );

        await waitFor(() => {
            expect(mockGet).toHaveBeenCalledWith(
                expect.stringMatching(/stats/)
            );
        });
    });
});

// ═══════════════════════════════════════════════════════════════════════════════
// SECCIÓN 3 — ErrorBoundary: captura de errores frontend
// ═══════════════════════════════════════════════════════════════════════════════

describe('ErrorBoundary — captura de errores de componentes React', () => {
    async function importBoundary() {
        const mod = await import(/* @vite-ignore */ path.join(SRC, 'components', 'ui', 'ErrorBoundary.jsx'));
        return mod.default ?? mod.ErrorBoundary;
    }

    function BrokenComponent({ shouldThrow }) {
        if (shouldThrow) throw new Error('Error de prueba en componente');
        return React.createElement('div', { 'data-testid': 'child-ok' }, 'Hijo OK');
    }

    it('exporta un componente ErrorBoundary', async () => {
        const ErrorBoundary = await importBoundary();
        expect(typeof ErrorBoundary).toBe('function');
    });

    it('renderiza los hijos normalmente cuando no se produce ningún error', async () => {
        const ErrorBoundary = await importBoundary();

        render(
            React.createElement(ErrorBoundary, null,
                React.createElement(BrokenComponent, { shouldThrow: false }))
        );

        expect(screen.getByTestId('child-ok')).toBeTruthy();
    });

    it('muestra un fallback cuando un hijo lanza un error durante el renderizado', async () => {
        const ErrorBoundary = await importBoundary();
        const consoleError  = vi.spyOn(console, 'error').mockImplementation(() => {});

        render(
            React.createElement(ErrorBoundary, null,
                React.createElement(BrokenComponent, { shouldThrow: true }))
        );

        expect(screen.queryByTestId('child-ok')).toBeNull();

        const fallback = screen.queryByTestId('error-boundary-fallback')
            ?? screen.queryByRole('alert')
            ?? screen.queryByText(/algo salió mal|error|fallo/i);
        expect(fallback).not.toBeNull();

        consoleError.mockRestore();
    });

    it('el fallback contiene un mensaje legible para el usuario', async () => {
        const ErrorBoundary = await importBoundary();
        const consoleError  = vi.spyOn(console, 'error').mockImplementation(() => {});

        render(
            React.createElement(ErrorBoundary, null,
                React.createElement(BrokenComponent, { shouldThrow: true }))
        );

        expect(document.body.textContent.trim().length).toBeGreaterThan(0);

        consoleError.mockRestore();
    });
});
