<?php

namespace App\Repositories;

use App\Models\Assurance;
use App\Models\Collaborator;
use App\Models\External;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

/**
 *class AssuranceRepository
 */
class AssuranceRepository
{
    /**
     * @var array|string[]
     */
    public array $defaultWith = [
        'personneAssuree'
    ];

    /**
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getFiltered(array $filters = []): LengthAwarePaginator
    {
        $perPage = $filters['per_page'] ?? 15;
        $query = Assurance::query()->with($this->defaultWith)->withTrashed();
        $this->applyFilters($query, $filters);
        return $query->latest('id')->paginate($perPage);
    }

    /**
     * @param Builder $query
     * @param array $filters
     * @return void
     */
    private function applyFilters(Builder $query, array $filters): void
    {
        // Search filter for organization, type, or the name of the insured person
        $query->when($filters['search'] ?? null, function (Builder $q, $search) {
            $q->where(function (Builder $subQuery) use ($search) {
                $subQuery->where('insurance_organization', 'like', "%{$search}%")
                    ->orWhere('insurance_type', 'like', "%{$search}%")
                    // Search within Collaborator relationships
                    ->orWhereHasMorph('personneAssuree', [Collaborator::class], function (Builder $morphQuery) use ($search) {
                        $morphQuery->where('first_name', 'like', "%{$search}%")
                                   ->orWhere('last_name', 'like', "%{$search}%");
                    })
                    // Search within External relationships
                    ->orWhereHasMorph('personneAssuree', [External::class], function (Builder $morphQuery) use ($search) {
                        $morphQuery->where('full_name', 'like', "%{$search}%");
                    });
            });
        });
        $query->when($filters['status'] ?? null, function (Builder $q, $status) {
            if (in_array($status, ['traité', 'non traité'])) {
                $q->where('status', $status);
            }
        });
        $query->when($filters['activation_status'] ?? 'active', function (Builder $q, $status) {
            if ($status === 'active') {
                $q->whereNull('deleted_at');
            } elseif ($status === 'deactivated') {
                $q->whereNotNull('deleted_at');
            }
        });
    }

    /**
     * @param int $id
     * @return Assurance|null
     */
    public function find(int $id): ?Assurance
    {
        return Assurance::with($this->defaultWith)->withTrashed()->find($id);
    }

    /**
     * @param array $data
     * @return Assurance
     */
    public function create(array $data): Assurance
    {
        return Assurance::create($data);
    }

    /**
     * @param Assurance $assurance
     * @param array $data
     * @return bool
     */
    public function update(Assurance $assurance, array $data): bool
    {
        return $assurance->update($data);
    }

    /**
     * @param array $ids
     * @return array
     */
    public function toggleActivation(array $ids): array
    {
        $results = [];
        $assurances = Assurance::withTrashed()->whereIn('id', $ids)->get();

        foreach ($assurances as $assurance) {
            try {
                if ($assurance->trashed()) {
                    $assurance->restore();
                    $message = 'Assurance réactivée avec succès.';
                } else {
                    $assurance->delete();
                    $message = 'Assurance désactivée avec succès.';
                }
                $results[] = ['id' => $assurance->id, 'success' => true, 'message' => $message];
            } catch (Exception $e) {
                Log::error("Error toggling activation for assurance ID {$assurance->id}: " . $e->getMessage());
                $results[] = ['id' => $assurance->id, 'success' => false, 'message' => 'Erreur: ' . $e->getMessage()];
            }
        }
        return $results;
    }
}

