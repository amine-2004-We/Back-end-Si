<?php

namespace App\Services;

use App\Models\Checkout;
use App\Repositories\CheckoutRepository;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as PaginationLengthAwarePaginator;
use Illuminate\Support\Collection;

class CheckoutService
{
    protected CheckoutRepository $checkoutRepository;

    public function __construct(CheckoutRepository $checkoutRepository)
    {
        $this->checkoutRepository = $checkoutRepository;
    }

    public function getAll(Request $request): PaginationLengthAwarePaginator
    {
        $filters = $request->only([
            'is_active',
            'code',
            'operation_type',
            'project_id',
            'collaborator_id',
            'created_by',
            'per_page'
        ]);

        return $this->checkoutRepository->all($filters);
    }

    public function allBasic(): Collection
    {
        return $this->checkoutRepository->allBasic();
    }

    public function find(int $id): Checkout
    {
        return $this->checkoutRepository->find($id);
    }

    public function create(array $data): Checkout
    {
        return $this->checkoutRepository->create($data);
    }

    public function update(int $id, array $data): Checkout
    {
        return $this->checkoutRepository->update($id, $data);
    }

    public function delete(int $id): int
    {
        return $this->checkoutRepository->delete($id);
    }

    public function bulkDelete(array $ids): int
    {
        return $this->checkoutRepository->bulkDelete($ids);
    }

    public function restore(int $id): Checkout
    {
        try {
            return $this->checkoutRepository->restore($id);
        } catch (Exception $e) {
            abort(409, $e->getMessage());
        }
    }
}
