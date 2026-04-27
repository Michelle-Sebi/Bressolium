<?php

namespace App\Exceptions;

class ActionLimitExceededException extends DomainException
{
    public function __construct()
    {
        parent::__construct('No actions remaining for this round.', 403);
    }
}
