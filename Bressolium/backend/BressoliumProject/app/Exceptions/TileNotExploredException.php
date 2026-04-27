<?php

namespace App\Exceptions;

class TileNotExploredException extends DomainException
{
    public function __construct(string $message = 'La casilla aún no ha sido explorada.')
    {
        parent::__construct($message, 422);
    }
}
