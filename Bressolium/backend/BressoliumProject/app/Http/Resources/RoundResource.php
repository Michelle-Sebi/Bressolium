<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RoundResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'         => $this->id,
            'number'     => $this->number,
            'start_date' => $this->start_date,
            'ended_at'   => $this->ended_at,
        ];
    }
}
