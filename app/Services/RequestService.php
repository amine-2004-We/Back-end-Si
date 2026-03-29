<?php

namespace App\Services;

use App\Models\RequestModel;
use App\Repositories\RequestRepository;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as PaginationLengthAwarePaginator;
use Illuminate\Support\Collection;

class RequestService
{
    /** @var RequestRepository */
    protected RequestRepository $requestRepository;

    /**
     * @param RequestRepository $requestRepository
     */
    public function __construct(RequestRepository $requestRepository)
    {
        $this->requestRepository = $requestRepository;
    }

    /**
     * Get all requests with filters and pagination.
     *
     * @param Request $request
     * @return PaginationLengthAwarePaginator
     */
    public function getAll(Request $request): PaginationLengthAwarePaginator
    {
        $filters = $request->only([
            'is_active',
            'request_type_id',
            'collaborator_id',
            'request_status',
            'per_page'
        ]);

        return $this->requestRepository->all($filters);
    }
    /**
     * Get all request types with id and type_name only.
     *
     * @return Collection
     */
    public function allTypes(): Collection
    {
        return $this->requestRepository->allTypes();
    }

    /**
     * Find a single request by ID.
     *
     * @param int $id
     * @return RequestModel
     * @throws ModelNotFoundException
     */
    public function find(int $id): RequestModel
    {
        return $this->requestRepository->find($id);
    }

    /**
     * Create a new request.
     *
     * @param array $data
     * @return RequestModel
     */
    public function create(array $data): RequestModel
    {
        return $this->requestRepository->create($data);
    }

    /**
     * Update a request by ID.
     *
     * @param int $id
     * @param array $data
     * @return RequestModel
     */
    public function update(int $id, array $data): RequestModel
    {
        return $this->requestRepository->update($id, $data);
    }

    /**
     * Delete a request by ID.
     *
     * @param int $id
     * @return int
     * @throws ModelNotFoundException
     */
    public function delete(int $id): int
    {
        return $this->requestRepository->delete($id);
    }

    /**
     * Bulk delete multiple requests by IDs.
     *
     * @param array $ids
     * @return int
     */
    public function bulkDelete(array $ids): int
    {
        return $this->requestRepository->bulkDelete($ids);
    }

    /**
     * Restore a soft-deleted request by ID.
     *
     * @param int $id
     * @return RequestModel
     * @throws ModelNotFoundException
     */
    public function restore(int $id): RequestModel
    {
        try {
            return $this->requestRepository->restore($id);
        } catch (Exception $e) {
            abort(409, $e->getMessage());
        }
    }
}
