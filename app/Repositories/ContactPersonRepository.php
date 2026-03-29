<?php

namespace App\Repositories;

use App\Models\ContactPerson;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ContactPersonRepository
{
    /**
     * Get paginated list of Contacts with optional filters.
     *
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function all(array $filters = []): LengthAwarePaginator
    {
        $query = ContactPerson::query()
            ->orderBy('created_at', 'desc')
            ->orderByRaw('deleted_at IS NOT NULL');

        isset($filters['is_active']) && $filters['is_active'] === 'false'
            ? $query->onlyTrashed() : $query->withoutTrashed();

        if (!empty($filters['contact_code'])) {
            $query->where('contact_code', 'ilike', '%' . $filters['contact_code'] . '%');
        }
        if (!empty($filters['last_name'])) {
            $query->where('last_name', 'ilike', '%' . $filters['contact_name'] . '%');
        }
        if (!empty($filters['email'])) {
            $query->where('email', 'ilike', '%' . $filters['contact_email'] . '%');
        }
        if (!empty($filters['organisation'])) {
            $query->where('organisation', 'ilike', '%' . $filters['organisation'] . '%');
        }
        if (!empty($filters['original_channel'])) {
            $query->where('original_channel', $filters['original_channel']);
        }
        if (!empty($filters['contact_status'])) {
            $query->where('contact_status', $filters['contact_status']);
        }

        $perPage = !empty($filters['per_page']) ? $filters['per_page'] : 10;

        return $query->paginate($perPage);
    }

    /**
     * Get all contacts with only id and basic fields.
     *
     * @return Collection
     */
    public function allBasic(): Collection
    {
        return ContactPerson::all([
            'id',
            'contact_code',
            'last_name',
            'first_name',
            'email',
            'phone',
            'organisation',
            'contact_status'
        ]);
    }

    /**
     * Find a Contact by ID, including soft deleted.
     *
     * @param int $id
     * @return ContactPerson
     * @throws ModelNotFoundException
     */
    public function find(int $id): ContactPerson
    {
        return ContactPerson::withTrashed()->findOrFail($id);
    }

    /**
     * Create a new Contact.
     *
     * @param array $data
     * @return ContactPerson
     */
    public function create(array $data): ContactPerson
    {
        return ContactPerson::create($data);
    }

    /**
     * Update a Contact by ID.
     *
     * @param int $id
     * @param array $data
     * @return ContactPerson
     * @throws ModelNotFoundException
     */
    public function update(int $id, array $data): ContactPerson
    {
        $contact = $this->find($id);
        $contact->update($data);
        return $contact;
    }

    /**
     * Delete a Contact by ID.
     *
     * @param int $id
     * @return int
     * @throws ModelNotFoundException
     */
    public function delete(int $id): int
    {
        $contact = $this->find($id);
        return $contact->delete();
    }

    /**
     * Bulk delete Contacts by IDs.
     *
     * @param array $ids
     * @return int
     */
    public function bulkDelete(array $ids): int
    {
        return ContactPerson::whereIn('id', $ids)->delete();
    }

    /**
     * Restore a soft deleted Contact by ID.
     *
     * @param int $id
     * @return ContactPerson
     */
    public function restore(int $id): ContactPerson
    {
        $contact = ContactPerson::onlyTrashed()->findOrFail($id);
        $contact->restore();
        return $contact;
    }
}
