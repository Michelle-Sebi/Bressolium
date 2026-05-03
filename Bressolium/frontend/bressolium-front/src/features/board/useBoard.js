import { useDispatch } from 'react-redux';
import { bressoliumApi } from '../../services/bressoliumApi';
import { tileExploreRequested, tileUpgradeRequested } from './boardSlice';

export function useBoard(gameId) {
    const dispatch = useDispatch();
    const { data, isLoading, error } = bressoliumApi.useGetBoardQuery(gameId, { skip: !gameId });
    const [exploreTileMutation] = bressoliumApi.useExploreTileMutation();
    const [upgradeTileMutation] = bressoliumApi.useUpgradeTileMutation();

    const tiles = data?.tiles ?? [];

    const exploreTile = (tileId) => {
        dispatch(tileExploreRequested(tileId));
        return exploreTileMutation(tileId);
    };

    const upgradeTile = (tileId) => {
        dispatch(tileUpgradeRequested(tileId));
        return upgradeTileMutation(tileId);
    };

    return { tiles, isLoading, error, exploreTile, upgradeTile };
}
