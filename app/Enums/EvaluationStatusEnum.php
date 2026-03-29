<?php

namespace App\Enums;

enum EvaluationStatusEnum: string
{
    case EN_PREPARATION = 'En préparation';
    case EN_COURS = 'En cours';
    case ACCEPTED = 'Validée';
    case ARCHIVED = 'Archivée';

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
