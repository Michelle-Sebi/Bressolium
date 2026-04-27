import httpClient from '../../../lib/httpClient';

/**
 * Obtiene todas las partidas disponibles.
 * @returns {Promise<object[]>}
 */
export const getGames = async () => {
    const { data } = await httpClient.get('/game/all');
    return data;
};

/**
 * Obtiene las partidas en las que participa el usuario autenticado.
 * @returns {Promise<object[]>}
 */
export const getMyGames = async () => {
    const { data } = await httpClient.get('/game/my');
    return data;
};

/**
 * Crea una nueva partida con el nombre de equipo indicado.
 * @param {string} teamName
 * @returns {Promise<object>}
 */
export const createGame = async (teamName) => {
    const { data } = await httpClient.post('/game/create', { team_name: teamName });
    return data;
};

/**
 * Se une a una partida aleatoria disponible.
 * @returns {Promise<object>}
 */
export const joinRandom = async () => {
    const { data } = await httpClient.post('/game/join-random');
    return data;
};

/**
 * Se une a una partida específica por nombre de equipo.
 * @param {string} teamName
 * @returns {Promise<object>}
 */
export const joinByName = async (teamName) => {
    const { data } = await httpClient.post('/game/join', { team_name: teamName });
    return data;
};
