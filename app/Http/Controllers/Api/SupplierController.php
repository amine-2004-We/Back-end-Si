<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;
use App\Http\Resources\SupplierResource;
use App\Services\SupplierService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Exception;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SupplierController extends Controller
{
    /**
     * The supplier service instance.
     * @var SupplierService
     */
    protected SupplierService $supplierService;

    /**
     * Create a new controller instance.
     *
     * @param SupplierService $supplierService
     */
    public function __construct(SupplierService $supplierService)
    {
        $this->supplierService = $supplierService;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Get all parameters from the request URL for filtering/pagination.
            $params = $request->all();

            // Delegate to the service to get a paginated list of suppliers.
            $suppliers = $this->supplierService->getFilteredSuppliers($params);

            // Return the data as a JSON response with pagination metadata.
            return response()->json([
                'data' => SupplierResource::collection($suppliers),
                'pagination' => [
                    'total' => $suppliers->total(),
                    'count' => $suppliers->count(),
                    'per_page' => $suppliers->perPage(),
                    'current_page' => $suppliers->currentPage(),
                    'total_pages' => $suppliers->lastPage(),
                ]
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse|Response
     */
    public function show(int $id): JsonResponse|Response
    {
        try {
            // Delegate to the service to find the supplier, including soft-deleted ones.
            $supplier = $this->supplierService->show($id);

            // If the supplier is not found, return a 404 response.
            if (!$supplier) {
                return response()->json(['message' => 'Supplier not found'], Response::HTTP_NOT_FOUND);
            }

            // Return the supplier data using a resource transformer.
            return response()->json(new SupplierResource($supplier));
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Supplier not found: ' . $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreSupplierRequest $request
     * @return JsonResponse
     */
    public function store(StoreSupplierRequest $request): JsonResponse
    {
        try {
            $supplier = $this->supplierService->create($request->validated());
            return response()->json(new SupplierResource($supplier), Response::HTTP_CREATED);
        } catch (QueryException $ex) {
            if ($ex->errorInfo[1] === 1062) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'The supplier name already exists.'
                ], Response::HTTP_CONFLICT);
            }
            throw $ex;
        } catch (Exception $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateSupplierRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateSupplierRequest $request, int $id): JsonResponse
    {
        try {
            $supplier = $this->supplierService->update($id, $request->validated());

            return response()->json([
                'message' => 'Supplier updated successfully.',
                'data' => new SupplierResource($supplier)
            ], Response::HTTP_OK);
        } catch (ConflictHttpException $exception) {
            return response()->json(['message' => $exception->getMessage()], $exception->getStatusCode());
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Supplier not found: ' . $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {

            $deleted = $this->supplierService->delete($id);
            return response()->json(['message' => 'Supplier deleted successfully'], $deleted ? Response::HTTP_NO_CONTENT : Response::HTTP_BAD_REQUEST);
        } catch (ConflictHttpException $exception) {
            return response()->json(['message' => $exception->getMessage()], $exception->getStatusCode());
        } catch (Exception $exception) {
            return response()->json(['error' => 'Server error: ' . $exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove multiple resources from storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);

            if (empty($ids)) {
                return response()->json(['message' => 'No suppliers selected for deletion.'], Response::HTTP_BAD_REQUEST);
            }
            $deleted = $this->supplierService->bulkDelete($ids);

            return response()->json(['message' => "$deleted supplier(s) deleted successfully."], Response::HTTP_OK);
        } catch (ConflictHttpException $exception) {
            return response()->json(['message' => $exception->getMessage()], $exception->getStatusCode());
        } catch (Exception $exception) {
            return response()->json(['error' => 'Server error: ' . $exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Restore a soft-deleted supplier by its ID.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function restore(int $id): JsonResponse
    {
        try {
            $supplier = $this->supplierService->restore($id);
            return response()->json([
                'message' => 'Supplier restored successfully.',
                'data' => new SupplierResource($supplier)
            ], Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Supplier not found: ' . $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    /**
     * Download a specific document for a supplier.
     *
     * @param string $fileName
     * @return StreamedResponse|JsonResponse
     */
    public function downloadDocument(string $fileName): StreamedResponse|JsonResponse
    {
        try {
            return $this->supplierService->downloadDocument($fileName);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'File not found.'], Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
