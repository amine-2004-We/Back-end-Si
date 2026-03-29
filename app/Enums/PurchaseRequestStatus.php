<?php

namespace App\Enums;

enum PurchaseRequestStatus: string
{
    case Draft = 'draft';
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match($this) {
            self::Draft => 'Brouillon',
            self::Pending => 'À valider',
            self::Approved => 'Validée',
            self::Rejected => 'Rejetée',
        };
    }
}
