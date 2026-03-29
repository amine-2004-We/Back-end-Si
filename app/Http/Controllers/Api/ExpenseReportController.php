<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreExpenseReportRequest;
use App\Http\Requests\UpdateExpenseReportRequest;
use App\Services\ExpenseReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;

use App\Models\MissionOrder;
use App\Models\Project;
use App\Models\Collaborator;
use App\Models\Province;
use App\Models\Advance;

class ExpenseReportController extends Controller
{
    protected ExpenseReportService $expenseReportService;

    public function __construct(ExpenseReportService $expenseReportService)
    {
        $this->expenseReportService = $expenseReportService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 10);

            $filters = [
                'search' => $request->get('search'),
                'status' => $request->get('status'),
                'project_id' => $request->get('project_id'),
                'created_by_id' => $request->get('collaborator_id'),
                'date_from' => $request->get('date_from'),
                'date_to' => $request->get('date_to'),
                'amount_min' => $request->get('amount_min'),
                'amount_max' => $request->get('amount_max'),
            ];

            // Remove null/empty values
            $filters = array_filter($filters, function($value) {
                return $value !== null && $value !== '';
            });

            $expenseReports = $this->expenseReportService->repository->all($perPage, $filters);

            return response()->json([
                'success' => true,
                'data' => $expenseReports
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching expense reports: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des notes de frais',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
        public function store(StoreExpenseReportRequest $request): JsonResponse
        {
            try {
                Log::info('Creating expense report with files', [
                    'has_files' => !empty($request->files->all()),
                    'expense_lines_count' => count($request->input('expense_lines', [])),
                ]);

                $reportData = [
                    'project_id' => $request->project_id,
                    'mission_order_id' => $request->mission_order_id,
                    'budget_line_id' => $request->budget_line_id,
                    'status' => $request->status ?? \App\Models\ExpenseReport::STATUS_CREATED,
                    'total_amount' => $request->total_amount,
                ];

                $expenseLinesData = [];
                foreach ($request->input('expense_lines', []) as $index => $lineData) {
                    $lineWithFile = $lineData;

                    if ($request->hasFile("expense_lines.{$index}.justification")) {
                        $file = $request->file("expense_lines.{$index}.justification");

                        Log::info('Processing file upload', [
                            'line_index' => $index,
                            'file_name' => $file->getClientOriginalName(),
                            'file_size' => $file->getSize(),
                        ]);

                        $fileName = time() . '_' . $file->getClientOriginalName();
                        $filePath = $file->storeAs('expense-justifications', $fileName, 'public');

                        $lineWithFile['justification_path'] = $filePath;

                        Log::info('File uploaded successfully', [
                            'file_path' => $filePath,
                            'storage_path' => storage_path('app/public/' . $filePath)
                        ]);
                    }

                    $expenseLinesData[] = $lineWithFile;
                }

                $expenseReport = $this->expenseReportService->createWithLines(
                    $reportData,
                    $expenseLinesData
                );

                return response()->json([
                    'success' => true,
                    'message' => 'Note de frais créée avec succès',
                    'data' => $expenseReport->load(['project', 'createdBy', 'expenseLines'])
                ], 201);

            } catch (\Exception $e) {
                Log::error('Error creating expense report', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la création de la note de frais',
                    'error' => $e->getMessage()
                ], 500);
            }
        }

    /**
     * Display the specified resource.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $expenseReport = $this->expenseReportService->repository->find($id);

            return response()->json([
                'success' => true,
                'data' => $expenseReport->load([
                    'project',
                    'createdBy',
                    'missionOrder',
                    'advance',
                    'expenseLines.departure',
                    'expenseLines.arrival'
                ])
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Note de frais non trouvée'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération de la note de frais',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(UpdateExpenseReportRequest $request, int $id): JsonResponse
    {
        try {
            $expenseReport = $this->expenseReportService->repository->update($id, $request->validated());

            if ($request->has('expense_lines')) {
                foreach ($request->input('expense_lines', []) as $lineData) {
                    if (isset($lineData['id'])) {
                        $line = \App\Models\ExpenseLine::find($lineData['id']);

                        if ($line) {
                            $line->update([
                                'amount_manager' => $lineData['manager_amount'] ?? $line->amount_manager,
                                'amount_finance' => $lineData['finance_amount'] ?? $line->amount_finance,
                                'amount'      => $lineData['amount'] ?? $line->amount,
                                'designation' => $lineData['designation'] ?? $line->designation,
                            ]);
                        }
                    }
                }

            }


            return response()->json([
                'success' => true,
                'message' => 'Note de frais mise à jour avec succès',
                'data' => $expenseReport->load(['project', 'createdBy', 'expenseLines'])
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Note de frais non trouvée'], 404);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erreur lors de la mise à jour', 'error' => $e->getMessage()], 500);
        }
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->expenseReportService->repository->delete($id);

            return response()->json([
                'success' => true,
                'message' => 'Note de frais supprimée avec succès'
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Note de frais non trouvée'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de la note de frais',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Soumettre une note de frais pour validation
     */
    public function submit(int $id): JsonResponse
    {
        try {
            $expenseReport = $this->expenseReportService->repository->find($id);

            if ($expenseReport->status !== \App\Models\ExpenseReport::STATUS_CREATED) {
                return response()->json([
                    'success' => false,
                    'message' => 'La note de frais a déjà été soumise'
                ], 400);
            }

            $this->expenseReportService->changeStatus($expenseReport, \App\Models\ExpenseReport::STATUS_SUBMITTED);

            return response()->json([
                'success' => true,
                'message' => 'Note de frais soumise avec succès',
                'data' => $expenseReport
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Note de frais non trouvée'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la soumission de la note de frais',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Valider par le manager (N+1)
     * Accepts optional validated amounts per line
     * Body: { "expense_lines": [{ "id": 1, "amount_manager": 150 }, ...] }
     */
    public function validateManager(Request $request, int $id): JsonResponse
    {
        try {
            $expenseReport = $this->expenseReportService->repository->find($id);

            if ($expenseReport->status !== \App\Models\ExpenseReport::STATUS_SUBMITTED) {
                return response()->json([
                    'success' => false,
                    'message' => 'La note de frais doit être soumise avant validation manager'
                ], 400);
            }
            
            // Update amount_manager for each line
            // If no specific amount is provided, copy the original amount
            $expenseReport->load('expenseLines');
            $validatedAmounts = collect($request->input('expense_lines', []))->keyBy('id');
            
            foreach ($expenseReport->expenseLines as $line) {
                $managerAmount = $validatedAmounts->has($line->id) 
                    ? $validatedAmounts[$line->id]['amount_manager'] ?? $line->amount
                    : $line->amount;
                    
                $line->update(['amount_manager' => $managerAmount]);
            }

            $this->expenseReportService->changeStatus($expenseReport, \App\Models\ExpenseReport::STATUS_VALIDATED_MANAGER);

            return response()->json([
                'success' => true,
                'message' => 'Note de frais validée par le manager',
                'data' => $expenseReport->load(['project', 'createdBy', 'expenseLines'])
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Note de frais non trouvée'], 404);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erreur lors de la validation', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Valider par la trésorerie
     */
    public function validateTreasury(int $id): JsonResponse
    {
        try {
            $expenseReport = $this->expenseReportService->repository->find($id);

            if ($expenseReport->status !== \App\Models\ExpenseReport::STATUS_VALIDATED_MANAGER) {
                return response()->json([
                    'success' => false,
                    'message' => 'La note de frais doit être validée par le manager avant validation trésorerie'
                ], 400);
            }

            // TODO: Ajouter vérification rôle Trésorerie si nécessaire

            $this->expenseReportService->changeStatus($expenseReport, \App\Models\ExpenseReport::STATUS_VALIDATED_TREASURY);

            return response()->json([
                'success' => true,
                'message' => 'Note de frais validée par la trésorerie',
                'data' => $expenseReport
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Note de frais non trouvée'], 404);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erreur lors de la validation', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Valider par la comptabilité
     */
    public function validateAccounting(int $id): JsonResponse
    {
        try {
            $expenseReport = $this->expenseReportService->repository->find($id);

            if ($expenseReport->status !== \App\Models\ExpenseReport::STATUS_VALIDATED_TREASURY) {
                return response()->json([
                    'success' => false,
                    'message' => 'La note de frais doit être validée par la trésorerie avant validation comptabilité'
                ], 400);
            }

            // TODO: Ajouter vérification rôle Comptabilité si nécessaire

            $this->expenseReportService->changeStatus($expenseReport, \App\Models\ExpenseReport::STATUS_VALIDATED_ACCOUNTING);

            return response()->json([
                'success' => true,
                'message' => 'Note de frais validée par la comptabilité',
                'data' => $expenseReport
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Note de frais non trouvée'], 404);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erreur lors de la validation', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Rejeter une note de frais
     */
    public function reject(int $id, Request $request): JsonResponse
    {
        try {
            $expenseReport = $this->expenseReportService->repository->find($id);

            // On peut rejeter à n'importe quelle étape sauf si déjà rejetée ou validée finale ? 
            // Pour l'instant on permet le rejet si pas encore validée compta (ou même si validée compta ?)
            // Supposons qu'on peut rejeter tant que ce n'est pas payé/clôturé. 
            // Ici le dernier statut est STATUS_VALIDATED_ACCOUNTING.

            $this->expenseReportService->changeStatus($expenseReport, \App\Models\ExpenseReport::STATUS_REJECTED);

            // On pourrait ajouter un commentaire de rejet ici si la DB le permet

            return response()->json([
                'success' => true,
                'message' => 'Note de frais rejetée',
                'data' => $expenseReport
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Note de frais non trouvée'], 404);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erreur lors du rejet', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Bulk delete expense reports
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'integer|exists:expense_reports,id'
            ]);

            $this->expenseReportService->repository->bulkDelete($request->ids);

            return response()->json([
                'success' => true,
                'message' => 'Notes de frais supprimées avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression des notes de frais',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restore a soft-deleted expense report
     */
    public function restore(int $id): JsonResponse
    {
        try {
            $expenseReport = $this->expenseReportService->repository->restore($id);

            return response()->json([
                'success' => true,
                'message' => 'Note de frais restaurée avec succès',
                'data' => $expenseReport
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Note de frais non trouvée'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la restauration de la note de frais',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get options for expense reports (projects, collaborators, types, etc.)
     */
    public function options(): JsonResponse
    {
        try {
            $options = [
                'statuses' => [
                    'created' => 'Créée',
                    'submitted' => 'Soumise',
                    'validated_manager' => 'Validée Manager',
                    'validated_treasury' => 'Validée Trésorerie',
                    'validated_accounting' => 'Validée Comptabilité',
                    'rejected' => 'Rejetée'
                ],
                'types' => [
                    'restauration' => 'Restauration',
                    'deplacement' => 'Déplacement',
                    'hebergement' => 'Hébergement',
                    'transport' => 'Transport',
                    'autre' => 'Autre'
                ],
                'transportModes' => [
                    'train' => 'Train',
                    'taxi' => 'Taxi',
                    'avion' => 'Avion',
                    'voiture' => 'Voiture',
                    'bus' => 'Bus'
                ]
            ];

            if (class_exists(Project::class)) {
                try {
                    $options['projects'] = Project::with('missionOrders','budgetLines')->get(['id', 'project_name']);
                } catch (\Exception $e) {
                    $options['projects'] = [];
                    Log::warning('Failed to fetch projects: ' . $e->getMessage());
                }
            }

            if (class_exists(Collaborator::class)) {
                try {
                    $options['collaborators'] = Collaborator::select('id', 'first_name', 'last_name')
                        ->get()
                        ->map(function ($collaborator) {
                            return [
                                'id' => $collaborator->id,
                                'name' => $collaborator->first_name . ' ' . $collaborator->last_name
                            ];
                        })->toArray();
                } catch (\Exception $e) {
                    $options['collaborators'] = [];
                    Log::warning('Failed to fetch collaborators: ' . $e->getMessage());
                }
            }

            if (class_exists(MissionOrder::class)) {
                try {
                    $options['mission_orders'] = MissionOrder::select('id', 'mission_order_code as code')->get()->toArray();
                } catch (\Exception $e) {
                    $options['mission_orders'] = [];
                    Log::warning('Failed to fetch mission orders: ' . $e->getMessage());
                }
            }

            if (class_exists(Province::class)) {
                try {
                    $options['provinces'] = Province::select('id', 'name')->get()->toArray();
                } catch (\Exception $e) {
                    $options['provinces'] = [];
                    Log::warning('Failed to fetch provinces: ' . $e->getMessage());
                }
            }

            $options['advances'] = [];

            return response()->json([
                'success' => true,
                'data' => $options
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des options',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get advances for a specific project
     */
    public function getAdvancesByProject(int $projectId): JsonResponse
    {
        try {
            $advances = Advance::where('project_id', $projectId)
                ->select('id', 'advance_code as reference', 'advance_amount')
                ->get()
                ->map(function ($advance) {
                    return [
                        'id' => $advance->id,
                        'reference' => $advance->reference,
                        'amount' => $advance->advance_amount,
                        'label' => $advance->reference . ' - ' . number_format($advance->advance_amount, 2) . ' MAD'
                    ];
                })->toArray();

            return response()->json([
                'success' => true,
                'data' => $advances
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des avances',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get mission orders for a specific project
     */
    public function getMissionOrdersByProject(int $projectId): JsonResponse
    {
        try {
            $missionOrders = MissionOrder::where('project_id', $projectId)
                ->select('id', 'mission_order_code as code')
                ->get()
                ->toArray();

            return response()->json([
                'success' => true,
                'data' => $missionOrders
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des ordres de mission',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
