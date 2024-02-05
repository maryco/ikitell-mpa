<?php

namespace App\Enums\Alert;

enum NotificationTarget: int
{
    case OWNER = 1;
    case USER = 2;
    case CONTACTS = 3;
}
