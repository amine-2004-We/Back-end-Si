<?php

namespace App\Enums;

enum EvaluationTypeEnum: string
{
    case FINALE = 'Évaluation finale';
    case Average = 'Évaluation intermédiaire';
    case QualityFollowUp = 'Suivi qualité';
    case AUDIT = 'Audit';

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
