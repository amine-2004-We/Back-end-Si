<?php

namespace App\Enums;

enum ChequeStatusEnum: string
{
    case ISSUED = 'Emis';
    case CANCELED = 'Annulé';
    case CASHED = 'Encaissé';
}