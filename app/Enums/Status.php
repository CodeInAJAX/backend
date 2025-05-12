<?php

namespace App\Enums;

enum Status : string
{
    case ACTIVE = 'active';
    case COMPLETED = 'completed';

    case PENDING = 'pending';
}
