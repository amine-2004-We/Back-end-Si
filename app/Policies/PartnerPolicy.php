<?php

namespace App\Policies;

use App\Constants\Department;
use App\Constants\Role;
use App\Models\Partner;
use App\Models\PartnerNote;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 *
 */
class PartnerPolicy
{

    /**
     * @param User $user
     * @param string $ability
     * @return bool|null
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole(Role::ADMIN_SI)) {
            return true;
        }
        return null;
    }

    /**
     * Autorise la consultation (voir la liste)
     */
    public function viewAny(User $user): bool
    {
        if ($user->hasRole(Role::ADMIN_SI)) {
            return true;
        }
         return $user->belongsToPartnershipDept();
    }

    /**
     *  Autorise la consultation (voir un partenaire)
     */
    public function view(User $user, Partner $partner): bool
    {
        if ($user->hasRole(Role::ADMIN_SI)) {
            return true;
        }
        return $user->belongsToPartnershipDept();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole([
            Role::CHARGE_DE_PARTENARIAT,
            Role::ADMIN_SI,
        ]);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Partner $partner): bool
    {
        return $user->hasAnyRole([
            Role::RESPONSABLE_PARTENARIAT,
            Role::ADMIN_SI,
        ]);
    }

    /**
     * @param User $user
     * @param Partner $partner
     * @return bool
     */
    public function validate(User $user, Partner $partner): bool
    {
        return $user->hasAnyRole([
            Role::RESPONSABLE_PARTENARIAT,
            Role::ADMIN_SI,
        ]);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Partner $partner): bool
    {
        return $this->update($user, $partner);
    }

    /**
     * Determine whether user can perform bulk actions.
     * Aligns with the 'update' permission.
     */
    public function massUpdate(User $user): bool
    {
        return $user->hasAnyRole([
            Role::RESPONSABLE_PARTENARIAT,
            Role::ADMIN_SI,
        ]);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Partner $partner): bool
    {
        return $this->update($user, $partner);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Partner $partner): bool
    {
        return $this->update($user, $partner);
    }

    /**
     * Détermine si l'utilisateur peut ajouter une note.
     *
     * @param User $user
     * @param Partner $partner
     * @return bool
     */
    public function addNote(User $user, Partner $partner): bool
    {
        return $this->view($user, $partner);
    }

    /**
     * Détermine si l'utilisateur peut supprimer une note spécifique.
     */
    public function deleteNote(User $user, PartnerNote $note): bool
    {
        return $this->update($user, $note->partner);
    }

}
