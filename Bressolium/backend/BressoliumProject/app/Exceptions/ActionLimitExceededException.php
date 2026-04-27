<?php

namespace App\Exceptions;

class ActionLimitExceededException extends DomainException
{
    public function __construct()
    {
        parent::__construct('No quedan acciones disponibles en esta jornada.', 403);
    }
}
