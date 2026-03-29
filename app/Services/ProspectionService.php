<?php

namespace App\Services;

use App\Models\Prospection;
use App\Repositories\ProspectionRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class ProspectionService
{
    protected ProspectionRepository $prospectionRepository;

    public function __construct(ProspectionRepository $prospectionRepository)
    {
        $this->prospectionRepository = $prospectionRepository;
    }

    /**
     * Récupération filtrée + paginée
     */
    public function getFiltered(array $params): LengthAwarePaginator
    {
        return $this->prospectionRepository->getFiltered($params);
    }
          public function getTrashed(array $params): LengthAwarePaginator
    {
        
        $params['withTrashed'] = 'true';

        return $this->prospectionRepository->getFiltered($params);
    }
     public function getAll()
    {
        return Prospection::with(['program', 'site'])->get();
    }
    public function findProspectionById(int $id): ?Prospection
    {
        return $this->find($id);
    }
    public function findDeletedProspectionById(int $id): ?Prospection
{
    return Prospection::onlyTrashed()
        ->with(['program', 'site'])
        ->find($id);
}

    /**
     * Création
     */
    public function create(array $data): Prospection
    {
        return $this->prospectionRepository->create($data);
    }

    /**
     * Mise à jour
     */
    public function update(int $id, array $data): Prospection
    {
        return $this->prospectionRepository->update($id, $data);
    }

    /**
     * Suppression soft delete
     */
    public function delete(int $id): bool
    {
        return $this->prospectionRepository->delete($id);
    }

    /**
     * Suppression multiple
     */
    public function bulkDelete(array $ids): int
    {
        return $this->prospectionRepository->bulkDelete($ids);
    }

    /**
     * Consultation simple
     */
    public function find(int $id): ?Prospection
    {
        return $this->prospectionRepository->find($id);
    }

    /**
     * Consultation (y compris deleted)
     */
    public function findWithTrashed(int $id): ?Prospection
    {
        return Prospection::withTrashed()
            ->with(['program', 'site'])
            ->find($id);
    }

    /**
     * Restauration soft delete
     */
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