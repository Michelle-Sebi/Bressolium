/**
 * @module authService
 * @description Servicio de autenticación que centraliza las llamadas al API de login/register.
 * Comunica con los endpoints de Sanctum y gestiona el token en localStorage.
 * @see Tarea 3 - Frontend Structure, Auth Routing and Redux
 */

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';

const authService = {
  /**
   * Realiza el login del usuario contra el backend.
   * @param {string} email - Email del usuario.
   * @param {string} password - Contraseña del usuario.
   * @returns {Promise<{token: string, user: object}>} Respuesta con token y datos del usuario.
   */
  login: async (email, password) => {
    const response = await fetch(`${API_URL}/login`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify({ email, password }),
    });

    const data = await response.json();

    if (!response.ok) {
      let errorMessage = 'Login fallido';
      if (typeof data.error === 'object' && data.error !== null) {
        errorMessage = Object.values(data.error).flat().join(' ');
      } else if (data.error) {
        errorMessage = data.error;
      }
      throw new Error(errorMessage);
    }

    // Guardamos el token en localStorage según DoD de Tarea 3
    localStorage.setItem('auth_token', data.data?.token || data.token);

    return data.data || data;
  },

  /**
   * Registra un nuevo usuario.
   * @param {string} name - Nombre del usuario.
   * @param {string} email - Email del usuario.
   * @param {string} password - Contraseña del usuario.
   * @returns {Promise<{token: string, user: object}>}
   */
  register: async (name, email, password) => {
    const response = await fetch(`${API_URL}/register`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify({ name, email, password, password_confirmation: password }),
    });

    const data = await response.json();

    if (!response.ok) {
      let errorMessage = 'Registro fallido';
      if (typeof data.error === 'object' && data.error !== null) {
        // Flatten the validation errors into a single string
        errorMessage = Object.values(data.error).flat().join(' ');
      } else if (data.error) {
        errorMessage = data.error;
      }
      throw new Error(errorMessage);
    }

    localStorage.setItem('auth_token', data.data?.token || data.token);

    return data.data || data;
  },

  /**
   * Cierra la sesión eliminando el token del cliente.
   */
  logout: () => {
    localStorage.removeItem('auth_token');
  },

  /**
   * Recupera el token guardado en cliente.
   * @returns {string|null}
   */
  getToken: () => localStorage.getItem('auth_token'),
};

export default authService;
