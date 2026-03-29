<?php

namespace App\Enums;

enum ProfessionalOptionEnum: string
{
    case FORMATION_METIER = 'Formation métier';
    case ATELIER_PRATIQUE = 'Atelier pratique';

    public static function options(): array
    {
        return array_map(fn($case) => [
            'value' => $case->value,
            'label' => $case->value,
        ], self::cases());
    }
}