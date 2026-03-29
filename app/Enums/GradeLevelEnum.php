<?php

namespace App\Enums;

enum GradeLevelEnum: string
{
    case GS = 'GS';
    case MS = 'MS';
    case CP = 'CP';
    case CE1 = 'CE1';
    case CE2 = 'CE2';
    case CM1 = 'CM1';
    case CM2 = 'CM2';

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
