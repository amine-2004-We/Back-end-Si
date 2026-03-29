<?php

namespace App\Enums;

enum ClassStateEnum: string
{
    case CREATION = 'Création';
    case TRANSFER = 'Transfert';
    case RELOCATION = 'Relocalisation';

    public function label(): string
    {
        return match($this) {
            self::CREATION => 'Création',
            self::TRANSFER => 'Transfert',
            self::RELOCATION => 'Relocalisation',
        };
    }

    public static function values(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }
}
