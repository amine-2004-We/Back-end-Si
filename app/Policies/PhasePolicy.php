<?php

namespace App\Policies;

use App\Constants\Role;
use App\Models\Phase;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PhasePolicy
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
     * "Qui peut consulter ?"
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole([
            Role::RESPONSABLE_PARTENARIAT,
            Role::CHEF_DE_PROJET,
            Role::FINANCE,
            Role::CHARGE_DE_PARTENARIAT,
            Role::RESPONSABLE_PARTENARIAT_ET_DEVELOPPEMENT,
            Role::ADMIN_SI,
        ]);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Phase $phase): bool
    {
        return $this->viewAny($user);
    }

    /**
     * Determine whether the user can create models.
     * "Qui peut créer ?"
     */
    public function create(User $user): bool
    {
        return $user->hasRole([
            Role::RESPONSABLE_OPERATIONNEL,
            Role::ADMIN_SI
        ]);
    }

    /**
     * Determine whether the user can update the model.
     * "Qui peut modifier ?"
     */
    public function update(User $user, Phase $phase): bool
    {
        return $user->hasRole([
            Role::RESPONSABLE_OPERATIONNEL,
            Role::ADMIN_SI
        ]);
    }

    /**
     * Determine whether the user can validate the model.
     * "Qui peut valider ?"
     */
    public function validatePhase(User $user, Phase $phase): bool
    {
        return $user->hasRole([
            Role::RESPONSABLE_OPERATIONNEL,
            Role::ADMIN_SI
        ]);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Phase $phase): bool
    {
        return $this->update($user, $phase);
    }

    /**
     * Determine whether user can perform bulk updates (like toggleActivation).
     */
    public function massUpdate(User $user): bool
    {
        return $user->hasRole([
            Role::RESPONSABLE_OPERATIONNEL,
            Role::ADMIN_SI
        ]);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Phase $phase): bool
    {
        return $this->update($user, $phase);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Phase $phase): bool
    {
        return $this->update($user, $phase);
    }
}
