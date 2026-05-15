<?php

namespace App\Repositories\Contracts;

use App\Models\Game;
use App\Models\Invention;
use App\Models\Round;

interface CloseRoundRepositoryInterface
{
    public function findGameWithUsers(string $gameId): Game;

    public function getLatestRound(Game $game): Round;

    public function resolveTechnologyVote(Round $round): ?array;

    public function resolveInventionVote(Round $round): ?array;

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

    public function markRoundResult(Round $round, string $inventionId, bool $noConsensus): void;

    public function markRoundTechResult(Round $round, string $techId, bool $noConsensus): void;

    public function markRoundEnded(Round $round): void;

    public function setPlayerFinishedAt(Round $round, string $userId): void;

    public function clearPlayerFinishedAt(Round $round, string $userId): void;

    public function isPlayerFinished(Round $round, string $userId): bool;

    public function getPlayerActionsSpent(Round $round, string $userId): int;

    public function hasPlayerVoted(Round $round, string $userId): bool;

    public function hasPlayerVotedForTechnology(Round $round, string $userId): bool;

    public function hasPlayerVotedForInvention(Round $round, string $userId): bool;

    public function countFinishedPlayers(Round $round): int;

    public function markAllUnfinishedPlayersAsDone(Round $round, Game $game): void;
}
