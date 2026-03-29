<?php

namespace App\Enums;

enum AvenantModificationNatureEnum: string
{
    case Financier = 'Financier';
    case Perimetre = 'Périmètre';
    case Duree = 'Durée';
    case Autre = 'Autre';
}

