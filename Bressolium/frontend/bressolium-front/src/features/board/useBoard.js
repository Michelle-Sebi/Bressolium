import { useDispatch } from 'react-redux';
import { bressoliumApi } from '../../services/bressoliumApi';
import { tileExploreRequested, tileUpgradeRequested } from './boardSlice';
import { useToast } from '../../contexts/ToastContext';

export function useBoard(gameId) {
    const dispatch = useDispatch();
    const { show } = useToast();
    const { data, isLoading, error } = bressoliumApi.useGetBoardQuery(gameId, { skip: !gameId });
    const [exploreTileMutation] = bressoliumApi.useExploreTileMutation();
    const [upgradeTileMutation] = bressoliumApi.useUpgradeTileMutation();

    const tiles = data?.tiles ?? [];

    const exploreTile = async (tileId) => {
        dispatch(tileExploreRequested(tileId));
        const result = await exploreTileMutation(tileId);
        if (result.error) {
            const message = result.error.data?.error
                ?? result.error.data?.message
                ?? 'No se puede explorar esta casilla.';
            show(message, 'error');
        }
        return result;
    };

    const upgradeTile = async (tileId) => {
        dispatch(tileUpgradeRequested(tileId));
        const result = await upgradeTileMutation(tileId);
        if (result.error) {
            const message = result.error.data?.error
                ?? result.error.data?.message
                ?? 'No se puede evolucionar esta casilla.';
            show(message, 'error');
        } else {
            const level = result.data?.data?.type?.level ?? result.data?.type?.level;
            const levelText = level != null ? ` (Nv. ${level})` : '';
            show(`Casilla evolucionada${levelText}`, 'success');
        }
        return result;
    };

    return { tiles, isLoading, error, exploreTile, upgradeTile };
}
