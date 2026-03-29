<?php

namespace App\Policies;

use App\Constants\Role;
use App\Models\Level;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class LevelPolicy
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
     * "Consulter": "Tous rôles terrain et direction des opérations"
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole([
            Role::RESPONSABLE_PEDAGOGIQUE_NATIONAL,
            Role::RESPONSABLE_PEDAGOGIQUE_LOCAL,
            Role::RESPONSABLE_PEDAGOGIQUE_REGIONAL,
            Role::RESPONSABLE_PEDAGOGIQUE,
            Role::RESPONSABLE_OPERATIONNEL_REGIONAL,
            Role::RESPONSABLE_OPERATIONNEL_LOCAL,
            Role::RESPONSABLE_OPERATIONNEL_NATIONAL,
            Role::CHEF_DE_PROJET ,
            Role::ADMIN_SI
        ]);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Level $level): bool
    {
        return $this->viewAny($user);
    }

    /**
     * Determine whether the user can create models.
     * "Créer": "Superviseur, chef de projet"
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole([
            Role::RESPONSABLE_PEDAGOGIQUE_NATIONAL,
            Role::RESPONSABLE_PEDAGOGIQUE_LOCAL,
            Role::CHEF_DE_PROJET ,
        ]);
    }

    /**
     * Determine whether the user can update the model.
     * "Modifier": "Responsable pédagogique / Responsable pédagogique national"
     */
    public function update(User $user, Level $level): bool
    {
        return $user->hasAnyRole([
            Role::RESPONSABLE_PEDAGOGIQUE_NATIONAL,
        ]);
    }

    /**
     * Determine whether the user can validate the model.
     * "Valider": "Responsable régional / Responsable pédagogique national"
     */
    public function validateLevel(User $user, Level $level): bool
    {
        return $user->hasAnyRole([
            Role::RESPONSABLE_PEDAGOGIQUE_NATIONAL,
        ]);
    }

    /**
     * Determine whether the user can delete the model.
     * Aligns with the 'update' permission as a safe default.
     */
    public function delete(User $user, Level $level): bool
    {
        return $this->update($user, $level);
    }

    /**
     * Determine whether user can perform bulk actions.
     * Aligns with the 'update' permission.
     */
    public function massUpdate(User $user): bool
    {
        return $user->hasAnyRole([
            Role::RESPONSABLE_PEDAGOGIQUE_NATIONAL,
        ]);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Level $level): bool
    {
        return $this->update($user, $level);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Level $level): bool
    {
        return $this->update($user, $level);
    }
}
