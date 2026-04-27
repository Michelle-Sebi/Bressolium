<?php

namespace App\Exceptions;

class TileAlreadyExploredException extends DomainException
{
    public function __construct()
    {
        parent::__construct('La casilla ya ha sido explorada.', 422);
    }
}
