<?php

namespace App\Enums;

enum GrantStatusesEnum: string
{
    case En_attente= 'En attente';
    case Partiellement_reçue  ='Partiellement reçue';
    case Reçue_intégralement = 'Reçue intégralement';
    case Clôturée = 'Clôturée';
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