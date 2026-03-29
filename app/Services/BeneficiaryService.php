<?php

namespace App\Services;

use App\Models\Beneficiary;
use App\Models\BeneficiaryStatusHistory;
use App\Models\Group;
use App\Models\Level;
use App\Models\User;
use App\Models\ParentModel;
use App\Repositories\BeneficiaryRepository;
use App\Services\ParentService; 
use App\Services\NotificationService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB; 
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Http\UploadedFile;
use Carbon\Carbon;
use App\Models\Collaborator;


class BeneficiaryService
{
    public BeneficiaryRepository $beneficiaryRepository;
    public ParentService $parentService;
    protected NotificationService $notificationService;

    public function __construct(
        BeneficiaryRepository $beneficiaryRepository, 
        ParentService $parentService,
        NotificationService $notificationService
    ) {
        $this->beneficiaryRepository = $beneficiaryRepository;
        $this->parentService = $parentService;
        $this->notificationService = $notificationService;
    }

    /**
     *
     * @return Collection<int, Beneficiary>
     */
    public function getAllBeneficiaries(): Collection
    {
        return $this->beneficiaryRepository->getAll();
    }

    /**
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator<Beneficiary>
     */
    public function getPaginatedBeneficiaries(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->beneficiaryRepository->getPaginated($filters, $perPage);
    }

    /**
     *
     * @param int $id
     * @return Beneficiary|null
     */
    public function getBeneficiary(int $id): ?Beneficiary
    {
        return $this->beneficiaryRepository->findById($id);
    }
  

    /**
     *
     * @param array<string, mixed> $data
     * @return Beneficiary|null
     */
    public function createBeneficiary(array $data, $beneficiaryStatus): ?Beneficiary
    {
        return DB::transaction(function () use ($data, $beneficiaryStatus) {
            $parentData = $data['parents'] ?? [];
            unset($data['parents']);
            unset($data['new_status_data']);

            if (!isset($data['created_by']) && auth()->check()) {
                $data['created_by'] = auth()->id();
            }
          
            $beneficiary = $this->beneficiaryRepository->create($data);
            
            if (!empty($beneficiaryStatus)){
                BeneficiaryStatusHistory::create([
                    'beneficiary_id' => $beneficiary['id'],
                    'status' => $beneficiaryStatus['status'],
                    'reason' => $beneficiaryStatus['reason'] ?? null, 
                    'previous_group_id' => $beneficiary['group_id'] ?? null, 
                    'transfer_commune_id' => $beneficiaryStatus['transfer_commune_id'] ?? null, 
                    'transfer_class_id' => $beneficiaryStatus['transfer_class_id'] ?? null,
                    'change_date' => $beneficiaryStatus['change_date'],
                    'created_by' => auth()->id(),
                ]);
            }

            if ($beneficiary && !empty($parentData)) {
                $this->syncParents($beneficiary, $parentData);
            }

            return $beneficiary;
        });
    }

    /**
     *
     * @param Beneficiary $beneficiary
     * @param array<string, mixed> $data
     * @return Beneficiary|null
     */
    public function updateBeneficiary(Beneficiary $beneficiary, array $data, $beneficiaryStatus): ?Beneficiary
    {
        return DB::transaction(function () use ($beneficiary, $data, $beneficiaryStatus) {
            $parentData = $data['parents'] ?? null;
            unset($data['parents']);
            unset($data['new_status_data']);
            $newGroupId = $beneficiaryStatus['destination_group_id'] ?? null;

            $updated = $this->beneficiaryRepository->update($beneficiary, $data, $newGroupId);
            
            if ($updated && !empty($beneficiaryStatus)){
                BeneficiaryStatusHistory::create([
                    'beneficiary_id' => $beneficiary['id'],
                    'status' => $beneficiaryStatus['status'],
                    'reason' => $beneficiaryStatus['reason'] ?? null, 
                    'previous_group_id' => $beneficiary['group_id'] ?? null, 
                    'transfer_commune_id' => $beneficiaryStatus['transfer_commune_id'] ?? null, 
                    'transfer_class_id' => $beneficiaryStatus['transfer_class_id'] ?? null, 
                    'change_date' => $beneficiaryStatus['change_date'], 
                    'destination_group_id' => $beneficiaryStatus['destination_group_id'] ?? null,
                    'created_by' => auth()->id(),
                ]);
            }
            
            if ($updated && !is_null($parentData)) {
                $this->syncParents($beneficiary, $parentData);
            }

            return $beneficiary->fresh();
        });
    }

    protected function syncParents(Beneficiary $beneficiary, array $parentData): void
    {
        $syncData = [];

        if (!empty($parentData['existing'])) {
            foreach ($parentData['existing'] as $parent) {
                $syncData[$parent['id']] = ['legal_role' => $parent['legal_role']];
            }
        }

        if (!empty($parentData['new'])) {
            foreach ($parentData['new'] as $parentInput) {
                $legalRole = $parentInput['legal_role'];
                unset($parentInput['legal_role']);
                
                $newParent = $this->parentService->create($parentInput);
                $syncData[$newParent->id] = ['legal_role' => $legalRole];
            }
        }

        $beneficiary->parents()->sync($syncData);
    }

    /**
     *
     * @param Beneficiary $beneficiary
     * @return bool|null
     */
    public function deleteBeneficiary(Beneficiary $beneficiary): ?bool
    {
        return $this->beneficiaryRepository->delete($beneficiary);
    }

    /**
     *
     * @param array<int> $ids
     * @return array<array{id: int, success: bool, message: string}>
     */
    public function toggleBeneficiaryActivation(array $ids): array
    {
        return $this->beneficiaryRepository->toggleActivation($ids);
    }

    /**
     *
     * @param array $filters Additional filters for related data
     * @return array
     */
    public function getFormOptions(array $filters = []): array
    {
        $levels = Level::select('id', 'title as name', 'min_age', 'max_age')->get();
        $groups = class_exists(Group::class) ? Group::all()->map(fn($group) => ['id' => $group->id, 'name' => $group->name, 'code' => $group->code]) : collect();
        $users = User::all()->map(fn($user) => ['id' => $user->id, 'name' => $user->name]);
        $statuses = ['Inscrit', 'Déperdition', 'Récupération', 'Remplacement','Transfert'];
        $genders = ['Masculin', 'Féminin'];
        $nationalities = ['Marocain', 'Étranger'];
        $radiationReasons = ['Abandon', 'Mutation', 'Désistement', 'Autre'];
        $parents = ParentModel::all()->map(fn($parent) => ['id' => $parent->id, 'name' => $parent->first_name. ' ' . $parent->last_name]);

        return [
            'statuses' => $statuses,
            'genders' => $genders,
            'nationalities' => $nationalities,
            'levels' => $levels,
            'groups' => $groups,
            'radiation_reasons' => $radiationReasons,
            'users' => $users,
            'parents' => $parents, 
        ];
    }

    /**
     * Get the count of beneficiaries in a given class by class ID.
     *
     * @param int $classId
     * @return int
     */
    public function countByClassId(int $classId): int
    {
        return $this->beneficiaryRepository->countByClassId($classId);
    }

    /**
     * Export beneficiaries for insurance and send notification
     * 
     * @param int|null $actorCollaboratorId
     * @return string Path to the exported file
     */
   public function exportAssurance(?int $actorCollaboratorId = null): string
{
    \Log::info('Fetching beneficiaries for assurance export...');
    $beneficiaries = $this->beneficiaryRepository->getEligibleForAssuranceExport();
    \Log::info('Number of beneficiaries to export for assurance: ' . $beneficiaries->count());

    $fileName = 'temp/assurance_export_' . time() . '.xlsx';
    $fullPath = storage_path('app/' . $fileName);

    $dir = dirname($fullPath);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    try {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = ['ID','Annee','MOIS D\'INSCRIPTION','PROJET','REGION','ACTIVITE','ECOLE','PROVINCE','NOM', 'PRENOM','DATE DE NAISSANCE' ,'CIN TUTEUR', 'SEXE', 'STATUT'];
        $sheet->fromArray($headers, null, 'A1');

        $row = 2;
        foreach ($beneficiaries as $b) {
            \Log::info('beneficiary legal guardian',['legal_guardian'=>$b->father?->first()?->cin ?? '']);
            $startDateMonth = (int) ($b->group?->start_date?->format('m') ?? 0);
            $startDateYear = (int) ($b->group?->start_date?->format('Y') ?? 0);
            $guardianCin = $b->legal_guardian_cin;

            $schoolYear = '';
            if ($startDateYear > 0) {
                $schoolYear = $startDateMonth >= 9
                    ? $startDateYear . '/' . ($startDateYear + 1)
                    : ($startDateYear - 1) . '/' . $startDateYear;
            }

            $sheet->fromArray([
                $b->id,
                $schoolYear,
                $b->enrollment_date->format('M-d') ?? '',
                $b->group?->class?->favoriteClassResource?->project?->project_name,
                $b->group?->class?->favoriteClassResource?->project?->province?->region?->name ?? '',
                $b->group?->class?->favoriteClassResource?->project?->programType?->name ?? '',
                $b->group?->class?->unit?->name ?? '',
                $b->group?->class?->favoriteClassResource?->project?->province?->name ?? '',
                $b->last_name ?? '',
                $b->first_name ?? '',
                $b->date_of_birth?->format('Y-m-d') ?? '',
                $guardianCin,
                $b->gender ?? '',
                $b->insurance_status ?? '',
            ], null, 'A' . $row);

            $row++;
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($fullPath);

    } catch (\Throwable $e) {
        Log::error('Failed to generate assurance export file: ' . $e->getMessage());
        throw $e;
    }

    $ids = $beneficiaries->pluck('id')->toArray();

    if (!empty($ids)) {
        // Wrap the status update in a transaction
        DB::transaction(function() use ($ids) {
            $this->beneficiaryRepository->updateAssuranceStatus($ids, 'En cours');
        });
    }

    // Send notifications outside transaction
    if (!empty($ids)) {
        $this->sendExportNotification($actorCollaboratorId, $ids);
    }

    return $fullPath;
}

/**
 * Updated sendExportNotification to optionally handle multiple beneficiaries
 */
protected function sendExportNotification(?int $actorCollaboratorId = null, array $beneficiaryIds = []): void
{
    try {
        if (empty($actorCollaboratorId)) {
            $actorCollaboratorId = auth()->user()->collaborator->id ?? null;
        }

        if (!$actorCollaboratorId) {
            \Log::warning('BeneficiaryService: Cannot send export notification - no actor collaborator ID');
            return;
        }

        $actor = Collaborator::find($actorCollaboratorId);
        if (!$actor) {
            \Log::warning('BeneficiaryService: Actor collaborator not found', ['id' => $actorCollaboratorId]);
            return;
        }

       
        $positionNames = ['Admin SI', 'Trésorerie'];
        $recipientIds = Collaborator::whereHas('position', fn($q) => $q->whereIn('title', $positionNames))
            ->pluck('id')
            ->toArray();

        if (empty($recipientIds)) {
            \Log::warning('BeneficiaryService: No recipients found for export notification');
            return;
        }

        $title = "Liste des bénéficiaires pour assurance exportée";
        $text = "{$actor->first_name} {$actor->last_name} a exporté la liste des bénéficiaires pour l'assurance (" . count($beneficiaryIds) . " bénéficiaires).";
        $target = [
            'type' => 'open_modal',
            'name' => 'view_beneficiary_list',
            'id' => 1,
        ];

        $notification = $this->notificationService->save(
            $title,
            $text,
            $actorCollaboratorId,
            $target,
            $recipientIds
        );

        \Log::info('BeneficiaryService: export notification created', [
            'notification_id' => $notification->id ?? null,
            'recipients' => $recipientIds
        ]);

    } catch (\Throwable $e) {
        \Log::error('BeneficiaryService: failed to send export notification: ' . $e->getMessage());
    }
}

    /**
     * Import insurance data and send notifications
     * 
     * @param UploadedFile $file
     * @param int|null $actorCollaboratorId
     * @return array
     */
    public function importAssurance(UploadedFile $file, ?int $actorCollaboratorId = null): array
    {
        try {
            \Log::info('Starting assurance import...');
            
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();
            
            \Log::info('Excel Headers:', ['headers' => $rows[0] ?? []]);
            
            $headers = $rows[0] ?? [];
            $startDateColumnIndex = null;
            $endDateColumnIndex = null;
            
            foreach ($headers as $index => $header) {
                $normalizedHeader = strtoupper(trim($header ?? ''));
                
                if (strpos($normalizedHeader, 'DÉBUT') !== false || 
                    strpos($normalizedHeader, 'DEBUT') !== false ||
                    strpos($normalizedHeader, 'START') !== false) {
                    $startDateColumnIndex = $index;
                    \Log::info("Found start date column at index: {$index}");
                }
                
                if (strpos($normalizedHeader, 'FIN') !== false ||
                    strpos($normalizedHeader, 'END') !== false) {
                    $endDateColumnIndex = $index;
                    \Log::info("Found end date column at index: {$index}");
                }
            }
            
            if ($startDateColumnIndex === null || $endDateColumnIndex === null) {
                $startDateColumnIndex = count($headers) - 2;
                $endDateColumnIndex = count($headers) - 1;
                \Log::warning("Could not find date columns by header name. Using last two columns: start={$startDateColumnIndex}, end={$endDateColumnIndex}");
            }
            
            array_shift($rows);
            
            $results = [
                'success' => 0,
                'failed' => 0,
                'errors' => [],
                'updated_ids' => []
            ];
            
            DB::beginTransaction();
            
            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2;
                
                try {
                    if (empty(array_filter($row))) {
                        continue;
                    }
                    
                    $beneficiaryId = $row[0] ?? null;
                    $insuranceStartDate = $row[$startDateColumnIndex] ?? null;
                    $insuranceEndDate = $row[$endDateColumnIndex] ?? null;
                    
                    \Log::info("Processing row {$rowNumber}", [
                        'beneficiary_id' => $beneficiaryId,
                        'raw_start_date' => $insuranceStartDate,
                        'raw_end_date' => $insuranceEndDate,
                    ]);
                    
                    if (empty($beneficiaryId)) {
                        $results['failed']++;
                        $results['errors'][] = [
                            'row' => $rowNumber,
                            'message' => 'ID bénéficiaire manquant'
                        ];
                        continue;
                    }
                    
                    $beneficiary = $this->beneficiaryRepository->findById((int)$beneficiaryId);
                    
                    if (!$beneficiary) {
                        $results['failed']++;
                        $results['errors'][] = [
                            'row' => $rowNumber,
                            'beneficiary_id' => $beneficiaryId,
                            'message' => 'Bénéficiaire non trouvé'
                        ];
                        continue;
                    }
                    
                    $startDate = $this->parseExcelDate($insuranceStartDate);
                    $endDate = $this->parseExcelDate($insuranceEndDate);
                    
                    if (!$startDate || !$endDate) {
                        $results['failed']++;
                        $results['errors'][] = [
                            'row' => $rowNumber,
                            'beneficiary_id' => $beneficiaryId,
                            'message' => 'Dates d\'assurance invalides',
                            'raw_start' => $insuranceStartDate,
                            'raw_end' => $insuranceEndDate,
                        ];
                        continue;
                    }
                    
                    $updateData = [
                        'insurance_status' => 'Assuré',
                        'insurance_start_date' => $startDate,
                        'insurance_end_date' => $endDate
                    ];
                    
                    $updated = $beneficiary->update($updateData);
                    
                    if ($updated) {
                        $results['success']++;
                        $results['updated_ids'][] = $beneficiaryId;
                        
                        \Log::info("Beneficiary {$beneficiaryId} insurance updated successfully");
                    } else {
                        $results['failed']++;
                        $results['errors'][] = [
                            'row' => $rowNumber,
                            'beneficiary_id' => $beneficiaryId,
                            'message' => 'Échec de la mise à jour'
                        ];
                    }
                    
                } catch (\Exception $e) {
                    $results['failed']++;
                    $results['errors'][] = [
                        'row' => $rowNumber,
                        'beneficiary_id' => $beneficiaryId ?? 'inconnu',
                        'message' => 'Erreur: ' . $e->getMessage()
                    ];
                    \Log::error("Error importing row {$rowNumber}: " . $e->getMessage());
                }
            }
            
            DB::commit();

           
            if ($results['success'] > 0) {
                $this->sendImportNotifications($actorCollaboratorId, $results['updated_ids']);
            }
            
            \Log::info('Assurance import completed', [
                'success' => $results['success'],
                'failed' => $results['failed'],
            ]);
            
            return $results;
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Fatal error during assurance import: ' . $e->getMessage());
            
            return [
                'success' => 0,
                'failed' => 0,
                'errors' => [
                    [
                        'row' => 0,
                        'message' => 'Erreur fatale: ' . $e->getMessage()
                    ]
                ],
                'updated_ids' => []
            ];
        }
    }

    /**
     * Send notifications for import action
     * 
     * @param int|null $actorCollaboratorId
     * @param array $updatedBeneficiaryIds
     * @return void
     */
 protected function sendImportNotifications(?int $actorCollaboratorId, array $updatedBeneficiaryIds): void
{
    try {
        // Resolve actor collaborator
        $actorCollaboratorId = $actorCollaboratorId ?? auth()->user()->collaborator->id ?? null;
        if (!$actorCollaboratorId || !($actor = Collaborator::find($actorCollaboratorId))) {
            Log::warning('Import notification aborted: actor collaborator not found', ['id' => $actorCollaboratorId]);
            return;
        }

        $beneficiaries = Beneficiary::whereIn('id', $updatedBeneficiaryIds)
            ->with([
                'creator.collaborator.superior',
                'group.class.favoriteClassResource.project.responsible.collaborator'
            ])
            ->get();

        if ($beneficiaries->isEmpty()) {
            Log::warning('Import notification aborted: no beneficiaries found');
            return;
        }

        // Determine recipients once
        $recipientCollaboratorIds = [];

        // Admin SI & Responsable régional
        $recipientCollaboratorIds = array_merge(
            $recipientCollaboratorIds,
            Collaborator::whereHas('position', fn($q) => $q->whereIn('title', ['Admin SI', 'Responsable régional']))
                ->pluck('id')->toArray()
        );

        // Remove duplicates
        $recipientCollaboratorIds = array_values(array_unique($recipientCollaboratorIds));

        if (empty($recipientCollaboratorIds)) {
            Log::warning('Import notification aborted: no recipients found');
            return;
        }

        // Send **individual notification per beneficiary**
        foreach ($beneficiaries as $beneficiary) {
            $dynamicRecipients = $recipientCollaboratorIds;

            // Add creator supervisor if exists
            if ($beneficiary->creator?->collaborator?->superior?->id) {
                $dynamicRecipients[] = $beneficiary->creator->collaborator->superior->id;
            }

            // Add project manager collaborator if exists
            if ($pmId = $beneficiary
                    ->group
                    ?->class
                    ?->favoriteClassResource
                    ?->project
                    ?->responsible
                    ?->collaborator
                    ?->id
            ) {
                $dynamicRecipients[] = $pmId;
            }

            $dynamicRecipients = array_values(array_unique($dynamicRecipients));

            // Send notification
            $title = 'Importation Assurance';
            $text = "{$actor->first_name} {$actor->last_name} a importé le bénéficiaire #{$beneficiary->id} ({$beneficiary->last_name} {$beneficiary->first_name}).";

            $target = [
                'type' => 'open_modal',
                'name' => 'view_beneficiary_list',
                'id' => $beneficiary->id,
            ];

            $notification = $this->notificationService->save(
                $title,
                $text,
                $actorCollaboratorId,
                $target,
                $dynamicRecipients
            );

            Log::info('Import notification sent', [
                'notification_id' => $notification->id ?? null,
                'beneficiary_id' => $beneficiary->id,
                'recipients' => $dynamicRecipients
            ]);
        }

    } catch (\Throwable $e) {
        Log::error('Failed to send import notifications', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
}

    /**
     * Parse Excel date value to Carbon date string
     * EXPECTED FORMAT: dd/mm/yyyy (16/12/2024)
     *
     * @param mixed $dateValue
     * @return string|null
     */
    private function parseExcelDate($dateValue): ?string
    {
        if (empty($dateValue) || $dateValue === '' || $dateValue === null) {
            return null;
        }
        
        try {
            $dateValue = is_string($dateValue) ? trim($dateValue) : $dateValue;
            
            if (is_string($dateValue) && preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $dateValue)) {
                try {
                    $date = Carbon::createFromFormat('d/m/Y', $dateValue);
                    if ($date && $date->year >= 1900 && $date->year <= 2100) {
                        $result = $date->format('Y-m-d');
                        \Log::info("Parsed date (dd/mm/yyyy): {$dateValue} → {$result}");
                        return $result;
                    }
                } catch (\Exception $e) {
                    \Log::warning("Failed to parse dd/mm/yyyy format: {$dateValue}");
                }
            }
            
            if (is_numeric($dateValue) && $dateValue > 1) {
                try {
                    $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateValue);
                    if ($date) {
                        $result = $date->format('Y-m-d');
                        \Log::info("Parsed Excel numeric date: {$dateValue} → {$result}");
                        return $result;
                    }
                } catch (\Exception $e) {
                    \Log::warning("Failed to parse Excel numeric date: {$dateValue}");
                }
            }
            
            \Log::error("Date format invalide. Format attendu: jj/mm/aaaa (ex: 16/12/2024)", [
                'valeur_reçue' => $dateValue,
                'type' => gettype($dateValue)
            ]);
            
            return null;
            
        } catch (\Exception $e) {
            \Log::error("Erreur lors de l'analyse de la date", [
                'valeur' => $dateValue,
                'erreur' => $e->getMessage()
            ]);
            return null;
        }
    }
}