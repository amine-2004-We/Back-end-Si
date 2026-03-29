<?php

namespace App\Enums;


enum CandidateStatusEnum: string
{
   case Nouveau= 'Nouveau';
   case En_entretien= 'En entretien';
   case Rejeté= 'Rejeté';
   case Sélectionné= 'Sélectionné';


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