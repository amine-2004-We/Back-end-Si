<?php

namespace App\Policies;

use App\Models\TrainingSession;
use App\Models\User;

class TrainingSessionPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('Admin SI')) {
            return true;
        }
        return null;
    }

    public function viewAny(User $user): bool
    {
        // FIX: Replaced role names containing slashes with simplified versions.
        return $user->hasAnyRole(['Formateur', 'Superviseur', 'Responsable pédagogique régional ou national', 'Direction des Opérations']);
    }
    
    public function view(User $user, TrainingSession $trainingSession): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        // FIX: Replaced role name containing slashes with a simplified version.
        return $user->hasRole('Formateur');
    }

    public function update(User $user, TrainingSession $trainingSession): bool
    {
        // FIX: Replaced role name containing slashes with a simplified version.
        return $user->hasRole('Superviseur');
    }
    
    /**
     * Determine whether the user can perform bulk actions.
     * Aligns with the 'update' permission.
     */
    public function massUpdate(User $user): bool
    {
        // FIX: Replaced role name containing slashes with a simplified version.
        return $user->hasRole('Superviseur');
    }
}
