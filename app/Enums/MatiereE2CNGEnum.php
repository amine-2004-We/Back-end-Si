<?php

namespace App\Enums;

enum MatiereE2CNGEnum: string
{
    case ARABE = 'Arabe';
    case FRANCAIS = 'Français';
    case ANGLAIS = 'Anglais';
    case EDUCATION_ISLAMIQUE = 'Éducation islamique';
    case MATHEMATIQUES = 'Mathématiques';
    case PHYSIQUE_CHIMIE = 'Physique et Chimie';
    case SVT = 'Sciences de la vie et de la Terre (SVT)';
    case HISTOIRE_GEOGRAPHIE = 'Histoire et Géographie';
    case ACTIVITES_PARASCOLAIRES = 'Activités parascolaires';

    public function labelArabe(): string
    {
        return match($this) {
            self::ARABE => 'اللغة العربية',
            self::FRANCAIS => 'اللغة الفرنسية',
            self::ANGLAIS => 'اللغة الإنجليزية',
            self::EDUCATION_ISLAMIQUE => 'التربية الإسلامية',
            self::MATHEMATIQUES => 'الرياضيات',
            self::PHYSIQUE_CHIMIE => 'الفيزياء والكمياء',
            self::SVT => 'علوم الحياة والأرض',
            self::HISTOIRE_GEOGRAPHIE => 'التاريخ والجغرافية',
            self::ACTIVITES_PARASCOLAIRES => 'أنشطة موازية',
        };
    }

    public static function options(): array
    {
        return array_map(fn($case) => [
            'value' => $case->value,
            'label' => $case->value,
            'label_ar' => $case->labelArabe(),
        ], self::cases());
    }
}