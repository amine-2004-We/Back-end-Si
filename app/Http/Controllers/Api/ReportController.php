<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreReportRequest;
use App\Http\Resources\ReportResource;
use App\Services\ReportService;
use Illuminate\Http\Request;
use App\Models\Report;
use Illuminate\Contracts\Cache\Store;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateReportRequest;
use Illuminate\Support\Facades\Storage;

class ReportController extends Controller
{
    //

    public function __construct(protected ReportService $reportService)
    {
        //
        $this->reportService = $reportService;
        //apply policies
      
       

    }

    public function index(Request $request)
    {
        $filters = $request->all();
        try {
            $reports = $this->reportService->getFilteredReports($filters);
            return response()->json([
                'data' => ReportResource::collection($reports),
                'pagination' => [
                    'total' => $reports->total(),
                    'count' => $reports->count(),
                    'per_page' => $reports->perPage(),
                    'current_page' => $reports->currentPage(),
                ]
            ], Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            Log::error('Reports not found: ' . $e->getMessage());
            return response()->json(['message' => 'Reports not found'], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            Log::error('Error fetching reports: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred while fetching reports'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }
    public function store(StoreReportRequest $request)
    {
        try {
            $data = $request->validated();
            
            $report = $this->reportService->create($data);
            return response()->json(new ReportResource($report), Response::HTTP_CREATED);
        } catch (HttpException $e) {
            Log::error('Error creating report: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred while creating the report'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function show(Report $report)
    {
        try {
            $report = $this->reportService->show($report->id);
            return response()->json(new ReportResource($report), Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            Log::error('Report not found: ' . $e->getMessage());
            return response()->json(['message' => 'Report not found'], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            Log::error('Error fetching report: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred while fetching the report'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
public function update(UpdateReportRequest $request,Report $report)
{
    try {
        
        $this->authorize('update', $report);
        $data = $request->validated();
        if ($request->hasFile('attachment_path')) {
            $data['attachment_path'] = $request->file('attachment_path');
        }
        $report = $this->reportService->update($report->id, $data);
        return response()->json(new ReportResource($report));
    } catch (ModelNotFoundException $e) {
        return response()->json(['error' => 'Report not found'], 404);
    }
}
    public function destroy(Report $report)
    {
        try {
            $this->reportService->delete($report->id);
            return response()->json(['message' => 'Report deleted successfully'], Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            Log::error('Report not found: ' . $e->getMessage());
            return response()->json(['message' => 'Report not found'], Response::HTTP_NOT_FOUND);
        } catch (HttpException $e) {
            Log::error('Error deleting report: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred while deleting the report'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        try {
            $deletedCount = $this->reportService->bulkDelete($ids);
            return response()->json(['message' => "$deletedCount reports deleted successfully"], Response::HTTP_OK);
        } catch (HttpException $e) {
            Log::error('Error bulk deleting reports: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred while deleting the reports'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function restore(Report $report)
    {
        try {
            $this->reportService->restore($report->id);
            return response()->json(['message' => 'Report restored successfully'], Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            Log::error('Report not found: ' . $e->getMessage());
            return response()->json(['message' => 'Report not found'], Response::HTTP_NOT_FOUND);
        } catch (HttpException $e) {
            Log::error('Error restoring report: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred while restoring the report'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

     /**
     * Downloads a file from the server's storage directory.
     * 
     *
     * @param  string  $fileName
     * @return \Symfony\Component\HttpFoundation\StreamedResponse|\Illuminate\Http\JsonResponse
     */
    public function downloadFile(string $fileName)
    {
        $decodedFileName = urldecode($fileName);
        $sanitizedFileName = basename($decodedFileName);

        $filePath = 'attachment_path/' . $sanitizedFileName;
        Log::info('Attempting to download file from path: ' . Storage::disk('public')->path($filePath));

        // Check if the file exists on the public disk.
        if (!Storage::disk('public')->exists($filePath)) {
            return response()->json(['error' => 'File not found.'], 404);
        }

        return Storage::disk('public')->download($filePath, $sanitizedFileName);
    }

}
