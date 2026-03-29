<?php

namespace App\Enums;

/**
 * Enum representing types of financial resources.
 *
 * @method static FinancialResourcesTypeEnum SUBVENTION()
 * @method static FinancialResourcesTypeEnum DON()
 */
enum FinancialResourcesTypeEnum: string
{
    case SUBVENTION = 'Subvention';
    case DON = 'Don';

    /**
     * Get all enum cases as an array of options.
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
     * Get all enum values as a simple array.
     *
     * @return string[] Array of values
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
