<?php

namespace App\Services;

use App\Models\ContactPerson;
use App\Repositories\ContactPersonRepository;
use Exception;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as PaginationLengthAwarePaginator;
use Illuminate\Support\Collection;

class ContactPersonService
{
    /** @var ContactPersonRepository */
    protected ContactPersonRepository $contactRepository;

    /**
     * @param ContactPersonRepository $contactRepository
     */
    public function __construct(ContactPersonRepository $contactRepository)
    {
        $this->contactRepository = $contactRepository;
    }

    /**
     * Get all contacts with filters and pagination.
     *
     * @param Request $request
     * @return PaginationLengthAwarePaginator
     */
    public function getAll(Request $request): PaginationLengthAwarePaginator
    {
        $filters = $request->only([
            'is_active',
            'contact_code',
            'contact_name',
            'contact_email',
            'organisation',
            'original_channel',
            'contact_status',
            'per_page'
        ]);

        return $this->contactRepository->all($filters);
    }

    /**
     * Get all contacts with basic fields only.
     *
     * @return Collection
     */
    public function allBasic(): Collection
    {
        return $this->contactRepository->allBasic();
    }

    /**
     * Find a single contact by ID.
     *
     * @param int $id
     * @return ContactPerson
     * @throws ModelNotFoundException
     */
    public function find(int $id): ContactPerson
    {
        return $this->contactRepository->find($id);
    }

    /**
     * Create a new contact.
     *
     * @param array $data
     * @return ContactPerson
     */
    public function create(array $data): ContactPerson
    {
        return $this->contactRepository->create($data);
    }

    /**
     * Update a contact by ID.
     *
     * @param int $id
     * @param array $data
     * @return ContactPerson
     */
    public function update(int $id, array $data): ContactPerson
    {
        return $this->contactRepository->update($id, $data);
    }

    /**
     * Delete a contact by ID.
     *
     * @param int $id
     * @return int
     * @throws ModelNotFoundException
     */
    public function delete(int $id): int
    {
        return $this->contactRepository->delete($id);
    }

    /**
     * Bulk delete multiple contacts by IDs.
     *
     * @param array $ids
     * @return int
     */
    public function bulkDelete(array $ids): int
    {
        return $this->contactRepository->bulkDelete($ids);
    }

    /**
     * Restore a soft-deleted contact by ID.
     *
     * @param int $id
     * @return ContactPerson
     * @throws ModelNotFoundException
     */
    public function restore(int $id): ContactPerson
    {
        try {
            return $this->contactRepository->restore($id);
        } catch (Exception $e) {
            abort(409, $e->getMessage());
        }
    }
}
