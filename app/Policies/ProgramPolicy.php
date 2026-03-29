<?php

namespace App\Policies;

use App\Constants\Role;
use App\Models\Program;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProgramPolicy
{
    /**
     * Perform pre-authorization checks.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole(Role::ADMIN_SI)) {
            return true;
        }
        return null;
    }

    /**
     * Determine whether the user can view any models.
     * "Consulter": "Tous rôles"
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Program $program): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     * "Créer": "Cf. Processus Partenariat" - Assuming high-level operational and project roles.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole([
            'Chef de projet',
            'Direction',
            'Responsable Opérationnel National',
            'Admin SI',
        ]);
    }

    /**
     * Determine whether the user can update the model.
     * "Modifier": "Cf. Processus Partenariat" - Assuming the same roles as creation.
     */
    public function update(User $user, Program $program): bool
    {
        return $this->create($user);
    }

    /**
     * Determine whether the user can delete the model.
     * Aligns with the restrictive 'update' permission as a safe default.
     */
    public function delete(User $user, Program $program): bool
    {
        return $this->update($user, $program);
    }

    /**
     * Determine whether user can perform bulk actions like toggle activation.
     * Aligns with the 'update' permission.
     */
    public function massUpdate(User $user): bool
    {
        // Re-using the logic from 'create' since no model instance is passed.
        return $this->create($user);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Program $program): bool
    {
        return $this->update($user, $program);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Program $program): bool
    {
        return $this->update($user, $program);
    }
}
