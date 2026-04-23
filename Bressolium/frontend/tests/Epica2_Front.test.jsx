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
import InventoryPanel from '../src/features/inventory/InventoryPanel';

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

// ─────────────────────────────────────────────────────────────────────────────
// TESTS PARA: TAREA 18 (Raw_Tareas)
// Título: Material Inventory Side-Panel (SidePanel Izquierdo)
// HUs: 2.4
// ─────────────────────────────────────────────────────────────────────────────

// ── Helpers ───────────────────────────────────────────────────────────────────

/**
 * Genera una lista de materiales simulando el inventario de una partida.
 * Los primeros `activeCount` tendrán quantity > 0; el resto quantity = 0.
 * @param {{ activeCount?: number }} options
 */
function createMockMaterials({ activeCount = 0 } = {}) {
    const catalog = [
        { id: 'mat-01', name: 'roble',           group: 'bosque',  tier: 0 },
        { id: 'mat-02', name: 'pino',            group: 'bosque',  tier: 0 },
        { id: 'mat-03', name: 'carbon-natural',  group: 'bosque',  tier: 0 },
        { id: 'mat-04', name: 'silex',           group: 'cantera', tier: 0 },
        { id: 'mat-05', name: 'granito',         group: 'cantera', tier: 0 },
        { id: 'mat-06', name: 'agua',            group: 'rio',     tier: 0 },
        { id: 'mat-07', name: 'tierras-fertiles',group: 'rio',     tier: 0 },
        { id: 'mat-08', name: 'lino',            group: 'prado',   tier: 0 },
        { id: 'mat-09', name: 'lana',            group: 'prado',   tier: 0 },
        { id: 'mat-10', name: 'hierro',          group: 'mina',    tier: 0 },
        { id: 'mat-11', name: 'cobre',           group: 'mina',    tier: 0 },
    ];
    return catalog.map((mat, idx) => ({
        ...mat,
        quantity: idx < activeCount ? (idx + 1) * 3 : 0,
    }));
}

function mockInventoryState({ materials = [], status = 'SUCCESS' } = {}) {
    reactRedux.useSelector.mockImplementation((selectorFn) =>
        selectorFn({
            auth:      { user: { id: CURRENT_USER_ID, name: 'Michelle' } },
            game:      { currentGame: { id: GAME_ID, name: 'Expedición Test' } },
            board:     { tiles: [], status: 'SUCCESS', error: null },
            inventory: { materials, status, error: null },
        })
    );
}

const renderInventoryPanel = () =>
    render(
        <MemoryRouter>
            <InventoryPanel />
        </MemoryRouter>
    );

// ─────────────────────────────────────────────────────────────────────────────
// HU 2.4 — Visualización de recursos en el panel lateral
// ─────────────────────────────────────────────────────────────────────────────

describe('HU 2.4 — Panel lateral de inventario de materiales', () => {

    it('renderiza un ítem por cada material del catálogo', () => {
        const materials = createMockMaterials();
        mockInventoryState({ materials });
        renderInventoryPanel();

        const items = screen.getAllByTestId('material-item');
        expect(items).toHaveLength(materials.length);
    });

    it('cada material es identificable por su nombre en el DOM', () => {
        const materials = createMockMaterials();
        mockInventoryState({ materials });
        renderInventoryPanel();

        expect(screen.getByTestId('material-item-roble')).toBeInTheDocument();
        expect(screen.getByTestId('material-item-hierro')).toBeInTheDocument();
    });

    it('cada ítem renderiza un icono de imagen del material', () => {
        const materials = createMockMaterials();
        mockInventoryState({ materials });
        renderInventoryPanel();

        materials.forEach(mat => {
            const icon = screen.getByTestId(`material-icon-${mat.name}`);
            expect(icon.tagName).toBe('IMG');
            expect(icon).toHaveAttribute('alt', mat.name);
        });
    });

    it('materiales con quantity > 0 tienen la clase material--active', () => {
        const materials = createMockMaterials({ activeCount: 3 });
        mockInventoryState({ materials });
        renderInventoryPanel();

        const activeItems = materials.filter(m => m.quantity > 0);
        activeItems.forEach(mat => {
            expect(screen.getByTestId(`material-item-${mat.name}`)).toHaveClass('material--active');
        });
    });

    it('materiales con quantity = 0 tienen la clase material--inactive', () => {
        const materials = createMockMaterials({ activeCount: 0 });
        mockInventoryState({ materials });
        renderInventoryPanel();

        materials.forEach(mat => {
            expect(screen.getByTestId(`material-item-${mat.name}`)).toHaveClass('material--inactive');
        });
    });

    it('mezcla correcta: active e inactive según quantity', () => {
        const materials = createMockMaterials({ activeCount: 4 });
        mockInventoryState({ materials });
        renderInventoryPanel();

        const allItems = screen.getAllByTestId('material-item');
        const activeItems   = allItems.filter(el => el.classList.contains('material--active'));
        const inactiveItems = allItems.filter(el => el.classList.contains('material--inactive'));

        expect(activeItems).toHaveLength(4);
        expect(inactiveItems).toHaveLength(materials.length - 4);
    });

    it('los materiales activos muestran un badge con la cantidad correcta', () => {
        const materials = createMockMaterials({ activeCount: 2 });
        mockInventoryState({ materials });
        renderInventoryPanel();

        const activeMaterials = materials.filter(m => m.quantity > 0);
        activeMaterials.forEach(mat => {
            const badge = screen.getByTestId(`material-badge-${mat.name}`);
            expect(badge).toBeInTheDocument();
            expect(badge).toHaveTextContent(String(mat.quantity));
        });
    });

    it('los materiales inactivos no muestran badge de cantidad', () => {
        const materials = createMockMaterials({ activeCount: 0 });
        mockInventoryState({ materials });
        renderInventoryPanel();

        materials.forEach(mat => {
            expect(screen.queryByTestId(`material-badge-${mat.name}`)).not.toBeInTheDocument();
        });
    });

    it('muestra un indicador de carga mientras status es LOADING', () => {
        mockInventoryState({ materials: [], status: 'LOADING' });
        renderInventoryPanel();

        expect(screen.getByTestId('inventory-loading')).toBeInTheDocument();
    });

    it('el panel no muestra ítems cuando el inventario está vacío', () => {
        mockInventoryState({ materials: [] });
        renderInventoryPanel();

        expect(screen.queryAllByTestId('material-item')).toHaveLength(0);
    });

});
