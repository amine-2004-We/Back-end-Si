<?php

namespace App\Enums;

enum ProcessingStatus:string
{
    case RECU = 'Reçu';
    case ENCOURS = 'En cours';
    case PAYER = 'Payer';
    CASE REJET = 'Rejet';

    public static function options(): array
    {
        return array_map(fn(self $case) => [
            'value' => $case->value,
        ], self::cases());
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
