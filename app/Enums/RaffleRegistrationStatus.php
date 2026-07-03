<?php

namespace App\Enums;

enum RaffleRegistrationStatus: string
{
    case Active = 'active';
    case Flagged = 'flagged';
    case Cancelled = 'cancelled';
}
