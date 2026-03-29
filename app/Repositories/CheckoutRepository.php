<?php

namespace App\Repositories;

use App\Models\Checkout;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CheckoutRepository
{
    public function all(array $filters = []): LengthAwarePaginator
    {
        $query = Checkout::query()
            ->orderBy('created_at', 'desc')
            ->orderByRaw('deleted_at IS NOT NULL');

        isset($filters['is_active']) && $filters['is_active'] === 'false'
            ? $query->onlyTrashed() : $query->withoutTrashed();

        if (!empty($filters['code'])) {
            $query->where('code', 'ilike', '%' . $filters['code'] . '%');
        }
        if (!empty($filters['operation_type'])) {
            $query->where('operation_type', $filters['operation_type']);
        }
        if (!empty($filters['project_id'])) {
            $query->where('project_id', $filters['project_id']);
        }
        if (!empty($filters['collaborator_id'])) {
            $query->where('collaborator_id', $filters['collaborator_id']);
        }
        if (!empty($filters['created_by'])) {
            $query->where('created_by', $filters['created_by']);
        }

        $perPage = !empty($filters['per_page']) ? $filters['per_page'] : 10;

        return $query->paginate($perPage);
    }

    public function allBasic(): Collection
    {
        return Checkout::all([
            'id',
            'code',
            'operation_type',
            'project_id',
            'initiale_amount',
            'used_amount',
            'available_balance',
            'collaborator_id',
        ]);
    }

    public function find(int $id): Checkout
    {
        return Checkout::withTrashed()->findOrFail($id);
    }

    public function create(array $data): Checkout
    {
        return Checkout::create($data);
    }

    public function update(int $id, array $data): Checkout
    {
        $checkout = $this->find($id);
        $checkout->update($data);
        return $checkout;
    }

    public function delete(int $id): int
    {
        $checkout = $this->find($id);
        return $checkout->delete();
    }

    public function bulkDelete(array $ids): int
    {
        return Checkout::whereIn('id', $ids)->delete();
    }

    public function restore(int $id): Checkout
    {
        $checkout = Checkout::onlyTrashed()->findOrFail($id);
        $checkout->restore();
        return $checkout;
    }
}
