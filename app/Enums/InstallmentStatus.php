<?php

namespace App\Enums;

enum InstallmentStatus: string
{
    case Planned = 'Prévue';
    case Pending = 'En attente';
    case Received = 'Reçue';
    case Delayed = 'Retardée';
}
