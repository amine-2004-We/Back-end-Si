<?php

namespace App\Enums;

enum GridType: string
{
    case Evaluation = 'evaluation';
    case Impact     = 'impact';
    case FollowUp   = 'follow_up';
}
