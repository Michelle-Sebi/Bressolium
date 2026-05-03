<?php

namespace App\Exceptions;

class VoteValidationException extends DomainException
{
    public function __construct(string $message)
    {
        parent::__construct($message, 422);
    }
}
