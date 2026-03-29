<?php

namespace App\Enums;

enum FinalAcceptanceStatusEnum: string
{
    case VALIDATED = 'Validé';
    case CLOSED = 'Clôturé';
    case REJECTED = 'Rejeté';
}
