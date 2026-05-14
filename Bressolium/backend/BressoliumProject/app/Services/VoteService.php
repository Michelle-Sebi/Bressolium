<?php

namespace App\Services;

use App\DTOs\VoteDTO;
use App\Events\VoteCast;
use App\Exceptions\VoteValidationException;
use App\Models\Vote;
use App\Repositories\Contracts\SyncRepositoryInterface;
use App\Repositories\Contracts\VoteRepositoryInterface;
use Exception;
use Illuminate\Support\Facades\DB;

class VoteService
{
    public function __construct(
        private VoteRepositoryInterface $voteRepository,
        private SyncRepositoryInterface $syncRepository,
    ) {}

    public function vote(VoteDTO $dto): Vote
    {
        $round = $this->syncRepository->getCurrentRound($dto->gameId);

        if (! $round) {
            throw new Exception('No hay una jornada activa para esta partida.');
        }

        if ($dto->technologyId) {
            if ($this->voteRepository->hasVotedForTechnology($round->id, $dto->userId)) {
                throw new VoteValidationException('Ya has votado una tecnología en esta jornada.');
            }
            if ($this->voteRepository->isTechnologyCompleted($dto->gameId, $dto->technologyId)) {
                throw new VoteValidationException('Esta tecnología ya está investigada.');
            }
        }

        if ($dto->inventionId) {
            if ($this->voteRepository->hasVotedForInvention($round->id, $dto->userId)) {
                throw new VoteValidationException('Ya has votado un invento en esta jornada.');
            }
            if (! $this->voteRepository->hasEnoughMaterialsForInvention($dto->gameId, $dto->inventionId)) {
                throw new VoteValidationException('Materiales insuficientes para construir este invento.');
            }
        }

        if (! $dto->technologyId && ! $dto->inventionId) {
            throw new VoteValidationException('Debes seleccionar una tecnología o un invento.');
        }

        $vote = $this->voteRepository->store($round->id, $dto->userId, $dto->technologyId, $dto->inventionId);

        // Cualquier voto activo limpia el AFK de toda la partida:
        // si alguien está votando, la sesión está viva y el quórum debe recalcularse desde cero
        DB::table('game_user')
            ->where('game_id', $dto->gameId)
            ->update(['is_afk' => false]);

        VoteCast::dispatch($dto->userId, $dto->gameId);

        return $vote;
    }
}
