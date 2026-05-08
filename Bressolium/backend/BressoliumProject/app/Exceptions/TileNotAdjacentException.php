<?php

namespace App\Exceptions;

class TileNotAdjacentException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Solo puedes explorar casillas adyacentes a tu territorio.', 422);
    }
}
