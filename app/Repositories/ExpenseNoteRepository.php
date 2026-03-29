<?php

namespace App\Repositories;

use App\Models\ExpenseNote;
use App\Models\Training;
use App\Models\BudgetLine;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class ExpenseNoteRepository
{
    /**
     * Base query.
     */
    public function all(): Builder
    {
        return ExpenseNote::query();
    }

    public function allWithFilters(array $filters): Builder
    {
        $q = ExpenseNote::query()
            ->orderByRaw('deleted_at IS NOT NULL') // active first
            ->orderByDesc('created_at');

        if (!empty($filters['is_active'])) {
            if ($filters['is_active'] === 'true') {
                $q->withoutTrashed();
            } elseif ($filters['is_active'] === 'false') {
                $q->onlyTrashed();
            } else {
                $q->withTrashed();
            }
        } else {
            $q->withoutTrashed();
        }

        if (!empty($filters['code'])) {
            $q->where('code', 'like', '%'.$filters['code'].'%');
        }

        if (!empty($filters['validation_status'])) {
            $values = (array) $filters['validation_status'];
            $q->whereIn('validation_status', $values);
        }

        if (!empty($filters['expense_nature'])) {
            $values = (array) $filters['expense_nature'];
            $q->whereIn('expense_nature', $values);
        }

        if (!empty($filters['training_id'])) {
            $q->where('training_id', (int) $filters['training_id']);
        }

        if (!empty($filters['budget_line_id'])) {
            $q->where('budget_line_id', (int) $filters['budget_line_id']);
        }

        if (!empty($filters['beneficiary_type'])) {
            $q->where('beneficiary_type', $filters['beneficiary_type']);
        }

        if (!empty($filters['beneficiary_id'])) {
            $q->where('beneficiary_id', (int) $filters['beneficiary_id']);
        }

        if (!empty($filters['created_by_id'])) {
            $q->where('created_by_id', (int) $filters['created_by_id']);
        }

        if (!empty($filters['expense_from'])) {
            $q->whereDate('expense_date', '>=', $filters['expense_from']);
        }
        if (!empty($filters['expense_to'])) {
            $q->whereDate('expense_date', '<=', $filters['expense_to']);
        }

        if (isset($filters['amount_min']) && $filters['amount_min'] !== '') {
            $q->where('amount_ttc', '>=', (float) $filters['amount_min']);
        }
        if (isset($filters['amount_max']) && $filters['amount_max'] !== '') {
            $q->where('amount_ttc', '<=', (float) $filters['amount_max']);
        }

        return $q;
    }

    /**
     * For select dropdowns, etc.
     */
    public function allWithoutPagination(): Collection
    {
        return ExpenseNote::select('id', 'code')->get();
    }

    /**
     * Find by ID (with trashed).
     */
    public function find(int $id): ExpenseNote
    {
        return ExpenseNote::withTrashed()->findOrFail($id);
    }

    /**
     * Find by ID + eager relations.
     */
    public function findWithRelations(int $id): ExpenseNote
    {
        return ExpenseNote::withTrashed()
            ->with(['beneficiary', 'training', 'budgetLine', 'createdBy'])
            ->findOrFail($id);
    }

    /**
     * Find by code (unique).
     */
    public function findByCode(string $code): ?ExpenseNote
    {
        return ExpenseNote::withTrashed()->where('code', $code)->first();
    }

    /**
     * Create simple.
     */
    public function create(array $data): ExpenseNote
    {
        return ExpenseNote::create($data);
    }

    /**
     * Create with associations.
     */
    public function createWithAssociations(
        array $data,
        ?Model $beneficiary,
        Training $training,
        BudgetLine $budgetLine,
        User $creator
    ): ExpenseNote {
        $note = new ExpenseNote($data);

        if ($beneficiary) {
            $note->beneficiary()->associate($beneficiary);
        }
        $note->training()->associate($training);
        $note->budgetLine()->associate($budgetLine);
        $note->createdBy()->associate($creator);

        $note->save();

        return $note->fresh(['beneficiary', 'training', 'budgetLine', 'createdBy']);
    }

    /**
     * Update with associations (all params optional except $id and $data).
     */
    public function update(
        int $id,
        array $data,
        ?Model $beneficiary = null,
        ?Training $training = null,
        ?BudgetLine $budgetLine = null,
        ?User $creator = null
    ): ExpenseNote {
        $note = ExpenseNote::findOrFail($id);
        $note->fill($data);

        if ($beneficiary !== null) {
            $note->beneficiary()->associate($beneficiary);
        }
        if ($training !== null) {
            $note->training()->associate($training);
        }
        if ($budgetLine !== null) {
            $note->budgetLine()->associate($budgetLine);
        }
        if ($creator !== null) {
            $note->createdBy()->associate($creator);
        }
        $note->save();

        return $note->fresh(['beneficiary', 'training', 'budgetLine', 'createdBy']);
    }

    /**
     * Soft delete one.
     */
    public function delete(int $id): int
    {
        $note = $this->find($id);
        return (int) $note->delete();
    }

    /**
     * Bulk soft delete.
     */
    public function bulkDelete(array $ids): int
    {
        return ExpenseNote::whereIn('id', $ids)->delete();
    }

    /**
     * Restore a soft-deleted note.
     */
    public function restore(int $id): ExpenseNote
    {
        $note = ExpenseNote::withTrashed()->findOrFail($id);
        $note->restore();
        return $note;
    }

    /**
     * Update status only (optionally update comments).
     */
    public function updateStatus(int $id, string $status, ?string $comments = null): ExpenseNote
    {
        $note = ExpenseNote::findOrFail($id);
        $note->validation_status = $status;
        if ($comments !== null) {
            $note->comments = $comments;
        }
        $note->save();

        return $note;
    }
}
