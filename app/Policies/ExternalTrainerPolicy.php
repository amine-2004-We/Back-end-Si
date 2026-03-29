<?php

namespace App\Policies;

use App\Models\ExternalTrainer;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ExternalTrainerPolicy
{
    /**
     * Perform pre-authorization checks.
     *
     * @param \App\Models\User $user
     * @param string $ability
     * @return bool|null
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('Admin SI')) {
            return true;
        }
        return null;
    }

    /**
     * Determine whether the user can view any models.
     * "Qui peut consulter ?" -> ZA
     *
     * @param \App\Models\User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['ZA', 'Assistante', 'Admin SI']);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param \App\Models\User $user
     * @param \App\Models\ExternalTrainer $externalTrainer
     * @return bool
     */
    public function view(User $user, ExternalTrainer $externalTrainer): bool
    {
        return $this->viewAny($user);
    }

    /**
     * Determine whether the user can create models.
     * "Qui peut créer ?" -> Assistante
     *
     * @param \App\Models\User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['Assistante', 'Admin SI']);
    }

    /**
     * Determine whether the user can update the model.
     * "Qui peut modifier ?" -> Assistante
     *
     * @param \App\Models\User $user
     * @param \App\Models\ExternalTrainer $externalTrainer
     * @return bool
     */
    public function update(User $user, ExternalTrainer $externalTrainer): bool
    {
        return $user->hasAnyRole(['Assistante', 'Admin SI']);
    }

    /**
     * Determine whether the user can delete the model.
     * Aligns with update permissions as a safe default.
     *
     * @param \App\Models\User $user
     * @param \App\Models\ExternalTrainer $externalTrainer
     * @return bool
     */
    public function delete(User $user, ExternalTrainer $externalTrainer): bool
    {
        return $this->update($user, $externalTrainer);
    }

    /**
     * Determine whether user can perform bulk updates (like toggleActivation).
     *
     * @param \App\Models\User $user
     * @return bool
     */
    public function massUpdate(User $user): bool
    {
        return $user->hasAnyRole(['Assistante', 'Admin SI']);
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param \App\Models\User $user
     * @param \App\Models\ExternalTrainer $externalTrainer
     * @return bool
     */
    public function restore(User $user, ExternalTrainer $externalTrainer): bool
    {
        return $this->update($user, $externalTrainer);
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param \App\Models\User $user
     * @param \App\Models\ExternalTrainer $externalTrainer
     * @return bool
     */
    public function forceDelete(User $user, ExternalTrainer $externalTrainer): bool
    {
        return $this->update($user, $externalTrainer);
    }
}
