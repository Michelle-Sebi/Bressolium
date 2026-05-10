import { useState } from 'react';
import { bressoliumApi } from '../../services/bressoliumApi';

export function useVoting(gameId) {
    const { data, isLoading } = bressoliumApi.useGetSyncQuery(gameId, {
        skip:            !gameId,
        pollingInterval: 30000,
    });
    const [voteMutation]                                 = bressoliumApi.useVoteMutation();
    const [closeRoundMutation, { isLoading: isClosing }] = bressoliumApi.useCloseRoundMutation();

    const [votedRound, setVotedRound] = useState(null);
    const [votedName, setVotedName]   = useState(null);

    const rawTechs     = data?.progress?.technologies ?? [];
    const rawInvs      = data?.progress?.inventions   ?? [];
    const currentRound = data?.current_round ?? null;

    const hasVoted = votedRound === currentRound?.number;

    const technologies = rawTechs.map((t) => ({
        id:      t.id,
        name:    t.name,
        canVote: !t.is_active && (t.missing ?? []).length === 0,
        missing: t.missing ?? [],
    }));

    const inventions = rawInvs.map((i) => ({
        id:       i.id,
        name:     i.name,
        quantity: i.quantity,
        canVote:  (i.missing ?? []).length === 0,
        missing:  i.missing ?? [],
    }));

    const userActions = data?.user_actions?.actions_spent ?? 0;

    async function vote(voteData, name = null) {
        const result = await voteMutation({ gameId, ...voteData });
        if (!result.error) {
            setVotedRound(currentRound?.number ?? null);
            setVotedName(name);
        }
        return result;
    }

    async function abstain() {
        // Voto nulo: cuenta para el quórum pero no avanza ninguna tecnología
        const result = await voteMutation({ gameId });
        if (!result.error) {
            setVotedRound(currentRound?.number ?? null);
            setVotedName(null);
        }
        return result;
    }

    function closeRound() {
        return closeRoundMutation(gameId);
    }

    return { technologies, inventions, userActions, currentRound, isLoading, isClosing, hasVoted, votedName, vote, abstain, closeRound };
}
