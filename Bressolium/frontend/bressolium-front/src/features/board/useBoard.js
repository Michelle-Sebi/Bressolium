import { useDispatch, useSelector } from 'react-redux';
import {
    fetchBoardThunk,
    exploreTileThunk,
    upgradeTileThunk,
    tileExploreRequested,
    tileUpgradeRequested,
} from './boardSlice';

export function useBoard() {
    const dispatch = useDispatch();
    const { tiles, status, error } = useSelector((state) => state.board);

    const fetchBoard = (gameId) => dispatch(fetchBoardThunk(gameId));

    const exploreTile = (tileId) => {
        const result = dispatch(exploreTileThunk(tileId));
        dispatch(tileExploreRequested(tileId));
        return result;
    };

    const upgradeTile = (tileId) => {
        const result = dispatch(upgradeTileThunk(tileId));
        dispatch(tileUpgradeRequested(tileId));
        return result;
    };

    return { tiles, status, error, fetchBoard, exploreTile, upgradeTile };
}
