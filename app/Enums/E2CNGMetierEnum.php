<?php

namespace App\Enums;

enum E2CNGMetierEnum: string
{
    case ASSISTANT_COACH_SPORTIF = 'Atelier assistant coach sportif';
    case CHARGE_MATERIELS_SPORTS = 'Atelier chargé de matériels de sports';
    case ANIMATEUR_IA = 'Animateur atelier intelligence artificielle';
    case COIFFURE = 'Atelier coiffure';
    case CUISINE = 'Atelier Cuisine';
    case COUTURE = 'Atelier Couture';
    case REPARATION_ELECTRONIQUE = "Atelier Réparation d'appareils électroniques";
    case PATISSERIE = 'Atelier Pâtisserie';
    case ELECTRICITE_DOMESTIQUE = 'Atelier Électricité domestique';
    case AIDE_SOIGNANT = 'Atelier Aide-soignant';

    public static function values(): array
    {
        return array_map(fn($c) => $c->value, self::cases());
    }
}
