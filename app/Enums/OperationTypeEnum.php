<?php

namespace App\Enums;

/**
 * Class OperationTypeEnum
 *
 * Enum representing types of operations.
 *
 * @method static OperationTypeEnum ALIMENTATION()
 * @method static OperationTypeEnum DEPENSE()
 * @method static OperationTypeEnum PV_CAISSE()
 */
enum OperationTypeEnum: string
{
    case ALIMENTATION = 'Alimentation';
    case DEPENSE = 'Dépense';
    case PV_CAISSE = 'Pv de caisse';

    /**
     * Returns all enum cases as an array of options.
     *
     * @return array<int, array<string, string>> Array of options
     */
    public static function options(): array
    {
        return array_map(fn(self $case) => [
            'value' => $case->value,
        ], self::cases());
    }

    /**
     * Returns all enum values as a simple array.
     *
     * @return string[] Array of values
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
