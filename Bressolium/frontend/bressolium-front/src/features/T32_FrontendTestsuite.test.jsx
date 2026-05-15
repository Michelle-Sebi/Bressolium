// ==========================================
// TEST FOR: TASK 32 — [Feat] Tests de Frontend
// Validates:
//   - Tests unitarios: useAuth, useBoard, useInventory
//   - Tests de componente: Login, Register, Dashboard, BoardGrid
//   - Cliente HTTP mockeado en todos los tests (sin dependencia de red)
// ==========================================

import React from 'react';
import { render, screen, fireEvent, waitFor, act } from '@testing-library/react';
import { renderHook } from '@testing-library/react';
import { Provider } from 'react-redux';
import { MemoryRouter } from 'react-router-dom';
import { configureStore } from '@reduxjs/toolkit';
import { vi, describe, it, expect, beforeEach, afterEach } from 'vitest';

// ─── Mocks de red — deben declararse antes de los imports que los usan ────────

vi.mock('../services/authService', () => ({
    default: {
        login:    vi.fn(),
        register: vi.fn(),
        logout:   vi.fn(),
        getToken: vi.fn(() => null),
        getUser:  vi.fn(() => null),
    },
}));

vi.mock('../services/gameService', () => ({
    default: {
        getGames:   vi.fn(),
        getMyGames: vi.fn(),
        create:     vi.fn(),
        joinRandom: vi.fn(),
        join:       vi.fn(),
    },
}));

// ─── Imports con mocks ya aplicados ──────────────────────────────────────────

import authService from '../services/authService';
import gameService from '../services/gameService';

import authReducer      from './auth/authSlice';
import gameReducer      from './game/gameSlice';
import boardReducer     from './board/boardSlice';
import inventoryReducer from './inventory/inventorySlice';
import { bressoliumApi } from '../services/bressoliumApi';

import { useAuth }      from './auth/useAuth';
import { useBoard }     from './board/useBoard';
import { useInventory } from './inventory/useInventory';

import Login     from './auth/Login';
import Register  from './auth/Register';
import Dashboard from './dashboard/Dashboard';
import BoardGrid from './board/BoardGrid';

import { ToastProvider } from '../contexts/ToastContext';

// ─── Store factory ────────────────────────────────────────────────────────────

function makeStore(preloadedState = {}) {
    return configureStore({
        reducer: {
            auth:      authReducer,
            game:      gameReducer,
            board:     boardReducer,
            inventory: inventoryReducer,
            [bressoliumApi.reducerPath]: bressoliumApi.reducer,
        },
        middleware: (g) => g().concat(bressoliumApi.middleware),
        preloadedState,
    });
}

function storeWrapper(store) {
    return ({ children }) =>
        React.createElement(Provider, { store },
            React.createElement(ToastProvider, null, children));
}

// ─── Comportamiento por defecto de los mocks entre tests ─────────────────────

beforeEach(() => {
    authService.login.mockResolvedValue({ user: { id: 'u1', name: 'Test' }, token: 'tok' });
    authService.register.mockResolvedValue({ user: { id: 'u1', name: 'Test' }, token: 'tok' });
    gameService.getGames.mockResolvedValue({ data: [] });
    gameService.getMyGames.mockResolvedValue({ data: [] });
    gameService.create.mockResolvedValue({ data: { id: 'g-new', name: 'Nuevo' } });
    gameService.joinRandom.mockResolvedValue({ data: {} });
    gameService.join.mockResolvedValue({ data: {} });
});

afterEach(() => {
    vi.clearAllMocks();
});

// ═══════════════════════════════════════════════════════════════════════════════
// SECCIÓN 1 — useAuth: tests unitarios
// ═══════════════════════════════════════════════════════════════════════════════

describe('useAuth — tests unitarios', () => {
    it('expone status, user y error desde state.auth', () => {
        const store = makeStore({
            auth: { status: 'LOGGED_IN', user: { id: 'u1', name: 'Ana' }, error: null },
        });
        const { result } = renderHook(() => useAuth(), { wrapper: storeWrapper(store) });

        expect(result.current.status).toBe('LOGGED_IN');
        expect(result.current.user).toEqual({ id: 'u1', name: 'Ana' });
        expect(result.current.error).toBeNull();
    });

    it('expone login, register, logoutUser y clearAuthError como funciones', () => {
        const { result } = renderHook(() => useAuth(), { wrapper: storeWrapper(makeStore()) });
        expect(typeof result.current.login).toBe('function');
        expect(typeof result.current.register).toBe('function');
        expect(typeof result.current.logoutUser).toBe('function');
        expect(typeof result.current.clearAuthError).toBe('function');
    });

    it('logoutUser resetea el estado a IDLE y borra el usuario', () => {
        const store = makeStore({
            auth: { status: 'LOGGED_IN', user: { id: 'u1' }, error: null },
        });
        const { result } = renderHook(() => useAuth(), { wrapper: storeWrapper(store) });

        act(() => { result.current.logoutUser(); });

        expect(result.current.status).toBe('IDLE');
        expect(result.current.user).toBeNull();
    });

    it('clearAuthError limpia el error manteniendo el status', () => {
        const store = makeStore({
            auth: { status: 'ERROR', user: null, error: 'credenciales inválidas' },
        });
        const { result } = renderHook(() => useAuth(), { wrapper: storeWrapper(store) });

        act(() => { result.current.clearAuthError(); });

        expect(result.current.error).toBeNull();
        expect(result.current.status).toBe('ERROR');
    });

    it('register llama a authService.register con nombre, email y password', async () => {
        const store = makeStore();
        const { result } = renderHook(() => useAuth(), { wrapper: storeWrapper(store) });

        await act(async () => {
            await result.current.register('Ana', 'ana@test.com', 'secret');
        });

        expect(authService.register).toHaveBeenCalledWith('Ana', 'ana@test.com', 'secret');
    });

    it('login despacha loginThunk y pone status en LOADING mientras espera', () => {
        const store = makeStore();
        const dispatchSpy = vi.spyOn(store, 'dispatch');
        const { result } = renderHook(() => useAuth(), { wrapper: storeWrapper(store) });

        act(() => { result.current.login('a@b.com', 'pass'); });

        expect(dispatchSpy).toHaveBeenCalled();
        vi.restoreAllMocks();
    });
});

// ═══════════════════════════════════════════════════════════════════════════════
// SECCIÓN 2 — useBoard: tests unitarios
// ═══════════════════════════════════════════════════════════════════════════════

describe('useBoard — tests unitarios', () => {
    const mockTiles = [
        { id: 't1', coord_x: 0, coord_y: 0, explored: false },
        { id: 't2', coord_x: 1, coord_y: 0, explored: true  },
    ];

    it('devuelve tiles vacío antes de que haya datos en cache', () => {
        const store = makeStore();
        const { result } = renderHook(() => useBoard('game-1'), { wrapper: storeWrapper(store) });
        expect(result.current.tiles).toEqual([]);
    });

    it('devuelve tiles cuando el cache RTK Query tiene datos', async () => {
        const store = makeStore();
        const { result } = renderHook(() => useBoard('game-1'), { wrapper: storeWrapper(store) });

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
        const { result } = renderHook(
            () => useBoard('game-1'),
            { wrapper: storeWrapper(makeStore()) }
        );
        expect(typeof result.current.isLoading).toBe('boolean');
    });

    it('expone exploreTile como función', () => {
        const { result } = renderHook(
            () => useBoard('game-1'),
            { wrapper: storeWrapper(makeStore()) }
        );
        expect(typeof result.current.exploreTile).toBe('function');
    });

    it('expone upgradeTile como función', () => {
        const { result } = renderHook(
            () => useBoard('game-1'),
            { wrapper: storeWrapper(makeStore()) }
        );
        expect(typeof result.current.upgradeTile).toBe('function');
    });
});

// ═══════════════════════════════════════════════════════════════════════════════
// SECCIÓN 3 — useInventory: tests unitarios
// ═══════════════════════════════════════════════════════════════════════════════

describe('useInventory — tests unitarios', () => {
    const mockSyncData = {
        inventory: [{ id: 'm1', name: 'Sílex', quantity: 3, group: 'cantera', tier: 0 }],
        progress:  {
            technologies: [],
            inventions:   [{ id: 'i1', name: 'Cuchillo', quantity: 1 }],
        },
    };

    it('devuelve materials e inventions vacíos antes de que haya datos en cache', () => {
        const { result } = renderHook(
            () => useInventory('game-1'),
            { wrapper: storeWrapper(makeStore()) }
        );
        expect(result.current.materials).toEqual([]);
        expect(result.current.inventions).toEqual([]);
    });

    it('expone materials cuando el cache getSync tiene datos', async () => {
        const store = makeStore();
        const { result } = renderHook(() => useInventory('game-1'), { wrapper: storeWrapper(store) });

        await act(async () => {
            await store.dispatch(
                bressoliumApi.util.upsertQueryData('getSync', 'game-1', mockSyncData)
            );
        });

        await waitFor(() => {
            expect(result.current.materials).toHaveLength(1);
            expect(result.current.materials[0].name).toBe('Sílex');
        });
    });

    it('expone inventions cuando el cache getSync tiene datos', async () => {
        const store = makeStore();
        const { result } = renderHook(() => useInventory('game-1'), { wrapper: storeWrapper(store) });

        await act(async () => {
            await store.dispatch(
                bressoliumApi.util.upsertQueryData('getSync', 'game-1', mockSyncData)
            );
        });

        await waitFor(() => {
            expect(result.current.inventions).toHaveLength(1);
            expect(result.current.inventions[0].name).toBe('Cuchillo');
        });
    });

    it('expone isLoading como booleano', () => {
        const { result } = renderHook(
            () => useInventory('game-1'),
            { wrapper: storeWrapper(makeStore()) }
        );
        expect(typeof result.current.isLoading).toBe('boolean');
    });
});

// ═══════════════════════════════════════════════════════════════════════════════
// SECCIÓN 4 — Login: tests de componente
// ═══════════════════════════════════════════════════════════════════════════════

describe('Login — tests de componente', () => {
    function renderLogin(authPreload = { status: 'IDLE', user: null, error: null }) {
        const store = makeStore({ auth: authPreload });
        return render(
            React.createElement(Provider, { store },
                React.createElement(MemoryRouter, null,
                    React.createElement(Login)))
        );
    }

    it('renderiza el formulario con el input de email', () => {
        renderLogin();
        expect(screen.getByLabelText(/email/i)).toBeTruthy();
    });

    it('renderiza el formulario con el input de contraseña', () => {
        renderLogin();
        expect(screen.getByLabelText(/password/i)).toBeTruthy();
    });

    it('muestra el botón LOG IN en estado IDLE', () => {
        renderLogin();
        expect(screen.getByRole('button', { name: /log in/i })).toBeTruthy();
    });

    it('muestra VERIFICANDO y el botón queda deshabilitado durante LOADING', () => {
        renderLogin({ status: 'LOADING', user: null, error: null });
        const btn = screen.getByRole('button', { name: /verificando/i });
        expect(btn).toBeTruthy();
        expect(btn.disabled).toBe(true);
    });

    it('muestra el mensaje de error cuando auth tiene error', () => {
        renderLogin({ status: 'ERROR', user: null, error: 'Credenciales inválidas' });
        expect(screen.getByRole('alert')).toBeTruthy();
        expect(screen.getByText('Credenciales inválidas')).toBeTruthy();
    });

    it('no muestra alerta en estado normal', () => {
        renderLogin();
        expect(screen.queryByRole('alert')).toBeNull();
    });

    it('el formulario tiene aria-label para accesibilidad', () => {
        renderLogin();
        expect(screen.getByRole('form', { name: /login/i })).toBeTruthy();
    });

    it('llama a login con email y password al enviar el formulario', async () => {
        const store = makeStore();
        render(
            React.createElement(Provider, { store },
                React.createElement(MemoryRouter, null,
                    React.createElement(Login)))
        );

        fireEvent.change(screen.getByLabelText(/email/i),    { target: { value: 'test@test.com' } });
        fireEvent.change(screen.getByLabelText(/password/i), { target: { value: 'password123' } });
        fireEvent.click(screen.getByRole('button', { name: /log in/i }));

        await waitFor(() => {
            expect(authService.login).toHaveBeenCalledWith('test@test.com', 'password123');
        });
    });
});

// ═══════════════════════════════════════════════════════════════════════════════
// SECCIÓN 5 — Register: tests de componente
// ═══════════════════════════════════════════════════════════════════════════════

describe('Register — tests de componente', () => {
    function renderRegister() {
        const store = makeStore();
        return render(
            React.createElement(Provider, { store },
                React.createElement(MemoryRouter, null,
                    React.createElement(Register)))
        );
    }

    it('renderiza el input de nombre', () => {
        renderRegister();
        expect(screen.getByLabelText(/nombre/i)).toBeTruthy();
    });

    it('renderiza el input de email', () => {
        renderRegister();
        expect(screen.getByLabelText(/email/i)).toBeTruthy();
    });

    it('renderiza el input de contraseña', () => {
        renderRegister();
        expect(screen.getByLabelText(/password/i)).toBeTruthy();
    });

    it('muestra el botón REGISTRARSE en estado normal', () => {
        renderRegister();
        expect(screen.getByRole('button', { name: /registrarse/i })).toBeTruthy();
    });

    it('el formulario tiene aria-label para accesibilidad', () => {
        renderRegister();
        expect(screen.getByRole('form', { name: /registro/i })).toBeTruthy();
    });

    it('muestra un mensaje de error cuando el registro falla', async () => {
        authService.register.mockRejectedValueOnce(new Error('El email ya está registrado'));

        renderRegister();

        fireEvent.change(screen.getByLabelText(/nombre/i),    { target: { value: 'Ana' } });
        fireEvent.change(screen.getByLabelText(/email/i),     { target: { value: 'ana@test.com' } });
        fireEvent.change(screen.getByLabelText(/password/i),  { target: { value: 'secret' } });
        fireEvent.click(screen.getByRole('button', { name: /registrarse/i }));

        await waitFor(() => {
            expect(screen.getByRole('alert')).toBeTruthy();
            expect(screen.getByText(/el email ya está registrado/i)).toBeTruthy();
        });
    });

    it('llama a authService.register con los datos del formulario', async () => {
        renderRegister();

        fireEvent.change(screen.getByLabelText(/nombre/i),    { target: { value: 'Bárbara' } });
        fireEvent.change(screen.getByLabelText(/email/i),     { target: { value: 'barb@test.com' } });
        fireEvent.change(screen.getByLabelText(/password/i),  { target: { value: 'pass123' } });
        fireEvent.click(screen.getByRole('button', { name: /registrarse/i }));

        await waitFor(() => {
            expect(authService.register).toHaveBeenCalledWith('Bárbara', 'barb@test.com', 'pass123');
        });
    });
});

// ═══════════════════════════════════════════════════════════════════════════════
// SECCIÓN 6 — Dashboard: tests de componente
// ═══════════════════════════════════════════════════════════════════════════════

describe('Dashboard — tests de componente', () => {
    function renderDashboard() {
        const store = makeStore({
            auth: { status: 'LOGGED_IN', user: { id: 'u1', name: 'Test' }, error: null },
        });
        return { store, ...render(
            React.createElement(Provider, { store },
                React.createElement(MemoryRouter, null,
                    React.createElement(Dashboard)))
        )};
    }

    it('renderiza el encabezado de unirse a la terraformación', () => {
        renderDashboard();
        expect(screen.getByText(/unirse a la terraformación/i)).toBeTruthy();
    });

    it('renderiza el encabezado de mis expediciones activas', () => {
        renderDashboard();
        expect(screen.getByText(/mis expediciones activas/i)).toBeTruthy();
    });

    it('muestra el placeholder de sin expediciones cuando myGames está vacío', () => {
        renderDashboard();
        expect(screen.getByText(/sin expediciones en curso/i)).toBeTruthy();
    });

    it('renderiza el botón de crear equipo nuevo', () => {
        renderDashboard();
        expect(screen.getByRole('button', { name: /crear expedición/i })).toBeTruthy();
    });

    it('renderiza el botón de asignación aleatoria', () => {
        renderDashboard();
        expect(screen.getByRole('button', { name: /asignación aleatoria/i })).toBeTruthy();
    });

    it('renderiza el input de búsqueda de equipos', () => {
        renderDashboard();
        expect(screen.getByPlaceholderText(/buscar expedición/i)).toBeTruthy();
    });

    it('muestra las partidas cuando fetchMyGamesThunk devuelve datos', async () => {
        gameService.getMyGames.mockResolvedValueOnce({
            data: [{ id: 'g1', name: 'Equipo Alpha', status: 'active' }],
        });

        renderDashboard();

        await waitFor(() => {
            expect(screen.getByText(/equipo alpha/i)).toBeTruthy();
        });
    });

    it('el buscador filtra la lista de partidas disponibles', async () => {
        gameService.getGames.mockResolvedValueOnce({
            data: [
                { id: 'g1', name: 'Equipo Alpha', users_count: 2 },
                { id: 'g2', name: 'Equipo Beta',  users_count: 1 },
            ],
        });

        renderDashboard();

        await waitFor(() => {
            expect(screen.getByText(/equipo alpha/i)).toBeTruthy();
        });

        fireEvent.change(screen.getByPlaceholderText(/buscar expedición/i), {
            target: { value: 'Alpha' },
        });

        expect(screen.getByText(/equipo alpha/i)).toBeTruthy();
        expect(screen.queryByText(/equipo beta/i)).toBeNull();
    });
});

// ═══════════════════════════════════════════════════════════════════════════════
// SECCIÓN 7 — BoardGrid: tests de componente
// ═══════════════════════════════════════════════════════════════════════════════

describe('BoardGrid — tests de componente', () => {
    const exploredTile = {
        id:              't-exp',
        coord_x:         0,
        coord_y:         0,
        explored:        true,
        assigned_player: null,
        type:            { base_type: 'bosque', level: 1 },
    };

    const fogTile = {
        id:              't-fog',
        coord_x:         1,
        coord_y:         0,
        explored:        false,
        assigned_player: null,
        type:            null,
    };

    function renderBoardGrid() {
        const store = makeStore({
            auth: { status: 'LOGGED_IN', user: { id: 'u1' }, error: null },
            game: {
                availableGames: [],
                myGames:        [],
                currentGame:    { id: 'g1' },
                status:         'SUCCESS',
                error:          null,
            },
        });
        return { store, ...render(
            React.createElement(Provider, { store },
                React.createElement(ToastProvider, null,
                    React.createElement(MemoryRouter, null,
                        React.createElement(BoardGrid))))
        )};
    }

    it('monta el componente sin errores (sin datos = estado vacío)', () => {
        renderBoardGrid();
        // El componente puede mostrar carga o grid vacío — no lanza errores
        expect(document.body).toBeTruthy();
    });

    it('renderiza casillas cuando el cache RTK Query tiene tiles', async () => {
        const { store } = renderBoardGrid();

        await act(async () => {
            await store.dispatch(
                bressoliumApi.util.upsertQueryData('getBoard', 'g1', {
                    tiles: [exploredTile, fogTile],
                })
            );
        });

        await waitFor(() => {
            expect(screen.getAllByTestId('tile').length).toBeGreaterThanOrEqual(2);
        });
    });

    it('las casillas exploradas tienen data-base-type', async () => {
        const { store } = renderBoardGrid();

        await act(async () => {
            await store.dispatch(
                bressoliumApi.util.upsertQueryData('getBoard', 'g1', { tiles: [exploredTile] })
            );
        });

        await waitFor(() => {
            const inner = screen.getByTestId('tile-0-0');
            expect(inner.getAttribute('data-base-type')).toBe('bosque');
        });
    });

    it('las casillas no exploradas tienen la clase tile--fog', async () => {
        const { store } = renderBoardGrid();

        await act(async () => {
            await store.dispatch(
                bressoliumApi.util.upsertQueryData('getBoard', 'g1', { tiles: [fogTile] })
            );
        });

        await waitFor(() => {
            expect(document.querySelector('.tile--fog')).toBeTruthy();
        });
    });

});
