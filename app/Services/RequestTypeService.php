<?php

namespace App\Services;

use App\Models\RequestType;
use App\Repositories\RequestTypeRepository;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as PaginationLengthAwarePaginator;
use Illuminate\Support\Collection;

class RequestTypeService
{
    protected RequestTypeRepository $requestTypeRepository;

    public function __construct(RequestTypeRepository $requestTypeRepository)
    {
        $this->requestTypeRepository = $requestTypeRepository;
    }

    /**
     * Get paginated list of RequestTypes with filters from request.
     *
     * @param Request $request
     * @return PaginationLengthAwarePaginator
     */
    public function getAll(Request $request): PaginationLengthAwarePaginator
    {
        $filters = $request->only(['is_active', 'type_name', 'per_page']);
        return $this->requestTypeRepository->all($filters);
    }

    /**
     * Get all request types without pagination.
     *
     * @return Collection
     */
    public function getAllWithoutPagination(): Collection
    {
        return $this->requestTypeRepository->getAllWithoutPagination();
    }

    /**
     * Get all request types with id and type_name only.
     *
     * @return Collection
     */
    public function allTypes(): Collection
    {
        return $this->requestTypeRepository->allTypes();
    }

    /**
     * Find a request type by ID.
     *
     * @param int $id
     * @return RequestType
     * @throws ModelNotFoundException
     */
    public function find(int $id): RequestType
    {
        return $this->requestTypeRepository->find($id);
    }

    /**
     * Create a new request type.
     *
     * @param array $data
     * @return RequestType
     */
    public function create(array $data): RequestType
    {
        return $this->requestTypeRepository->create($data);
    }

    /**
     * Update a request type by ID.
     *
     * @param int $id
     * @param array $data
     * @return RequestType
     */
    public function update(int $id, array $data): RequestType
    {
        return $this->requestTypeRepository->update($id, $data);
    }

    /**
     * Delete a request type by ID.
     *
     * @param int $id
     * @return int
     * @throws ModelNotFoundException
     */
    public function delete(int $id): int
    {
        return $this->requestTypeRepository->delete($id);
    }

    /**
     * Bulk delete request types by IDs.
     *
     * @param array $ids
     * @return int
     */
    public function bulkDelete(array $ids): int
    {
        return $this->requestTypeRepository->bulkDelete($ids);
    }

    /**
     * Restore a soft-deleted request type by ID.
     *
     * @param int $id
     * @return RequestType
     */
    public function restore(int $id): RequestType
    {
        try {
            return $this->requestTypeRepository->restore($id);
        } catch (Exception $e) {
            abort(409, $e->getMessage());
        }
    }
}
