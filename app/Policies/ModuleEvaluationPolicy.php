<?php

namespace App\Policies;

use App\Models\ModuleEvaluation;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ModuleEvaluationPolicy
{
    /**
     * Grant all permissions to Admin SI.
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
        // Allow users with these roles to see the list of evaluations.
        return $user->hasAnyRole(['Admin SI', 'Responsable de formation', 'Formateur']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ModuleEvaluation $moduleEvaluation): bool
    {
        // Allow users to view an evaluation if they have a general viewing role.
        // You could add more specific logic here, e.g., if they are the trainer or participant.
        return $user->hasAnyRole(['Admin SI', 'Responsable de formation', 'Formateur']);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['Admin SI', 'Responsable de formation']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ModuleEvaluation $moduleEvaluation): bool
    {
        return $user->hasAnyRole(['Admin SI', 'Responsable de formation']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ModuleEvaluation $moduleEvaluation): bool
    {
        return $user->hasRole('Admin SI');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ModuleEvaluation $moduleEvaluation): bool
    {
        return $user->hasRole('Admin SI');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ModuleEvaluation $moduleEvaluation): bool
    {
        return $user->hasRole('Admin SI');
    }
}
