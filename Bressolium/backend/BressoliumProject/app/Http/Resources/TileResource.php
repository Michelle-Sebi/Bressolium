<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TileResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'           => $this->id,
            'coord_x'      => $this->coord_x,
            'coord_y'      => $this->coord_y,
            'tile_type_id' => $this->tile_type_id,
            'explored'     => (bool) $this->explored,
        ];
    }
}
