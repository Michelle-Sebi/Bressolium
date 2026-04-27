<?php

namespace App\Exceptions;

class UserNotInGameException extends DomainException
{
    public function __construct()
    {
        parent::__construct('User is not in this game.', 403);
    }
}
