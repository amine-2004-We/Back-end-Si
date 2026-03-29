<?php

namespace App\Enums;

enum TrainingModuleFormat: string
{
    case IN_PERSON = 'in_person'; 
    case REMOTE    = 'remote';   
    case HYBRID    = 'hybrid';      
    public function label(): string
    {
        return match ($this) {
            self::IN_PERSON => 'En présentiel',
            self::REMOTE    => 'À distance',
            self::HYBRID    => 'Hybride',
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
