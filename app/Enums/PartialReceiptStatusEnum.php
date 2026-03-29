<?php

namespace App\Enums;

enum PartialReceiptStatusEnum: string
{
    case COMPLETED = 'Complété';
    case PENDING_COMPLEMENT = 'En attente complément';
    case CLOSED = 'Clos';

    /**
     * Get all enum values.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
