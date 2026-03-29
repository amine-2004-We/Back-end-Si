<?php

namespace App\Enums;

enum TrainingModuleStatus: string
{
    case PLANNED     = 'planned';      
    case IN_PROGRESS = 'in_progress';   
    case COMPLETED   = 'completed';    
    case CANCELLED   = 'cancelled';    

    public function label(): string
    {
        return match ($this) {
            self::PLANNED     => 'Prévu',
            self::IN_PROGRESS => 'En cours',
            self::COMPLETED   => 'Terminé',
            self::CANCELLED   => 'Annulé',
        };
    }

    /** @return string[] */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /** @return array<string,string> value => label */
    public static function options(): array
    {
        $out = [];
        foreach (self::cases() as $c) {
            $out[$c->value] = $c->label();
        }
        return $out;
    }
}
