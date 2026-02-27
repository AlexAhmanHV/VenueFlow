<?php

namespace App\Enums;

enum BookingStatus: string
{
    case CONFIRMED = 'CONFIRMED';
    case CANCELLED = 'CANCELLED';
    case NO_SHOW = 'NO_SHOW';
    case CHECKED_IN = 'CHECKED_IN';
}
