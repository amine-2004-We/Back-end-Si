<?php

namespace App\Policies;

use App\Models\EvaluationCriteriaModel;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class EvaluationCriteriaModelPolicy
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
            'Responsable Partenariat',
            'Évaluateurs',
            'Chargé de Partenariat'.
            'Responsable partenariat et développement',
            'Direction Générale (DG)',
            'Admin SI'
        ]);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, EvaluationCriteriaModel $evaluationCriteriaModel): bool
    {
        return $user->hasRole([
            'Responsable Partenariat',
            'Évaluateurs',
            'Chargé de Partenariat'.
            'Responsable partenariat et développement',
            'Direction Générale (DG)',
            'Admin SI'
        ]);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole([
            'Responsable Partenariat',
            'Admin SI'
        ]);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, EvaluationCriteriaModel $evaluationCriteriaModel): bool
    {
        return $user->hasRole([
            'Responsable Partenariat',
            'Admin SI'
        ]);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, EvaluationCriteriaModel $evaluationCriteriaModel): bool
    {
        return $user->hasRole([
            'Responsable Partenariat',
            'Admin SI'
        ]);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, EvaluationCriteriaModel $evaluationCriteriaModel): bool
    {
        return $user->hasRole([
            'Responsable Partenariat',
            'Admin SI'
        ]);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, EvaluationCriteriaModel $evaluationCriteriaModel): bool
    {
        return $user->hasRole([
            'Responsable Partenariat',
            'Admin SI'
        ]);
    }
}
