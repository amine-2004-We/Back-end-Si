<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Leave;

class LeavePolicy
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
        return true;
    }

    public function view(User $user, Leave $leave): bool
    {
        return true;
    }


    public function create(User $user): bool
    {
        return true;
    }
    public function update(User $user, Leave $leave): bool
    {
        return $user->hasRole([
            'RH (Ressources Humaines)',
            'Admin SI',
        ]);
    }


    public function delete(User $user, Leave $leave): bool
    {
        return $user->id === $leave->user_id || $user->hasRole([
                'RH (Ressources Humaines)',
                'Admin SI',
            ]);
    }

    public function restore(User $user, Leave $leave): bool
    {
        return $user->hasRole(['RH (Ressources Humaines)', 'Admin SI',]);
    }

    public function approve(User $user, Leave $leave): bool
    {
        // Admin SI peut toujours valider
        if ($user->hasRole('Admin SI')) {
            return true;
        }

        // Supérieur hiérarchique peut valider si le status est 'pending'
        if ($leave->status === 'pending' && $user->id === $leave->collaborator->superior->user_id) {
            return true;
        }

        // RH peut valider si le status est 'approved_by_manager'
        if ($leave->status === 'approved_by_manager' && $user->hasRole('RH (Ressources Humaines)')) {
            return true;
        }

        return false;
    }
}
