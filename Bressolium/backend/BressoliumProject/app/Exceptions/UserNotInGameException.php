<?php

namespace App\Exceptions;

class UserNotInGameException extends DomainException
{
    public function __construct()
    {
        parent::__construct('El usuario no pertenece a esta partida.', 403);
    }
}
