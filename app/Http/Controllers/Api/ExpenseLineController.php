<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreExpenseLineRequest;
use App\Models\ExpenseReport;
use App\Repositories\ExpenseLineRepository;
use App\Services\ExpenseReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ExpenseLineController extends Controller
{
    protected ExpenseLineRepository $expenseLineRepository;
    protected ExpenseReportService $expenseReportService;

    public function __construct(
        ExpenseLineRepository $expenseLineRepository,
        ExpenseReportService $expenseReportService
    ) {
        $this->expenseLineRepository = $expenseLineRepository;
        $this->expenseReportService = $expenseReportService;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreExpenseLineRequest $request, ExpenseReport $expenseReport): JsonResponse
    {
        try {
            $data = $request->validated();

            // Gérer l'upload du fichier
            if ($request->hasFile('justification')) {
                $file = $request->file('justification');

                Log::info('Processing file upload for new expense line', [
                    'file_name' => $file->getClientOriginalName(),
                    'file_size' => $file->getSize(),
                ]);

                $fileName = time() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('expense-justifications', $fileName, 'public');
                $data['justification_path'] = $filePath;

                Log::info('File uploaded for new expense line', [
                    'file_path' => $filePath
                ]);
            }

            $expenseLine = $this->expenseLineRepository->create(array_merge(
                $data,
                ['expense_report_id' => $expenseReport->id]
            ));


            return response()->json([
                'success' => true,
                'message' => 'Ligne de frais ajoutée avec succès',
                'data' => $expenseLine->load(['departure', 'arrival'])
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error creating expense line', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'ajout de la ligne de frais',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreExpenseLineRequest $request, int $id): JsonResponse
    {
        try {
            $data = $request->validated();

            if ($request->hasFile('justification')) {
                $file = $request->file('justification');

                Log::info('📁 Processing file upload for expense line update', [
                    'line_id' => $id,
                    'file_name' => $file->getClientOriginalName(),
                ]);

                $fileName = time() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('expense-justifications', $fileName, 'public');
                $data['justification_path'] = $filePath;

                Log::info('File uploaded for expense line update', [
                    'file_path' => $filePath
                ]);
            }

            $expenseLine = $this->expenseLineRepository->update($id, $data);



            return response()->json([
                'success' => true,
                'message' => 'Ligne de frais mise à jour avec succès',
                'data' => $expenseLine->load(['departure', 'arrival'])
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ligne de frais non trouvée'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error updating expense line', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de la ligne de frais',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $expenseLine = $this->expenseLineRepository->find($id);
            $expenseReport = $expenseLine->expenseReport;

            $this->expenseLineRepository->delete($id);



            return response()->json([
                'success' => true,
                'message' => 'Ligne de frais supprimée avec succès'
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ligne de frais non trouvée'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de la ligne de frais',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
