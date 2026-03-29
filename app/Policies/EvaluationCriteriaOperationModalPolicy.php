<?php

namespace App\Policies;

use App\Models\EvaluationCriteriaOperationModal;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class EvaluationCriteriaOperationModalPolicy
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
            'Direction Générale (DG)',
            'Admin SI'
        ]);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, EvaluationCriteriaOperationModal $evaluationCriteriaOperationModal): bool
    {
        return $user->hasRole([
            'Responsable Partenariat',
            'Évaluateurs',
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
    public function update(User $user, EvaluationCriteriaOperationModal $evaluationCriteriaOperationModal): bool
    {
        return $user->hasRole([
            'Responsable Partenariat',
            'Admin SI'
        ]);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, EvaluationCriteriaOperationModal $evaluationCriteriaOperationModal): bool
    {
        return $user->hasRole([
            'Responsable Partenariat',
            'Admin SI'
        ]);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, EvaluationCriteriaOperationModal $evaluationCriteriaOperationModal): bool
    {
        return $user->hasRole([
            'Responsable Partenariat',
            'Admin SI'
        ]);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, EvaluationCriteriaOperationModal $evaluationCriteriaOperationModal): bool
    {
        return $user->hasRole([
            'Responsable Partenariat',
            'Admin SI'
        ]);
    }
}
