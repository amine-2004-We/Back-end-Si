<?php

namespace App\Services;

use App\Repositories\ConventionRepository;
use App\Http\Requests\StoreConventionRequest;
use App\Http\Requests\UpdateConventionRequest;
use App\Models\Convention;
use App\Models\Partner;
use App\Models\Project;
use App\Enums\ConventionType;
use App\Enums\ConventionStatus;
use App\Enums\CurrencyEnum;
use App\Enums\InstallmentStatus;
use App\Enums\ReportingPeriodicity; 
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ConventionService
{
    protected $conventionRepository;

    public function __construct(ConventionRepository $conventionRepository)
    {
        $this->conventionRepository = $conventionRepository;
    }

    /**
     *
     * @param array $filters
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getConventions(array $filters = [])
    {
        return $this->conventionRepository->all($filters);
    }

    /**
     *
     * @param array $data
     * @return Convention
     */
    public function createConvention(array $data): Convention
    {
        try {

            $data['created_by'] = Auth::id();

            $convention = $this->conventionRepository->create($data);
            return $convention->load(['partner', 'project', 'installments', 'responsible', 'trackedIndividual']);
        } catch (\Exception $e) {
            Log::error('Erreur création convention : '.$e->getMessage());
            throw $e;
        }
    }

    /**
     *
     * @param int $id
     * @return Convention
     */
    public function getConventionById(int $id): Convention
    {
        return $this->conventionRepository->find($id);
    }

    /**
     *
     * @param int $id
     * @param array $data
     * @return Convention
     */
    public function updateConvention(int $id, array $data): Convention
    {
        $convention = $this->conventionRepository->find($id);
        if (!$convention) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException("Convention not found");
        }

        $updatedConvention = $this->conventionRepository->update($id, $data);

        // ✅ REMOVED: 'project_ids' sync logic is no longer needed

        return $updatedConvention;
    }

    /**
     * Supprime une convention par ID.
     *
     * @param int $id
     * @return int
     */
    public function deleteConvention(int $id): int
    {
        return $this->conventionRepository->delete($id);
    }

    /**
     *
     * @param array $ids
     * @return int
     */
    public function deleteMultipleConventions(array $ids): int
    {
        return $this->conventionRepository->bulkDelete($ids);
    }

    /**
     *
     * @param int $id
     * @return Convention
     */
    public function restoreConvention(int $id): Convention
    {
        return $this->conventionRepository->restore($id);
    }

    /**
     **/
    public function getConventionOptions(): array
    {
        $partners = \App\Models\Partner::select('id', 'partner_name')->get();
        $projects = \App\Models\Project::select('id', 'project_name')->get();
        $reportingPeriodicity = array_column(\App\Enums\ReportingPeriodicity::cases(), 'value');
        $installmentStatuses = array_column(\App\Enums\InstallmentStatus::cases(), 'value');

        $types = array_column(ConventionType::cases(), 'value');
        $statuses = array_column(ConventionStatus::cases(), 'value');
        $devises = array_column(CurrencyEnum::cases(), 'value');

        return [
            'partners' => $partners,
            'projects' => $projects,
            'types' => $types,
            'statuses' => $statuses,
            'devises' => $devises,
            'reporting_periodicity' => $reportingPeriodicity,
            'installment_statuses' => $installmentStatuses,
        ];
    }

    /**
     * Récupère le document signé associé à une convention et prépare sa réponse pour téléchargement.
     **/
    public function getSignedDocument($id) {
        $convention = Convention::find($id);

        if (!$convention || !$convention->signed_document) {
            abort(404, 'Document non trouvé.');
        }

        return $convention->signed_document;
    }


    /**
     * Prépare les données pour le téléchargement du document d'une convention.
     *
     * @param int
     * @return array
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \Exception Si le document est introuvable
     */
    public function getDocumentForDownload(int $id): array
    {
        $convention = Convention::findOrFail($id);

        $filePath = $convention->signed_document;
        $diskName = 'private';

        if (empty($filePath)) {
            Log::warning("Aucun document n'est enregistré pour la convention ID: $id");
            throw new \Exception('Aucun document n\'est associé à cette convention.', 404);
        }

        if (!Storage::disk($diskName)->exists($filePath)) {
            Log::error("Fichier introuvable sur le disque '$diskName' : $filePath (Convention ID: $id)");
            throw new \Exception('Le fichier n\'existe pas ou a été supprimé du serveur.', 404);
        }

        $publicFileName = 'convention_' . $convention->agreement_code . '_' . basename($filePath);

        return [
            'disk' => $diskName,
            'path' => $filePath,
            'name' => $publicFileName,
        ];
    }

}