<?php

namespace App\Enums;

enum ServiceOrderStatusEnum: string
{
    case PREPARE = 'Préparé';
    case EMIS = 'Émis';
    case EN_COURS = 'En cours';
    case CLOTURE = 'Clôturé';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
