<?php

namespace App\Enums;

enum GridStatusEnum: string
{
    case BROUILLON = 'Brouillon';
    case VALIDEE = 'Validée';
    case ARCHIVEE = 'Archivée';

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
