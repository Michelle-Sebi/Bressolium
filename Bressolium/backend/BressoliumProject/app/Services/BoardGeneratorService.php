<?php

namespace App\Services;

use App\Models\TileType;
use App\Repositories\BoardRepository;

class BoardGeneratorService
{
    // Posiciones de inicio para hasta 5 jugadores (índice = orden de unión)
    // Distribuidas equidistantemente en el tablero 15×15 (coords 0-14)
    private const STARTING_POSITIONS = [
        ['x' => 2,  'y' => 2],   // P1: esquina superior-izquierda
        ['x' => 12, 'y' => 12],  // P2: esquina inferior-derecha
        ['x' => 12, 'y' => 2],   // P3: esquina superior-derecha
        ['x' => 2,  'y' => 12],  // P4: esquina inferior-izquierda
        ['x' => 7,  'y' => 7],   // P5: centro del tablero
    ];

    public function __construct(private BoardRepository $boardRepository) {}

    public function generate(string $gameId): void
    {
        $typeIds = TileType::where('level', 1)->pluck('id')->toArray();

        if (empty($typeIds)) {
            return;
        }

        $tiles = [];
        for ($x = 0; $x <= 14; $x++) {
            for ($y = 0; $y <= 14; $y++) {
                $tiles[] = [
                    'game_id' => $gameId,
                    'tile_type_id' => $typeIds[array_rand($typeIds)],
                    'coord_x' => $x,
                    'coord_y' => $y,
                ];
            }
        }

        $this->boardRepository->createMany($tiles);
    }

    public function assignStartingTile(string $gameId, string $userId, int $playerIndex): void
    {
        $position = self::STARTING_POSITIONS[$playerIndex] ?? self::STARTING_POSITIONS[0];
        $this->boardRepository->markAsStartingTile($gameId, $position['x'], $position['y'], $userId);
    }
}
