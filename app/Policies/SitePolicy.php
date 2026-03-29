<?php

namespace App\Policies;

use App\Constants\Role;
use App\Models\Site;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SitePolicy
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
            Role::CHEF_DE_PROJET,
            Role::RESPONSABLE_REGIONAL,
            Role::DIRECTEUR_DES_OPERATIONS,
            Role::RESPONSABLE_OPERATIONNEL_NATIONAL,
            Role::ADMIN_SI,
        ]);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Site $site): bool
    {
        return $this->viewAny($user);
    }

    /**
     * Determine whether the user can create models.
     * "Créer": "Responsable régional, Responsable opérationnel, chef de projet"
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole([
            Role::CHEF_DE_PROJET,
            Role::RESPONSABLE_REGIONAL,
            Role::ADMIN_SI,
        ]);
    }

    /**
     * Determine whether the user can update the model.
     * "Modifier": "Responsable régional / Responsable opérationnel national"
     */
    public function update(User $user, Site $site): bool
    {
        return $user->hasAnyRole([
            Role::CHEF_DE_PROJET,
            Role::RESPONSABLE_REGIONAL,
            Role::DIRECTEUR_DES_OPERATIONS,
            Role::ADMIN_SI

        ]);
    }

    /**
     * Determine whether the user can validate the model.
     * "Valider": "Responsable opérationnel national"
     */
    public function validateSite(User $user, Site $site): bool
    {
        return $user->hasAnyRole([
            Role::RESPONSABLE_OPERATIONNEL_NATIONAL,
        ]);
    }

    /**
     * Determine whether the user can delete the model.
     * Aligns with the more restrictive 'update' permission as a safe default.
     */
    public function delete(User $user, Site $site): bool
    {
        return $this->update($user, $site);
    }

    /**
     * Determine whether user can perform bulk actions.
     * Aligns with the 'update' permission.
     */
    public function massUpdate(User $user): bool
    {
        return $user->hasAnyRole([
            Role::CHEF_DE_PROJET,
            Role::RESPONSABLE_REGIONAL,
            Role::DIRECTEUR_DES_OPERATIONS,
            Role::ADMIN_SI
        ]);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Site $site): bool
    {
        return $this->update($user, $site);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Site $site): bool
    {
        return $this->update($user, $site);
    }
}
