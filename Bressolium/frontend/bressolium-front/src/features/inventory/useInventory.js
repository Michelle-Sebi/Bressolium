import { bressoliumApi } from '../../services/bressoliumApi';

export function useInventory(gameId) {
    const { data, isLoading, error } = bressoliumApi.useGetSyncQuery(gameId, {
        skip:                    !gameId,
        pollingInterval:         30000,
        refetchOnMountOrArgChange: true,
        refetchOnFocus:          true,
    });
    const materials    = data?.inventory                                          ?? [];
    const inventions   = data?.progress?.inventions                               ?? [];
    const technologies = (data?.progress?.technologies ?? []).filter(t => t.is_active);
    return { materials, inventions, technologies, isLoading, error };
}
