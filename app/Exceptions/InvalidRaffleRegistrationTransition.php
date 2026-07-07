<?php

namespace App\Exceptions;

use DomainException;

class InvalidRaffleRegistrationTransition extends DomainException
{
    public static function from(string $from, string $to): self
    {
        return new self("Cannot transition raffle registration from [{$from}] to [{$to}].");
    }
}
