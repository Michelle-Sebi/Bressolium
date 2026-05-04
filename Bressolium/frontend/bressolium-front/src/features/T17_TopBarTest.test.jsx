import React from 'react';
import { render, screen, fireEvent } from '@testing-library/react';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import * as reactRedux from 'react-redux';
import { MemoryRouter } from 'react-router-dom';

// Importamos el componente usando el alias definido en vite.config.js
import TopBar from '../components/layout/TopBar';

// Mockeamos react-redux para aislar el test del store real
vi.mock('react-redux', () => ({
  useSelector: vi.fn(),
  useDispatch: vi.fn(),
}));

describe('TopBar Component (Tarea 17)', () => {
  const dispatchMock = vi.fn();

  beforeEach(() => {
    vi.clearAllMocks();
    reactRedux.useDispatch.mockReturnValue(dispatchMock);
  });

  const renderComponent = () => render(
    <MemoryRouter>
      <TopBar />
    </MemoryRouter>
  );

  it('Muestra correctamente el nombre del usuario y el equipo actual (DoD)', () => {
    reactRedux.useSelector.mockImplementation((selectorFunc) => {
      // Simulamos el estado de Redux
      return selectorFunc({
        auth: { user: { name: 'Michelle' } },
        game: {
          currentGame: { id: 'g1', name: 'Equipo Bressolium' },
          myGames: []
        }
      });
    });

    renderComponent();

    // Debe mostrar el usuario (HU 1.8)
    expect(screen.getByText(/Michelle/i)).toBeInTheDocument();
    // Y el nombre del juego/equipo activo (HU 1.8)
    expect(screen.getByText(/Equipo Bressolium/i)).toBeInTheDocument();
  });

  it('Permite abrir el Quick Switcher para ver otras partidas (HU 1.8)', () => {
    reactRedux.useSelector.mockImplementation((selectorFunc) => {
      return selectorFunc({
        auth: { user: { name: 'Michelle' } },
        game: {
          currentGame: { id: 'g1', name: 'Equipo Bressolium' },
          myGames: [
            { id: 'g1', name: 'Equipo Bressolium' },
            { id: 'g2', name: 'Expedición Alfa' }
          ]
        }
      });
    });

    renderComponent();

    // Buscamos el botón que despliega el selector (o el selector en sí)
    // Asumimos que habrá un botón o un elemento interactuable para cambiar
    const switchToggle = screen.getByRole('button', { name: /cambiar|switch/i });
    fireEvent.click(switchToggle);

    // Debería aparecer la otra partida disponible
    expect(screen.getByText(/Expedición Alfa/i)).toBeInTheDocument();
  });

  it('Ejecuta la acción de logout desde la barra superior (DoD)', () => {
    reactRedux.useSelector.mockImplementation((selectorFunc) => {
      return selectorFunc({
        auth: { user: { name: 'Michelle' } },
        game: { currentGame: null, myGames: [] }
      });
    });

    renderComponent();

    const logoutBtn = screen.getByRole('button', { name: /salir|logout/i });
    fireEvent.click(logoutBtn);

    // Verifica que se haya llamado al dispatch (para ejecutar la acción logout del slice)
    expect(dispatchMock).toHaveBeenCalled();
  });
});
