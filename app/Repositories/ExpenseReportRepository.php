<?php

namespace App\Repositories;

use App\Models\ExpenseReport;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ExpenseReportRepository
{
    protected ExpenseReport $model;

    public function __construct(ExpenseReport $model)
    {
        $this->model = $model;
    }

    public function all($perPage = 10, $filters = []): LengthAwarePaginator
    {
        $query = $this->model->with(['project', 'missionOrder', 'createdBy.superior', 'expenseLines']);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('id', 'LIKE', "%{$search}%")
                    ->orWhereHas('project', function($projectQuery) use ($search) {
                        $projectQuery->where('project_name', 'LIKE', "%{$search}%");
                    })
                    ->orWhereHas('createdBy', function($collaboratorQuery) use ($search) {
                        $collaboratorQuery->where('first_name', 'LIKE', "%{$search}%")
                            ->orWhere('last_name', 'LIKE', "%{$search}%");
                    });
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['project_id'])) {
            $query->where('project_id', $filters['project_id']);
        }

        if (!empty($filters['created_by_id'])) {
            $query->where('created_by_id', $filters['created_by_id']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        if (!empty($filters['amount_min'])) {
            $query->where(column: 'total_amount', operator: '>=', value: $filters['amount_min']);
        }

        if (!empty($filters['amount_max'])) {
            $query->where('total_amount', '<=', $filters['amount_max']);
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function find(int $id): ExpenseReport
    {
        // CORRECTION OPTIONNELLE : charger 'createdBy' ici aussi si besoin pour l'affichage détail
        $report = $this->model->with(['expenseLines', 'advance', 'createdBy'])->find($id);

        if (!$report) {
            throw new ModelNotFoundException("Expense report not found with ID: {$id}");
        }

        return $report;
    }

    public function create(array $data): ExpenseReport
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): ExpenseReport
    {
        $report = $this->find($id);
        $report->update($data);
        return $report->fresh();
    }

    public function delete(int $id): bool
    {
        $report = $this->find($id);
        return $report->delete();
    }

    public function bulkDelete(array $ids): bool
    {
        return $this->model->whereIn('id', $ids)->delete();
    }

    public function restore(int $id): Model
    {
        $model = $this->model->withTrashed()->findOrFail($id);
        $model->restore();
        return $model;
    }

    public function findWithTrashed(int $id): ExpenseReport
    {
        return $this->model->withTrashed()->findOrFail($id);
    }
}
