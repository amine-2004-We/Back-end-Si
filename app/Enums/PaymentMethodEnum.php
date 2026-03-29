<?php

namespace App\Enums;

enum PaymentMethodEnum: string
{
    case VIREMENT='Virement';
    case CHEQUE='Chèque';
    case ESPECES='Espèces';

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
