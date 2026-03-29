<?php

namespace App\Enums;

enum OriginalChannelEnum: string
{
    case SITEWEB="Site Web";
    case FORMULAIREEVENEMENT="Formulaire évènement";
    case RESEAUXSOCIAUX="Réseaux sociaux";
    case SAISIEMANUELLE="Saisie manuelle";
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
