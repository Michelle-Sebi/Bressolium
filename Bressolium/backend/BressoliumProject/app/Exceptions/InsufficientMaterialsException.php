<?php

namespace App\Exceptions;

class InsufficientMaterialsException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Insufficient materials to perform this action.', 400);
    }
}
