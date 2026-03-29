<?php

namespace App\Enums;

enum EvaluationTypeCriteriaOperationsEnum: string
{
    case OBSERVATION_DIRECTE = 'Observation directe';
    case REPONSE_ORALE = 'Réponse orale';
    case PRATIQUE = 'Pratique';

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
