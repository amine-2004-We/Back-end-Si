<?php

namespace App\Policies;

use App\Constants\Role;
use App\Models\Cheque;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ChequePolicy
{
    /**
     * Perform pre-authorization checks.
     * Give 'Admin SI' all permissions.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole(Role::ADMIN_SI)) {
            return true;
        }
        return null;
    }

    /**
     * "Qui peut consulter ?": Tous
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * "Qui peut consulter ?": Tous
     */
    public function view(User $user, Cheque $cheque): bool
    {
        return true;
    }

    /**
     * "Qui peut créer ?": Trésorerie, Comptabilité
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole([Role::TRESORERIE]);
    }

    /**
     * "Qui peut modifier ?": Trésorerie, Comptabilité / Finance
     */
    public function update(User $user, Cheque $cheque): bool
    {
        return $user->hasAnyRole([Role::TRESORERIE]);
    }

    /**
     * Custom policy for "Qui peut valider ?": Direction financière
     */
    public function validate(User $user, Cheque $cheque): bool
    {
        return $user->hasRole(Role::DIRECTION_FINANCIERE);
    }

    /**
     * Delete permission is aligned with update permission as a safe default.
     */
    public function delete(User $user, Cheque $cheque): bool
    {
        return $this->update($user, $cheque);
    }

    /**
     * Permission for bulk actions like toggle activation.
     */
    public function massUpdate(User $user): bool
    {
        return $this->update(new User(), new Cheque());
    }

    /**
     * Restore permission is aligned with update permission.
     */
    public function restore(User $user, Cheque $cheque): bool
    {
        return $this->update($user, $cheque);
    }

    /**
     * Force delete permission is aligned with update permission.
     */
    public function forceDelete(User $user, Cheque $cheque): bool
    {
        return $this->update($user, $cheque);
    }
}
