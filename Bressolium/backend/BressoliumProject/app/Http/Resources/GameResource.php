<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GameResource extends JsonResource
{
    public function toArray($request): array
    {
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'status' => $this->status,
            'users_count' => $this->users_count ?? $this->users()->count(),
        ];

        if ($this->relationLoaded('users')) {
            $data['users'] = $this->users->map(fn ($u) => ['id' => $u->id, 'name' => $u->name])->values();
        }

        if ($this->relationLoaded('rounds')) {
            $data['rounds'] = RoundResource::collection($this->rounds);
        }

        return $data;
    }
}
