<?php

namespace App\Enums;

enum PublicationStatus: string
{
    case OPEN = 'open';
    case CLOSED = 'closed';
}
