/**
 * @module inventorySlice
 * @description Slice de Redux Toolkit para el inventario de materiales de la partida actual.
 * Los materiales se hidratan desde el endpoint de sincronización (T10).
 * @see Tarea 18 - Material Inventory Side-Panel
 */

import { createSlice } from '@reduxjs/toolkit';

/**
 * @typedef {Object} InventoryMaterial
 * @property {string} id
 * @property {string} name
 * @property {number} quantity
 * @property {string} group  - bosque | cantera | rio | prado | mina
 * @property {number} tier   - 0=base, 1=mid, 2=advanced
 */

/**
 * @typedef {Object} InventoryState
 * @property {InventoryMaterial[]} materials
 * @property {'IDLE'|'LOADING'|'SUCCESS'|'ERROR'} status
 * @property {string|null} error
 */

/** @type {InventoryState} */
const initialState = {
    materials: [],
    status:    'IDLE',
    error:     null,
};

const inventorySlice = createSlice({
    name: 'inventory',
    initialState,
    reducers: {
        /**
         * Hidrata el inventario completo desde el sync de partida.
         * @param {InventoryState} state
         * @param {{ payload: InventoryMaterial[] }} action
         */
        materialsReceived: (state, action) => {
            state.materials = action.payload;
            state.status    = 'SUCCESS';
        },
        inventoryLoadingStarted: (state) => {
            state.status = 'LOADING';
            state.error  = null;
        },
        inventoryErrorReceived: (state, action) => {
            state.status = 'ERROR';
            state.error  = action.payload;
        },
    },
});

export const {
    materialsReceived,
    inventoryLoadingStarted,
    inventoryErrorReceived,
} = inventorySlice.actions;

export default inventorySlice.reducer;
