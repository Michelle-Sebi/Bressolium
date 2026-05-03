import { bressoliumApi } from '../../services/bressoliumApi';

export function useInventory(gameId) {
    const { data, isLoading, error } = bressoliumApi.useGetSyncQuery(gameId, { skip: !gameId });
    const materials = data?.inventory ?? [];
    return { materials, isLoading, error };
}
