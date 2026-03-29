<?php

namespace App\Enums;

enum AtelierE2CNGEnum: string
{
    case ASSISTANT_COACH = 'Atelier assistant coach sportif';
    case CHARGE_MATERIEL = 'Atelier chargé de materiels de sports';
    case ANIMATEUR_IA = 'Animateur atelier intelligence artificielle';
    case COIFFURE = 'Atelier coiffure';
    case CUISINE = 'Atelier Cuisine';
    case COUTURE = 'Atelier Couture';
    case REPARATION_ELECTRONIQUE = 'Atelier Réparation d\'appareils électroniques';
    case PATISSERIE = 'Atelier Patisserie';
    case ELECTRICITE = 'Atelier Électricité domestique';
    case AIDE_SOIGNANT = 'Atelier Aide-soignant';

    public static function options(): array
    {
        return array_map(fn($case) => [
            'value' => $case->value,
            'label' => $case->value,  
        ], self::cases());
    }
}