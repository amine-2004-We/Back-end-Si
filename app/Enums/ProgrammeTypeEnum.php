<?php

namespace App\Enums;

enum ProgrammeTypeEnum: string
{
    case FORMATION_BASE = 'Formation de base';
    case INITIATION_PROFESSIONNELLE = 'Initiation professionnelle';

    public static function options(): array
    {
        return array_map(fn($case) => [
            'value' => $case->value,
            'label' => $case->value,
        ], self::cases());
    }
}