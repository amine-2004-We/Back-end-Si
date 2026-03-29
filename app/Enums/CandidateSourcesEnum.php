<?php 

namespace App\Enums;

enum CandidateSourcesEnum: string
{
    case Recommandation='Recommandation';
    case Réseau='Réseau';
    case Autre='Autre';
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