<?php

namespace App\Services;

use App\Models\Avenant;
use App\Repositories\AvenantRepository;
use Illuminate\Support\Facades\Auth;
use App\Traits\UploadFileTrait;
use Illuminate\Http\UploadedFile;
use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


class AvenantService
{
    use UploadFileTrait;

    /**
     * @var AvenantRepository
     */
    protected AvenantRepository $avenantRepository;

    /**
     * @param AvenantRepository $avenantRepository
     */
    public function __construct(AvenantRepository $avenantRepository)
    {
        $this->avenantRepository = $avenantRepository;
    }

    /**
     * Create a new avenant.
     *
     * @param array $data Validated data from the request.
     * @return Avenant
     * @throws \RuntimeException 
     * @throws Exception
     */
    public function create(array $data): Avenant
    {
        Log::info($data);

        $user = Auth::user();
        if ($user && $user->collaborator) {
            $data['responsible_id'] = $user->collaborator->id;
        }

        if (isset($data['document_path']) && $data['document_path'] instanceof UploadedFile) {
            $filePath = $this->uploadPublicFile($data['document_path'], 'avenants_documents');
            $data['document_path'] = $filePath;
        } else {
            $data['document_path'] = null;
        }

        // --- Logique métier 10% ---
        if (isset($data['marche_id']) && isset($data['additional_amount']) && $data['additional_amount'] > 0) {
            $marche = \App\Models\Calltender::find($data['marche_id']);
            if (!$marche) {
                throw new \Exception('Marché non trouvé');
            }
            $totalAvenants = Avenant::where('marche_id', $data['marche_id'])->sum('additional_amount');
            $montantInitial = $marche->total_amount;
            $nouveauTotal = $montantInitial + $totalAvenants + $data['additional_amount'];
            $plafond = $montantInitial * 1.1;
            if ($nouveauTotal > $plafond) {
                throw new \Exception('Le montant total du marché avec avenants ne doit pas dépasser 10% du montant initial.');
            }
            // Optionnel : mettre à jour le montant du marché
            $marche->total_amount = $nouveauTotal;
            $marche->save();
        }

        return $this->avenantRepository->create($data);
    }

    /**
     * Get all avenants.
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator|\Illuminate\Database\Eloquent\Collection
     */
    public function getFilteredAvenants(array $params)
    {
        return $this->avenantRepository->getFilteredAvenants($params);
    }

    /**
     * Show a specific avenant by ID.
     *
     * @param string $id
     * @return Avenant
     * @throws ModelNotFoundException
     */
    public function show(string $id): Avenant
    {
        return $this->avenantRepository->find($id);
    }

    /**
     * Update an avenant.
     *
     * @param string $id
     * @param array $data Validated data from the request.
     * @return Avenant
     * @throws ModelNotFoundException
     * @throws \RuntimeException 
     */
    public function update(string $id, array $data): Avenant
    {
        $avenant = $this->avenantRepository->find($id);
        if (isset($data['document_path']) && $data['document_path'] instanceof UploadedFile) {
            if ($avenant->document_path) {
                $this->deletePublicFile($avenant->document_path);
            }
            $filePath = $this->uploadPublicFile($data['document_path'], 'avenants_documents');
            $data['document_path'] = $filePath;
        } elseif (array_key_exists('document_path', $data) && is_null($data['document_path'])) {
            if ($avenant->document_path) {
                $this->deletePublicFile($avenant->document_path);
            }
        } else {
            unset($data['document_path']);
        }

        // --- Logique métier 10% (modification) ---
        if ((isset($data['marche_id']) || $avenant->marche_id) && isset($data['additional_amount']) && $data['additional_amount'] > 0) {
            $marcheId = $data['marche_id'] ?? $avenant->marche_id;
            $marche = \App\Models\Calltender::find($marcheId);
            if (!$marche) {
                throw new \Exception('Marché non trouvé');
            }
            // Exclure l'avenant courant de la somme
            $totalAvenants = Avenant::where('marche_id', $marcheId)->where('id', '!=', $avenant->id)->sum('additional_amount');
            $montantInitial = $marche->total_amount;
            $nouveauTotal = $montantInitial + $totalAvenants + $data['additional_amount'];
            $plafond = $montantInitial * 1.1;
            if ($nouveauTotal > $plafond) {
                throw new \Exception('Le montant total du marché avec avenants ne doit pas dépasser 10% du montant initial.');
            }
            // Optionnel : mettre à jour le montant du marché
            $marche->total_amount = $nouveauTotal;
            $marche->save();
        }

        return $this->avenantRepository->update($id, $data);
    }

    /**
     * Soft delete an avenant.
     *
     * @param string $id
     * @return bool
     * @throws ModelNotFoundException
     */
    public function delete(string $id): bool
    {
        
        return $this->avenantRepository->delete($id);
    }

    /**
     * Restore a soft-deleted avenant.
     *
     * @param string $id
     * @return Avenant
     * @throws Exception
     */
    public function restore(string $id): Avenant
    {
        try {
            return $this->avenantRepository->restore($id);
        } catch (Exception $e) {
            abort(409, $e->getMessage());
        }
    }

    /**
     * Bulk soft delete avenants.
     * **IMPORTANT:** Files are NOT deleted when soft-deleting.
     *
     * @param array $ids
     * @return int
     */
    public function bulkDelete(array $ids): int
    {
        // NO FILE DELETION HERE, as it's a bulk soft delete
        return $this->avenantRepository->bulkDelete($ids);
    }

    /**
     * For updating just the file without other fields.
     *
     * @param int $id
     * @param UploadedFile $file
     * @return Avenant
     * @throws ModelNotFoundException
     * @throws \RuntimeException If file upload/deletion fails
     */
    public function updateDocumentFile(int $id, UploadedFile $file): Avenant
    {
        $avenant = $this->avenantRepository->find($id);
        if ($avenant->document_path) {
            $this->deletePublicFile($avenant->document_path);
        }
        $filePath = $this->uploadPublicFile($file, 'avenants_documents');
        $avenant->document_path = $filePath;
        $avenant->save();
        return $avenant;
    }
   
}

