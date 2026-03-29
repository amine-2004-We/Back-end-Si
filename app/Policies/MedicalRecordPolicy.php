<?php

namespace App\Policies;

use App\Models\MedicalRecord;
use App\Models\User;

class MedicalRecordPolicy
{
    /**
     * Perform pre-authorization checks.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('Admin SI')) {
            return true;
        }
        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasRole([
            'RH (Ressources Humaines)',
            'Médecin du travail',
            'Admin SI',
        ]);
    }

    public function view(User $user, MedicalRecord $medicalRecord): bool
    {
        return $user->hasRole([
            'RH (Ressources Humaines)',
            'Médecin du travail',
            'Admin SI',
        ]);
    }


    public function create(User $user): bool
    {
        return $user->hasRole([
            'RH (Ressources Humaines)',
            'Admin SI',
        ]);
    }
    public function update(User $user, MedicalRecord $medicalRecord): bool
    {
        return $user->hasRole([
            'RH (Ressources Humaines)',
            'Admin SI',
        ]);
    }


    public function delete(User $user, MedicalRecord $medicalRecord): bool
    {
        return $user->id === $medicalRecord->user_id || $user->hasRole([
                'RH (Ressources Humaines)',
                'Admin SI',
            ]);
    }

    public function restore(User $user, MedicalRecord $medicalRecord): bool
    {
        return $user->hasRole(['RH (Ressources Humaines)', 'Admin SI',]);
    }

    public function approve(User $user, MedicalRecord $medicalRecord): bool
    {
        return $user->hasRole([
            'RH (Ressources Humaines)',
            'Admin SI',
        ]);
    }
}
