import { bressoliumApi } from '../../services/bressoliumApi';

export function useVoting(gameId) {
    const { data, isLoading } = bressoliumApi.useGetSyncQuery(gameId, { skip: !gameId });
    const [voteMutation]                              = bressoliumApi.useVoteMutation();
    const [closeRoundMutation, { isLoading: isClosing }] = bressoliumApi.useCloseRoundMutation();

    const rawTechs = data?.progress?.technologies ?? [];
    const rawInvs  = data?.progress?.inventions   ?? [];

    const technologies = rawTechs.map((t) => ({
        id:      t.id,
        name:    t.name,
        canVote: !t.is_active,
        missing: t.missing ?? [],
    }));

    const inventions = rawInvs.map((i) => ({
        id:      i.id,
        name:    i.name,
        canVote: i.quantity === 0,
        missing: i.missing ?? [],
    }));

    const userActions  = data?.user_actions?.actions_spent ?? 0;
    const currentRound = data?.current_round ?? null;

    function vote(voteData) {
        return voteMutation({ gameId, ...voteData });
    }

    function closeRound() {
        return closeRoundMutation(gameId);
    }

    return { technologies, inventions, userActions, currentRound, isLoading, isClosing, vote, closeRound };
}
