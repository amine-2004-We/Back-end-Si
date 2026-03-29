<?php

namespace App\Enums;

enum InsuranceEnum: string
{
    case CNSS= 'CNSS';
    case AMO = 'AMO';
    case RETRAITE_COMPLEMENTAIRE = 'Retraite complémentaire';
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