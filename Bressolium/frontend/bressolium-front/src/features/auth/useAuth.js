import { useDispatch, useSelector } from 'react-redux';
import { loginThunk, logout, clearError } from './authSlice';
import authService from '../../services/authService';

export function useAuth() {
    const dispatch = useDispatch();
    const { status, user, error } = useSelector((state) => state.auth);

    const login = (email, password) => dispatch(loginThunk({ email, password }));

    const register = async (name, email, password) => {
        await authService.register(name, email, password);
        return dispatch(loginThunk({ email, password }));
    };

    const logoutUser = () => dispatch(logout());
    const clearAuthError = () => dispatch(clearError());

    return { status, user, error, login, register, logoutUser, clearAuthError };
}
