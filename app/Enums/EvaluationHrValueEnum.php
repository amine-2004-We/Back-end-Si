<?php

namespace App\Enums;

use Illuminate\Database\Eloquent\SoftDeletes;

enum EvaluationHrValueEnum:string
{
    case TRESBIEN = 'Très Bien';
    case CONVENABLE = 'Convenable';
    case AMELIORER = 'À améliorer';
    case INSUFFISANT = 'Insuffisant';
    case DEFAILLANT = 'Défaillant';

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
