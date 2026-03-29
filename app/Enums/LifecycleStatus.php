<?php

namespace App\Enums;

enum LifecycleStatus: string
{
    case InProgress = 'in_progress';
    case Validated  = 'validated';
    case Archived   = 'archived';
}
