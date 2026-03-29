<?php

namespace App\Policies;

use App\Constants\Role;  
use App\Models\Avenant;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AvenantPolicy
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
     * Roles: Achat, demandeur, Finance, DG
     */
    public function viewAny(User $user): bool
    {
         return $user->hasAnyRole([
            Role::CHARGE_ACHATS,
            Role::RESPONSABLE_ACHATS,
            // Role::DEMANDEUR,  
            Role::FINANCE,
            Role::DG,
        ]);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Avenant $avenant): bool
    {
        return $this->viewAny($user);
    }

    /**
     * Determine whether the user can create models.
     * Roles: Chargé achats / Responsable achats
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole([
            Role::CHARGE_ACHATS,
            Role::RESPONSABLE_ACHATS,
        ]);
    }

    /**
     * Determine whether the user can update the model.
     * Roles: Responsable achats
     */
    public function update(User $user, Avenant $avenant): bool
    {
        return $user->hasRole(Role::RESPONSABLE_ACHATS);
    }

    /**
     * Determine whether the user can delete the model.
     * Aligned with 'update' permission for safety.
     */
    public function delete(User $user, Avenant $avenant): bool
    {
        return $this->update($user, $avenant);
    }

    /**
     * Determine whether the user can bulk delete models.
     * Aligns with the 'update' permission.
     */
    public function bulkDelete(User $user): bool
    {
         return $user->hasRole(Role::RESPONSABLE_ACHATS);
    }

    /**
     * Determine whether the user can restore the model.
     * Aligned with 'update' permission.
     */
    public function restore(User $user, Avenant $avenant): bool
    {
        return $this->update($user, $avenant);
    }

    /**
     * Determine whether the user can permanently delete the model.
     * Aligned with 'update' permission.
     */
    public function forceDelete(User $user, Avenant $avenant): bool
    {
        return $this->update($user, $avenant);
    }
}

