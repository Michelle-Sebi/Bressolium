import httpClient from '../../../lib/httpClient';

/**
 * Obtiene el estado completo del tablero para una partida.
 * @param {string} gameId
 * @returns {Promise<object>}
 */
export const getBoard = async (gameId) => {
    const { data } = await httpClient.get(`/board/${gameId}`);
    return data;
};

/**
 * Explora una casilla no explorada del jugador actual.
 * @param {string} tileId
 * @returns {Promise<object>}
 */
export const exploreTile = async (tileId) => {
    const { data } = await httpClient.post(`/tiles/${tileId}/explore`);
    return data;
};

/**
 * Evoluciona una casilla ya explorada del jugador actual.
 * @param {string} tileId
 * @returns {Promise<object>}
 */
export const upgradeTile = async (tileId) => {
    const { data } = await httpClient.post(`/tiles/${tileId}/upgrade`);
    return data;
};
