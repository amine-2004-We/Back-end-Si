<?php

namespace App\Enums;

enum PriorityFcEnum:string
{
    case HAUTE = 'Haute';
    case NORMALE = 'Normale';
    case BASSE = 'Basse';

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
