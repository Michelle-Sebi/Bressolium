<?php

namespace App\Exceptions;

class InsufficientMaterialsException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Materiales insuficientes para realizar esta acción.', 400);
    }
}
