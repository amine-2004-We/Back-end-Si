<?php

namespace App\Policies;

use App\Models\Report;
use App\Models\User;


class ReportPolicy
{
    /**
     * Determine whether the user can view any reports (index).
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['Superviseur', 'Pédagogie', 'Direction',  'Admin SI']);
    }

    /**
     * Determine whether the user can view a specific report (show).
     */
    public function view(User $user, Report $report): bool
    {
        return $user->hasAnyRole(['Superviseur', 'Pédagogie', 'Direction',  'Admin SI']);
    }

    /**
     * Determine whether the user can create reports (store).
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['Éducatrice', 'Superviseur', 'Admin SI']);
    }

    /**
     * Determine whether the user can update the report.
     */
   public function update(User $user, Report $report): bool
{
    \Log::info('Policy check', [
        'user_id' => $user->id,
        'user_roles' => $user->getRoleNames(),
        'required_role' => 'Responsable pédagogique local'
    ]);

    return $user->hasRole(['Responsable pédagogique local','Pédagogie', 'Admin SI',]);
}

    /**
     * Determine whether the user can delete the report.
     */
    public function delete(User $user, Report $report): bool
    {

        return $user->hasAnyRole(['Responsable pédagogique local', 'Admin SI',]);
    }

    /**
     * Determine whether the user can restore the report.
     */
    public function restore(User $user, Report $report): bool
    {

        return $user->hasAnyRole(['Responsable pédagogique local', 'Admin SI',]);
    }

    /**
     * Determine whether the user can bulk delete reports.
     */
    public function bulkDelete(User $user): bool
    {
        return $user->hasAnyRole(['Responsable pédagogique local', 'Admin SI',]);
    }
}
