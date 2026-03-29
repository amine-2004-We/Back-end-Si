<?php

namespace App\Enums;

enum MedicalRecordsTypes:string
{
    case MALADIR = 'maladie';
    case DENTAIRE = 'dentaire';
    case NAISSANCE = 'naissance';
    CASE OPTIQUE = 'optique';
    CASE DEVIS = 'devis';

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
