<?php

namespace App\Policies;

use App\Models\ExpenseNote;
use App\Models\User;

class ExpenseNotePolicy
{
    private array $adminRoles   = ['Admin SI', 'Super Admin'];
    private array $viewRoles    = ['Assistante', 'Directeur', "ZA (Zone d'Activité / Administration de la Formation)"];
    private array $editRoles    = ['Assistante'];
    private array $validateRoles= ['Directeur'];

    /**
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
     * @param \App\Models\User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole($this->viewRoles);
    }

    /**
     * @param \App\Models\User $user
     * @param \App\Models\ExpenseNote $expenseNote
     * @return bool
     */
    public function view(User $user, ExpenseNote $expenseNote): bool
    {
        return $user->hasRole($this->viewRoles);
    }

    /**
     * @param \App\Models\User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->hasRole($this->editRoles);
    }

    /**
     * @param \App\Models\User $user
     * @param \App\Models\ExpenseNote $expenseNote
     * @return bool
     */
    public function update(User $user, ExpenseNote $expenseNote): bool
    {
        return $user->hasRole($this->editRoles);
    }

    /**
     * @param \App\Models\User $user
     * @param \App\Models\ExpenseNote $expenseNote
     * @return bool
     */
    public function delete(User $user, ExpenseNote $expenseNote): bool
    {
        return $user->hasRole($this->editRoles);
    }

    /**
     * @param \App\Models\User $user
     * @param \App\Models\ExpenseNote $expenseNote
     * @return bool
     */
    public function restore(User $user, ExpenseNote $expenseNote): bool
    {
        return $user->hasRole($this->editRoles);
    }

    /**
     * @param \App\Models\User $user
     * @param \App\Models\ExpenseNote $expenseNote
     * @return bool
     */
    public function forceDelete(User $user, ExpenseNote $expenseNote): bool
    {
        return $user->hasRole($this->adminRoles);
    }

    /**
     * @param \App\Models\User $user
     * @param \App\Models\ExpenseNote $expenseNote
     * @return bool
     */
    public function updateStatus(User $user, ExpenseNote $expenseNote): bool
    {
        return $user->hasRole($this->validateRoles);
    }

    /**
     * @param \App\Models\User $user
     * @param \App\Models\ExpenseNote $expenseNote
     * @return bool
     */
    public function validate(User $user, ExpenseNote $expenseNote): bool
    {
        return $this->updateStatus($user, $expenseNote);
    }
}
