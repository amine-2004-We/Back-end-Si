<?php

namespace App\Policies;

use App\Models\EvaluationOperation;
use App\Models\User;

class EvaluationOperationPolicy
{

    /**
     * Perform pre-authorization checks.
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
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole([
            'Éducatrice',
            'Animatrice',
            'Facilitatrice',
            'Admin SI',
            'Superviseur',
            'Responsable pédagogique',
            'Admin SI'
        ]);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user,  EvaluationOperation $evaluationOperation): bool
    {
        return $user->hasRole([
            'Éducatrice',
            'Animatrice',
            'Facilitatrice',
            'Admin SI',
            'Superviseur',
            'Responsable pédagogique',
            'Admin SI'
        ]);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole([
            'Éducatrice',
            'Animatrice',
            'Facilitatrice',
            'Superviseur',
            'Responsable pédagogique',
            'Admin SI'
        ]);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user,  EvaluationOperation $evaluationOperation): bool
    {
        return $user->hasRole([
            'Superviseur',
            'Responsable pédagogique',
            'Admin SI'
        ]);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, EvaluationOperation $evaluationOperation): bool
    {
        return $user->hasRole([
            'Superviseur',
            'Responsable pédagogique',
            'Admin SI'
        ]);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, EvaluationOperation $evaluationOperation): bool
    {
        return $user->hasRole([
            'Superviseur',
            'Responsable pédagogique',
            'Admin SI'
        ]);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, EvaluationOperation $evaluationOperation): bool
    {
        return $user->hasRole([
            'Superviseur',
            'Responsable pédagogique',
            'Admin SI'
        ]);
    }
}
