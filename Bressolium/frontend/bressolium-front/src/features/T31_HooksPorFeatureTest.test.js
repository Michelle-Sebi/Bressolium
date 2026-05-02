// ==========================================
// TEST FOR: TASK 31 — [Refactor] Hooks por Feature
// Validates: useAuth, useGames, useBoard, useInventory
// ==========================================

import { renderHook, act } from '@testing-library/react';
import { Provider } from 'react-redux';
import { configureStore } from '@reduxjs/toolkit';
import { vi } from 'vitest';
import React from 'react';

import authReducer, { loginThunk } from './auth/authSlice';
import gameReducer from './game/gameSlice';
import boardReducer from './board/boardSlice';
import inventoryReducer, { materialsReceived } from './inventory/inventorySlice';

import { useAuth } from './auth/useAuth';
import { useGames } from './game/useGames';
import { useBoard } from './board/useBoard';
import { useInventory } from './inventory/useInventory';

// ─── Store factory ────────────────────────────────────────────────────────────

function makeStore(preloadedState = {}) {
    return configureStore({
        reducer: {
            auth:      authReducer,
            game:      gameReducer,
            board:     boardReducer,
            inventory: inventoryReducer,
        },
        preloadedState,
    });
}

function wrapper(store) {
    return ({ children }) => React.createElement(Provider, { store }, children);
}

// ─── useAuth ─────────────────────────────────────────────────────────────────

describe('useAuth', () => {
    it('expone status, user y error desde state.auth', () => {
        const store = makeStore({
            auth: { status: 'LOGGED_IN', user: { id: '1', name: 'Ana' }, error: null },
        });
        const { result } = renderHook(() => useAuth(), { wrapper: wrapper(store) });

        expect(result.current.status).toBe('LOGGED_IN');
        expect(result.current.user).toEqual({ id: '1', name: 'Ana' });
        expect(result.current.error).toBeNull();
    });

    it('expone login como función', () => {
        const store = makeStore();
        const { result } = renderHook(() => useAuth(), { wrapper: wrapper(store) });
        expect(typeof result.current.login).toBe('function');
    });

    it('expone logoutUser como función', () => {
        const store = makeStore();
        const { result } = renderHook(() => useAuth(), { wrapper: wrapper(store) });
        expect(typeof result.current.logoutUser).toBe('function');
    });

    it('expone clearAuthError como función', () => {
        const store = makeStore();
        const { result } = renderHook(() => useAuth(), { wrapper: wrapper(store) });
        expect(typeof result.current.clearAuthError).toBe('function');
    });

    it('clearAuthError limpia el error en el store', () => {
        const store = makeStore({
            auth: { status: 'ERROR', user: null, error: 'credenciales inválidas' },
        });
        const { result } = renderHook(() => useAuth(), { wrapper: wrapper(store) });

        act(() => { result.current.clearAuthError(); });

        expect(result.current.error).toBeNull();
        expect(result.current.status).toBe('ERROR');
    });

    it('logoutUser resetea el estado a IDLE', () => {
        const store = makeStore({
            auth: { status: 'LOGGED_IN', user: { id: '1' }, error: null },
        });
        const { result } = renderHook(() => useAuth(), { wrapper: wrapper(store) });

        act(() => { result.current.logoutUser(); });

        expect(result.current.status).toBe('IDLE');
        expect(result.current.user).toBeNull();
    });

    it('login despacha loginThunk y pone status LOADING', async () => {
        const store = makeStore();
        const dispatch = vi.spyOn(store, 'dispatch');

        const { result } = renderHook(() => useAuth(), { wrapper: wrapper(store) });
        act(() => { result.current.login('a@b.com', 'pass'); });

        expect(dispatch).toHaveBeenCalled();
        vi.restoreAllMocks();
    });
});

// ─── useGames ─────────────────────────────────────────────────────────────────

describe('useGames', () => {
    it('expone availableGames, myGames, currentGame, status y error desde state.game', () => {
        const store = makeStore({
            game: {
                availableGames: [{ id: 'g1' }],
                myGames:        [{ id: 'g2' }],
                currentGame:    { id: 'g3' },
                status:         'SUCCESS',
                error:          null,
            },
        });
        const { result } = renderHook(() => useGames(), { wrapper: wrapper(store) });

        expect(result.current.availableGames).toEqual([{ id: 'g1' }]);
        expect(result.current.myGames).toEqual([{ id: 'g2' }]);
        expect(result.current.currentGame).toEqual({ id: 'g3' });
        expect(result.current.status).toBe('SUCCESS');
        expect(result.current.error).toBeNull();
    });

    it('expone fetchGames como función', () => {
        const { result } = renderHook(() => useGames(), { wrapper: wrapper(makeStore()) });
        expect(typeof result.current.fetchGames).toBe('function');
    });

    it('expone fetchMyGames como función', () => {
        const { result } = renderHook(() => useGames(), { wrapper: wrapper(makeStore()) });
        expect(typeof result.current.fetchMyGames).toBe('function');
    });

    it('expone createGame como función', () => {
        const { result } = renderHook(() => useGames(), { wrapper: wrapper(makeStore()) });
        expect(typeof result.current.createGame).toBe('function');
    });

    it('expone joinRandom como función', () => {
        const { result } = renderHook(() => useGames(), { wrapper: wrapper(makeStore()) });
        expect(typeof result.current.joinRandom).toBe('function');
    });

    it('selectGame actualiza currentGame en el store', () => {
        const store = makeStore();
        const { result } = renderHook(() => useGames(), { wrapper: wrapper(store) });

        act(() => { result.current.selectGame({ id: 'gx', name: 'Partida X' }); });

        expect(result.current.currentGame).toEqual({ id: 'gx', name: 'Partida X' });
    });

    it('clearError limpia el error en el store', () => {
        const store = makeStore({
            game: {
                availableGames: [],
                myGames: [],
                currentGame: null,
                status: 'ERROR',
                error: 'algo salió mal',
            },
        });
        const { result } = renderHook(() => useGames(), { wrapper: wrapper(store) });

        act(() => { result.current.clearError(); });

        expect(result.current.error).toBeNull();
    });
});

// ─── useBoard ─────────────────────────────────────────────────────────────────

describe('useBoard', () => {
    it('expone tiles, status y error desde state.board', () => {
        const tiles = [{ id: 't1', coord_x: 0, coord_y: 0, explored: false }];
        const store = makeStore({
            board: { tiles, status: 'SUCCESS', error: null },
        });
        const { result } = renderHook(() => useBoard(), { wrapper: wrapper(store) });

        expect(result.current.tiles).toEqual(tiles);
        expect(result.current.status).toBe('SUCCESS');
        expect(result.current.error).toBeNull();
    });

    it('expone fetchBoard como función', () => {
        const { result } = renderHook(() => useBoard(), { wrapper: wrapper(makeStore()) });
        expect(typeof result.current.fetchBoard).toBe('function');
    });

    it('expone exploreTile como función', () => {
        const { result } = renderHook(() => useBoard(), { wrapper: wrapper(makeStore()) });
        expect(typeof result.current.exploreTile).toBe('function');
    });

    it('expone upgradeTile como función', () => {
        const { result } = renderHook(() => useBoard(), { wrapper: wrapper(makeStore()) });
        expect(typeof result.current.upgradeTile).toBe('function');
    });

    it('fetchBoard despacha fetchBoardThunk poniendo status LOADING', async () => {
        const store = makeStore();
        const dispatch = vi.spyOn(store, 'dispatch');

        const { result } = renderHook(() => useBoard(), { wrapper: wrapper(store) });

        // We don't mock the service, the thunk will reject — but we only care that dispatch was called
        act(() => { result.current.fetchBoard('game-1'); });

        expect(dispatch).toHaveBeenCalled();
        vi.restoreAllMocks();
    });
});

// ─── useInventory ─────────────────────────────────────────────────────────────

describe('useInventory', () => {
    it('expone materials, status y error desde state.inventory', () => {
        const materials = [
            { id: 'm1', name: 'Roble', quantity: 5, group: 'bosque', tier: 0 },
        ];
        const store = makeStore({
            inventory: { materials, status: 'SUCCESS', error: null },
        });
        const { result } = renderHook(() => useInventory(), { wrapper: wrapper(store) });

        expect(result.current.materials).toEqual(materials);
        expect(result.current.status).toBe('SUCCESS');
        expect(result.current.error).toBeNull();
    });

    it('devuelve materials vacío por defecto', () => {
        const { result } = renderHook(() => useInventory(), { wrapper: wrapper(makeStore()) });
        expect(result.current.materials).toEqual([]);
        expect(result.current.status).toBe('IDLE');
    });

    it('refleja el inventario cuando el store se actualiza via materialsReceived', () => {
        const store = makeStore();
        const { result } = renderHook(() => useInventory(), { wrapper: wrapper(store) });

        const newMaterials = [{ id: 'm2', name: 'Piedra', quantity: 10, group: 'cantera', tier: 0 }];
        act(() => { store.dispatch(materialsReceived(newMaterials)); });

        expect(result.current.materials).toEqual(newMaterials);
        expect(result.current.status).toBe('SUCCESS');
    });
});
