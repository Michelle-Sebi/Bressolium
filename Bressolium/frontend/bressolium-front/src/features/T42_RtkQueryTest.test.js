// ==========================================
// TEST FOR: TASK 42 — [Feat] RTK Query / Server State Cache
// Validates: createApi structure, store integration, boardSlice purity,
//            useBoard and useInventory migrated to RTK Query
// ==========================================

import { configureStore } from '@reduxjs/toolkit';
import { renderHook, act, waitFor } from '@testing-library/react';
import { Provider } from 'react-redux';
import React from 'react';

// RTK Query API (not yet created — tests will fail until T42 is implemented)
import { bressoliumApi } from '../services/bressoliumApi';

import authReducer      from './auth/authSlice';
import gameReducer      from './game/gameSlice';
import boardReducer     from './board/boardSlice';
import inventoryReducer from './inventory/inventorySlice';
import * as boardSliceExports from './board/boardSlice';

import { useBoard }     from './board/useBoard';
import { useInventory } from './inventory/useInventory';

// ─── Store factory con RTK Query ─────────────────────────────────────────────

function makeStoreWithApi() {
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

// ─── 1. Estructura de bressoliumApi ──────────────────────────────────────────

describe('bressoliumApi – estructura createApi', () => {
    it('exporta bressoliumApi con reducerPath como string', () => {
        expect(typeof bressoliumApi.reducerPath).toBe('string');
    });

    it('expone reducer función para el store', () => {
        expect(typeof bressoliumApi.reducer).toBe('function');
    });

    it('expone middleware función para el store', () => {
        expect(typeof bressoliumApi.middleware).toBe('function');
    });

    it('tiene endpoints definidos como objeto', () => {
        expect(typeof bressoliumApi.endpoints).toBe('object');
    });
});

// ─── 2. Endpoints – existencia ───────────────────────────────────────────────

describe('bressoliumApi – endpoints definidos', () => {
    it('define endpoint getBoard', () => {
        expect(bressoliumApi.endpoints.getBoard).toBeDefined();
    });

    it('define endpoint exploreTile', () => {
        expect(bressoliumApi.endpoints.exploreTile).toBeDefined();
    });

    it('define endpoint upgradeTile', () => {
        expect(bressoliumApi.endpoints.upgradeTile).toBeDefined();
    });

    it('define endpoint getSync', () => {
        expect(bressoliumApi.endpoints.getSync).toBeDefined();
    });

    it('define endpoint vote', () => {
        expect(bressoliumApi.endpoints.vote).toBeDefined();
    });
});

// ─── 3. Hooks generados por RTK Query ────────────────────────────────────────

describe('bressoliumApi – hooks generados', () => {
    it('expone useGetBoardQuery', () => {
        expect(typeof bressoliumApi.useGetBoardQuery).toBe('function');
    });

    it('expone useExploreTileMutation', () => {
        expect(typeof bressoliumApi.useExploreTileMutation).toBe('function');
    });

    it('expone useUpgradeTileMutation', () => {
        expect(typeof bressoliumApi.useUpgradeTileMutation).toBe('function');
    });

    it('expone useGetSyncQuery', () => {
        expect(typeof bressoliumApi.useGetSyncQuery).toBe('function');
    });

    it('expone useVoteMutation', () => {
        expect(typeof bressoliumApi.useVoteMutation).toBe('function');
    });
});

// ─── 4. Store – integración RTK Query ────────────────────────────────────────

describe('store – integración RTK Query', () => {
    it('el estado del store incluye la clave de bressoliumApi.reducerPath', () => {
        const store = makeStoreWithApi();
        expect(store.getState()[bressoliumApi.reducerPath]).toBeDefined();
    });

    it('la clave del reducerPath contiene la estructura de cache RTK Query', () => {
        const store = makeStoreWithApi();
        const rtkState = store.getState()[bressoliumApi.reducerPath];
        expect(rtkState).toHaveProperty('queries');
        expect(rtkState).toHaveProperty('mutations');
    });
});

// ─── 5. boardSlice – pureza de estado UI ─────────────────────────────────────

describe('boardSlice – sin lógica async (estado UI únicamente)', () => {
    it('NO exporta fetchBoardThunk', () => {
        expect(boardSliceExports.fetchBoardThunk).toBeUndefined();
    });

    it('NO exporta exploreTileThunk', () => {
        expect(boardSliceExports.exploreTileThunk).toBeUndefined();
    });

    it('NO exporta upgradeTileThunk', () => {
        expect(boardSliceExports.upgradeTileThunk).toBeUndefined();
    });

    it('el estado inicial de boardSlice no contiene tiles (estado de servidor eliminado)', () => {
        const store = makeStoreWithApi();
        expect(store.getState().board.tiles).toBeUndefined();
    });

    it('el estado inicial de boardSlice no contiene status de carga de API', () => {
        const store = makeStoreWithApi();
        expect(store.getState().board.status).toBeUndefined();
    });
});

// ─── 6. useBoard – integrado con RTK Query ───────────────────────────────────

describe('useBoard – lee tiles del cache RTK Query', () => {
    const mockTiles = [
        { id: 't1', coord_x: 0, coord_y: 0, explored: false },
        { id: 't2', coord_x: 1, coord_y: 0, explored: true  },
    ];

    it('devuelve tiles vacío antes de que haya datos en cache', () => {
        const store = makeStoreWithApi();
        const { result } = renderHook(() => useBoard('game-1'), { wrapper: wrapper(store) });
        expect(result.current.tiles).toEqual([]);
    });

    it('expone tiles cuando el cache RTK Query tiene datos', async () => {
        const store = makeStoreWithApi();
        const { result } = renderHook(() => useBoard('game-1'), { wrapper: wrapper(store) });

        await act(async () => {
            await store.dispatch(
                bressoliumApi.util.upsertQueryData('getBoard', 'game-1', { tiles: mockTiles })
            );
        });

        await waitFor(() => {
            expect(result.current.tiles).toEqual(mockTiles);
        });
    });

    it('expone isLoading como booleano', () => {
        const store = makeStoreWithApi();
        const { result } = renderHook(() => useBoard('game-1'), { wrapper: wrapper(store) });
        expect(typeof result.current.isLoading).toBe('boolean');
    });

    it('expone exploreTile como función (mutation RTK Query)', () => {
        const store = makeStoreWithApi();
        const { result } = renderHook(() => useBoard('game-1'), { wrapper: wrapper(store) });
        expect(typeof result.current.exploreTile).toBe('function');
    });

    it('expone upgradeTile como función (mutation RTK Query)', () => {
        const store = makeStoreWithApi();
        const { result } = renderHook(() => useBoard('game-1'), { wrapper: wrapper(store) });
        expect(typeof result.current.upgradeTile).toBe('function');
    });
});

// ─── 7. useInventory – datos desde getSync ───────────────────────────────────

describe('useInventory – derivado del endpoint getSync', () => {
    const mockInventory = [
        { id: 'm1', name: 'Silex',  quantity: 5,  group: 'cantera', tier: 0 },
        { id: 'm2', name: 'Madera', quantity: 10, group: 'bosque',  tier: 0 },
    ];

    const mockSyncData = {
        currentRound: { id: 'r1', number: 1, start_date: '2026-05-03' },
        userActions:  2,
        inventory:    mockInventory,
        progress:     { technologies: [], inventions: [] },
    };

    it('devuelve materials vacío antes de que haya datos en cache', () => {
        const store = makeStoreWithApi();
        const { result } = renderHook(() => useInventory('game-1'), { wrapper: wrapper(store) });
        expect(result.current.materials).toEqual([]);
    });

    it('expone materials derivados del campo inventory del cache getSync', async () => {
        const store = makeStoreWithApi();
        const { result } = renderHook(() => useInventory('game-1'), { wrapper: wrapper(store) });

        await act(async () => {
            await store.dispatch(
                bressoliumApi.util.upsertQueryData('getSync', 'game-1', mockSyncData)
            );
        });

        await waitFor(() => {
            expect(result.current.materials).toEqual(mockInventory);
        });
    });

    it('expone isLoading como booleano', () => {
        const store = makeStoreWithApi();
        const { result } = renderHook(() => useInventory('game-1'), { wrapper: wrapper(store) });
        expect(typeof result.current.isLoading).toBe('boolean');
    });
});
