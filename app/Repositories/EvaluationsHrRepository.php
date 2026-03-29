<?php

namespace App\Repositories;

use App\Models\EvaluationHr;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class EvaluationsHrRepository
{
    /**
     * Get paginated list of Evaluations HR with optional filters.
     *
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function all(array $filters = []): LengthAwarePaginator
    {
        $query = EvaluationHr::query()
            ->orderBy('created_at', 'desc')
            ->orderByRaw('deleted_at IS NOT NULL');
        // Gestion soft delete
        if (!empty($filters['is_active'])) {
            match ($filters['is_active']) {
                'true'  => $query->withoutTrashed(),
                'false' => $query->onlyTrashed(),
                default => $query->withTrashed(),
            };
        } else {
            $query->withoutTrashed();
        }

        $query->when($filters['collaborator_id'] ?? null, fn($q, $id) =>
        $q->where('collaborator_id', $id)
        );

        $query->when($filters['skill'] ?? null, fn($q, $skill) =>
        $q->where('skill', 'ILIKE', "%{$skill}%")
        );

        $query->when($filters['value'] ?? null, fn($q, $value) =>
        $q->where('value', $value)
        );

        $query->when($filters['objectif_nature'] ?? null, fn($q, $objectif) =>
        $q->where('objectif_nature', 'ILIKE', "%{$objectif}%")
        );

        $perPage = $filters['per_page'] ?? 10;

        return $query->paginate($perPage);
    }

    /**
     * Get all EvaluationHr records with selected fields.
     *
     * @return Collection
     */
    public function allTypes(): Collection
    {
        return EvaluationHr::all([
            'id',
            'name',
            'description',
            'objectif_nature',
            'measurement_indicators',
            'weight',
            'skill',
            'indicators',
            'value'
        ]);
    }

    /**
     * Find an EvaluationHr by ID, including soft deleted.
     *
     * @param int $id
     * @return EvaluationHr
     * @throws ModelNotFoundException
     */
    public function find(int $id): EvaluationHr
    {
        return EvaluationHr::withTrashed()->findOrFail($id);
    }

    /**
     * Create a new EvaluationHr.
     *
     * @param array $data
     * @return EvaluationHr
     */
    public function create(array $data): EvaluationHr
    {
        return EvaluationHr::create($data);
    }

    /**
     * Update an EvaluationHr by ID.
     *
     * @param int $id
     * @param array $data
     * @return EvaluationHr
     * @throws ModelNotFoundException
     */
    public function update(int $id, array $data): EvaluationHr
    {
        $evaluation = $this->find($id);
        $evaluation->update($data);
        return $evaluation;
    }

    /**
     * Delete an EvaluationHr by ID.
     *
     * @param int $id
     * @return int
     * @throws ModelNotFoundException
     */
    public function delete(int $id): int
    {
        $evaluation = $this->find($id);
        return $evaluation->delete();
    }

    /**
     * Bulk delete EvaluationHr by IDs.
     *
     * @param array $ids
     * @return int
     */
    public function bulkDelete(array $ids): int
    {
        return EvaluationHr::whereIn('id', $ids)->delete();
    }

    /**
     * Restore a soft deleted EvaluationHr by ID.
     *
     * @param int $id
     * @return EvaluationHr
     */
    public function restore(int $id): EvaluationHr
    {
        $evaluation = EvaluationHr::onlyTrashed()->findOrFail($id);
        $evaluation->restore();
        return $evaluation;
    }
}
