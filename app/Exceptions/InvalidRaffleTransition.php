<?php

namespace App\Exceptions;

use DomainException;

class InvalidRaffleTransition extends DomainException
{
    public static function from(string $from, string $to): self
    {
        return new self("Cannot transition raffle from [{$from}] to [{$to}].");
    }
}
