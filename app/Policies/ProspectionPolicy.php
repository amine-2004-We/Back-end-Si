<?php

namespace App\Policies;

use App\Constants\Role;
use App\Models\Prospection;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProspectionPolicy
{
    /**
     * Perform pre-authorization checks.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole(Role::ADMIN_SI)) {
            return true;
        }
        return null;
    }

    /**
     * Determine whether the user can view any models.
     * Consulter : Prospecteurs, Superviseurs, Responsables Opérationnel, Régional, National
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole([
            Role::SUPERVISEUR,
            Role::RESPONSABLE_OPERATIONNEL,
            Role::RESPONSABLE_OPERATIONNEL_REGIONAL,
            Role::RESPONSABLE_OPERATIONNEL_NATIONAL,
            Role::EDUCATRICE,
            Role::ANIMATRICE,
            Role::FACILITATRICE,
            Role::EDUCATEUR,
            Role::ANIMATEUR,
            Role::FACILITATEUR,
            Role::ADMIN_SI,
        ]);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Prospection $prospection): bool
    {
        return $this->viewAny($user);
    }

    /**
     * Determine whether the user can create models.
     * Créer : Prospecteurs (Superviseur, Éducatrice, Animatrice, Facilitatrice)
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole([
            Role::SUPERVISEUR,
            Role::EDUCATRICE,
            Role::ANIMATRICE,
            Role::FACILITATRICE,
            Role::EDUCATEUR,
            Role::ANIMATEUR,
            Role::FACILITATEUR,
            Role::ADMIN_SI,
        ]);
    }

    /**
     * Determine whether the user can update the model.
     * Modifier : Prospecteur créateur (draft only) + Validateurs à chaque niveau
     */
    public function update(User $user, Prospection $prospection): bool
    {
        // Creator can edit during draft
        if ($prospection->validation_status === 'draft' && $prospection->prospector?->user_id === $user->id) {
            return true;
        }

        // Validators cannot edit prospection itself, only validate
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     * Supprimer : Creator (draft only) ou Admin SI
     */
    public function delete(User $user, Prospection $prospection): bool
    {
        if ($prospection->validation_status === 'draft' && $prospection->prospector?->user_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Prospection $prospection): bool
    {
        return $this->delete($user, $prospection);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Prospection $prospection): bool
    {
        return false; // Only admins can force delete via artisan commands
    }

    // === VALIDATION WORKFLOW METHODS ===

    /**
     * Determine whether user can review at supervisor level
     * For teacher prospectors only
     */
    public function reviewAsSupervisor(User $user, Prospection $prospection): bool
    {
        // Admin SI can always validate
        if ($user->hasRole(Role::ADMIN_SI)) {
            return true;
        }

        // Only if prospection is pending supervisor validation
        if ($prospection->validation_status !== 'pending_supervisor') {
            return false;
        }

        // Only if prospector is a teacher
        if ($prospection->prospector_role_type !== 'teacher') {
            return false;
        }

        // Check if user is supervisor of the prospector
        $prospectorCollaborator = $prospection->prospector;
        if (!$prospectorCollaborator) {
            return false;
        }

        // User must be the hierarchical superior of the prospector
        $userCollaborator = $user->collaborator;
        if (!$userCollaborator) {
            return false;
        }

        // Check if current user is the superior of prospector
        return $prospectorCollaborator->hierarchical_superior === $userCollaborator->id;
    }

    /**
     * Determine whether user can review at operational level
     * For both superviseur and teacher prospectors
     */
    public function reviewAsOperational(User $user, Prospection $prospection): bool
    {
        // Admin SI can always validate
        if ($user->hasRole(Role::ADMIN_SI)) {
            return true;
        }

        $expectedStatus = match ($prospection->prospector_role_type) {
            'superviseur' => 'pending_operational',
            'teacher' => 'pending_operational',
            default => null,
        };

        if ($prospection->validation_status !== $expectedStatus) {
            return false;
        }

        // User must have Responsable Opérationnel role
        return $user->hasRole(Role::RESPONSABLE_OPERATIONNEL);
    }

    /**
     * Determine whether user can review at regional level
     */
    public function reviewAsRegional(User $user, Prospection $prospection): bool
    {
        // Admin SI can always validate
        if ($user->hasRole(Role::ADMIN_SI)) {
            return true;
        }

        $expectedStatus = match ($prospection->prospector_role_type) {
            'superviseur' => 'pending_regional',
            'teacher' => 'pending_regional',
            default => null,
        };

        if ($prospection->validation_status !== $expectedStatus) {
            return false;
        }

        // User must have Responsable Opérationnel Régional role
        return $user->hasRole(Role::RESPONSABLE_OPERATIONNEL_REGIONAL);
    }

    /**
     * Determine whether user can review at national level (final validation)
     * For all prospectors
     */
    public function reviewAsNational(User $user, Prospection $prospection): bool
    {
        // Admin SI can always validate
        if ($user->hasRole(Role::ADMIN_SI)) {
            return true;
        }

        // Check if prospection is pending national validation
        $pendingStatuses = ['pending_national', 'pending_national_direct'];
        if (!in_array($prospection->validation_status, $pendingStatuses)) {
            return false;
        }

        // User must have Responsable Opérationnel National role
        return $user->hasRole(Role::RESPONSABLE_OPERATIONNEL_NATIONAL);
    }

    /**
     * Determine whether user can reject a prospection during validation
     */
    public function reject(User $user, Prospection $prospection): bool
    {
        // Admin SI can always reject
        if ($user->hasRole(Role::ADMIN_SI)) {
            return true;
        }

        // Can only reject if in a pending state
        $pendingStatuses = [
            'pending_supervisor',
            'pending_operational',
            'pending_regional',
            'pending_national',
            'pending_national_direct',
        ];

        if (!in_array($prospection->validation_status, $pendingStatuses)) {
            return false;
        }

        // Check if user is authorized to review at current level
        return match ($prospection->validation_status) {
            'pending_supervisor' => $this->reviewAsSupervisor($user, $prospection),
            'pending_operational' => $this->reviewAsOperational($user, $prospection),
            'pending_regional' => $this->reviewAsRegional($user, $prospection),
            'pending_national', 'pending_national_direct' => $this->reviewAsNational($user, $prospection),
            default => false,
        };
    }

    /**
     * Determine whether user can reopen a rejected prospection for revision
     */
    public function reopen(User $user, Prospection $prospection): bool
    {
        // Only creator can reopen
        if ($prospection->prospector?->user_id !== $user->id) {
            return false;
        }

        // Can only reopen if rejected
        return $prospection->is_rejected === true && $prospection->validation_status === 'rejected';
    }
}
