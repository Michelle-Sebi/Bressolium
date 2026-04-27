import httpClient from '../../../lib/httpClient';

/**
 * Realiza el login del usuario.
 * @param {string} email
 * @param {string} password
 * @returns {Promise<{token: string, user: object}>}
 */
export const login = async (email, password) => {
    const { data } = await httpClient.post('/login', { email, password });
    const token = data.data?.token ?? data.token;
    if (token) localStorage.setItem('auth_token', token);
    return data.data ?? data;
};

/**
 * Registra un nuevo usuario.
 * @param {string} name
 * @param {string} email
 * @param {string} password
 * @returns {Promise<{token: string, user: object}>}
 */
export const register = async (name, email, password) => {
    const { data } = await httpClient.post('/register', {
        name,
        email,
        password,
        password_confirmation: password,
    });
    const token = data.data?.token ?? data.token;
    if (token) localStorage.setItem('auth_token', token);
    return data.data ?? data;
};

/**
 * Cierra la sesión eliminando el token del cliente.
 */
export const logout = () => {
    localStorage.removeItem('auth_token');
};
