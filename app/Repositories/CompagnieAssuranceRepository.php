<?php

namespace App\Repositories;

use App\Models\CompagnieAssurance;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class CompagnieAssuranceRepository
{
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = CompagnieAssurance::query()->withTrashed();
        $this->applyFilters($query, $filters);
        return $query->latest('id')->paginate($perPage);
    }

    private function applyFilters(Builder $query, array $filters): void
    {
        $query->when($filters['search'] ?? null, function (Builder $q, $search) {
            $q->where('name', 'like', "%{$search}%");
        });

        $query->when($filters['activation_status'] ?? 'active', function (Builder $q, $status) {
            if ($status === 'active') {
                $q->whereNull('deleted_at');
            } elseif ($status === 'deactivated') {
                $q->whereNotNull('deleted_at');
            }
        });
    }

    public function find(int $id): ?CompagnieAssurance
    {
        return CompagnieAssurance::withTrashed()->find($id);
    }

    public function create(array $data): CompagnieAssurance
    {
        return CompagnieAssurance::create($data);
    }

    public function update(CompagnieAssurance $compagnie, array $data): bool
    {
        return $compagnie->update($data);
    }

    public function toggleActivation(array $ids): array
    {
        $results = [];
        $compagnies = CompagnieAssurance::withTrashed()->whereIn('id', $ids)->get();

        foreach ($compagnies as $compagnie) {
            try {
                if ($compagnie->trashed()) {
                    $compagnie->restore();
                    $message = 'Compagnie réactivée avec succès.';
                } else {
                    $compagnie->delete();
                    $message = 'Compagnie désactivée avec succès.';
                }
                $results[] = ['id' => $compagnie->id, 'success' => true, 'message' => $message];
            } catch (Exception $e) {
                Log::error("Error toggling activation for Compagnie ID {$compagnie->id}: " . $e->getMessage());
                $results[] = ['id' => $compagnie->id, 'success' => false, 'message' => 'Erreur: ' . $e->getMessage()];
            }
        }
        return $results;
    }
}
