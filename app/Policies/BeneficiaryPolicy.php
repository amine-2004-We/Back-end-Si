<?php

namespace App\Policies;

use App\Constants\Role;
use App\Models\Beneficiary;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class BeneficiaryPolicy
{
    /**
     * Perform pre-authorization checks.
     */
    public function before(User $user, string $ability): ?bool
    {
        // 'Admin SI' has all permissions
        if ($user->hasRole(Role::ADMIN_SI)) {
            return true;
        }
        return null;
    }

    /**
     * Determine whether the user can view any models.
     * [cite_start]"Consulter": "Tous rôles terrain, direction des opérations et DAF pour assurance" [cite: 1]
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole([
            Role::EDUCATRICE,
            Role::ANIMATRICE,
            Role::ANIMATEUR,
            Role::ANIMATEUR_ANIMATRICE,
            Role::FACILITATEUR_FACILITATRICE,
            Role::FACILITATRICE,
            Role::FACILITATEUR,
            Role::SUPERVISEUR,
            Role::CHEF_DE_PROJET,
            Role::RESPONSABLE_REGIONAL,
            Role::RESPONSABLE_PEDAGOGIQUE_NATIONAL,
            Role::RESPONSABLE_PEDAGOGIQUE_LOCAL,
            Role::RESPONSABLE_OPERATIONNEL_REGIONAL,
            Role::RESPONSABLE_OPERATIONNEL_LOCAL,
            Role::DIRECTEUR_DES_OPERATIONS,
            Role::RESPONSABLE_TRESORERIE_ET_REGLEMENT,
            Role::RESPONSABLE_ADMINISTRATIF_ET_FINANCIER,
            Role::RESPONSABLE_TRESORERIE ,
            Role::ADMIN_SI,
            Role::VISITEUR
        ]);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Beneficiary $beneficiary): bool
    {
        return $this->viewAny($user);
    }

    /**
     * Determine whether the user can create models.
     * [cite_start]"Créer": "Éducatrice / Animatrice / Formateur / facilitatrice" [cite: 1]
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole([
            Role::EDUCATRICE,
            Role::ANIMATRICE,
            Role::FACILITATRICE,
            Role::FACILITATEUR,
            Role::ADMIN_SI,
            Role::VISITEUR
        ]);
    }

    /**
     * Determine whether the user can update the model.
     * [cite_start]"Modifier": "Superviseur / Chef de projet" [cite: 1]
     */
    public function update(User $user, Beneficiary $beneficiary): bool
    {
        return $user->hasAnyRole([
            Role::RESPONSABLE_PEDAGOGIQUE_LOCAL,
            Role::RESPONSABLE_OPERATIONNEL_LOCAL,
            Role::RESPONSABLE_OPERATIONNEL_REGIONAL,
            Role::CHEF_DE_PROJET,
            Role::SUPERVISEUR,
            Role::ADMIN_SI,
            Role::VISITEUR
        ]);
    }

    /**
     * Determine whether the user can validate the model.
     * [cite_start]"Valider": "Responsables Régionaux" [cite: 1]
     */
    public function validateBeneficiary(User $user, Beneficiary $beneficiary): bool
    {
        return $user->hasAnyRole([
            Role::RESPONSABLE_PEDAGOGIQUE_LOCAL,
            Role::RESPONSABLE_OPERATIONNEL_LOCAL,
            Role::RESPONSABLE_OPERATIONNEL_REGIONAL,
            Role::SUPERVISEUR,
            Role::CHEF_DE_PROJET,
            Role::VISITEUR
        ]);
    }

    /**
     * Determine whether the user can delete the model.
     * No specific rule, so it aligns with the more restrictive 'update' permission.
     */
    public function delete(User $user, Beneficiary $beneficiary): bool
    {
        return $this->update($user, $beneficiary);
    }

    /**
     * Determine whether user can perform bulk updates.
     * Aligns with 'update' permissions.
     */
    public function massUpdate(User $user): bool
    {
        return $user->hasAnyRole([
            Role::RESPONSABLE_PEDAGOGIQUE_LOCAL,
            Role::RESPONSABLE_OPERATIONNEL_LOCAL,
            Role::RESPONSABLE_OPERATIONNEL_REGIONAL,
            Role::SUPERVISEUR,
            Role::ADMIN_SI,
            Role::VISITEUR
        ]);
    }

    /**
     * Determine whether the user can restore the model.
     * Aligns with 'update' permissions.
     */
    public function restore(User $user, Beneficiary $beneficiary): bool
    {
        return $this->update($user, $beneficiary);
    }

    /**
     * Determine whether the user can permanently delete the model.
     * Aligns with 'update' permissions.
     */
    public function forceDelete(User $user, Beneficiary $beneficiary): bool
    {
        return $this->update($user, $beneficiary);
    }
}
