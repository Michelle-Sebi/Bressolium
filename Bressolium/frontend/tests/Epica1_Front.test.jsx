import React from 'react';
import { describe, it, expect, beforeEach, vi } from 'vitest';
import { render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { Provider } from 'react-redux';
import { configureStore } from '@reduxjs/toolkit';

import Login from '../src/features/auth/Login';
import authService from '../src/services/authService';
import authReducer from '../src/features/auth/authSlice';

// ==========================================
// TEST PARA: TAREA 3 (Raw_Tareas)
// Título: FrontAuth Routing, Redux 
// Módulo: Frontend (Vitest + React Testing Library)
// ==========================================

vi.mock('../src/services/authService', () => ({
  default: {
    login: vi.fn(),
  },
}));

describe('FrontAuth Routing', () => {
    let mockStore;

    beforeEach(() => {
        vi.clearAllMocks();
        localStorage.clear();
        
        mockStore = configureStore({
            reducer: {
                auth: authReducer
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

    describe.skip('Tarea 5: Dashboard Multiequipo Frontend (HU 1.6)', () => {
        it('Renderiza de forma dinámica la lista tras recibir JSON del backend (Pendiente RTL)', async () => {
            const mockPartidasData = [
                { id: 1, nombre: 'Alpha Team', miembros: 3 },
                { id: 2, nombre: 'Beta Team', miembros: 5 }
            ];

            document.body.innerHTML = `
                <div id="dashboard">
                   <ul id="team-list"></ul>
                </div>
            `;
            const list = document.getElementById('team-list');

            function renderTeams(teams) {
                teams.forEach(t => {
                    const li = document.createElement('li');
                    li.textContent = `Equipo: ${t.nombre} - Miembros: ${t.miembros}/5`;
                    list.appendChild(li);
                });
            }

            renderTeams(mockPartidasData);

            expect(list.children.length).toBe(2);
            expect(list.children[0].textContent).toContain('Alpha Team');
            expect(list.children[1].textContent).toContain('5/5');
        });
    });
});

