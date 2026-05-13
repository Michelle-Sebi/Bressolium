/**
 * @module gameSlice
 * @description Slice de Redux Toolkit para gestionar el estado de las partidas y equipos.
 */

import { createSlice, createAsyncThunk } from '@reduxjs/toolkit';
import gameService from '../../services/gameService';

/**
 * Thunk para obtener las partidas disponibles para unirse.
 */
export const fetchGamesThunk = createAsyncThunk(
  'game/fetchAvailable',
  async (_, { rejectWithValue }) => {
    try {
      const response = await gameService.getGames();
      return response.data;
    } catch (error) {
      return rejectWithValue(error.message);
    }
  }
);

/**
 * Thunk para obtener las partidas en las que ya participa el usuario.
 */
export const fetchMyGamesThunk = createAsyncThunk(
  'game/fetchMyGames',
  async (_, { rejectWithValue }) => {
    try {
      const response = await gameService.getMyGames();
      return response.data;
    } catch (error) {
      return rejectWithValue(error.message);
    }
  }
);

/**
 * Thunk para crear una nueva partida.
 */
export const createGameThunk = createAsyncThunk(
  'game/create',
  async ({ name }, { rejectWithValue }) => {
    try {
      const response = await gameService.create(name);
      return response.data;
    } catch (error) {
      return rejectWithValue(error.message);
    }
  }
);

/**
 * Thunk para unirse a una partida por nombre de equipo.
 */
export const joinByNameThunk = createAsyncThunk(
  'game/joinByName',
  async ({ teamName }, { rejectWithValue }) => {
    try {
      const response = await gameService.joinByName(teamName);
      return response.data;
    } catch (error) {
      return rejectWithValue(error.message);
    }
  }
);

/**
 * Thunk para unirse de forma aleatoria.
 */
export const joinRandomThunk = createAsyncThunk(
  'game/joinRandom',
  async (_, { rejectWithValue }) => {
    try {
      const response = await gameService.joinRandom();
      return response.data;
    } catch (error) {
      return rejectWithValue(error.message);
    }
  }
);

function loadCurrentGame() {
  try {
    const raw = localStorage.getItem('current_game');
    return raw ? JSON.parse(raw) : null;
  } catch {
    return null;
  }
}

const initialState = {
  availableGames: [],
  myGames: [],
  currentGame: loadCurrentGame(),
  status: 'IDLE', // 'IDLE' | 'LOADING' | 'SUCCESS' | 'ERROR'
  error: null,
};

const gameSlice = createSlice({
  name: 'game',
  initialState,
  reducers: {
    clearGameError: (state) => {
      state.error = null;
    },
    setCurrentGame: (state, action) => {
      state.currentGame = action.payload;
      if (action.payload) {
        localStorage.setItem('current_game', JSON.stringify(action.payload));
      } else {
        localStorage.removeItem('current_game');
      }
    },
  },
  extraReducers: (builder) => {
    builder
      // Fetch Available
      .addCase(fetchGamesThunk.pending, (state) => {
        state.status = 'LOADING';
      })
      .addCase(fetchGamesThunk.fulfilled, (state, action) => {
        state.status = 'SUCCESS';
        state.availableGames = action.payload;
      })
      .addCase(fetchGamesThunk.rejected, (state, action) => {
        state.status = 'ERROR';
        state.error = action.payload;
      })
      // Fetch My Games
      .addCase(fetchMyGamesThunk.fulfilled, (state, action) => {
        state.myGames = action.payload;
      })
      // Create Game
      .addCase(createGameThunk.fulfilled, (state, action) => {
        state.myGames.push(action.payload);
      })
      // Join By Name
      .addCase(joinByNameThunk.fulfilled, (state, action) => {
        state.myGames.push(action.payload);
      })
      // Join Random
      .addCase(joinRandomThunk.fulfilled, (state, action) => {
        state.myGames.push(action.payload);
      });
  },
});

export const { clearGameError, setCurrentGame } = gameSlice.actions;
export default gameSlice.reducer;
