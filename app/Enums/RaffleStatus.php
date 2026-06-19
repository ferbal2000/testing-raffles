<?php

namespace App\Enums;

enum RaffleStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Closed = 'closed';
}
