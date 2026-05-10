<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TileResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'coord_x' => $this->coord_x,
            'coord_y' => $this->coord_y,
            'tile_type_id' => $this->tile_type_id,
            'explored' => (bool) $this->explored,
            'type' => $this->whenLoaded('type', fn () => $this->type ? [
                'id' => $this->type->id,
                'name' => $this->type->name,
                'base_type' => $this->type->base_type,
                'level' => $this->type->level,
            ] : null),
        ];
    }
}
