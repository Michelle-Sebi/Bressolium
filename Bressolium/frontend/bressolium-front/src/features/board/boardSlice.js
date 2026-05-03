import { createSlice } from '@reduxjs/toolkit';

const initialState = {
    pendingTileId: null,
};

const boardSlice = createSlice({
    name: 'board',
    initialState,
    reducers: {
        tileExploreRequested: (state, action) => { state.pendingTileId = action.payload; },
        tileUpgradeRequested: (state, action) => { state.pendingTileId = action.payload; },
    },
});

export const { tileExploreRequested, tileUpgradeRequested } = boardSlice.actions;
export default boardSlice.reducer;
