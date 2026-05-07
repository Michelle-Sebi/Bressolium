<?php

namespace App\Services;

use App\DTOs\VoteDTO;
use App\Events\VoteCast;
use App\Models\Vote;
use App\Repositories\Contracts\VoteRepositoryInterface;
use App\Repositories\Contracts\SyncRepositoryInterface;
use Exception;

class VoteService
{
    public function __construct(
        private VoteRepositoryInterface $voteRepository,
        private SyncRepositoryInterface $syncRepository,
    ) {}

    public function vote(VoteDTO $dto): Vote
    {
        $round = $this->syncRepository->getCurrentRound($dto->gameId);

        if (!$round) {
            throw new Exception('No hay una jornada activa para esta partida.');
        }

        if ($this->voteRepository->hasVotedThisRound($round->id, $dto->userId)) {
            throw new \App\Exceptions\VoteValidationException('Ya has votado en esta jornada.');
        }

        if ($dto->technologyId) {
            if ($this->voteRepository->isTechnologyCompleted($dto->gameId, $dto->technologyId)) {
                throw new \App\Exceptions\VoteValidationException('Esta tecnología ya está investigada.');
            }
        }

        if ($dto->inventionId) {
            if (!$this->voteRepository->hasEnoughMaterialsForInvention($dto->gameId, $dto->inventionId)) {
                throw new \App\Exceptions\VoteValidationException('Materiales insuficientes para construir este invento.');
            }
        }

        $vote = $this->voteRepository->store($round->id, $dto->userId, $dto->technologyId, $dto->inventionId);
        VoteCast::dispatch($dto->userId, $dto->gameId);

        return $vote;
    }
}
