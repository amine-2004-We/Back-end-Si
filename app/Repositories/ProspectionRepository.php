<?php

namespace App\Repositories;

use App\Models\Prospection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class ProspectionRepository
{
    public array $defaultWith = ['program', 'site', 'prospector'];

    /**
     * Filtrage avancé avec gestion des paramètres directs (comme PhaseRepository)
     */
    public function getFiltered(array $params): LengthAwarePaginator
    {
        return $this->getPaginated($params);
    }

    /**
     * getPaginated - Accepte les filtres comme paramètres directs
     * Gère: search, activation_status, sort_by, sort_direction, per_page
     * 
     * activation_status:
     * - "active" → affiche uniquement les prospections NON supprimées
     * - "inactive" → affiche uniquement les prospections supprimées
     * - par défaut → affiche uniquement les prospections NON supprimées
     */
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Prospection::query()->with($this->defaultWith);

        // Gestion du filtre activation_status EN PREMIER
        $activationStatus = $filters['activation_status'] ?? 'active';
        
        if ($activationStatus === 'inactive') {
            // Affiche uniquement les prospections supprimées
            $query->onlyTrashed();
        } elseif ($activationStatus === 'all') {
            // Affiche toutes les prospections (actives et supprimées)
            $query->withTrashed();
        } else {
            // Par défaut (active) : affiche uniquement les prospections NON supprimées
            $query->whereNull('deleted_at');
        }

        // Traitement des autres filtres
        foreach ($filters as $key => $value) {
            if (empty($value) || $value === 'all' || $key === 'activation_status') {
                continue;
            }

            switch ($key) {
                case 'search':
                    $query->where(function (Builder $q) use ($value) {
                        $q->where('title', 'ilike', "%$value%")
                          ->orWhere('local_name', 'ilike', "%$value%")
                          ->orWhere('economic_activities', 'ilike', "%$value%")
                          ->orWhere('cultural_activities', 'ilike', "%$value%");
                    });
                    break;

                case 'program_id':
                case 'site_id':
                case 'douar_access':
                case 'main_language':
                case 'association_activity_type':
                case 'owner_type':
                case 'owner_status':
                case 'manager_status':
                case 'manager_structure':
                case 'decision':
                    $query->where($key, $value);
                    break;
            }
        }

        $perPageParam = $filters['per_page'] ?? $perPage;
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';

        if ($activationStatus === 'all') {
            $query->orderByRaw('deleted_at IS NOT NULL');
        }

        $query->orderBy($sortBy, $sortDirection);

        return $query->paginate($perPageParam);
    }

    
    public function create(array $data)
    {
        if (!isset($data['prospections_id'])) {
            $data['prospections_id'] = $this->generateBusinessId();
        }

        $data['association_activity_type'] = "aide_humanitaire_et_assistance";
        $data['created_by'] = Auth::id();

        $prospection = Prospection::create($data);

        return $prospection->load(['program', 'site', 'prospector']);
    }

   
    public function update(int $id, array $data)
    {
        $prospection = Prospection::withTrashed()
            ->with(['program', 'site', 'prospector'])
            ->find($id);

        if (!$prospection) {
            throw new ModelNotFoundException("Prospection non trouvée.");
        }

        // Gestion deleted_at
        if (array_key_exists('deleted_at', $data)) {
            if ($data['deleted_at'] === false && $prospection->trashed()) {
                $prospection->restore();
            } elseif ($data['deleted_at'] === true && !$prospection->trashed()) {
                $prospection->delete();
            }
            unset($data['deleted_at']);
        }

        $data['updated_by'] = Auth::id();
        $prospection->update($data);

        return $prospection->refresh()->load(['program', 'site', 'prospector']);
    }

    
    private function generateBusinessId(): int
    {
        do {
            $value = random_int(100000000, 999999999); // 9 digits
        } while (Prospection::where('prospections_id', $value)->exists());

        return $value;
    }

    public function find(int $id): ?Prospection
    {
        return Prospection::with(['program', 'site', 'prospector'])->find($id);
    }

   
    public function delete(int $id): bool
    {
        $prospection = $this->find($id);

        if (!$prospection) {
            throw new ModelNotFoundException("Prospection non trouvée.");
        }

        return $prospection->delete();
    }

    
    public function bulkDelete(array $ids): int
    {
        return Prospection::whereIn('id', $ids)->delete();
    }

    
    public function restore(int $id): Prospection
    {
        $prospection = Prospection::withTrashed()->find($id);

        if (!$prospection) {
            throw new ModelNotFoundException("Prospection non trouvée.");
        }

        if (!$prospection->trashed()) {
            throw new ConflictHttpException("La prospection n'est pas supprimée.");
        }

        $prospection->restore();

        return $prospection;
    }
}
