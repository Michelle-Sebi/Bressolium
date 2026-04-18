<?php

namespace App\Services;

use App\Models\TileType;
use App\Repositories\BoardRepository;

class BoardGeneratorService
{
    public function __construct(private BoardRepository $boardRepository) {}

    public function generate(string $gameId): void
    {
        $tileTypeIds = TileType::pluck('id')->toArray();

        if (empty($tileTypeIds)) {
            return;
        }

        $tiles = [];
        for ($x = 0; $x <= 14; $x++) {
            for ($y = 0; $y <= 14; $y++) {
                $tiles[] = [
                    'game_id'      => $gameId,
                    'tile_type_id' => $tileTypeIds[array_rand($tileTypeIds)],
                    'coord_x'      => $x,
                    'coord_y'      => $y,
                ];
            }
        }

        $this->boardRepository->createMany($tiles);
    }
}
