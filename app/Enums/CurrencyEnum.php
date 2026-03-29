<?php

namespace App\Enums;

/**
 * Class CurrencyEnum
 *
 * Enum representing supported currencies.
 *
 * @method static CurrencyEnum MAD()
 * @method static CurrencyEnum USD()
 * @method static CurrencyEnum EUR()
 */
enum CurrencyEnum: string
{
    case MAD = 'MAD';
    case USD = 'USD';
    case EUR = 'EUR';

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
