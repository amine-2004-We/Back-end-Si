<?php

namespace App\Http\Controllers\Api;

use App\Constants\Role;
use App\Enums\ConventionStatus;
use App\Http\Controllers\Controller;
use App\Models\Collaborator;
use App\Models\Convention;
use App\Models\Project;
use App\Models\ProjectStatus;
use App\Models\FinancialInstallment;
use App\Services\ConventionService;
use App\Services\Notification\MailService;
use App\Services\NotificationService;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Requests\StoreConventionRequest;
use App\Http\Requests\UpdateConventionRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Carbon\CarbonPeriod;


class ConventionController extends Controller
{
    protected $conventionService;

    public function __construct(ConventionService $conventionService)
    {
        $this->conventionService = $conventionService;
    }

    /**
     * Display a listing of conventions with pagination and optional filters.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $this->authorize('viewAny', Convention::class);

            $filters = $request->only([
                'agreement_code', 'title', 'partner_id', 'status', 'per_page', 'is_active', 'type', 'reporting_periodicity','tracked_individual_id','observations',
            ]);

            $conventions = $this->conventionService->getConventions($filters);

            $conventions->through(function ($item) {
                if (!$item->relationLoaded('installments')) {
                    $item->load('installments');
                }

                $item->signed_document = $item->signed_document
                    ? asset('storage/' . $item->signed_document)
                    : null;
                return $item;
            });

            return response()->json($conventions);
        } catch (Exception $e) {
            Log::error("Erreur lors de l'indexation des conventions : " . $e->getMessage());
            return response()->json(['message' => 'Échec de la récupération des conventions.'], 500);
        }
    }

    /**
     * Store a newly created convention.
     */
    public function store(StoreConventionRequest $request): JsonResponse
    {
        try {
            $this->authorize('create', Convention::class);

            $data = $request->validated();

            $convention = $this->conventionService->createConvention($data);

            $convention->signed_document = $convention->signed_document
            ? url()->route('conventions.downloadDocument', ['id' => $convention->id])
            : null;

            return response()->json($convention, 201);
        } catch (Exception $e) {
            return response()->json(['message' => 'Échec de la création de la convention.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display a specific convention.
     */
    public function show($id): JsonResponse
    {
        try {
            $convention = $this->conventionService->getConventionById((int)$id);
            $this->authorize('view', $convention);

            $convention->signed_document = $convention->signed_document
            ? url()->route('conventions.downloadDocument', ['id' => $convention->id])
            : null;

            return response()->json($convention);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
             return response()->json(['message' => 'Convention non trouvée.'], 404);
        } catch (Exception $e) {
             Log::error("Erreur lors de l'affichage de la convention (ID: $id) : " . $e->getMessage());
             return response()->json(['message' => 'Erreur interne.'], 500);
        }
    }

    /**
     * Update a convention by ID.
     */
    public function update(UpdateConventionRequest $request, $id): JsonResponse
    {
        try {
            $convention = $this->conventionService->getConventionById((int)$id);
            $this->authorize('update', $convention);

            $validatedData = $request->validated();

            $updatedConvention = $this->conventionService->updateConvention((int)$id, $validatedData);

            $updatedConvention->signed_document = $updatedConvention->signed_document
                ? asset('storage/' . $updatedConvention->signed_document)
                : null;

            return response()->json($updatedConvention);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validation error',
                'messages' => $e->errors()
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Convention non trouvée'], 404);
        } catch (\Throwable $e) {
            Log::error("Erreur lors de la mise à jour de la convention (ID: $id) : " . $e->getMessage());
            return response()->json(['error' => 'Erreur serveur: ' . $e->getMessage()], 500);
        }
    }


    /**
     * Delete a convention by ID.
     */
    public function destroy($id): JsonResponse
    {
        try {
            $convention = $this->conventionService->getConventionById((int)$id);
            $this->authorize('delete', $convention);

            $this->conventionService->deleteConvention((int)$id);
            return response()->json(null, 204);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
             return response()->json(['message' => 'Convention non trouvée.'], 404);
        } catch (Exception $e) {
            Log::error("Échec de la suppression de la convention (ID: $id) : " . $e->getMessage());
            return response()->json(['message' => 'Échec de la suppression de la convention.'], 500);
        }
    }

    /**
     * Bulk delete conventions by IDs.
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:conventions,id',
        ]);

        try {

            $ids = array_map('intval', $request->ids);

            $deletedCount = $this->conventionService->deleteMultipleConventions($ids);

            return response()->json([
                'message' => "$deletedCount convention(s) supprimée(s) avec succès."
            ]);
        } catch (Exception $e) {
            Log::error("Échec de la suppression multiple des conventions : " . $e->getMessage());
            return response()->json(['message' => 'Échec de la suppression multiple.'], 500);
        }
    }


    /**
     * Restore a soft deleted convention by ID.
     */
    public function restore($id): JsonResponse
    {
        try {
            $id = (int) $id;
            $convention = $this->conventionService->restoreConvention($id);

            $convention->signed_document = $convention->signed_document
            ? url()->route('conventions.downloadDocument', ['id' => $convention->id])
            : null;

            return response()->json($convention);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
             return response()->json(['message' => 'Convention non trouvée.'], 404);
        } catch (Exception $e) {
            Log::error("Échec de la restauration de la convention (ID: $id) : " . $e->getMessage());
            return response()->json(['message' => 'Échec de la restauration de la convention.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Récupère les options nécessaires à la création ou à l'édition d'une convention.
     **/
    public function getConventionOptions(): JsonResponse
    {
        try {
            $options = $this->conventionService->getConventionOptions();
            return response()->json($options);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des options de convention : ' . $e->getMessage());
            return response()->json([
                'message' => 'Impossible de récupérer les options de convention.'
            ], 500);
        }
    }

    /**
     * Affiche le document signé d'une convention.
     **/
    public function showDocument($id)
{
    try {
        $documentPath = $this->conventionService->getSignedDocument((int)$id); 

        if (!$documentPath) {
             return response()->json(['message' => 'Document non spécifié pour cette convention.'], 404);
        }

        $fullPath = Storage::disk('private')->path($documentPath);

        if (!Storage::disk('private')->exists($documentPath)) {
            Log::error("Fichier introuvable sur le disque 'private' : $documentPath (ID: $id)");
            return response()->json(['message' => 'Document non trouvé sur le serveur.'], 404);
        }

        $filename = basename($fullPath);
        $mimeType = mime_content_type($fullPath);

        return response()->file($fullPath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . $filename . '"',  
        ]);

    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return response()->json(['message' => 'Convention non trouvée.'], 404);
    } catch (\Exception $e) {
        Log::error("Erreur lors de l'affichage du document (ID: $id) : " . $e->getMessage());
        return response()->json([
            'message' => 'Une erreur est survenue lors de la récupération du document.'
        ], 500);
    }
}


    /**
     * Gère la requête de téléchargement du document signé.
     *
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|JsonResponse
     */
    public function downloadDocument(int $id)
    {
        try {
            $fileData = $this->conventionService->getDocumentForDownload($id);

            $fullPath = Storage::disk($fileData['disk'])->path($fileData['path']);

             return response()->download($fullPath, $fileData['name']);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Convention non trouvée.'], 404);

        } catch (\Exception $e) {
            $statusCode = $e->getCode() === 404 ? 404 : 500;
            return response()->json(['error' => $e->getMessage()], $statusCode);

        } catch (\Throwable $e) {
            Log::error("Erreur fatale lors du téléchargement du document (ID: $id): " . $e->getMessage());
            return response()->json(['error' => 'Erreur serveur inattendue.'], 500);
        }
    }

    /**
     * Validate a convention and manage status transitions
     */
    public function validateConvention(Request $request, Convention $convention): JsonResponse
    {
        try {
            $this->authorize('validate', $convention);

            $user = $request->user();
            $previousStatus = $convention->status;

            $convention->load('responsible', 'project', 'partner');

            switch ($convention->status) {
                case ConventionStatus::Draft:
                    if (!$user->hasAnyRole([Role::RESPONSABLE_PARTENARIAT, Role::RESPONSABLE_PARTENARIAT_ET_DEVELOPPEMENT])) {
                        return response()->json([
                            'error' => 'Only Responsable Partenariat can validate this convention at this stage.'
                        ], Response::HTTP_FORBIDDEN);
                    }

                    $convention->status = ConventionStatus::Validated;
                    $convention->save();

                    $this->sendStatusChangeNotifications($convention, $previousStatus, $user);
                    break;

                case ConventionStatus::Validated:
                    if (!$user->hasRole(Role::DIRECTION_GENERALE)) {
                        return response()->json([
                            'error' => 'Only Direction Générale (DG) can sign this convention at this stage.'
                        ], Response::HTTP_FORBIDDEN);
                    }

                    $convention->status = ConventionStatus::Signed;
                    $convention->save();

                    // $this->sendStatusChangeNotifications($convention, $previousStatus, $user);
                    break;

                case ConventionStatus::Signed:
                    if (!$user->hasAnyRole([Role::CHEF_DE_PROJET, Role::FINANCE, Role::DIRECTION_FINANCIERE])) {
                        return response()->json([
                            'error' => 'Only Responsable Projet or Finance can start operational and financial monitoring at this stage.'
                        ], Response::HTTP_FORBIDDEN);
                    }

                    $convention->status = ConventionStatus::InProgress;
                    $convention->save();

                    $this->sendStatusChangeNotifications($convention, $previousStatus, $user);
                    break;

                case ConventionStatus::InProgress:
                    if (!$user->hasAnyRole([Role::RESPONSABLE_PARTENARIAT, Role::RESPONSABLE_PARTENARIAT_ET_DEVELOPPEMENT])) {
                        return response()->json([
                            'error' => 'Only Responsable Partenariat can close this convention at this stage.'
                        ], Response::HTTP_FORBIDDEN);
                    }

                    $convention->status = ConventionStatus::Closed;
                    $convention->save();

                    $this->sendStatusChangeNotifications($convention, $previousStatus, $user);
                    break;

                default:
                    return response()->json([
                        'error' => 'This convention cannot be validated in its current status.'
                    ], Response::HTTP_FORBIDDEN);
            }

            return response()->json([
                'message' => "Convention status changed from {$previousStatus->value} to {$convention->status->value}",
                'convention' => $convention->fresh(['partner', 'project', 'installments', 'responsible', 'trackedIndividual'])
            ]);

        } catch (Exception $e) {
            return response()->json([
                'error' => 'Server error: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Send notifications to relevant stakeholders when status changes
     */
    private function sendStatusChangeNotifications(
        Convention $convention,
        ConventionStatus $previousStatus,
        $validator
    ): void {
        $recipientIds = [];

        if ($convention->responsible) {
            $recipientIds[] = $convention->responsible->id;
        }

        $dgCollaborators = Collaborator::whereHas('position', function ($query) {
            $query->where('title', Role::DIRECTION_GENERALE);
        })->pluck('id')->toArray();
        $recipientIds = array_merge($recipientIds, $dgCollaborators);

        $partenariatCollaborators = Collaborator::whereHas('position', function ($query) {
            $query->whereIn('title', [
                Role::RESPONSABLE_PARTENARIAT,
                Role::RESPONSABLE_PARTENARIAT_ET_DEVELOPPEMENT,
                Role::CHARGE_DE_PARTENARIAT,
            ]);
        })->pluck('id')->toArray();
        $recipientIds = array_merge($recipientIds, $partenariatCollaborators);

        // Get Finance collaborators
        $financeCollaborators = Collaborator::whereHas('position', function ($query) {
            $query->whereIn('title', [
                Role::FINANCE,
                Role::DIRECTION_FINANCIERE,
            ]);
        })->pluck('id')->toArray();
        $recipientIds = array_merge($recipientIds, $financeCollaborators);

        $recipientIds = array_unique($recipientIds);

        $senderId = auth()->user()->collaborator?->id ?? $convention->responsible?->id ?? 1;

        NotificationService::save(
            "Convention Status Updated",
            "Convention '{$convention->title}' status changed from {$previousStatus->value} to {$convention->status->value}",
            $senderId,
            [
                'type' => 'open_modal',
                'name' => 'view_convention',
                'id' => $convention->id
            ],
            $recipientIds
        );

        $emails = [];
        foreach ($recipientIds as $recipientId) {
            $collaborator = Collaborator::find($recipientId);
            if ($collaborator && $collaborator->email) {
                $emails[] = $collaborator->email;
            }
        }

        if (!empty($emails)) {
            MailService::sendMail(
                $emails,
                "Convention Status Updated",
                'emails.training_alert',
                [
                    'item' => $convention,
                    'validator' => $validator,
                    'previousStatus' => $previousStatus->value,
                    'newStatus' => $convention->status->value,
                ]
            );
        }
    }

    /**
     * Get dashboard chart data for conventions and projects
     * Includes:
     * - Convention rate (Gauge chart)
     * - Total subventions engaged (Quarterly histogram)
     * - Total active projects count
     * - Projects in progress count
     */
    public function getDashboardChartData(Request $request): JsonResponse
    {
        try {
            $currentYear = now()->year;
            $year = $request->query('year', $currentYear);

            $conventionRateData = $this->getConventionRateData($year);
            $subventionsData = $this->getSubventionsQuarterlyData($year);
            $projectsStats = $this->getProjectsStatistics();

            return response()->json([
                'convention_rate' => $conventionRateData,
                'subventions' => $subventionsData,
                'projects' => $projectsStats,
            ]);

        } catch (Exception $e) {
            Log::error("Erreur lors de la récupération des données du tableau de bord : " . $e->getMessage());
            return response()->json([
                'message' => 'Échec de la récupération des données du tableau de bord.'
            ], 500);
        }
    }

    /**
     * Calculate convention rate by month
     * Formula: (Conventions with status "Signed") / (Total Conventions) × 100
     */
    private function getConventionRateData(int $year): array
    {
        $months = [];
        $rates = [];

        for ($month = 1; $month <= 12; $month++) {
            $startDate = Carbon::createFromDate($year, $month, 1);
            $endDate = $startDate->clone()->endOfMonth();

            $totalConventions = Convention::whereBetween('created_at', [$startDate, $endDate])
                ->count();

            $signedConventions = Convention::whereBetween('created_at', [$startDate, $endDate])
                ->where('status', ConventionStatus::Signed)
                ->count();

            $rate = $totalConventions > 0 ? round(($signedConventions / $totalConventions) * 100, 2) : 0;

            $months[] = $startDate->format('M');
            $rates[] = $rate;
        }

        return [
            'type' => 'gauge',
            'title' => 'Taux de conventions en cours',
            'unit' => '%',
            'frequency' => 'Mensuelle',
            'months' => $months,
            'data' => $rates,
            'current_value' => end($rates) ?? 0,
        ];
    }

    /**
     * Calculate total subventions engaged by quarter
     */
    private function getSubventionsQuarterlyData(int $year): array
    {
        $quarters = [];
        $amounts = [];

        for ($quarter = 1; $quarter <= 4; $quarter++) {
            $startMonth = ($quarter - 1) * 3 + 1;
            $endMonth = $startMonth + 2;

            $startDate = Carbon::createFromDate($year, $startMonth, 1);
            $endDate = Carbon::createFromDate($year, $endMonth, 1)->endOfMonth();

            $totalAmount = FinancialInstallment::where(function ($query) use ($startDate, $endDate) {
                  $query->whereBetween('reception_date', [$startDate, $endDate])
                      ->orWhere(function ($q) use ($startDate, $endDate) {
                        $q->whereNull('reception_date')
                          ->whereBetween('due_date', [$startDate, $endDate]);
                      });
                })
                ->sum('amount_received');

            $quarters[] = "Q{$quarter} {$year}";
            $amounts[] = round((float) $totalAmount, 2);
        }

        return [
            'type' => 'histogram',
            'title' => 'Montant total des subventions engagées',
            'unit' => 'DH',
            'frequency' => 'Trimestrielle',
            'labels' => $quarters,
            'data' => $amounts,
            'total' => array_sum($amounts),
        ];
    }

    /**
     * Get projects statistics
     */
    private function getProjectsStatistics(): array
    {
        $activeStatusIds = ProjectStatus::whereIn('name', ['Actif', 'Active'])->pluck('id');
        $inProgressStatusIds = ProjectStatus::whereIn('name', ['En cours', 'In progress'])->pluck('id');

        $totalProjects = Project::when($activeStatusIds->isNotEmpty(), function ($query) use ($activeStatusIds) {
            $query->whereIn('project_status_id', $activeStatusIds);
        })->count();

        $projectsInProgress = Project::when($inProgressStatusIds->isNotEmpty(), function ($query) use ($inProgressStatusIds) {
            $query->whereIn('project_status_id', $inProgressStatusIds);
        })->count();

        return [
            'total_active_projects' => $totalProjects,
            'projects_in_progress' => $projectsInProgress,
            'label' => 'Nombre total de projets actifs',
            'label_in_progress' => 'Nombre de projets ayant un statut "En cours"',
        ];
    }
}
