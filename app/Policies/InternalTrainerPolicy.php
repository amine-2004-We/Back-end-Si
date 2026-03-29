<?php

namespace App\Policies;

use App\Models\InternalTrainer;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class InternalTrainerPolicy
{
    private $roles = ['Assistante','Assistant','Assistant/Assistante','Admin SI'];
    public function before(User $user, string $ability)
    {
        if ($user->hasRole('Admin SI')) {
            return true;
        }
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole($this->roles);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, InternalTrainer $internalTrainer): bool
    {
        return $user->hasRole($this->roles);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole($this->roles);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, InternalTrainer $internalTrainer): bool
    {
        return $user->hasRole($this->roles);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, InternalTrainer $internalTrainer): bool
    {
        return $user->hasRole($this->roles);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, InternalTrainer $internalTrainer): bool
    {
        return $user->hasRole($this->roles);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, InternalTrainer $internalTrainer): bool
    {
        return $user->hasRole($this->roles);
    }
}
