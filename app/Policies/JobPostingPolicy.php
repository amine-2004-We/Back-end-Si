<?php

namespace App\Policies;

use App\Models\JobPosting;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class JobPostingPolicy
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
        return $user->hasAnyRole(['RH', 'DG', 'Responsable de recrutement', 'Responsable département','Admin SI',]);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, JobPosting $jobPosting): bool
    {
         return $user->hasAnyRole(['RH', 'DG', 'Responsable de recrutement', 'Responsable département',]);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['RH Responsable département', 'Admin SI',]);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, JobPosting $jobPosting): bool
    {
        return $user->hasAnyRole(['RH', 'Admin SI']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, JobPosting $jobPosting): bool
    {
        return $user->hasAnyRole(['RH', 'Admin SI']);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, JobPosting $jobPosting): bool
    {
        return $user->hasAnyRole(['RH', 'Admin SI']);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, JobPosting $jobPosting): bool
    {
        return false;
    }
}
