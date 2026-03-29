<?php

namespace App\Enums;

enum ProvisionalAcceptanceStatusEnum: string
{
    case PENDING_RESERVES = 'En attente de levée de réserves';
    case VALIDATED = 'Validé';
    case REJECTED = 'Rejeté';
}
