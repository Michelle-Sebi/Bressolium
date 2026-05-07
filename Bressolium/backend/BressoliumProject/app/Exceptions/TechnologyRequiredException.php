<?php

namespace App\Exceptions;

class TechnologyRequiredException extends DomainException
{
    public function __construct(string $techName = '')
    {
        $message = $techName
            ? "Se requiere la tecnología «{$techName}» para evolucionar esta casilla."
            : 'Se requiere una tecnología específica para evolucionar esta casilla.';

        parent::__construct($message, 400);
    }
}
