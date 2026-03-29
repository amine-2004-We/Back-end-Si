<?php

namespace App\Enums;

/**
 * Enum representing the status of a Delivery Receipt.
 */
enum DeliveryReceiptStatusEnum: string
{
    case EN_COURS = 'En cours';
    case RECEPTIONNEE = 'Réceptionnée';
    case REFUSE_PARTIEL = 'Refusée partiellement';
    case REFUSE_TOTAL = 'Refusée totalement';

    /**
     * Returns all enum values as a simple array.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
