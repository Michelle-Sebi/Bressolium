/**
 * @module boardSlice
 * @description Slice de Redux Toolkit para gestionar el estado del tablero de juego.
 * Almacena las casillas (tiles), el estado de carga y los errores de la petición.
 * @see Tarea 9 - Board Grid Component y Frontend UI
 */

import { createSlice, createAsyncThunk } from '@reduxjs/toolkit';
import * as boardService from '../../services/boardService';

/**
 * Thunk para cargar todas las casillas del tablero de la partida actual.
 * @param {string} gameId
 */
export const fetchBoardThunk = createAsyncThunk(
    'board/fetchBoard',
    async (gameId, { rejectWithValue }) => {
        try {
            return await boardService.getBoard(gameId);
        } catch (error) {
            return rejectWithValue(error.message);
        }
    }
);

/**
 * Thunk para explorar una casilla no explorada propia.
 * @param {string} tileId
 */
export const exploreTileThunk = createAsyncThunk(
    'board/exploreTile',
    async (tileId, { rejectWithValue }) => {
        try {
            return await boardService.exploreTile(tileId);
        } catch (error) {
            return rejectWithValue(error.message);
        }
    }
);

/**
 * Thunk para evolucionar una casilla ya explorada propia.
 * @param {string} tileId
 */
export const upgradeTileThunk = createAsyncThunk(
    'board/upgradeTile',
    async (tileId, { rejectWithValue }) => {
        try {
            return await boardService.upgradeTile(tileId);
        } catch (error) {
            return rejectWithValue(error.message);
        }
    }
);

/**
 * @typedef {Object} TileType
 * @property {string} base_type - Tipo base del terreno (bosque, cantera, rio, prado, mina, pueblo).
 * @property {number} level - Nivel de evolución (1-5).
 * @property {string} name - Nombre del tipo de casilla.
 */

/**
 * @typedef {Object} Tile
 * @property {string} id
 * @property {number} coord_x
 * @property {number} coord_y
 * @property {boolean} explored
 * @property {string} assigned_player - UUID del jugador propietario.
 * @property {TileType} type
 */

/**
 * @typedef {Object} BoardState
 * @property {Tile[]} tiles
 * @property {'IDLE'|'LOADING'|'SUCCESS'|'ERROR'} status
 * @property {string|null} error
 */

/** @type {BoardState} */
const initialState = {
    tiles: [],
    status: 'IDLE',
    error: null,
};

const boardSlice = createSlice({
    name: 'board',
    initialState,
    reducers: {
        /** Solicita explorar una casilla (dispara el thunk de exploración). */
        tileExploreRequested: (state, action) => { state.pendingTileId = action.payload; },
        /** Solicita evolucionar una casilla (dispara el thunk de evolución). */
        tileUpgradeRequested: (state, action) => { state.pendingTileId = action.payload; },
    },
    extraReducers: (builder) => {
        builder
            .addCase(fetchBoardThunk.pending, (state) => {
                state.status = 'LOADING';
                state.error = null;
            })
            .addCase(fetchBoardThunk.fulfilled, (state, action) => {
                state.status = 'SUCCESS';
                state.tiles = action.payload.data ?? action.payload;
            })
            .addCase(fetchBoardThunk.rejected, (state, action) => {
                state.status = 'ERROR';
                state.error = action.payload;
            })
            .addCase(exploreTileThunk.fulfilled, (state, action) => {
                const updatedTile = action.payload.data ?? action.payload;
                const tileIndex = state.tiles.findIndex((tile) => tile.id === updatedTile.id);
                if (tileIndex !== -1) state.tiles[tileIndex] = updatedTile;
            })
            .addCase(upgradeTileThunk.fulfilled, (state, action) => {
                const updatedTile = action.payload.data ?? action.payload;
                const tileIndex = state.tiles.findIndex((tile) => tile.id === updatedTile.id);
                if (tileIndex !== -1) state.tiles[tileIndex] = updatedTile;
            });
    },
});

export const { tileExploreRequested, tileUpgradeRequested } = boardSlice.actions;
export default boardSlice.reducer;
