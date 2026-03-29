<?php

namespace App\Enums;

enum NiveauAppreciationEnum: string
{
    case QUATRE = '4';
    case TROIS = '3';
    case DEUX = '2';
    case UN = '1';
    case ZERO = '0';
    case NON_TENTE = 'Non tenté';
    case ABSENT = 'Absent';

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

