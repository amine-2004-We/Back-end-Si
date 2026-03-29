<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ImportGeographicDataRequest;
use App\Services\GeographicDataImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Exception;


class ImportController extends Controller
{
    /**
     * @var GeographicDataImportService
     */
    protected GeographicDataImportService $importService;

    /**
     * ImportController constructor.
     *
     * @param GeographicDataImportService $importService
     */
    public function __construct(GeographicDataImportService $importService)
    {
        $this->importService = $importService;
    }

    /**
     * Handle the geographic data import from an Excel file.
     *
     * @param ImportGeographicDataRequest $request
     * @return JsonResponse
     */
    public function importGeographicData(ImportGeographicDataRequest $request): JsonResponse
    {
        if (!$request->hasFile('file')) {
            return response()->json([
                'message' => 'Aucun fichier n\'a été téléchargé.'
            ], Response::HTTP_BAD_REQUEST);
        }

        $file = $request->file('file');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $filePath = $file->storeAs('imports', $fileName); // stored in storage/app/imports

        try {
            $results = $this->importService->importFromExcel(Storage::path($filePath));
            Storage::delete($filePath);

            return response()->json([
                'message' => 'Importation des données géographiques terminée avec succès.',
                'details' => $results
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            Storage::delete($filePath);

            return response()->json([
                'message' => 'Erreur lors de l\'importation des données géographiques.',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
