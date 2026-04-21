/**
 * @module boardService
 * @description Servicio para comunicación con los endpoints de tablero y acciones de casilla.
 */

const BASE_URL = import.meta.env.VITE_API_URL ?? 'http://localhost/api';

/**
 * @param {string} url
 * @param {RequestInit} [options]
 * @returns {Promise<object>}
 */
async function apiFetch(url, options = {}) {
    const token = localStorage.getItem('auth_token');
    const response = await fetch(url, {
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            ...(token ? { Authorization: `Bearer ${token}` } : {}),
        },
        ...options,
    });
    if (!response.ok) throw new Error(`HTTP ${response.status}`);
    return response.json();
}

/**
 * Obtiene el estado completo del tablero para una partida.
 * @param {string} gameId
 * @returns {Promise<object>}
 */
export const getBoard = (gameId) =>
    apiFetch(`${BASE_URL}/board/${gameId}`);

/**
 * Explora una casilla no explorada del jugador actual.
 * @param {string} tileId
 * @returns {Promise<object>}
 */
export const exploreTile = (tileId) =>
    apiFetch(`${BASE_URL}/tiles/${tileId}/explore`, { method: 'POST' });

/**
 * Evoluciona una casilla ya explorada del jugador actual.
 * @param {string} tileId
 * @returns {Promise<object>}
 */
export const upgradeTile = (tileId) =>
    apiFetch(`${BASE_URL}/tiles/${tileId}/upgrade`, { method: 'POST' });
