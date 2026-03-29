<?php

namespace App\Services;

use App\Models\CallForProject;
use App\Models\Collaborator;
use App\Models\ProjectPartner;
use App\Models\ProjectStatus;
use App\Repositories\CallForProjectRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CallForProjectService
{
    /**
     * @var CallForProjectRepository 
     */

    protected CallForProjectRepository $callForProjectRepository;
    protected ProjectService $projectService;

    /**
     * @param CallForProjectRepository $callForProjectRepository
     */

    public function __construct(CallForProjectRepository $callForProjectRepository, ProjectService $projectService)
    {
        $this->callForProjectRepository = $callForProjectRepository;
        $this->projectService = $projectService;
    }

    /**
     * @return lengthAwarePaginator
     */

    public function getFilteredCallForProjects($params) : LengthAwarePaginator
    {
        return $this->callForProjectRepository->getFilteredAll($params);
    }

    /**
     * @param 
     */

    public function createCallForProject(array $data): CallForProject
    {
        try {
            $files = $data['required_documents'] ?? null;
            unset($data['required_documents']);

            // ensure creator is set
            $data['created_by'] = $data['created_by'] ?? auth()->id();

            // create the DB record via repository
            $callForProject = $this->callForProjectRepository->create($data);

            $saved = [];
            if (!empty($files) && is_array($files)) {
                $folder = "call_for_projects/{$callForProject->id}";

                foreach ($files as $file) {
                    if (!($file instanceof UploadedFile)) {
                        continue;
                    }

                    $originalName = $file->getClientOriginalName();
                    $storeName = $originalName;

                    if (Storage::disk('public')->exists("$folder/$storeName")) {
                        $storeName = time() . '_' . $originalName;
                    }

                    $path = $file->storeAs($folder, $storeName, 'public');

                    $saved[] = [
                        'path' => $path,
                        'original_name' => $originalName,
                        'size' => $file->getSize(),
                        'stored_name' => $storeName,
                    ];
                }
            }

            // persist file metadata via repository and refresh model
            if (!empty($saved)) {
                $this->callForProjectRepository->update($callForProject->id, [
                    'required_documents' => $saved,
                ]);
                $callForProject = $this->callForProjectRepository->showCallForProject($callForProject->id);
            }

            return $callForProject;
        } catch (\Exception $e) {
            \Log::error('Error creating call for project: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
            throw $e;
        }
    }

    /**
     * @param int $id
     * @return CallForProject
     */
    public function showCallForProject(int $id): CallForProject
    {
        return $this->callForProjectRepository->showCallForProject($id);
    }   

    /**
     * @param int $id
     * @param array $data
     */

   public function updateCallForProject(array $data, int $id): CallForProject
{
    try {
        $callForProject = $this->callForProjectRepository->showCallForProject($id);
        
        // 1. Récupérer et décoder les documents ACTUELS.
        $currentDocuments = json_decode($callForProject->required_documents ?? '[]', true); 
        
        $newFiles = $data['required_documents'] ?? null;
        
        // 2. Récupérer et normaliser les chemins à SUPPRIMER du Frontend.
        $documentsToDeletePaths = [];
        $documentsToDeleteInput = $data['documents_to_delete'] ?? null;
        
        if ($documentsToDeleteInput) {
            $decoded = json_decode($documentsToDeleteInput, true);
            
            if (is_array($decoded)) {
        
                $documentsToDeletePaths = array_map(function ($path) {
                    return str_replace('\\', '/', $path);
                }, $decoded);
            } else {
                Log::warning('documents_to_delete was present but failed to decode as an array.', ['input' => $documentsToDeleteInput]);
            }
        }
        
        unset($data['required_documents'], $data['documents_to_delete']);
        
        $folder = "call_for_projects/{$id}";
        $updatedDocuments = [];
        
        // 3. LOGIQUE DE SUPPRESSION / CONSERVATION
        
        // Convertir la liste des chemins à supprimer en un Set pour une recherche rapide O(1)
        $pathsToDeleteSet = array_flip($documentsToDeletePaths);
        
        foreach ($currentDocuments as $doc) {
            // 🔥 NOUVEAU : Normaliser le chemin du document en BDD pour la comparaison
            $docPathNormalized = str_replace('\\', '/', $doc['path']);
            
            // Si le chemin NORMALISÉ EST dans la liste normalisée des chemins à supprimer
            if (isset($pathsToDeleteSet[$docPathNormalized])) {
                // Supprimer le fichier du stockage. 
                // Utilisez $doc['path'] (le chemin original stocké) pour la suppression, car c'est le chemin exact attendu par Storage.
                Storage::disk('public')->delete($doc['path']); 
                // Ne pas inclure dans $updatedDocuments (suppression en BDD)
            } else {
                // Conserver le document
                $updatedDocuments[] = $doc;
            }
        }
        
        // 4. Gérer l'Ajout de Nouveaux Fichiers (pas de changement ici, mais inclus pour la complétude)
        if (!empty($newFiles) && is_array($newFiles)) {
            foreach ($newFiles as $file) {
                if (!($file instanceof UploadedFile)) {
                    continue;
                }
                
                $originalName = $file->getClientOriginalName();
                $storeName = $originalName;

                if (Storage::disk('public')->exists("{$folder}/{$storeName}")) {
                    $storeName = time() . '_' . pathinfo($originalName, PATHINFO_FILENAME) . '.' . $file->getClientOriginalExtension();
                }

                $path = $file->storeAs($folder, $storeName, 'public');

                $updatedDocuments[] = [
                    'path' => $path,
                    'original_name' => $originalName,
                    'size' => $file->getSize(),
                    'stored_name' => $storeName,
                ];
            }
        }

        // 5. Mettre à jour la base de données.
        $data['required_documents'] = json_encode($updatedDocuments);

        $this->callForProjectRepository->update($id, $data);

        return $this->callForProjectRepository->showCallForProject($id);

    } catch (\Exception $e) {
        Log::error('Error updating call for project: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
        throw $e;
    }
}


    /**
     * @param int $id
     */

    public function deleteCallForProject(int $id): void
    {
        $this->callForProjectRepository->delete($id);
    }

    /**
     *@param array $ids
     *
     */

    public function bulkDelete(array $ids)
    {
        return $this->callForProjectRepository->bulkDelete($ids);
    }

    /**
     * @param int $id
     * @return CallForProject
     */

    public function restoreCallForProject(int $id): CallForProject
    {
        return $this->callForProjectRepository->restore($id);
    }


     public function validateAndProcess(
        int $callForProjectId,
        string $status,
        int $userId
    ): void {
        DB::beginTransaction();

        try {
            $callForProject = $this->callForProjectRepository->showCallForProject($callForProjectId);

            $this->callForProjectRepository->updateStatus(
                $callForProject,
                $status
            );

            if ($status === 'Accepté') {
                $this->createProjectFromCallForProject(
                    $callForProject,
                    $userId
                );
            }

            DB::commit();

            \Log::info('CallForProject validated successfully', [
                'call_for_project_id' => $callForProjectId,
                'status' => $status,
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e; // IMPORTANT: bubble up
        }
    }

    private function createProjectFromCallForProject(
        CallForProject $callForProject,
        int $userId
    ): void {

        //return collaborator of responsible id
        $collaboratorId=Collaborator::where('id',$callForProject->responsible_id)->value('user_id');
        \Log::info('collaborator found for responsible id', ['collaborator_id' => $collaboratorId]);
        $projectData = [
            'project_name'      => $callForProject->title,
            'start_date'        => $callForProject->debut_date,
            'end_date'          => $callForProject->end_date,
            'created_by_id'     => $userId,
            'project_status_id' => ProjectStatus::where('name', 'Brouillon')->value('id'),
            'responsible_id'    => $collaboratorId?$collaboratorId:null,
        ];

        $partners = [];

        if ($callForProject->sponsor_id) {
            $partners[] = [
                'partner_id' => $callForProject->sponsor_id,
                'partner_role' => ProjectPartner::ROLE_PRINCIPAL,
                'partner_contribution' => 0,
            ];
        }

        $result = $this->projectService->createProjectWithAutomaticTasks(
            $projectData,
            $partners,
            null
        );

        if (empty($result['project'] ?? null)) {
            throw new \RuntimeException('Project creation failed');
        }
    }



}