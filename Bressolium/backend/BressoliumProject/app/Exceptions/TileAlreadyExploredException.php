<?php

namespace App\Exceptions;

class TileAlreadyExploredException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Tile has already been explored.', 422);
    }
}
