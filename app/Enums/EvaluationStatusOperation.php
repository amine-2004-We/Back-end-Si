<?php

namespace App\Enums;

enum EvaluationStatusOperation: string
{
    case REALISEE = 'Réalisée';
    case NON_REALISEE = 'Non Réalisée';
    case ABSENT = 'Absent';
    case EN_COURS = 'en cours';

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
