<?php

namespace App\Policies;

use App\Constants\Role;
use App\Models\Cycle;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CyclePolicy
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
    public function view(User $user, Cycle $cycle): bool
    {
        return $this->viewAny($user);
    }

    /**
     * Determine whether the user can create models.
     * "Créer": "Responsable pédagogique régional, chef de projet"
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
     * "Modifier": "Responsable régional / Responsable pédagogique national"
     */
    public function update(User $user, Cycle $cycle): bool
    {
        return $user->hasAnyRole([
            Role::RESPONSABLE_PEDAGOGIQUE_NATIONAL,
        ]);
    }

    /**
     * Determine whether the user can validate the model.
     * "Valider": "Responsable pédagogique national"
     */
    public function validateCycle(User $user, Cycle $cycle): bool
    {
        return $user->hasAnyRole([
            Role::RESPONSABLE_PEDAGOGIQUE_NATIONAL,

        ]);
    }

    /**
     * Determine whether the user can delete the model.
     * Aligns with the 'update' permission as a safe default.
     */
    public function delete(User $user, Cycle $cycle): bool
    {
        return $this->update($user, $cycle);
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
     * Aligns with 'update' permissions.
     */
    public function restore(User $user, Cycle $cycle): bool
    {
        return $this->update($user, $cycle);
    }

    /**
     * Determine whether the user can permanently delete the model.
     * Aligns with 'update' permissions.
     */
    public function forceDelete(User $user, Cycle $cycle): bool
    {
        return $this->update($user, $cycle);
    }
}
