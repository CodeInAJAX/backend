<?php

namespace App\Enums;

enum Role : string
{
    case ADMIN = 'admin';
    case STUDENT = 'student';
    case MENTOR = 'mentor';
}
