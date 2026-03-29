<?php

namespace App\Enums;

enum ProspectionValidationStatusEnum: string
{
    // Base status
    case DRAFT = 'draft';
    
    // Initial pending statuses (only one pending per role type)
    case PENDING_SUPERVISOR = 'pending_supervisor';
    case PENDING_OPERATIONAL = 'pending_operational';
    case PENDING_NATIONAL_DIRECT = 'pending_national_direct';
    
    // Reviewed statuses (validation levels)
    case SUPERVISOR_REVIEWED = 'supervisor_reviewed';
    case OPERATIONAL_REVIEWED = 'operational_reviewed';
    case REGIONAL_REVIEWED = 'regional_reviewed';
    
    // Final status (same for all cases)
    case VALIDATED = 'validated';
    case REJECTED = 'rejected';
    
    /**
     * Get all valid statuses
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
    
    /**
     * Get status labels in French
     */
    public static function labels(): array
    {
        return [
            self::DRAFT->value => 'Brouillon',
            self::PENDING_SUPERVISOR->value => 'En attente - Superviseur',
            self::PENDING_OPERATIONAL->value => 'En attente - Responsable Opérationnel',
            self::PENDING_NATIONAL_DIRECT->value => 'En attente - Responsable National',
            self::SUPERVISOR_REVIEWED->value => '✓ Validée par Superviseur',
            self::OPERATIONAL_REVIEWED->value => '✓ Validée par Responsable Opérationnel',
            self::REGIONAL_REVIEWED->value => '✓ Validée par Responsable Régional',
            self::VALIDATED->value => '✓ Validée (Finale)',
            self::REJECTED->value => '✗ Rejetée',
        ];
    }
    
    /**
     * Get label for a specific status
     */
    public static function getLabel(string $status): string
    {
        return self::labels()[$status] ?? $status;
    }
}
