import React from 'react';
import { render, screen, fireEvent } from '@testing-library/react';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import * as reactRedux from 'react-redux';
import { MemoryRouter } from 'react-router-dom';

// ==========================================
// TESTS PARA: TAREA 9 (Raw_Tareas)
// Título: Board Grid Component y Frontend UI
// HUs: 2.1, 2.2, 2.6
// ==========================================

import BoardGrid from '../src/features/board/BoardGrid';
import * as boardService from '../src/services/boardService';

vi.mock('react-redux', () => ({
    useSelector: vi.fn(),
    useDispatch: vi.fn(),
}));

vi.mock('../src/services/boardService');

// ── Helpers ──────────────────────────────────────────────────────────────────

const CURRENT_USER_ID = 'user-abc-123';
const OTHER_USER_ID   = 'user-xyz-999';
const GAME_ID         = 'game-001';

/** Genera los 225 tiles de un tablero 15×15 */
function createMockTiles({ exploredCount = 0, otherPlayerTiles = [] } = {}) {
    const tiles = [];
    let idx = 0;
    for (let x = 0; x < 15; x++) {
        for (let y = 0; y < 15; y++) {
            const id = `tile-${x}-${y}`;
            const isOtherPlayer = otherPlayerTiles.includes(id);
            tiles.push({
                id,
                coord_x: x,
                coord_y: y,
                explored: idx < exploredCount,
                assigned_player: isOtherPlayer ? OTHER_USER_ID : CURRENT_USER_ID,
                type: { base_type: 'bosque', level: 1, name: 'Bosque Nv1' },
            });
            idx++;
        }
    }
    return tiles;
}

function mockReduxState({ tiles = [], status = 'SUCCESS' } = {}) {
    reactRedux.useSelector.mockImplementation((selectorFn) =>
        selectorFn({
            auth: { user: { id: CURRENT_USER_ID, name: 'Michelle' } },
            game: { currentGame: { id: GAME_ID, name: 'Expedición Test' } },
            board: { tiles, status, error: null },
        })
    );
}

const renderComponent = () =>
    render(
        <MemoryRouter>
            <BoardGrid />
        </MemoryRouter>
    );

// ── Setup ─────────────────────────────────────────────────────────────────────

const dispatchMock = vi.fn();

beforeEach(() => {
    vi.clearAllMocks();
    reactRedux.useDispatch.mockReturnValue(dispatchMock);
});

// ─────────────────────────────────────────────────────────────────────────────
// HU 2.1 — Cuadrícula 15×15 y carga de datos desde la API
// ─────────────────────────────────────────────────────────────────────────────

describe('HU 2.1 — Renderizado de cuadrícula 15×15', () => {

    it('renderiza exactamente 225 casillas (15×15)', () => {
        mockReduxState({ tiles: createMockTiles() });
        renderComponent();

        const tiles = screen.getAllByTestId('tile');
        expect(tiles).toHaveLength(225);
    });

    it('cada casilla expone sus coordenadas como atributos data-x / data-y', () => {
        mockReduxState({ tiles: createMockTiles() });
        renderComponent();

        const tileAt00 = screen.getByTestId('tile-0-0');
        const tileAt14_14 = screen.getByTestId('tile-14-14');

        expect(tileAt00).toHaveAttribute('data-x', '0');
        expect(tileAt00).toHaveAttribute('data-y', '0');
        expect(tileAt14_14).toHaveAttribute('data-x', '14');
        expect(tileAt14_14).toHaveAttribute('data-y', '14');
    });

    it('al montar el componente despacha la acción de carga del tablero', () => {
        mockReduxState({ tiles: [], status: 'IDLE' });
        renderComponent();

        expect(dispatchMock).toHaveBeenCalled();
    });

    it('muestra un indicador de carga mientras status es LOADING', () => {
        mockReduxState({ tiles: [], status: 'LOADING' });
        renderComponent();

        expect(screen.getByTestId('board-loading')).toBeInTheDocument();
    });

    it('el slice de Redux expone los tiles tal como los devuelve la API', () => {
        const tiles = createMockTiles();
        mockReduxState({ tiles });
        renderComponent();

        // Verificamos que todos los tiles del estado se renderizan
        expect(screen.getAllByTestId('tile')).toHaveLength(tiles.length);
    });

});

// ─────────────────────────────────────────────────────────────────────────────
// HU 2.2 — Niebla de guerra y visibilidad
// ─────────────────────────────────────────────────────────────────────────────

describe('HU 2.2 — Niebla de guerra y visibilidad de casillas', () => {

    it('las casillas no exploradas tienen clase de niebla (tile--fog)', () => {
        mockReduxState({ tiles: createMockTiles({ exploredCount: 0 }) });
        renderComponent();

        const allTiles = screen.getAllByTestId('tile');
        allTiles.forEach(tile => {
            expect(tile).toHaveClass('tile--fog');
        });
    });

    it('las casillas exploradas NO tienen clase de niebla', () => {
        mockReduxState({ tiles: createMockTiles({ exploredCount: 225 }) });
        renderComponent();

        const allTiles = screen.getAllByTestId('tile');
        allTiles.forEach(tile => {
            expect(tile).not.toHaveClass('tile--fog');
        });
    });

    it('las casillas exploradas muestran su base_type en el DOM', () => {
        mockReduxState({ tiles: createMockTiles({ exploredCount: 225 }) });
        renderComponent();

        // Al menos una casilla debe mostrar el tipo de terreno
        const tileAt00 = screen.getByTestId('tile-0-0');
        expect(tileAt00).toHaveAttribute('data-base-type', 'bosque');
    });

    it('las casillas no exploradas no revelan su tipo de terreno', () => {
        mockReduxState({ tiles: createMockTiles({ exploredCount: 0 }) });
        renderComponent();

        const tileAt00 = screen.getByTestId('tile-0-0');
        expect(tileAt00).not.toHaveAttribute('data-base-type');
    });

    it('mezcla correcta: las primeras N casillas exploradas y el resto con niebla', () => {
        mockReduxState({ tiles: createMockTiles({ exploredCount: 5 }) });
        renderComponent();

        const allTiles = screen.getAllByTestId('tile');
        const fogTiles      = allTiles.filter(t => t.classList.contains('tile--fog'));
        const exploredTiles = allTiles.filter(t => !t.classList.contains('tile--fog'));

        expect(exploredTiles).toHaveLength(5);
        expect(fogTiles).toHaveLength(220);
    });

});

// ─────────────────────────────────────────────────────────────────────────────
// HU 2.6 — Conexión con API de acciones (explorar / evolucionar)
// ─────────────────────────────────────────────────────────────────────────────

describe('HU 2.6 — Acciones sobre casillas', () => {

    it('click en casilla propia NO explorada despacha la acción explorar', () => {
        mockReduxState({ tiles: createMockTiles({ exploredCount: 0 }) });
        renderComponent();

        const unexploredOwnTile = screen.getByTestId('tile-0-0');
        fireEvent.click(unexploredOwnTile);

        expect(dispatchMock).toHaveBeenCalled();
        const dispatchedArg = dispatchMock.mock.calls[dispatchMock.mock.calls.length - 1][0];
        // La acción despachada debe referenciar el id de la casilla
        expect(JSON.stringify(dispatchedArg)).toContain('tile-0-0');
    });

    it('click en casilla propia YA explorada despacha la acción evolucionar', () => {
        mockReduxState({ tiles: createMockTiles({ exploredCount: 225 }) });
        renderComponent();

        const exploredOwnTile = screen.getByTestId('tile-0-0');
        fireEvent.click(exploredOwnTile);

        expect(dispatchMock).toHaveBeenCalled();
        const dispatchedArg = dispatchMock.mock.calls[dispatchMock.mock.calls.length - 1][0];
        expect(JSON.stringify(dispatchedArg)).toContain('tile-0-0');
    });

    it('click en casilla de otro jugador NO despacha ninguna acción de juego', () => {
        const tiles = createMockTiles({ otherPlayerTiles: ['tile-0-0'] });
        mockReduxState({ tiles });
        renderComponent();

        // Contamos dispatches previos (p.ej. fetchBoard al montar)
        const dispatchCountBeforeClick = dispatchMock.mock.calls.length;

        const otherPlayerTile = screen.getByTestId('tile-0-0');
        fireEvent.click(otherPlayerTile);

        expect(dispatchMock).toHaveBeenCalledTimes(dispatchCountBeforeClick);
    });

    it('las casillas de otro jugador tienen el atributo data-owner distinto del usuario actual', () => {
        const tiles = createMockTiles({ otherPlayerTiles: ['tile-3-3'] });
        mockReduxState({ tiles });
        renderComponent();

        const otherTile = screen.getByTestId('tile-3-3');
        expect(otherTile).toHaveAttribute('data-owner', OTHER_USER_ID);
    });

    it('las casillas propias tienen el atributo data-owner igual al usuario actual', () => {
        mockReduxState({ tiles: createMockTiles() });
        renderComponent();

        const ownTile = screen.getByTestId('tile-0-0');
        expect(ownTile).toHaveAttribute('data-owner', CURRENT_USER_ID);
    });

});
