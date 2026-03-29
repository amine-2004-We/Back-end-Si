<?php

namespace App\Http\Controllers\Api;

use App\Enums\InsuranceEnum;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\InsuranceService;
use Illuminate\Http\Response;
use App\Http\Resources\InsuranceResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;

use Illuminate\Support\Facades\Log;
use App\Http\Requests\StoreInsuranceRequest;
use App\Http\Requests\UpdateInsuranceRequest;
use App\Models\Insurance;

class InsuranceController extends Controller
{
    private $service;

    public function __construct(InsuranceService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->all();
        try {
            $insurances = $this->service->getFiltered($filters);
            return response()->json([
                'data' => InsuranceResource::collection($insurances),
                'pagination' => [
                    'total' => $insurances->total(),
                    'count' => $insurances->count(),
                    'per_page' => $insurances->perPage(),
                    'current_page' => $insurances->currentPage(),
                ]
            ], Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            Log::error('Insurances not found: ' . $e->getMessage());
            return response()->json(['message' => 'Insurances not found'], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            Log::error('Error fetching insurances: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred while fetching insurances'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreInsuranceRequest $request)
    {
        try {
            $data = $request->validated();
            $insurance = $this->service->create($data);
            return new InsuranceResource($insurance);
        } catch (\Exception $e) {
            Log::error('Error creating insurance: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred while creating the insurance'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    /**
     * Display the specified resource.
     */
    public function show(Insurance $insurance)
    {
        try {
            $insurance = $this->service->find($insurance->id);
            return new InsuranceResource($insurance);
        } catch (ModelNotFoundException $e) {
            Log::error('Insurance not found: ' . $e->getMessage());
            return response()->json(['message' => 'Insurance not found'], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            Log::error('Error fetching insurance: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred while fetching the insurance'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateInsuranceRequest $request, Insurance $insurance)
    {
        try {
            $data = $request->validated();
            $insurance = $this->service->update($insurance->id, $data);
            return new InsuranceResource($insurance);
        } catch (ModelNotFoundException $e) {
            Log::error('Insurance not found: ' . $e->getMessage());
            return response()->json(['message' => 'Insurance not found'], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            Log::error('Error updating insurance: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred while updating the insurance'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Insurance $insurance)
    {
        try {
            $this->service->delete($insurance->id);
            return response()->json(['message' => 'Insurance deleted successfully'], Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            Log::error('Insurance not found: ' . $e->getMessage());
            return response()->json(['message' => 'Insurance not found'], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            Log::error('Error deleting insurance: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred while deleting the insurance'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    /**
     * Restore a soft-deleted resource.
     * @param int $id
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \Exception
     */
    public function restore(Insurance $insurance)
    {
        try {
            $insurance = $this->service->restore($insurance->id);
            return new InsuranceResource($insurance);
        } catch (ModelNotFoundException $e) {
            Log::error('Insurance not found or already active: ' . $e->getMessage());
            return response()->json(['message' => 'Insurance not found or already active'], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            Log::error('Error restoring insurance: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred while restoring the insurance'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    /**
     * Bulk delete resources.
     */
    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        try {
            $deletedCount = $this->service->bulkDelete($ids);
            return response()->json(['message' => "$deletedCount insurances deleted successfully"], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('Error bulk deleting insurances: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred while deleting the insurances'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    //enum list of insurance types
    public function getInsuranceTypes()
    {
        return response()->json([
            'insurance_types' => InsuranceEnum::values()
        ], Response::HTTP_OK);
    }


}
