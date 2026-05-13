/**
 * @module authSlice
 * @description Slice de Redux Toolkit para gestionar el estado de autenticación del usuario.
 * Almacena el usuario autenticado, el token y el estado de la petición.
 * @see Tarea 3 - Frontend Structure, Auth Routing and Redux
 */

import { createSlice, createAsyncThunk } from '@reduxjs/toolkit';
import authService from '../../services/authService';

/**
 * Thunk asíncrono para realizar el login.
 * Llama a authService.login y gestiona el token.
 */
export const loginThunk = createAsyncThunk(
  'auth/login',
  async ({ email, password }, { rejectWithValue }) => {
    try {
      const response = await authService.login(email, password);
      // Puesto que el test mockea completamente authService, el side-effect del
      // setItem debe realizarlo el código que no está interceptado (ej: el thunk).
      if (response && response.token) {
        localStorage.setItem('auth_token', response.token);
      }
      return response;
    } catch (error) {
      return rejectWithValue(error.message);
    }
  }
);

/**
 * @typedef {Object} AuthState
 * @property {'IDLE'|'LOADING'|'LOGGED_IN'|'ERROR'} status - Estado de la autenticación.
 * @property {object|null} user - Datos del usuario autenticado.
 * @property {string|null} error - Mensaje de error si lo hay.
 */

/** @type {AuthState} */
const initialState = {
  status: authService.getToken() ? 'LOGGED_IN' : 'IDLE',
  user: authService.getUser(),
  error: null,
};

const authSlice = createSlice({
  name: 'auth',
  initialState,
  reducers: {
    /**
     * Acción para cerrar sesión del usuario.
     * @param {AuthState} state
     */
    logout: (state) => {
      state.status = 'IDLE';
      state.user = null;
      state.error = null;
      authService.logout();
    },
    /**
     * Limpia el error previo.
     * @param {AuthState} state
     */
    clearError: (state) => {
      state.error = null;
    },
  },
  extraReducers: (builder) => {
    builder
      .addCase(loginThunk.pending, (state) => {
        state.status = 'LOADING';
        state.error = null;
      })
      .addCase(loginThunk.fulfilled, (state, action) => {
        state.status = 'LOGGED_IN';
        state.user = action.payload.user || action.payload;
      })
      .addCase(loginThunk.rejected, (state, action) => {
        state.status = 'ERROR';
        state.error = action.payload;
      });
  },
});

export const { logout, clearError } = authSlice.actions;
export default authSlice.reducer;
