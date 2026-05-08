<?php

namespace App\Repositories\Contracts;

use App\Models\Game;
use App\Models\Invention;
use App\Models\Round;

interface CloseRoundRepositoryInterface
{
    public function findGameWithUsers(string $gameId): Game;

    public function getLatestRound(Game $game): Round;

    public function getMostVotedTechnologyId(Round $round): ?string;

    public function getMostVotedInventionId(Round $round): ?string;

    public function activateTechnology(Game $game, string $technologyId): void;

    public function getInventionWithDependencies(string $inventionId): ?Invention;

    public function inventionPrerequisitesMet(Game $game, Invention $invention): bool;

    public function inventionResourcesMet(Game $game, Invention $invention): bool;

    public function buildInvention(Game $game, Invention $invention): void;

    public function finishGame(Game $game): void;

    public function produceMaterialsFromExploredTiles(Game $game): void;

    public function createNextRound(Game $game): Round;

    public function initializePlayersForRound(Round $round, Game $game): void;

    public function markAfkPlayers(Round $round, Game $game): void;

    public function allNonAfkPlayersHaveVoted(Round $round, Game $game): bool;
}
