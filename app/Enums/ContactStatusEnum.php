<?php

namespace App\Enums;

enum ContactStatusEnum:string
{
    case ABONNE = 'Abonné';
    case DESABONNE='Désabonné';
    case RECONTACTER='À recontacter';
    case INACTIF='Inactif';
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
