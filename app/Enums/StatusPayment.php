<?php

namespace App\Enums;

enum StatusPayment : string
{
    case PENDING = 'pending';
    case SUCCESS = 'success';
    case FAILED = 'failed';
}
