// ==========================================
// TEST FOR: TASK 12 — [Feat] Action & Decision Control Panel
// Validates: useVoting hook y VotingPanel component
// DoD: dos zonas de votación separadas (Tecnologías/Inventos), contador visual
//      de acciones, timer de fase (número de ronda), botón finalizar turno,
//      items activos vs gris, indicación de qué falta, API via bressoliumApi (T30)
// ==========================================

import React from 'react';
import { render, screen, fireEvent, waitFor, act } from '@testing-library/react';
import { renderHook } from '@testing-library/react';
import { configureStore } from '@reduxjs/toolkit';
import { Provider } from 'react-redux';
import { describe, it, expect, beforeAll } from 'vitest';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

import { bressoliumApi } from '../services/bressoliumApi';
import authReducer      from './auth/authSlice';
import gameReducer      from './game/gameSlice';
import boardReducer     from './board/boardSlice';
import inventoryReducer from './inventory/inventorySlice';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const SRC = path.resolve(__dirname, '..');

// ─── Store factory con RTK Query ─────────────────────────────────────────────

function makeStore() {
    return configureStore({
        reducer: {
            auth:      authReducer,
            game:      gameReducer,
            board:     boardReducer,
            inventory: inventoryReducer,
            [bressoliumApi.reducerPath]: bressoliumApi.reducer,
        },
        middleware: (getDefault) => getDefault().concat(bressoliumApi.middleware),
    });
}

function wrapper(store) {
    return ({ children }) => React.createElement(Provider, { store }, children);
}

// ─── Mock data (formato real del backend — SyncResource / SyncRepository) ────
//
// progress.technologies: [{ id, name, is_active }]
//   is_active: false → aún no investigada → canVote: true
//   is_active: true  → ya investigada    → canVote: false
//
// progress.inventions: [{ id, name, quantity }]
//   quantity: 0 → aún no construida → se puede votar
//   quantity > 0 → ya construida
//
// vote body: { technology_id } o { invention_id }

const mockTechs = [
    { id: 't1', name: 'Escritura',   is_active: false, missing: [] },
    { id: 't2', name: 'Agricultura', is_active: true,  missing: [] },
    { id: 't3', name: 'Metalurgia',  is_active: false, missing: [{ type: 'technology', name: 'Control del Fuego' }] },
];

const mockInvs = [
    { id: 'i1', name: 'Arado',   quantity: 0, missing: [{ type: 'resource', name: 'Silex', required: 5, have: 2 }] },
    { id: 'i2', name: 'Palanca', quantity: 1, missing: [] },
];

const mockSyncData = {
    current_round: { number: 3, start_date: '2026-05-03' },
    user_actions:  { actions_spent: 2 },
    inventory:     [],
    progress:      { technologies: mockTechs, inventions: mockInvs },
};

// ─── Helpers de importación dinámica (patrón T44: path como parámetro) ────────
// Vite analiza string-literals en imports dinámicos; pasando el path como
// argumento de función evitamos que el analyzer lo resuelva en tiempo de build.

async function dynamicImport(relativePath) {
    try {
        return await import(/* @vite-ignore */ relativePath);
    } catch {
        throw new Error(`Módulo no encontrado: ${relativePath} — ¿T12 implementada?`);
    }
}

async function importUseVoting() {
    const mod = await dynamicImport('./game/useVoting.js');
    return mod.useVoting ?? mod.default;
}

async function importVotingPanel() {
    const mod = await dynamicImport('./game/VotingPanel.jsx');
    return mod.default ?? mod.VotingPanel;
}

// ─── 1. Ficheros existentes ───────────────────────────────────────────────────

describe('T12 — ficheros existentes', () => {
    it('existe features/game/useVoting.js', () => {
        expect(
            fs.existsSync(path.join(SRC, 'features', 'game', 'useVoting.js'))
        ).toBe(true);
    });

    it('existe features/game/VotingPanel.jsx', () => {
        expect(
            fs.existsSync(path.join(SRC, 'features', 'game', 'VotingPanel.jsx'))
        ).toBe(true);
    });
});

// ─── 2. useVoting — valores iniciales sin datos en cache ─────────────────────

describe('useVoting — sin datos en cache', () => {
    let useVoting;
    beforeAll(async () => { useVoting = await importUseVoting(); });

    it('devuelve technologies como array vacío', () => {
        const store = makeStore();
        const { result } = renderHook(() => useVoting('game-1'), { wrapper: wrapper(store) });
        expect(result.current.technologies).toEqual([]);
    });

    it('devuelve inventions como array vacío', () => {
        const store = makeStore();
        const { result } = renderHook(() => useVoting('game-1'), { wrapper: wrapper(store) });
        expect(result.current.inventions).toEqual([]);
    });

    it('devuelve userActions como 0', () => {
        const store = makeStore();
        const { result } = renderHook(() => useVoting('game-1'), { wrapper: wrapper(store) });
        expect(result.current.userActions).toBe(0);
    });

    it('devuelve currentRound como null', () => {
        const store = makeStore();
        const { result } = renderHook(() => useVoting('game-1'), { wrapper: wrapper(store) });
        expect(result.current.currentRound).toBeNull();
    });

    it('expone isLoading como booleano', () => {
        const store = makeStore();
        const { result } = renderHook(() => useVoting('game-1'), { wrapper: wrapper(store) });
        expect(typeof result.current.isLoading).toBe('boolean');
    });

    it('expone vote como función', () => {
        const store = makeStore();
        const { result } = renderHook(() => useVoting('game-1'), { wrapper: wrapper(store) });
        expect(typeof result.current.vote).toBe('function');
    });

    it('no lanza con gameId undefined (skip)', () => {
        const store = makeStore();
        expect(() =>
            renderHook(() => useVoting(undefined), { wrapper: wrapper(store) })
        ).not.toThrow();
    });
});

// ─── 3. useVoting — datos derivados del cache RTK Query ──────────────────────

describe('useVoting — con datos del cache getSync', () => {
    let useVoting;
    beforeAll(async () => { useVoting = await importUseVoting(); });

    it('technologies refleja las tecnologías con canVote=!is_active', async () => {
        const store = makeStore();
        const { result } = renderHook(() => useVoting('game-1'), { wrapper: wrapper(store) });

        await act(async () => {
            await store.dispatch(
                bressoliumApi.util.upsertQueryData('getSync', 'game-1', mockSyncData)
            );
        });

        await waitFor(() => {
            expect(result.current.technologies).toHaveLength(3);
        });

        const escritura   = result.current.technologies.find((t) => t.name === 'Escritura');
        const agricultura = result.current.technologies.find((t) => t.name === 'Agricultura');

        expect(escritura.canVote).toBe(true);   // is_active: false → se puede votar
        expect(agricultura.canVote).toBe(false); // is_active: true  → ya investigada
    });

    it('cada technology expone id, name, canVote y missing', async () => {
        const store = makeStore();
        const { result } = renderHook(() => useVoting('game-1'), { wrapper: wrapper(store) });

        await act(async () => {
            await store.dispatch(
                bressoliumApi.util.upsertQueryData('getSync', 'game-1', mockSyncData)
            );
        });

        await waitFor(() => expect(result.current.technologies.length).toBeGreaterThan(0));

        const tech = result.current.technologies[0];
        expect(tech).toHaveProperty('id');
        expect(tech).toHaveProperty('name');
        expect(tech).toHaveProperty('canVote');
        expect(tech).toHaveProperty('missing');
        expect(Array.isArray(tech.missing)).toBe(true);
    });

    it('propaga el array missing del backend en cada technology', async () => {
        const store = makeStore();
        const { result } = renderHook(() => useVoting('game-1'), { wrapper: wrapper(store) });

        await act(async () => {
            await store.dispatch(
                bressoliumApi.util.upsertQueryData('getSync', 'game-1', mockSyncData)
            );
        });

        await waitFor(() => expect(result.current.technologies.length).toBeGreaterThan(0));

        const metalurgia = result.current.technologies.find((t) => t.name === 'Metalurgia');
        expect(metalurgia.missing).toHaveLength(1);
        expect(metalurgia.missing[0].name).toBe('Control del Fuego');

        const escritura = result.current.technologies.find((t) => t.name === 'Escritura');
        expect(escritura.missing).toHaveLength(0);
    });

    it('propaga el array missing del backend en cada invention', async () => {
        const store = makeStore();
        const { result } = renderHook(() => useVoting('game-1'), { wrapper: wrapper(store) });

        await act(async () => {
            await store.dispatch(
                bressoliumApi.util.upsertQueryData('getSync', 'game-1', mockSyncData)
            );
        });

        await waitFor(() => expect(result.current.inventions.length).toBeGreaterThan(0));

        const arado = result.current.inventions.find((i) => i.name === 'Arado');
        expect(arado.missing).toHaveLength(1);
        expect(arado.missing[0].name).toBe('Silex');
    });

    it('inventions refleja los inventos del cache con id y name', async () => {
        const store = makeStore();
        const { result } = renderHook(() => useVoting('game-1'), { wrapper: wrapper(store) });

        await act(async () => {
            await store.dispatch(
                bressoliumApi.util.upsertQueryData('getSync', 'game-1', mockSyncData)
            );
        });

        await waitFor(() => {
            expect(result.current.inventions).toHaveLength(2);
        });

        const arado = result.current.inventions.find((i) => i.name === 'Arado');
        expect(arado).toBeDefined();
        expect(arado).toHaveProperty('id');
        expect(arado).toHaveProperty('canVote');
        expect(Array.isArray(arado.missing)).toBe(true);
    });

    it('userActions lee actions_spent de user_actions', async () => {
        const store = makeStore();
        const { result } = renderHook(() => useVoting('game-1'), { wrapper: wrapper(store) });

        await act(async () => {
            await store.dispatch(
                bressoliumApi.util.upsertQueryData('getSync', 'game-1', mockSyncData)
            );
        });

        await waitFor(() => {
            expect(result.current.userActions).toBe(2);
        });
    });

    it('currentRound lee current_round del cache (incluye number)', async () => {
        const store = makeStore();
        const { result } = renderHook(() => useVoting('game-1'), { wrapper: wrapper(store) });

        await act(async () => {
            await store.dispatch(
                bressoliumApi.util.upsertQueryData('getSync', 'game-1', mockSyncData)
            );
        });

        await waitFor(() => {
            expect(result.current.currentRound).not.toBeNull();
        });

        expect(result.current.currentRound).toMatchObject({ number: 3 });
    });

    it('usa bressoliumApi.useGetSyncQuery (no thunk propio)', async () => {
        // El hook debe consumir la misma clave de cache que los demás hooks (getSync)
        const store = makeStore();
        const { result } = renderHook(() => useVoting('game-1'), { wrapper: wrapper(store) });

        await act(async () => {
            await store.dispatch(
                bressoliumApi.util.upsertQueryData('getSync', 'game-1', mockSyncData)
            );
        });

        // Si el hook usa getSync, technologies estará disponible tras el upsert
        await waitFor(() => {
            expect(result.current.technologies.length).toBeGreaterThan(0);
        });
    });
});

// ─── 4. VotingPanel — renderizado básico ─────────────────────────────────────

describe('VotingPanel — renderizado básico', () => {
    let VotingPanel;
    beforeAll(async () => { VotingPanel = await importVotingPanel(); });

    it('se renderiza sin errores con un gameId', () => {
        const store = makeStore();
        expect(() =>
            render(
                React.createElement(Provider, { store },
                    React.createElement(VotingPanel, { gameId: 'game-1' })
                )
            )
        ).not.toThrow();
    });

    it('muestra un encabezado de sección para Tecnologías', () => {
        const store = makeStore();
        render(
            React.createElement(Provider, { store },
                React.createElement(VotingPanel, { gameId: 'game-1' })
            )
        );
        expect(screen.getByText(/tecnolog/i)).toBeInTheDocument();
    });

    it('muestra un encabezado de sección para Inventos', () => {
        const store = makeStore();
        render(
            React.createElement(Provider, { store },
                React.createElement(VotingPanel, { gameId: 'game-1' })
            )
        );
        expect(screen.getByText(/invento/i)).toBeInTheDocument();
    });

    it('tiene un botón para finalizar turno', () => {
        const store = makeStore();
        render(
            React.createElement(Provider, { store },
                React.createElement(VotingPanel, { gameId: 'game-1' })
            )
        );
        const btn = screen.getByRole('button', { name: /finalizar|turno/i });
        expect(btn).toBeInTheDocument();
    });
});

// ─── 5. VotingPanel — contador de acciones y número de ronda ─────────────────

describe('VotingPanel — contador de acciones y timer de fase', () => {
    let VotingPanel;
    beforeAll(async () => { VotingPanel = await importVotingPanel(); });

    async function renderWithData() {
        const store = makeStore();
        await act(async () => {
            await store.dispatch(
                bressoliumApi.util.upsertQueryData('getSync', 'game-1', mockSyncData)
            );
        });
        render(
            React.createElement(Provider, { store },
                React.createElement(VotingPanel, { gameId: 'game-1' })
            )
        );
    }

    it('muestra el número de acciones gastadas (2)', async () => {
        await renderWithData();
        expect(screen.getByText(/2/)).toBeInTheDocument();
    });

    it('muestra el número de jornada/ronda actual (3)', async () => {
        await renderWithData();
        expect(screen.getByText(/3/)).toBeInTheDocument();
    });
});

// ─── 6. VotingPanel — zona Tecnologías ───────────────────────────────────────

describe('VotingPanel — zona Tecnologías', () => {
    let VotingPanel;
    beforeAll(async () => { VotingPanel = await importVotingPanel(); });

    async function renderWithData() {
        const store = makeStore();
        await act(async () => {
            await store.dispatch(
                bressoliumApi.util.upsertQueryData('getSync', 'game-1', mockSyncData)
            );
        });
        render(
            React.createElement(Provider, { store },
                React.createElement(VotingPanel, { gameId: 'game-1' })
            )
        );
    }

    it('renderiza los nombres de las tecnologías del sync', async () => {
        await renderWithData();
        await waitFor(() => {
            expect(screen.getByText(/escritura/i)).toBeInTheDocument();
            expect(screen.getByText(/metalurgia/i)).toBeInTheDocument();
        });
    });

    it('renderiza también la tecnología ya investigada (is_active=true)', async () => {
        await renderWithData();
        await waitFor(() => {
            expect(screen.getByText(/agricultura/i)).toBeInTheDocument();
        });
    });

    it('las tecnologías votables (canVote=true) tienen un botón clickable', async () => {
        await renderWithData();
        await waitFor(() => {
            expect(screen.getByText(/escritura/i)).toBeInTheDocument();
        });
        const escrituraEl = screen.getByText(/escritura/i);
        const btn = escrituraEl.closest('button') ?? escrituraEl.closest('[role="button"]');
        expect(btn).not.toBeNull();
    });
});

// ─── 7. VotingPanel — zona Inventos ──────────────────────────────────────────

describe('VotingPanel — zona Inventos', () => {
    let VotingPanel;
    beforeAll(async () => { VotingPanel = await importVotingPanel(); });

    async function renderWithData() {
        const store = makeStore();
        await act(async () => {
            await store.dispatch(
                bressoliumApi.util.upsertQueryData('getSync', 'game-1', mockSyncData)
            );
        });
        render(
            React.createElement(Provider, { store },
                React.createElement(VotingPanel, { gameId: 'game-1' })
            )
        );
    }

    it('renderiza los nombres de los inventos del sync', async () => {
        await renderWithData();
        await waitFor(() => {
            expect(screen.getByText(/arado/i)).toBeInTheDocument();
            expect(screen.getByText(/palanca/i)).toBeInTheDocument();
        });
    });

    it('los inventos con quantity=0 tienen un botón clickable', async () => {
        await renderWithData();
        await waitFor(() => {
            expect(screen.getByText(/arado/i)).toBeInTheDocument();
        });
        const aradoEl = screen.getByText(/arado/i);
        const btn = aradoEl.closest('button') ?? aradoEl.closest('[role="button"]');
        expect(btn).not.toBeNull();
    });
});

// ─── 8. VotingPanel — interacción de voto ────────────────────────────────────

describe('VotingPanel — interacción de voto', () => {
    let VotingPanel;
    beforeAll(async () => { VotingPanel = await importVotingPanel(); });

    it('hacer click en una tecnología votable no lanza excepción', async () => {
        const store = makeStore();
        await act(async () => {
            await store.dispatch(
                bressoliumApi.util.upsertQueryData('getSync', 'game-1', mockSyncData)
            );
        });
        render(
            React.createElement(Provider, { store },
                React.createElement(VotingPanel, { gameId: 'game-1' })
            )
        );

        await waitFor(() => expect(screen.getByText(/escritura/i)).toBeInTheDocument());

        const el  = screen.getByText(/escritura/i);
        const btn = el.closest('button') ?? el.closest('[role="button"]');
        expect(() => fireEvent.click(btn)).not.toThrow();
    });

    it('hacer click en un invento votable no lanza excepción', async () => {
        const store = makeStore();
        await act(async () => {
            await store.dispatch(
                bressoliumApi.util.upsertQueryData('getSync', 'game-1', mockSyncData)
            );
        });
        render(
            React.createElement(Provider, { store },
                React.createElement(VotingPanel, { gameId: 'game-1' })
            )
        );

        await waitFor(() => expect(screen.getByText(/arado/i)).toBeInTheDocument());

        const el  = screen.getByText(/arado/i);
        const btn = el.closest('button') ?? el.closest('[role="button"]');
        expect(() => fireEvent.click(btn)).not.toThrow();
    });
});
