<?php

namespace App\Enums;

enum VoteType: string
{
    case RECOMMEND = 'recommend';
    case NOT_RECOMMEND = 'not_recommend';
}
