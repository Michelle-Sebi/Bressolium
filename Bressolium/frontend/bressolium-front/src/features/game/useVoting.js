import { useState, useEffect } from 'react';
import { bressoliumApi } from '../../services/bressoliumApi';

export function useVoting(gameId) {
    const [votedRound,    setVotedRound]    = useState(null);
    const [votedName,     setVotedName]     = useState(null);
    const [finishedRound, setFinishedRound] = useState(null);

    const { data, isLoading, refetch } = bressoliumApi.useGetSyncQuery(gameId, {
        skip:                      !gameId,
        pollingInterval:           30000,
        refetchOnMountOrArgChange: true,
        refetchOnFocus:            true,
    });

    // Mientras el jugador espera nueva jornada, refetch cada segundo
    const isWaiting = finishedRound !== null;
    useEffect(() => {
        if (!isWaiting || !gameId) return;
        refetch();
        const id = setInterval(refetch, 1000);
        return () => clearInterval(id);
    }, [isWaiting, gameId, refetch]);

    const [voteMutation]                                 = bressoliumApi.useVoteMutation();
    const [closeRoundMutation, { isLoading: isClosing }] = bressoliumApi.useCloseRoundMutation();

    const rawTechs        = data?.progress?.technologies ?? [];
    const rawInvs         = data?.progress?.inventions   ?? [];
    const currentRound    = data?.current_round ?? null;
    const lastRoundResult = data?.last_round_result ?? null;

    // Reset local "finished" flag when a new round arrives from the server
    useEffect(() => {
        if (finishedRound !== null && currentRound?.number > finishedRound) {
            setFinishedRound(null);
        }
    }, [currentRound?.number, finishedRound]);

    // Server is authoritative; local state gives immediate feedback before the next poll
    const hasVoted    = (data?.has_voted    ?? false) || votedRound    === currentRound?.number;
    const hasFinished = (data?.has_finished ?? false) || finishedRound === currentRound?.number;

    const technologies = rawTechs
        .filter((t) => !t.is_active)
        .map((t) => ({
            id:      t.id,
            name:    t.name,
            canVote: (t.missing ?? []).length === 0,
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
            setVotedRound(currentRound?.number ?? null);
            setVotedName(name);
        }
        return result;
    }

    async function abstain() {
        const result = await voteMutation({ gameId });
        if (!result.error) {
            setVotedRound(currentRound?.number ?? null);
            setVotedName(null);
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

    return { technologies, inventions, userActions, currentRound, lastRoundResult, isLoading, isClosing, hasVoted, hasFinished, votedName, vote, abstain, closeRound };
}
