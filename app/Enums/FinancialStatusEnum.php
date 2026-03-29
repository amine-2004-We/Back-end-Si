<?php

namespace App\Enums;

enum FinancialStatusEnum: string
{
    case ENATTENTE='En attente';
    case ENCOURS='En cours';
    case CLOTUREE='Clôturée';
    case ANNULEE = 'Annulée';

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
