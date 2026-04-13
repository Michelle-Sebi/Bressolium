/**
 * @module gameService
 * @description Servicio para gestionar la comunicación con el backend en lo referente a partidas y equipos.
 */

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';

const gameService = {
  /**
   * Obtiene la lista de partidas disponibles para el usuario.
   */
  async getGames() {
    // Implementación pendiente
  },

  /**
   * Obtiene la lista de partidas en las que el usuario ya participa.
   */
  async getMyGames() {
    // Implementación pendiente
  },

  /**
   * Crea un nuevo equipo/partida.
   * @param {string} teamName 
   * @param {string} civilization 
   */
  async create(teamName, civilization) {
    // Implementación pendiente
  },

  /**
   * Se une a una partida aleatoria.
   */
  async joinRandom() {
    // Implementación pendiente
  },

  /**
   * Se une a una partida específica por nombre.
   * @param {string} teamName 
   */
  async joinByName(teamName) {
    // Implementación pendiente
  }
};

export default gameService;
