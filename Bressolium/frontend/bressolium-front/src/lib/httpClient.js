import axios from 'axios';

let _store = null;
export const injectStore = (store) => { _store = store; };

const httpClient = axios.create({
    baseURL: import.meta.env.VITE_API_URL ?? 'http://localhost/api/v1',
});

httpClient.defaults.headers.common['Accept'] = 'application/json';
httpClient.defaults.headers.common['Content-Type'] = 'application/json';

httpClient.interceptors.request.use((config) => {
    const token = localStorage.getItem('auth_token');
    if (token) {
        config.headers['Authorization'] = `Bearer ${token}`;
    }
    return config;
});

httpClient.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 401) {
            localStorage.removeItem('auth_token');
            _store?.dispatch({ type: 'auth/logout' });
        }
        return Promise.reject(error);
    }
);

export default httpClient;
