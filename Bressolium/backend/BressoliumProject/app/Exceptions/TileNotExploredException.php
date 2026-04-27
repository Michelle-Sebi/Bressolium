<?php

namespace App\Exceptions;

class TileNotExploredException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Tile has not been explored yet.', 422);
    }
}
