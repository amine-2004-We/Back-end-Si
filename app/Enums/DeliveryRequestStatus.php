<?php

namespace App\Enums;

enum DeliveryRequestStatus:string
{
    case VALIDEE = 'Validée';
    case ENATTENT = 'En attente';
    case REFUSEE = 'Refusée';

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
