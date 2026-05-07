<?php

namespace App\Exceptions;

class PuebloTileActionException extends DomainException
{
    public function __construct()
    {
        parent::__construct('La casilla pueblo central no puede explorarse ni mejorarse.', 422);
    }
}
