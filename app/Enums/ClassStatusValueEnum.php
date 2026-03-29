<?php

namespace App\Enums;

enum ClassStatusValueEnum: string
{
    case OPERATIONAL = 'Opérationnel';
    case STOPPED = 'En arrêt';
    case TERMINATED = 'Résilié';
    case CLOSED = 'Clôturé';
    case PERPETUATED = 'Pérennisé';

    public function label(): string
    {
        return match($this) {
            self::OPERATIONAL => 'Opérationnel',
            self::STOPPED => 'En arrêt',
            self::TERMINATED => 'Résilié',
            self::CLOSED => 'Clôturé',
            self::PERPETUATED => 'Pérennisé',
        };
    }

    public static function values(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }
}
