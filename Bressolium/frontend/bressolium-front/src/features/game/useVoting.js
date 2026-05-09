import { useState, useEffect } from 'react';
import { bressoliumApi } from '../../services/bressoliumApi';

export function useVoting(gameId) {
    const { data, isLoading } = bressoliumApi.useGetSyncQuery(gameId, { skip: !gameId });
    const [voteMutation]                                     = bressoliumApi.useVoteMutation();
    const [closeRoundMutation, { isLoading: isClosing }]     = bressoliumApi.useCloseRoundMutation();

    const [hasVoted, setHasVoted]   = useState(false);
    const [votedName, setVotedName] = useState(null);

    const rawTechs     = data?.progress?.technologies ?? [];
    const rawInvs      = data?.progress?.inventions   ?? [];
    const currentRound = data?.current_round ?? null;

    useEffect(() => {
        setHasVoted(false);
        setVotedName(null);
    }, [currentRound?.number]);

    const technologies = rawTechs.map((t) => ({
        id:      t.id,
        name:    t.name,
        canVote: !t.is_active,
        missing: t.missing ?? [],
    }));

    const inventions = rawInvs.map((i) => ({
        id:       i.id,
        name:     i.name,
        quantity: i.quantity,
        canVote:  i.missing.length === 0,
        missing:  i.missing ?? [],
    }));

    const userActions = data?.user_actions?.actions_spent ?? 0;

    async function vote(voteData, name = null) {
        const result = await voteMutation({ gameId, ...voteData });
        if (!result.error) {
            setHasVoted(true);
            setVotedName(name);
        }
        return result;
    }

    function closeRound() {
        return closeRoundMutation(gameId);
    }

    return { technologies, inventions, userActions, currentRound, isLoading, isClosing, hasVoted, votedName, vote, closeRound };
}
