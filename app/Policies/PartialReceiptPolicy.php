<?php

namespace App\Policies;

use App\Constants\Role;
use App\Models\PartialReceipt;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PartialReceiptPolicy
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
     * Based on Avenant logic: Achat, demandeur, Finance, DG
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole([
            Role::CHARGE_ACHAT,
            Role::RESPONSABLE_ACHAT,
            Role::DIRECTEUR_GENERAL,
        ]);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PartialReceipt $partialReceipt): bool
    {
        return $this->viewAny($user);
    }

    /**
     * Determine whether the user can create models.
     * The "Réceptionnaire" is a collaborator, likely from Achats or a Demogitndeur
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole([
            Role::CHARGE_ACHAT,
            Role::RESPONSABLE_ACHAT,
        ]);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PartialReceipt $partialReceipt): bool
    {
        return $user->hasAnyRole([
            Role::CHARGE_ACHAT,
            Role::RESPONSABLE_ACHAT,
        ]);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PartialReceipt $partialReceipt): bool
    {
        return $user->hasRole(Role::RESPONSABLE_ACHAT);
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function bulkDelete(User $user): bool
    {
        return $user->hasRole(Role::RESPONSABLE_ACHAT);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, PartialReceipt $partialReceipt): bool
    {
        return $this->delete($user, $partialReceipt);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, PartialReceipt $partialReceipt): bool
    {
        return $this->delete($user, $partialReceipt);
    }
}
