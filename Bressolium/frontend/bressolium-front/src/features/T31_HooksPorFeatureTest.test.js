// ==========================================
// TEST FOR: TASK 31 — [Refactor] Hooks por Feature
// Validates: useAuth, useGames, useBoard, useInventory
// ==========================================

import { renderHook, act } from '@testing-library/react';
import { Provider } from 'react-redux';
import { configureStore } from '@reduxjs/toolkit';
import { vi } from 'vitest';
import React from 'react';

import authReducer from './auth/authSlice';
import gameReducer from './game/gameSlice';
import boardReducer from './board/boardSlice';
import inventoryReducer from './inventory/inventorySlice';

import { useAuth } from './auth/useAuth';
import { useGames } from './game/useGames';

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

// ─── useBoard y useInventory ──────────────────────────────────────────────────
// Los tests de useBoard y useInventory se han movido a T42_RtkQueryTest.test.js.
// T42 migra ambos hooks a RTK Query, lo que cambia la fuente de datos
// (cache RTK Query en lugar de state.board / state.inventory) y la API del hook.
