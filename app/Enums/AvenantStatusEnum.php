<?php

namespace App\Enums;

enum AvenantStatusEnum: string
{
    case EnPreparation = 'En préparation';
    case Signe = 'Signé';
    case Annule = 'Annulé';
}

