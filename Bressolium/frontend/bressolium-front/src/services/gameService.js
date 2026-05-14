/**
 * @module gameService
 * @description Servicio para gestionar la comunicación con el backend en lo referente a partidas y equipos.
 */

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';

/**
 * Helper para obtener las cabeceras con el token de autenticación.
 */
const getHeaders = () => {
  const token = localStorage.getItem('auth_token');
  return {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'Authorization': `Bearer ${token}`
  };
};

const gameService = {
  /**
   * Obtiene la lista de partidas disponibles para el usuario.
   */
  async getGames() {
    const response = await fetch(`${API_URL}/game/all`, {
      method: 'GET',
      headers: getHeaders(),
    });
    if (!response.ok) throw new Error('Error al obtener partidas disponibles');
    return await response.json();
  },

  /**
   * Obtiene la lista de partidas en las que el usuario ya participa.
   */
  async getMyGames() {
    const response = await fetch(`${API_URL}/game/my`, {
      method: 'GET',
      headers: getHeaders(),
    });
    if (!response.ok) throw new Error('Error al obtener tus partidas');
    return await response.json();
  },

  /**
   * Crea un nuevo equipo/partida.
   * @param {string} teamName 
   */
  async create(teamName) {
    const response = await fetch(`${API_URL}/game/create`, {
      method: 'POST',
      headers: getHeaders(),
      body: JSON.stringify({ team_name: teamName })
    });
    const result = await response.json();
    if (!response.ok) throw new Error(result.error || 'Error al crear equipo');
    return result;
  },

  /**
   * Se une a una partida aleatoria.
   */
  async joinRandom() {
    const response = await fetch(`${API_URL}/game/join-random`, {
      method: 'POST',
      headers: getHeaders(),
    });
    const result = await response.json();
    if (!response.ok) throw new Error(result.error || 'Error al unirse aleatoriamente');
    return result;
  },

  /**
   * Se une a una partida específica por nombre.
   * @param {string} teamName 
   */
  async joinByName(teamName) {
    const response = await fetch(`${API_URL}/game/join`, {
      method: 'POST',
      headers: getHeaders(),
      body: JSON.stringify({ team_name: teamName })
    });
    const result = await response.json();
    if (!response.ok) throw new Error(result.error || 'Error al unirse al equipo');
    return result;
  },

  async leave(gameId) {
    const response = await fetch(`${API_URL}/game/${gameId}/leave`, {
      method: 'DELETE',
      headers: getHeaders(),
    });
    const result = await response.json();
    if (!response.ok) throw new Error(result.error || 'Error al abandonar la partida');
    return result;
  },
};

export default gameService;
