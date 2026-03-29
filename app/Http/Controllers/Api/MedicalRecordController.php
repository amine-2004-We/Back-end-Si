<?php

namespace App\Http\Controllers\Api;

use App\Enums\MedicalRecordsTypes;
use App\Enums\ProcessingStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMedicalRecordRequest;
use App\Http\Requests\UpdateMedicalRecordRequest;
use App\Http\Resources\MedicalRecordResource;
use App\Models\MedicalRecord;
use App\Services\MedicalRecordService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MedicalRecordController extends Controller
{
    protected MedicalRecordService $medicalRecordService;

    public function __construct(MedicalRecordService $medicalRecordService)
    {
        $this->medicalRecordService = $medicalRecordService;
        $this->authorizeResource(MedicalRecord::class, 'medicalRecord');
    }

    /**
     * Display a listing of the medical records with filters & pagination.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $records = $this->medicalRecordService->getAll($request);
            return response()->json($records, Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'message' => 'Erreur survenue dans le serveur.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Return a summary list (id + key fields).
     */
    public function summary(): JsonResponse
    {
        try {
            $records = $this->medicalRecordService->allSummary();
            return response()->json($records, Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified medical record.
     */
    public function show(MedicalRecord $medicalRecord): JsonResponse
    {
        try {
            $record = $this->medicalRecordService->find($medicalRecord->id);
            return response()->json(new MedicalRecordResource($record), Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la récupération du dossier médical : ' . $e->getMessage()
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Store a newly created medical record.
     */
    public function store(StoreMedicalRecordRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $extension = $file->getClientOriginalExtension();
                $filename = $originalName . '_' . time() . '.' . $extension;
                $path = $file->storeAs('attachments_medical_records', $filename, 'public');
                $validated['attachment'] = $path;
            }

            $record = $this->medicalRecordService->create($validated);
            return response()->json(new MedicalRecordResource($record), Response::HTTP_CREATED);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la création du dossier médical : ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified medical record.
     */
    public function update(UpdateMedicalRecordRequest $request, MedicalRecord $medicalRecord): JsonResponse
    {
        try {
            $validated = $request->validated();

            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $extension = $file->getClientOriginalExtension();
                $filename = $originalName . '_' . time() . '.' . $extension;
                $path = $file->storeAs('attachments_medical_records', $filename, 'public');
                $validated['attachment'] = $path;
            }

            $record = $this->medicalRecordService->update($medicalRecord->id, $validated);
            return response()->json(new MedicalRecordResource($record), Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la mise à jour du dossier médical : ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified medical record.
     */
    public function destroy(MedicalRecord $medicalRecord): JsonResponse
    {
        try {
            $this->medicalRecordService->delete($medicalRecord->id);
            return response()->json([
                'message' => 'Le dossier médical a été supprimé avec succès.'
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la suppression du dossier médical : ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Bulk delete medical records.
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'integer|exists:medical_records,id',
            ]);

            $this->medicalRecordService->bulkDelete($validated['ids']);

            return response()->json([
                'message' => 'Suppression effectuée avec succès.'
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la suppression des dossiers médicaux : ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Restore a soft-deleted medical record.
     */
    public function restore(int $id): JsonResponse
    {
        try {
            $record = $this->medicalRecordService->restore($id);
            return response()->json(new MedicalRecordResource($record), Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la restauration du dossier médical : ' . $e->getMessage()
            ], Response::HTTP_CONFLICT);
        }
    }
    public function enums(): JsonResponse
    {
        return response()->json([
            'medical_records_types' => MedicalRecordsTypes::options(),
            'processing_status' => ProcessingStatus::options(),
        ]);
    }
}
