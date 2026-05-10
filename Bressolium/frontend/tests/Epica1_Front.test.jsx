import React from 'react';
import { describe, it, expect, beforeEach, vi } from 'vitest';
import { render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { Provider } from 'react-redux';
import { configureStore } from '@reduxjs/toolkit';

import Login from '../src/features/auth/Login';
import Dashboard from '../src/features/dashboard/Dashboard';
import authService from '../src/services/authService';
import gameService from '../src/services/gameService';
import authReducer from '../src/features/auth/authSlice';
import gameReducer from '../src/features/game/gameSlice';

// ==========================================
// TEST PARA: TAREA 3 (Raw_Tareas)
// Título: FrontAuth Routing, Redux 
// Módulo: Frontend (Vitest + React Testing Library)
// ==========================================

vi.mock('../src/services/authService', () => ({
  default: {
    login: vi.fn(),
    getToken: vi.fn(() => null),
  },
}));

vi.mock('../src/services/gameService', () => ({
  default: {
    getGames: vi.fn(),
    getMyGames: vi.fn(),
    create: vi.fn(),
    joinRandom: vi.fn(),
    joinByName: vi.fn(),
  },
}));

// Mock para react-router-dom
vi.mock('react-router-dom', async () => {
    const actual = await vi.importActual('react-router-dom');
    return {
        ...actual,
        useNavigate: () => vi.fn(),
    };
});

describe('FrontAuth Routing', () => {
    let mockStore;

    beforeEach(() => {
        vi.clearAllMocks();
        localStorage.clear();
        
        mockStore = configureStore({
            reducer: {
                auth: authReducer,
                game: gameReducer
            }
        });
    });

    describe('Tarea 3: Estructura Auth y Redux (HU 1.1)', () => {
        it('El formulario de login procesa submit y despacha el token contra authService local', async () => {
            const user = userEvent.setup();
            
            const fakeResponse = { token: '1|somerandomstring', user: { id: 1, name: 'Burbur' } };
            authService.login.mockResolvedValueOnce(fakeResponse);

            render(
                <Provider store={mockStore}>
                    <Login />
                </Provider>
            );

            const emailInput = screen.getByLabelText(/email/i);
            const passwordInput = screen.getByLabelText(/password/i);
            const submitButton = screen.getByRole('button', { name: /log in/i });

            await user.type(emailInput, 'test@front.com');
            await user.type(passwordInput, '1234');
            await user.click(submitButton);

            expect(authService.login).toHaveBeenCalledWith('test@front.com', '1234');
            
            await waitFor(() => {
                expect(localStorage.getItem('auth_token')).toBe(fakeResponse.token);
            });
        });
    });

    describe('Tarea 5: Game Lobby & Team Manager UI (HUs 1.2, 1.3, 1.4, 1.5, 1.7)', () => {
        const mockActiveGames = [
            { id: '1', name: 'Alpha Expedition', status: 'RUNNING' }
        ];
        const mockAvailableGames = [
            { id: '2', name: 'Beta Team', members: 3 },
            { id: '3', name: 'Gamma Squad', members: 2 }
        ];

        beforeEach(() => {
            gameService.getMyGames.mockResolvedValue({ data: mockActiveGames });
            gameService.getGames.mockResolvedValue({ data: mockAvailableGames });
        });

        it('Muestra el layout dividido con seccion de Lobby y de Partidas Activas (HU 1.7)', async () => {
            render(
                <Provider store={mockStore}>
                    <Dashboard />
                </Provider>
            );

            expect(await screen.findByText(/unirse a la terraformación/i)).toBeDefined();
            expect(screen.getByText(/mis expediciones activas/i)).toBeDefined();
            expect(screen.getByText(/Alpha Expedition/i)).toBeDefined();
        });

        it('Permite buscar equipos en la lista (HU 1.3)', async () => {
            const user = userEvent.setup();
            render(
                <Provider store={mockStore}>
                    <Dashboard />
                </Provider>
            );

            const searchInput = await screen.findByPlaceholderText(/buscar equipo/i);
            await user.type(searchInput, 'Beta');

            expect(screen.getByText(/Beta Team/i)).toBeDefined();
            // Gamma Squad debería estar filtrado si la lógica es local, 
            // o bien comprobamos que se renderiza el resultado correcto.
        });

        it('Permite abrir el modal de creación y elegir civilización (HU 1.2 & 1.5)', async () => {
            const user = userEvent.setup();
            gameService.create.mockResolvedValue({ data: { id: '4', name: 'New Team' } });

            render(
                <Provider store={mockStore}>
                    <Dashboard />
                </Provider>
            );

            const createButton = await screen.findByRole('button', { name: /crear equipo/i });
            await user.click(createButton);

            const nameInput = screen.getByLabelText(/nombre del equipo/i);
            const submitButton = screen.getByRole('button', { name: /fundar civilización/i });

            await user.type(nameInput, 'Nova');
            await user.click(submitButton);

            expect(gameService.create).toHaveBeenCalledWith('Nova');
        });

        it('Ejecuta la unión aleatoria al pulsar el botón correspondiente (HU 1.4)', async () => {
            const user = userEvent.setup();
            gameService.joinRandom.mockResolvedValue({ data: { id: '5', name: 'Random Team' } });

            render(
                <Provider store={mockStore}>
                    <Dashboard />
                </Provider>
            );

            const randomButton = await screen.findByRole('button', { name: /asignación aleatoria/i });
            await user.click(randomButton);

            expect(gameService.joinRandom).toHaveBeenCalled();
        });

        it('Navega a la partida activa al hacer click (HU 1.6)', async () => {
            const user = userEvent.setup();
            render(
                <Provider store={mockStore}>
                    <Dashboard />
                </Provider>
            );

            const gameItem = await screen.findByText(/Alpha Expedition/i);
            await user.click(gameItem);

            // Aquí podríamos verificar que se llama a un mock de navigate si lo hemos inyectado
        });
    });
});

