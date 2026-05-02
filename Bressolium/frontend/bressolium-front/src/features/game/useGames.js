import { useDispatch, useSelector } from 'react-redux';
import {
    fetchGamesThunk,
    fetchMyGamesThunk,
    createGameThunk,
    joinRandomThunk,
    setCurrentGame,
    clearGameError,
} from './gameSlice';

export function useGames() {
    const dispatch = useDispatch();
    const { availableGames, myGames, currentGame, status, error } = useSelector(
        (state) => state.game
    );

    const fetchGames = () => dispatch(fetchGamesThunk());
    const fetchMyGames = () => dispatch(fetchMyGamesThunk());
    const createGame = (name) => dispatch(createGameThunk({ name }));
    const joinRandom = () => dispatch(joinRandomThunk());
    const selectGame = (game) => dispatch(setCurrentGame(game));
    const clearError = () => dispatch(clearGameError());

    return {
        availableGames,
        myGames,
        currentGame,
        status,
        error,
        fetchGames,
        fetchMyGames,
        createGame,
        joinRandom,
        selectGame,
        clearError,
    };
}
