import { useState, useEffect } from 'react';
import { bressoliumApi } from '../../services/bressoliumApi';

export function useVoting(gameId) {
    const [votedTechRound, setVotedTechRound] = useState(null);
    const [votedInvRound,  setVotedInvRound]  = useState(null);
    const [votedName,      setVotedName]      = useState(null);
    const [finishedRound,  setFinishedRound]  = useState(null);

    const { data, isLoading, refetch } = bressoliumApi.useGetSyncQuery(gameId, {
        skip:                      !gameId,
        pollingInterval:           30000,
        refetchOnMountOrArgChange: true,
        refetchOnFocus:            true,
    });

    const [voteMutation]                                 = bressoliumApi.useVoteMutation();
    const [closeRoundMutation, { isLoading: isClosing }] = bressoliumApi.useCloseRoundMutation();

    const rawTechs        = data?.progress?.technologies ?? [];
    const rawInvs         = data?.progress?.inventions   ?? [];
    const currentRound    = data?.current_round ?? null;
    const lastRoundResult = data?.last_round_result ?? null;
    const gameStatus      = data?.game_status ?? null;
    const playersCount    = data?.players_count ?? 1;

    // Reset local flags when a new round arrives
    useEffect(() => {
        if (currentRound?.number > (votedTechRound ?? 0)) setVotedTechRound(null);
        if (currentRound?.number > (votedInvRound  ?? 0)) setVotedInvRound(null);
        if (currentRound?.number > (finishedRound  ?? 0)) setFinishedRound(null);
    }, [currentRound?.number]);

    const hasVotedTech = (data?.has_voted_tech ?? false) || votedTechRound === currentRound?.number;
    const hasVotedInv  = (data?.has_voted_inv  ?? false) || votedInvRound  === currentRound?.number;
    const hasVoted     = hasVotedTech || hasVotedInv;
    const hasFinished  = (data?.has_finished   ?? false) || finishedRound  === currentRound?.number;

    // Faster polling while waiting for a new round
    const isWaiting = finishedRound !== null;
    useEffect(() => {
        if (!isWaiting || !gameId) return;
        refetch();
        const id = setInterval(refetch, 1000);
        return () => clearInterval(id);
    }, [isWaiting, gameId, refetch]);

    const technologies = rawTechs
        .map((t) => ({
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
        costs:    i.costs ?? [],
    }));

    const userActions = data?.user_actions?.actions_spent ?? 0;

    async function vote(voteData, name = null) {
        const result = await voteMutation({ gameId, ...voteData });
        if (!result.error) {
            if (voteData.technology_id) {
                setVotedTechRound(currentRound?.number ?? null);
                setVotedName(name);
            }
            if (voteData.invention_id) {
                setVotedInvRound(currentRound?.number ?? null);
            }
        }
        return result;
    }

    async function closeRound() {
        const result = await closeRoundMutation(gameId);
        if (!result.error) {
            setFinishedRound(currentRound?.number ?? null);
            refetch();
        }
        return result;
    }

    return {
        technologies, inventions, userActions, currentRound, lastRoundResult,
        gameStatus, playersCount, isLoading, isClosing,
        hasVoted, hasVotedTech, hasVotedInv, hasFinished, votedName,
        vote, closeRound,
    };
}
