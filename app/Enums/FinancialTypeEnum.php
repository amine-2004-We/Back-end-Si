<?php

namespace App\Enums;

/**
 * Enum representing types of financial support.
 *
 * @method static FinancialTypeEnum SUBVENTION()
 * @method static FinancialTypeEnum DIRECTE()
 * @method static FinancialTypeEnum APPELAPROJET()
 */
enum FinancialTypeEnum: string
{
    case SUBVENTION = 'Subvention';
    case DIRECTE = 'Directe';
    case APPELAPROJET = 'Appel à projets';

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
    public function value(): array
    {
        return array_column(self::cases(), 'value');
    }
}
