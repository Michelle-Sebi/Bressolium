<?php

namespace App\Http\Resources;

use App\DTOs\SyncResponseDTO;
use Illuminate\Http\Resources\Json\JsonResource;

class SyncResource extends JsonResource
{
    public function __construct(private SyncResponseDTO $dto) {}

    public function toArray($request): array
    {
        return [
            'current_round'     => $this->dto->currentRound,
            'user_actions'      => $this->dto->userActions,
            'has_voted'         => $this->dto->hasVoted,
            'has_finished'      => $this->dto->hasFinished,
            'last_round_result' => $this->dto->lastRoundResult ?: null,
            'game_status'       => $this->dto->gameStatus,
            'inventory'         => $this->dto->inventory,
            'progress'          => [
                'technologies' => $this->dto->technologies,
                'inventions'   => $this->dto->inventions,
            ],
        ];
    }
}
